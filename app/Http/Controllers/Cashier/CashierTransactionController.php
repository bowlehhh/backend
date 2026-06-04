<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierDraft;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleDeleteLog;
use App\Models\SaleEditLog;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockHistory;
use App\Services\CheckoutService;
use App\Services\SalesReturnService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CashierTransactionController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly SalesReturnService $salesReturnService,
    ) {
    }

    /**
     * Tambahkan 1 qty produk ke keranjang kasir aktif (session-based cart).
     */
    public function add(Request $request, Product $product): RedirectResponse
    {
        $batch = ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->latest('id')
            ->first();

        if (! $batch) {
            return back()->withErrors(['cart' => 'Stok produk habis atau tidak tersedia.']);
        }

        $cart = (array) $request->session()->get('cashier_cart', []);
        $key = (string) $batch->id;
        $existingQty = (int) ($cart[$key]['qty'] ?? 0);
        $nextQty = $existingQty + 1;

        if ($nextQty > (int) $batch->stock) {
            return back()->withErrors(['cart' => "Stok {$product->name} tidak cukup."]);
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => $product->name,
            'price' => (float) $batch->selling_price,
            'qty' => $nextQty,
        ];

        $request->session()->put('cashier_cart', $cart);

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    /**
     * Update qty dan harga jual item di keranjang.
     *
     * Catatan:
     * - Harga boleh diubah kasir per transaksi.
     * - Harga negatif ditolak.
     */
    public function update(Request $request, ProductBatch $batch): RedirectResponse
    {
        $qty = max(0, (int) $request->input('qty', 1));
        $priceInput = $request->input('price');
        $cart = (array) $request->session()->get('cashier_cart', []);
        $key = (string) $batch->id;

        if (! isset($cart[$key])) {
            return back();
        }

        if ($qty === 0) {
            unset($cart[$key]);
            $request->session()->put('cashier_cart', $cart);

            return back();
        }

        if ($qty > (int) $batch->stock) {
            return back()->withErrors(['cart' => "Stok {$cart[$key]['product_name']} tidak cukup."]);
        }

        $price = $priceInput !== null ? (float) $priceInput : (float) ($cart[$key]['price'] ?? $batch->selling_price);
        if ($price < 0) {
            return back()->withErrors(['cart' => 'Harga jual tidak boleh kurang dari 0.']);
        }

        $cart[$key]['qty'] = $qty;
        $cart[$key]['price'] = $price;
        $request->session()->put('cashier_cart', $cart);

        return back();
    }

    public function remove(Request $request, ProductBatch $batch): RedirectResponse
    {
        $cart = (array) $request->session()->get('cashier_cart', []);
        unset($cart[(string) $batch->id]);
        $request->session()->put('cashier_cart', $cart);

        return back();
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cashier_cart');

        return back()->with('success', 'Keranjang dikosongkan.');
    }

    /**
     * Pindahkan seluruh keranjang aktif ke draft database.
     *
     * Kenapa DB draft:
     * - Bisa lebih dari satu draft.
     * - Tidak hilang saat session browser berubah.
     */
    public function hold(Request $request): RedirectResponse
    {
        $cart = (array) $request->session()->get('cashier_cart', []);

        if ($cart === []) {
            return back()->withErrors(['cart' => 'Keranjang masih kosong, tidak ada yang bisa ditunda.']);
        }

        $items = collect($cart)->values()->all();
        $itemsCount = collect($items)->sum(fn (array $item): int => (int) ($item['qty'] ?? 0));
        $total = (float) collect($items)->sum(
            fn (array $item): float => ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0))
        );

        CashierDraft::query()->create([
            'user_id' => $request->user()->id,
            'title' => 'Draft ' . now()->format('d/m H:i'),
            'items' => $items,
            'items_count' => $itemsCount,
            'total' => $total,
        ]);

        $request->session()->forget('cashier_cart');

        return redirect()->route('cashier.drafts')->with('success', 'Transaksi berhasil dipindahkan ke draft.');
    }

    public function drafts(Request $request): View
    {
        $drafts = CashierDraft::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(15);

        return view('cashier.drafts', [
            'user' => $request->user(),
            'drafts' => $drafts,
        ]);
    }

    /**
     * Muat draft ke keranjang aktif dan hapus draft yang sudah dipakai.
     */
    public function resume(Request $request, CashierDraft $draft): RedirectResponse
    {
        if ((int) $draft->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak mengakses draft ini.');
        }

        $activeCart = (array) $request->session()->get('cashier_cart', []);
        if ($activeCart !== []) {
            return back()->withErrors(['cart' => 'Selesaikan atau kosongkan keranjang aktif sebelum memuat draft.']);
        }

        $items = collect((array) $draft->items)
            ->keyBy(fn (array $item): string => (string) ($item['product_batch_id'] ?? uniqid('draft_', true)))
            ->all();

        $request->session()->put('cashier_cart', $items);
        $draft->delete();

        return redirect()->route('cashier.dashboard')->with('success', 'Draft transaksi berhasil dimuat ke keranjang.');
    }

    public function destroyDraft(Request $request, CashierDraft $draft): RedirectResponse
    {
        if ((int) $draft->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak menghapus draft ini.');
        }

        $draft->delete();

        return back()->with('success', 'Draft berhasil dihapus.');
    }

    /**
     * Proses checkout transaksi kasir.
     *
     * Mengirim payload final ke CheckoutService supaya seluruh perubahan data
     * (sale, sale_items, stock_history) tetap terpusat dalam satu tempat.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $cart = collect((array) $request->session()->get('cashier_cart', []))->values();

        if ($cart->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $paymentMethod = (string) $request->input('payment_method', 'cash');
        $paidAmount = (float) $request->input('paid_amount', 0);
        $creditDueDate = trim((string) $request->input('credit_due_date', ''));
        $customerName = trim((string) $request->input('customer_name', ''));
        $customerPhone = trim((string) $request->input('customer_phone', ''));
        $cashierServiceName = trim((string) $request->input('cashier_service_name', ''));
        $cashierPhone = trim((string) $request->input('cashier_phone', ''));
        $shouldPrintReceipt = $request->boolean('print_receipt');

        try {
            $sale = $this->checkoutService->checkout($request->user(), [
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount,
                'credit_due_date' => $creditDueDate !== '' ? $creditDueDate : null,
                'customer_name' => $customerName !== '' ? mb_substr($customerName, 0, 100) : null,
                'customer_phone' => $customerPhone !== '' ? mb_substr($customerPhone, 0, 30) : null,
                'cashier_service_name' => $cashierServiceName !== '' ? mb_substr($cashierServiceName, 0, 100) : null,
                'cashier_phone' => $cashierPhone !== '' ? mb_substr($cashierPhone, 0, 30) : null,
                'items' => $cart->map(fn (array $item): array => [
                    'product_id' => (int) $item['product_id'],
                    'product_batch_id' => (int) $item['product_batch_id'],
                    'qty' => (int) $item['qty'],
                    'price' => (float) ($item['price'] ?? 0),
                ])->all(),
            ]);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        $request->session()->forget('cashier_cart');

        return redirect()
            ->route('cashier.receipt', array_filter([
                'sale' => $sale,
                'pdf' => $shouldPrintReceipt ? 1 : null,
            ]))
            ->with('success', "Transaksi berhasil. Invoice: {$sale->invoice_number}");
    }

    public function history(Request $request): View
    {
        $user = $request->user();

        $sales = Sale::query()
            ->withCount('items')
            ->withSum('items as sold_qty', 'qty')
            ->withSum('returnedItems as returned_qty', 'qty_return')
            ->withSum('returns as total_return_refund', 'refund_amount')
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        $returns = SalesReturn::query()
            ->with(['user:id,name', 'items'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(20)
            ->get();

        $editLogs = SaleEditLog::query()
            ->with('editor:id,name')
            ->where('edited_by_user_id', $user->id)
            ->latest('id')
            ->limit(20)
            ->get();

        $deleteLogs = SaleDeleteLog::query()
            ->with('deleter:id,name')
            ->where('deleted_by_user_id', $user->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return view('cashier.history', [
            'user' => $user,
            'sales' => $sales,
            'returns' => $returns,
            'editLogs' => $editLogs,
            'deleteLogs' => $deleteLogs,
        ]);
    }

    public function historyBySupplier(Request $request): View
    {
        $user = $request->user();

        $rows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('product_batches', 'product_batches.id', '=', 'sale_items.product_batch_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'product_batches.supplier_id')
            ->where('sales.user_id', $user->id)
            ->whereNull('sales.deleted_at')
            ->selectRaw('
                COALESCE(suppliers.id, 0) as supplier_id,
                COALESCE(suppliers.name, "Tanpa Supplier") as supplier_name,
                sales.id as sale_id,
                sales.invoice_number,
                sales.payment_method,
                sales.total as sale_total,
                sales.credit_amount,
                sales.credit_due_date,
                sales.created_at,
                SUM(sale_items.qty) as qty,
                SUM(sale_items.subtotal) as subtotal
            ')
            ->groupBy(
                'suppliers.id',
                'suppliers.name',
                'sales.id',
                'sales.invoice_number',
                'sales.payment_method',
                'sales.total',
                'sales.credit_amount',
                'sales.credit_due_date',
                'sales.created_at'
            )
            ->orderByDesc('sales.id')
            ->get();

        $now = Carbon::now();
        $grouped = $rows
            ->groupBy('supplier_id')
            ->map(function ($items) use ($now) {
                $supplierName = (string) ($items->first()->supplier_name ?? 'Tanpa Supplier');
                $transactions = $items->map(function ($row) use ($now) {
                    $method = strtoupper((string) $row->payment_method);
                    $creditAmount = (float) ($row->credit_amount ?? 0);
                    $dueDateRaw = $row->credit_due_date;
                    $dueDate = $dueDateRaw ? Carbon::parse($dueDateRaw) : null;

                    $status = 'LUNAS';
                    if ($method === 'CREDIT' && $creditAmount > 0) {
                        $status = ($dueDate && $dueDate->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS';
                    }

                    return [
                        'sale_id' => (int) $row->sale_id,
                        'invoice_number' => (string) $row->invoice_number,
                        'waktu' => $row->created_at ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-',
                        'metode' => $method,
                        'qty' => (int) ($row->qty ?? 0),
                        'subtotal' => 'Rp ' . number_format((float) ($row->subtotal ?? 0), 0, ',', '.'),
                        'credit_amount' => 'Rp ' . number_format($creditAmount, 0, ',', '.'),
                        'credit_due_date' => $dueDate ? $dueDate->format('d M Y') : '-',
                        'status' => $status,
                    ];
                })->values();

                return [
                    'supplier_id' => (int) ($items->first()->supplier_id ?? 0),
                    'supplier_name' => $supplierName,
                    'summary' => [
                        'total_transaksi' => $transactions->count(),
                        'total_qty' => $transactions->sum('qty'),
                        'total_nilai' => 'Rp ' . number_format((float) $items->sum('subtotal'), 0, ',', '.'),
                        'kredit' => $transactions->whereIn('status', ['BELUM LUNAS', 'JATUH TEMPO'])->count(),
                        'lunas' => $transactions->where('status', 'LUNAS')->count(),
                    ],
                    'transactions' => $transactions,
                ];
            })
            ->values();

        return view('cashier.history-supplier', [
            'user' => $user,
            'groups' => $grouped,
        ]);
    }

    public function editHistory(Request $request, Sale $sale): View
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak mengubah transaksi ini.');
        }

        $sale->loadMissing(['items.productBatch', 'returns']);

        if ($sale->returns->isNotEmpty()) {
            return view('cashier.history-edit', [
                'user' => $request->user(),
                'sale' => $sale,
                'blockedByReturn' => true,
            ]);
        }

        return view('cashier.history-edit', [
            'user' => $request->user(),
            'sale' => $sale,
            'blockedByReturn' => false,
        ]);
    }

    public function updateHistory(Request $request, Sale $sale): RedirectResponse
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak mengubah transaksi ini.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer,qris,debit,credit'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'credit_due_date' => ['nullable', 'date'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'cashier_service_name' => ['nullable', 'string', 'max:100'],
            'cashier_phone' => ['nullable', 'string', 'max:30'],
            'edit_note' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $sale, $validated): void {
                $sale->loadMissing(['items.productBatch', 'returns']);

                if ($sale->returns->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'edit_note' => ['Transaksi yang sudah memiliki retur tidak bisa diedit.'],
                    ]);
                }

                $saleItems = $sale->items->keyBy('id');
                $incomingItems = collect($validated['items']);

                $incomingItemIds = $incomingItems->pluck('id')->map(fn ($id): int => (int) $id)->all();
                $unknownItemIds = array_diff($incomingItemIds, $saleItems->keys()->map(fn ($id): int => (int) $id)->all());
                if ($unknownItemIds !== []) {
                    throw ValidationException::withMessages([
                        'items' => ['Ada item transaksi yang tidak valid untuk invoice ini.'],
                    ]);
                }

                $batches = ProductBatch::query()
                    ->whereIn('id', $saleItems->pluck('product_batch_id')->all())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $oldSnapshot = [
                    'sale' => $sale->only([
                        'customer_name',
                        'customer_phone',
                        'cashier_service_name',
                        'cashier_phone',
                        'payment_method',
                        'paid_amount',
                        'change_amount',
                        'credit_amount',
                        'credit_days',
                        'credit_due_date',
                        'total',
                    ]),
                    'items' => $sale->items->map(fn ($item): array => $item->only([
                        'id',
                        'product_id',
                        'product_batch_id',
                        'product_name',
                        'price',
                        'qty',
                        'subtotal',
                    ]))->values()->all(),
                ];

                $newTotal = 0.0;
                foreach ($incomingItems as $incomingItem) {
                    $item = $saleItems->get((int) $incomingItem['id']);
                    $batch = $batches->get((int) $item->product_batch_id);
                    if (! $batch) {
                        throw ValidationException::withMessages([
                            'items' => ["Batch untuk {$item->product_name} tidak ditemukan."],
                        ]);
                    }

                    $oldQty = (int) $item->qty;
                    $newQty = (int) $incomingItem['qty'];
                    $delta = $newQty - $oldQty;

                    if ($delta > 0) {
                        if ((int) $batch->stock < $delta) {
                            throw ValidationException::withMessages([
                                'items' => ["Stok {$item->product_name} tidak cukup untuk tambah qty."],
                            ]);
                        }

                        $stockBefore = (int) $batch->stock;
                        $stockAfter = $stockBefore - $delta;
                        $batch->update(['stock' => $stockAfter]);

                        StockHistory::create([
                            'product_id' => $item->product_id,
                            'product_batch_id' => $item->product_batch_id,
                            'user_id' => $request->user()->id,
                            'type' => StockHistory::TYPE_OUT,
                            'qty' => $delta,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'reference' => $sale->invoice_number,
                            'description' => 'Penyesuaian qty saat edit transaksi kasir.',
                        ]);
                    } elseif ($delta < 0) {
                        $returnedQty = abs($delta);
                        $stockBefore = (int) $batch->stock;
                        $stockAfter = $stockBefore + $returnedQty;
                        $batch->update(['stock' => $stockAfter]);

                        StockHistory::create([
                            'product_id' => $item->product_id,
                            'product_batch_id' => $item->product_batch_id,
                            'user_id' => $request->user()->id,
                            'type' => StockHistory::TYPE_IN,
                            'qty' => $returnedQty,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'reference' => $sale->invoice_number,
                            'description' => 'Pengembalian stok saat edit transaksi kasir.',
                        ]);
                    }

                    $newPrice = (float) $incomingItem['price'];
                    $newSubtotal = $newPrice * $newQty;
                    $newTotal += $newSubtotal;

                    $item->update([
                        'qty' => $newQty,
                        'price' => $newPrice,
                        'subtotal' => $newSubtotal,
                    ]);
                }

                $paymentMethod = strtolower((string) $validated['payment_method']);
                $paidAmount = (float) ($validated['paid_amount'] ?? 0);
                $creditAmount = 0.0;
                $creditDays = null;
                $creditDueDate = null;
                $changeAmount = 0.0;

                if ($paymentMethod === 'cash') {
                    if ($paidAmount < $newTotal) {
                        throw ValidationException::withMessages([
                            'paid_amount' => ['Jumlah bayar tunai harus >= total transaksi.'],
                        ]);
                    }
                    $changeAmount = max(0, $paidAmount - $newTotal);
                } elseif ($paymentMethod === 'credit') {
                    $paidAmount = min($newTotal, max(0, $paidAmount));
                    $creditAmount = max(0, $newTotal - $paidAmount);

                    if ($creditAmount > 0) {
                        $dueDate = trim((string) ($validated['credit_due_date'] ?? ''));
                        if ($dueDate === '') {
                            throw ValidationException::withMessages([
                                'credit_due_date' => ['Tanggal jatuh tempo wajib diisi untuk transaksi kredit.'],
                            ]);
                        }

                        $creditDueDate = $dueDate;
                        $creditDays = max(
                            0,
                            now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($dueDate)->startOfDay(), false)
                        );
                    }
                } else {
                    $paidAmount = $paidAmount > 0 ? $paidAmount : $newTotal;
                    $changeAmount = max(0, $paidAmount - $newTotal);
                }

                $sale->update([
                    'customer_name' => trim((string) ($validated['customer_name'] ?? '')) ?: null,
                    'customer_phone' => trim((string) ($validated['customer_phone'] ?? '')) ?: null,
                    'cashier_service_name' => trim((string) ($validated['cashier_service_name'] ?? '')) ?: null,
                    'cashier_phone' => trim((string) ($validated['cashier_phone'] ?? '')) ?: null,
                    'payment_method' => $paymentMethod,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'credit_amount' => $creditAmount,
                    'credit_days' => $creditDays,
                    'credit_due_date' => $creditDueDate,
                    'total' => $newTotal,
                ]);

                $sale->refresh()->load('items');

                $newSnapshot = [
                    'sale' => $sale->only([
                        'customer_name',
                        'customer_phone',
                        'cashier_service_name',
                        'cashier_phone',
                        'payment_method',
                        'paid_amount',
                        'change_amount',
                        'credit_amount',
                        'credit_days',
                        'credit_due_date',
                        'total',
                    ]),
                    'items' => $sale->items->map(fn ($item): array => $item->only([
                        'id',
                        'product_id',
                        'product_batch_id',
                        'product_name',
                        'price',
                        'qty',
                        'subtotal',
                    ]))->values()->all(),
                ];

                $changedFields = collect([
                    'customer_name',
                    'customer_phone',
                    'cashier_service_name',
                    'cashier_phone',
                    'payment_method',
                    'paid_amount',
                    'change_amount',
                    'credit_amount',
                    'credit_days',
                    'credit_due_date',
                    'total',
                ])->filter(fn ($field): bool => ($oldSnapshot['sale'][$field] ?? null) != ($newSnapshot['sale'][$field] ?? null));

                SaleEditLog::create([
                    'sale_id' => $sale->id,
                    'edited_by_user_id' => $request->user()->id,
                    'invoice_number' => (string) $sale->invoice_number,
                    'old_data' => $oldSnapshot,
                    'new_data' => $newSnapshot,
                    'changed_fields' => $changedFields->implode(', '),
                    'edit_note' => trim((string) $validated['edit_note']),
                ]);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('cashier.history')
            ->with('success', "Transaksi {$sale->invoice_number} berhasil diperbarui.");
    }

    public function destroyHistory(Request $request, Sale $sale): RedirectResponse
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak menghapus transaksi ini.');
        }

        $validated = $request->validate([
            'delete_note' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $sale, $validated): void {
                $sale->loadMissing(['items.productBatch', 'returns']);

                if ($sale->returns->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'delete_note' => ['Transaksi yang sudah memiliki retur tidak bisa dihapus.'],
                    ]);
                }

                $items = $sale->items;
                $batchIds = $items->pluck('product_batch_id')->all();
                $batches = ProductBatch::query()
                    ->whereIn('id', $batchIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $batch = $batches->get((int) $item->product_batch_id);
                    if (! $batch) {
                        continue;
                    }

                    $stockBefore = (int) $batch->stock;
                    $stockAfter = $stockBefore + (int) $item->qty;
                    $batch->update(['stock' => $stockAfter]);

                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'product_batch_id' => $item->product_batch_id,
                        'user_id' => $request->user()->id,
                        'type' => StockHistory::TYPE_IN,
                        'qty' => (int) $item->qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference' => $sale->invoice_number,
                        'description' => 'Pengembalian stok karena transaksi dihapus.',
                    ]);
                }

                SaleDeleteLog::create([
                    'sale_id' => $sale->id,
                    'invoice_number' => (string) $sale->invoice_number,
                    'deleted_by_user_id' => $request->user()->id,
                    'payment_method' => (string) $sale->payment_method,
                    'total' => (float) $sale->total,
                    'credit_amount' => (float) ($sale->credit_amount ?? 0),
                    'items_count' => (int) $items->count(),
                    'delete_note' => trim((string) $validated['delete_note']),
                    'snapshot' => [
                        'sale' => $sale->toArray(),
                        'items' => $items->map(fn ($item): array => $item->toArray())->all(),
                    ],
                ]);

                $sale->delete();
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('cashier.history')
            ->with('success', "Transaksi {$sale->invoice_number} berhasil dihapus.");
    }

    public function returnForm(Request $request, Sale $sale): View
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak memproses retur transaksi ini.');
        }

        $sale->loadMissing(['items.productBatch', 'user:id,name']);

        $returnedQtyMap = SalesReturnItem::query()
            ->selectRaw('sales_return_items.sale_item_id, SUM(sales_return_items.qty_return) AS total_qty')
            ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
            ->where('sales_returns.sale_id', $sale->id)
            ->groupBy('sales_return_items.sale_item_id')
            ->get()
            ->mapWithKeys(fn ($row): array => [(int) $row->sale_item_id => (int) $row->total_qty]);

        return view('cashier.return-form', [
            'user' => $request->user(),
            'sale' => $sale,
            'returnedQtyMap' => $returnedQtyMap,
        ]);
    }

    public function storeReturn(Request $request, Sale $sale): RedirectResponse
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak memproses retur transaksi ini.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer'],
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $salesReturn = $this->salesReturnService->create($request->user(), $sale, $validated);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('cashier.return.receipt', $salesReturn)
            ->with('success', "Retur berhasil diproses. Nomor retur: {$salesReturn->return_number}");
    }

    public function returnReceipt(Request $request, SalesReturn $salesReturn): View|Response
    {
        $salesReturn->loadMissing(['sale.user:id,name', 'items']);

        if ((int) $salesReturn->sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak melihat nota retur ini.');
        }

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('cashier.return-receipt', [
                'salesReturn' => $salesReturn,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => route('cashier.history'),
                'saleUrl' => route('cashier.receipt', $salesReturn->sale),
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$salesReturn->return_number}.pdf");
        }

        return view('cashier.return-receipt', [
            'salesReturn' => $salesReturn,
            'storeName' => config('app.name', 'Toko Pak Paul'),
            'historyUrl' => route('cashier.history'),
            'saleUrl' => route('cashier.receipt', $salesReturn->sale),
        ]);
    }

    public function receipt(Request $request, Sale $sale): View|Response
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak melihat nota ini.');
        }

        $sale->loadMissing(['items', 'user:id,name', 'returns']);

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('cashier.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => route('cashier.history'),
                'newTransactionUrl' => route('cashier.dashboard'),
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$sale->invoice_number}.pdf");
        }

        return response()
            ->view('cashier.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => route('cashier.history'),
                'newTransactionUrl' => route('cashier.dashboard'),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}

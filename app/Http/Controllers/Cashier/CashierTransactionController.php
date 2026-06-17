<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierDraft;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleDeleteLog;
use App\Models\SaleEditLog;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockHistory;
use App\Services\CheckoutService;
use App\Services\SalesReturnService;
use App\Support\AdminBesarCache;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CashierTransactionController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly SalesReturnService $salesReturnService,
    ) {
    }

    private function canViewAllTransactions(Request $request): bool
    {
        $user = $request->user();

        return (bool) ($user?->isAdmin() || $user?->isAdminBesar());
    }

    private function canAccessSale(Request $request, Sale $sale): bool
    {
        if ($this->canViewAllTransactions($request)) {
            return true;
        }

        return (int) $sale->user_id === (int) $request->user()->id;
    }

    private function canManageAdminToko(Request $request): bool
    {
        $user = $request->user();

        return (bool) ($user?->isAdmin() || $user?->isAdminBesar());
    }

    private function getProductAvailableStock(Product $product): int
    {
        return (int) ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->sum('stock');
    }

    private function getProductPartNumber(Product $product): string
    {
        $barcode = strtoupper(trim((string) ($product->barcode ?? '')));

        return $barcode !== '' ? $barcode : 'PRODUCT-' . $product->id;
    }

    private function getBatchPartNumber(ProductBatch $batch): string
    {
        $batch->loadMissing('product:id,barcode');

        return $this->getProductPartNumber($batch->product);
    }

    private function getBatchStock(ProductBatch $batch): int
    {
        return (int) $batch->stock;
    }

    private function getAvailableStockByPartNumber(string $partNumber): int
    {
        $normalized = strtoupper(trim($partNumber));

        if ($normalized === '') {
            return 0;
        }

        if (str_starts_with($normalized, 'PRODUCT-')) {
            $productId = (int) substr($normalized, 8);

            return (int) ProductBatch::query()
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->sum('stock');
        }

        return (int) ProductBatch::query()
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->whereRaw('UPPER(TRIM(barcode)) = ?', [$normalized]))
            ->sum('stock');
    }

    private function findFirstBatchForPartNumber(string $partNumber): ?ProductBatch
    {
        $normalized = strtoupper(trim($partNumber));

        if ($normalized === '') {
            return null;
        }

        $query = ProductBatch::query()
            ->with('product:id,barcode')
            ->where('is_active', true);

        if (str_starts_with($normalized, 'PRODUCT-')) {
            $query->where('product_id', (int) substr($normalized, 8));
        } else {
            $query->whereHas('product', fn ($q) => $q->whereRaw('UPPER(TRIM(barcode)) = ?', [$normalized]));
        }

        return $query->oldest('id')->first();
    }

    private function findCartItemKeyByPartNumber(array $cart, string $partNumber): ?string
    {
        $normalized = strtoupper(trim($partNumber));

        foreach ($cart as $key => $item) {
            if (strtoupper(trim((string) ($item['part_number'] ?? ''))) === $normalized) {
                return (string) $key;
            }
        }

        return null;
    }

    private function findMergedCartItemKeyByPartNumber(array $cart, string $partNumber): ?string
    {
        $normalized = strtoupper(trim($partNumber));

        foreach ($cart as $key => $item) {
            if (strtoupper(trim((string) ($item['part_number'] ?? ''))) !== $normalized) {
                continue;
            }

            if (! (bool) ($item['merge_stock'] ?? false)) {
                continue;
            }

            return (string) $key;
        }

        return null;
    }

    private function findCartItemKeyByBatchId(array $cart, int $batchId): ?string
    {
        foreach ($cart as $key => $item) {
            if ((int) ($item['product_batch_id'] ?? 0) === $batchId) {
                return (string) $key;
            }
        }

        return null;
    }

    private function normalizeStockAllocations(array $item): array
    {
        $allocations = $item['stock_allocations'] ?? null;
        $normalized = [];

        if (is_array($allocations)) {
            foreach ($allocations as $batchId => $qty) {
                $batchKey = (int) $batchId;
                $batchQty = max(0, (int) $qty);
                if ($batchKey > 0 && $batchQty > 0) {
                    $normalized[$batchKey] = ($normalized[$batchKey] ?? 0) + $batchQty;
                }
            }
        }

        if ($normalized === []) {
            $batchId = (int) ($item['product_batch_id'] ?? 0);
            $qty = max(0, (int) ($item['qty'] ?? 0));
            if ($batchId > 0 && $qty > 0) {
                $normalized[$batchId] = $qty;
            }
        }

        return $normalized;
    }

    private function getCartPartNumberQty(array $cart, string $partNumber, ?string $ignoreKey = null): int
    {
        $total = 0;
        $normalized = strtoupper(trim($partNumber));

        foreach ($cart as $key => $item) {
            if ($ignoreKey !== null && (string) $key === $ignoreKey) {
                continue;
            }

            if (strtoupper(trim((string) ($item['part_number'] ?? ''))) !== $normalized) {
                continue;
            }

            $total += (int) ($item['qty'] ?? 0);
        }

        return $total;
    }

    /**
     * Tambahkan 1 qty produk ke keranjang kasir aktif (session-based cart).
     */
    public function add(Request $request, ProductBatch $batch): RedirectResponse
    {
        $batch->loadMissing('product:id,name,barcode,is_active');
        $product = $batch->product;

        if (! $product || ! $product->is_active) {
            return back()->withErrors(['cart' => 'Produk sudah tidak aktif atau tidak tersedia.']);
        }

        $cart = (array) $request->session()->get('cashier_cart', []);
        $partNumber = $this->getBatchPartNumber($batch);
        $mergedKey = $this->findMergedCartItemKeyByPartNumber($cart, $partNumber);
        $key = (string) $batch->id;
        $existingKey = $this->findCartItemKeyByBatchId($cart, (int) $batch->id);
        if ($mergedKey !== null) {
            $key = $mergedKey;
        } elseif ($existingKey !== null) {
            $key = $existingKey;
        }

        $currentLineQty = (int) ($cart[$key]['qty'] ?? 0);
        $currentSubtotal = (float) ($cart[$key]['merged_subtotal'] ?? ((float) ($cart[$key]['price'] ?? $batch->selling_price) * $currentLineQty));
        $currentAllocations = $this->normalizeStockAllocations($cart[$key] ?? []);
        $nextQty = $currentLineQty + 1;
        $availableStock = $mergedKey !== null
            ? $this->getAvailableStockByPartNumber($partNumber)
            : $this->getBatchStock($batch);
        $nextSubtotal = $mergedKey !== null
            ? $currentSubtotal + (float) $batch->selling_price
            : (float) $batch->selling_price * $nextQty;
        $nextPrice = $nextQty > 0 ? $nextSubtotal / $nextQty : (float) $batch->selling_price;

        if ($mergedKey !== null) {
            $currentAllocations[(int) $batch->id] = ($currentAllocations[(int) $batch->id] ?? 0) + 1;
        } else {
            $currentAllocations = [(int) $batch->id => $nextQty];
        }

        if ($nextQty > $availableStock) {
            return back()->withErrors(['cart' => "Stok {$product->name} tidak cukup."]);
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'product_name' => $product->name,
            'part_number' => $partNumber,
            'price' => $mergedKey !== null ? $nextPrice : (float) $batch->selling_price,
            'qty' => $nextQty,
            'merged_subtotal' => $mergedKey !== null ? $nextSubtotal : null,
            'merge_stock' => (bool) ($cart[$key]['merge_stock'] ?? false),
            'stock_allocations' => $currentAllocations,
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
        $partNumber = $this->getBatchPartNumber($batch);
        $key = (string) $batch->id;

        if (! isset($cart[$key])) {
            $legacyKey = $this->findCartItemKeyByPartNumber($cart, $partNumber);
            if ($legacyKey !== null) {
                $key = $legacyKey;
            } else {
                return back();
            }
        }

        if (! isset($cart[$key])) {
            return back();
        }

        if ($qty === 0) {
            unset($cart[$key]);
            $request->session()->put('cashier_cart', $cart);

            return back();
        }

        $product = Product::query()->find($batch->product_id);
        if (! $product || ! $product->is_active) {
            return back()->withErrors(['cart' => 'Produk sudah tidak aktif atau tidak tersedia.']);
        }

        $mergeStock = (bool) ($cart[$key]['merge_stock'] ?? false);
        $availableStock = $mergeStock
            ? $this->getAvailableStockByPartNumber($partNumber)
            : $this->getBatchStock($batch);

        if ($qty > $availableStock) {
            return back()->withErrors(['cart' => "Stok {$cart[$key]['product_name']} tidak cukup."]);
        }

        $price = $priceInput !== null ? (float) $priceInput : (float) ($cart[$key]['price'] ?? $batch->selling_price);
        if ($price < 0) {
            return back()->withErrors(['cart' => 'Harga jual tidak boleh kurang dari 0.']);
        }

        $allocations = $this->normalizeStockAllocations($cart[$key]);
        $cart[$key]['qty'] = $qty;
        if ((bool) ($cart[$key]['merge_stock'] ?? false)) {
            $cart[$key]['merged_subtotal'] = $price;
            $cart[$key]['price'] = $qty > 0 ? ($price / $qty) : $price;
            $currentAllocated = array_sum($allocations);
            $delta = $qty - $currentAllocated;

            if ($delta > 0) {
                $allocations[(int) $batch->id] = ($allocations[(int) $batch->id] ?? 0) + $delta;
            } elseif ($delta < 0) {
                $remainingToRemove = abs($delta);
                $preferredBatchId = (int) ($cart[$key]['product_batch_id'] ?? $batch->id);
                if (isset($allocations[$preferredBatchId])) {
                    $remove = min($allocations[$preferredBatchId], $remainingToRemove);
                    $allocations[$preferredBatchId] -= $remove;
                    $remainingToRemove -= $remove;
                    if ($allocations[$preferredBatchId] <= 0) {
                        unset($allocations[$preferredBatchId]);
                    }
                }

                if ($remainingToRemove > 0) {
                    foreach (array_keys($allocations) as $allocationBatchId) {
                        if ($remainingToRemove <= 0) {
                            break;
                        }

                        $remove = min($allocations[$allocationBatchId], $remainingToRemove);
                        $allocations[$allocationBatchId] -= $remove;
                        $remainingToRemove -= $remove;

                        if ($allocations[$allocationBatchId] <= 0) {
                            unset($allocations[$allocationBatchId]);
                        }
                    }
                }
            }

            $cart[$key]['stock_allocations'] = $allocations;
        } else {
            $cart[$key]['price'] = $price;
            unset($cart[$key]['merged_subtotal']);
            $cart[$key]['stock_allocations'] = [(int) $batch->id => $qty];
        }
        $request->session()->put('cashier_cart', $cart);

        return back();
    }

    public function toggleMergeStock(Request $request, ProductBatch $batch): RedirectResponse
    {
        $cart = (array) $request->session()->get('cashier_cart', []);
        $key = $this->findCartItemKeyByBatchId($cart, (int) $batch->id);

        if ($key === null) {
            return back()->withErrors(['cart' => 'Item tidak ditemukan di keranjang.']);
        }

        if ((bool) ($cart[$key]['merge_stock'] ?? false)) {
            return back()->with('success', 'Stok ini sudah digabung.');
        }

        $partNumber = $this->getBatchPartNumber($batch);
        $sameKeys = [];
        $totalQty = 0;
        $totalSubtotal = 0.0;
        $mergedAllocations = [];

        foreach ($cart as $cartKey => $item) {
            if (strtoupper(trim((string) ($item['part_number'] ?? ''))) !== strtoupper(trim($partNumber))) {
                continue;
            }

            $sameKeys[] = (string) $cartKey;
            $totalQty += (int) ($item['qty'] ?? 0);
            $itemPrice = (float) ($item['price'] ?? 0);
            $itemQty = (int) ($item['qty'] ?? 0);
            $totalSubtotal += (float) ($item['merged_subtotal'] ?? ($itemPrice * $itemQty));

            $allocations = $this->normalizeStockAllocations($item);
            foreach ($allocations as $allocationBatchId => $allocationQty) {
                $mergedAllocations[$allocationBatchId] = ($mergedAllocations[$allocationBatchId] ?? 0) + $allocationQty;
            }
        }

        $availableStock = $this->getAvailableStockByPartNumber($partNumber);
        if ($totalQty > $availableStock) {
            return back()->withErrors(['cart' => 'Stok gabungan tidak mencukupi.']);
        }

        $cart[$key]['merge_stock'] = true;
        $cart[$key]['qty'] = $totalQty;
        $cart[$key]['merged_subtotal'] = $totalSubtotal;
        $cart[$key]['price'] = $totalQty > 0 ? ($totalSubtotal / $totalQty) : (float) ($cart[$key]['price'] ?? 0);
        $cart[$key]['stock_allocations'] = $mergedAllocations;

        foreach ($sameKeys as $cartKey) {
            if ($cartKey !== $key) {
                unset($cart[$cartKey]);
            }
        }

        $request->session()->put('cashier_cart', $cart);

        return back()->with('success', 'Stok berhasil digabung.');
    }

    public function remove(Request $request, ProductBatch $batch): RedirectResponse
    {
        $cart = (array) $request->session()->get('cashier_cart', []);
        $batchKey = (string) $batch->id;

        if (isset($cart[$batchKey])) {
            unset($cart[$batchKey]);
        } else {
            $partKey = $this->getBatchPartNumber($batch);
            $legacyKey = $this->findCartItemKeyByPartNumber($cart, $partKey);
            if ($legacyKey !== null) {
                unset($cart[$legacyKey]);
            }
        }

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

        return view('admin.transaksi.drafts', [
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
            ->keyBy(fn (array $item): string => (string) ($item['part_number'] ?? $item['product_batch_id'] ?? uniqid('draft_', true)))
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
        $sessionCart = collect((array) $request->session()->get('cashier_cart', []))->values();

        $incomingItems = collect((array) $request->input('items', []))
            ->map(function ($item): array {
                return [
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'product_batch_id' => (int) ($item['product_batch_id'] ?? 0),
                    'product_name' => (string) ($item['product_name'] ?? ''),
                    'part_number' => (string) ($item['part_number'] ?? ''),
                    'merge_stock' => filter_var($item['merge_stock'] ?? false, FILTER_VALIDATE_BOOL),
                    'qty' => max(0, (int) ($item['qty'] ?? 0)),
                    'price' => max(0, (float) ($item['price'] ?? 0)),
                ];
            })
            ->filter(fn (array $item): bool => $item['qty'] > 0 && $item['product_batch_id'] > 0)
            ->values();

        $cart = $sessionCart;

        if ($incomingItems->isNotEmpty()) {
            $overridesByBatch = $incomingItems->keyBy(fn (array $item): int => $item['product_batch_id']);

            $cart = $sessionCart->map(function (array $item) use ($overridesByBatch): array {
                $override = $overridesByBatch->get((int) ($item['product_batch_id'] ?? 0));
                if (! $override) {
                    return $item;
                }

                $qty = (int) $override['qty'];
                $price = (float) $override['price'];
                $mergeStock = (bool) ($item['merge_stock'] ?? $override['merge_stock'] ?? false);

                $item['qty'] = $qty;

                if ($mergeStock) {
                    $item['merged_subtotal'] = $price;
                    $item['price'] = $qty > 0 ? ($price / $qty) : 0;
                } else {
                    $item['price'] = $price;
                    unset($item['merged_subtotal']);
                }

                return $item;
            })->values();
        }

        if ($cart->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        $paymentMethod = (string) $request->input('payment_method', 'cash');
        $paidAmount = (float) $request->input('paid_amount', 0);
        $creditDays = (int) $request->input('credit_days', 0);
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
                'credit_days' => $creditDays > 0 ? $creditDays : null,
                'credit_due_date' => $creditDueDate !== '' ? $creditDueDate : null,
                'customer_name' => $customerName !== '' ? mb_substr($customerName, 0, 100) : null,
                'customer_phone' => $customerPhone !== '' ? mb_substr($customerPhone, 0, 30) : null,
                'cashier_service_name' => $cashierServiceName !== '' ? mb_substr($cashierServiceName, 0, 100) : null,
                'cashier_phone' => $cashierPhone !== '' ? mb_substr($cashierPhone, 0, 30) : null,
                'items' => $cart->map(fn (array $item): array => [
                    'product_id' => (int) $item['product_id'],
                    'product_batch_id' => (int) $item['product_batch_id'],
                    'product_name' => (string) ($item['product_name'] ?? ''),
                    'part_number' => (string) ($item['part_number'] ?? ''),
                    'merge_stock' => (bool) ($item['merge_stock'] ?? false),
                    'qty' => (int) $item['qty'],
                    'price' => (float) ($item['price'] ?? 0),
                    'stock_allocations' => is_array($item['stock_allocations'] ?? null) ? $item['stock_allocations'] : [],
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
        $viewAll = $this->canViewAllTransactions($request);

        $sales = Sale::query()
            ->withCount('items')
            ->withSum('items as sold_qty', 'qty')
            ->withSum('returnedItems as returned_qty', 'qty_return')
            ->withSum('returns as total_return_refund', 'refund_amount')
            ->when(! $viewAll, fn ($query) => $query->where('user_id', $user->id))
            ->latest('id')
            ->paginate(15);

        $returns = SalesReturn::query()
            ->with([
                'user:id,name',
                'sale:id,user_id,cashier_service_name',
                'sale.user:id,name',
                'items',
            ])
            ->when(! $viewAll, fn ($query) => $query->where('user_id', $user->id))
            ->latest('id')
            ->limit(20)
            ->get();

        // Keep history page working even if the log tables have not been migrated yet.
        $editLogs = Schema::hasTable('sale_edit_logs')
            ? SaleEditLog::query()
                ->with('editor:id,name')
                ->when(! $viewAll, fn ($query) => $query->where('edited_by_user_id', $user->id))
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        $deleteLogs = Schema::hasTable('sale_delete_logs')
            ? SaleDeleteLog::query()
                ->with('deleter:id,name')
                ->when(! $viewAll, fn ($query) => $query->where('deleted_by_user_id', $user->id))
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        $installmentPaidMap = $this->getSaleInstallmentPaidMap($sales->getCollection()->pluck('id')->all());

        return view('admin.transaksi.history', [
            'user' => $user,
            'sales' => $sales,
            'returns' => $returns,
            'editLogs' => $editLogs,
            'deleteLogs' => $deleteLogs,
            'installmentPaidMap' => $installmentPaidMap,
        ]);
    }

    public function installmentForm(Request $request, Sale $sale): View
    {
        if (! $this->canAccessSale($request, $sale)) {
            abort(403, 'Anda tidak berhak memproses cicilan transaksi ini.');
        }

        $sale->loadMissing(['items', 'user:id,name', 'installments.user:id,name']);

        $installmentPaid = $this->getSaleInstallmentPaidTotal($sale->id);
        $remainingCredit = max(0, (float) ($sale->credit_amount ?? 0));
        $history = $sale->installments()->with('user:id,name')->latest('paid_at')->latest('id')->get();

        return view('admin.transaksi.installment-form', [
            'user' => $request->user(),
            'sale' => $sale,
            'installmentPaid' => $installmentPaid,
            'remainingCredit' => $remainingCredit,
            'history' => $history,
        ]);
    }

    public function storeInstallment(Request $request, Sale $sale): RedirectResponse
    {
        if (! $this->canAccessSale($request, $sale)) {
            abort(403, 'Anda tidak berhak memproses cicilan transaksi ini.');
        }

        if (! Schema::hasTable('sale_installments')) {
            return back()->withErrors(['installment' => 'Fitur cicilan belum aktif. Jalankan migrasi database terlebih dahulu.']);
        }

        $validated = $request->validate([
            'amount' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $remainingCredit = max(0, (float) ($sale->credit_amount ?? 0));
        $amount = (float) preg_replace('/[^\d]/', '', (string) $validated['amount']);

        if ($remainingCredit <= 0) {
            return back()->withErrors(['amount' => 'Transaksi ini sudah lunas.']);
        }

        if ($amount <= 0) {
            return back()->withErrors(['amount' => 'Nominal cicilan harus lebih dari 0.'])->withInput();
        }

        $appliedAmount = min($amount, $remainingCredit);
        $changeAmount = max(0, $amount - $appliedAmount);
        session()->flash('last_installment_change', $changeAmount);
        $isFullSettlement = $appliedAmount >= $remainingCredit;

        $installment = null;

        DB::transaction(function () use ($request, $sale, $amount, $appliedAmount, $changeAmount, $validated, $remainingCredit, &$installment): void {
            $installment = SaleInstallment::create([
                'sale_id' => $sale->id,
                'user_id' => $request->user()->id,
                'amount' => $appliedAmount,
                'received_amount' => $amount,
                'change_amount' => $changeAmount,
                'paid_at' => now(),
                'note' => trim((string) ($validated['note'] ?? '')) ?: null,
            ]);

            $newRemaining = max(0, $remainingCredit - $appliedAmount);
            $sale->update([
                'credit_amount' => $newRemaining,
                'credit_due_date' => $newRemaining > 0 ? $sale->credit_due_date : null,
            ]);
        });

        AdminBesarCache::forgetToday();

        $successMessage = $isFullSettlement
            ? ($amount > $remainingCredit
                ? 'Cicilan berhasil dicatat. Nominal berlebih otomatis dipakai sampai kredit lunas.'
                : 'Cicilan berhasil dicatat dan kredit sudah lunas.')
            : 'Cicilan berhasil dicatat.';

        return redirect()
            ->route('cashier.receipt', $sale)
            ->with('success', $successMessage)
            ->with('last_installment_id', $installment?->id);
    }

    public function installmentReceipt(Request $request, Sale $sale, SaleInstallment $installment): View|Response
    {
        if (! $this->canAccessSale($request, $sale) || (int) $installment->sale_id !== (int) $sale->id) {
            abort(403, 'Anda tidak berhak melihat nota cicilan ini.');
        }

        $sale->loadMissing(['user:id,name', 'installments.user:id,name', 'items']);
        $installmentPaid = $this->getSaleInstallmentPaidTotal($sale->id);
        $remainingCredit = max(0, (float) ($sale->credit_amount ?? 0));

        $viewData = [
            'sale' => $sale,
            'installment' => $installment,
            'installmentPaid' => $installmentPaid,
            'remainingCredit' => $remainingCredit,
            'historyUrl' => route('cashier.history'),
            'installmentUrl' => route('cashier.history.installment.form', $sale),
        ];

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.transaksi.installment-receipt', $viewData)->setPaper('a4', 'portrait');

            return $pdf->download("cicilan-{$sale->invoice_number}-{$installment->id}.pdf");
        }

        return response()->view('admin.transaksi.installment-receipt', $viewData)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function historyBySupplier(Request $request): View
    {
        $user = $request->user();
        $viewAll = $this->canViewAllTransactions($request);
        $userId = (int) $user->id;

        return view('admin.transaksi.history-supplier', [
            'user' => $user,
            'groups' => $this->getPtCvGroupsForCashier($viewAll ? null : $userId),
        ]);
    }

    public function historyBySupplierDetail(Request $request): View
    {
        $user = $request->user();
        $ptName = $this->normalizePtCvName((string) $request->query('pt', ''));

        if ($ptName === 'TANPA PT/CV') {
            abort(404, 'Nama PT/CV tidak ditemukan.');
        }

        $group = $this->getPtCvDetailForCashier($this->canViewAllTransactions($request) ? null : (int) $user->id, $ptName);

        if ($group === null) {
            abort(404, 'Riwayat PT/CV tidak ditemukan.');
        }

        return view('admin.transaksi.history-supplier-detail', [
            'user' => $user,
            'group' => $group,
        ]);
    }

    /**
     * Ambil daftar kelompok transaksi PT/CV untuk kasir aktif.
     *
     * Data ini dipakai di halaman list dan halaman detail supaya konsisten.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getPtCvGroupsForCashier(?int $userId): array
    {
        $rows = $this->getPtCvRowsForCashier($userId);
        $now = Carbon::now();

        return $rows
            ->groupBy(fn ($row) => $this->normalizePtCvName((string) ($row->pt_name ?? '')))
            ->map(function ($items) use ($now): array {
                $ptName = $this->normalizePtCvName((string) ($items->first()->pt_name ?? ''));
                $transactions = $items->map(fn ($row): array => $this->mapPtCvTransactionRow($row))->values();

                return [
                    'pt_name' => $ptName,
                    'summary' => [
                        'total_transaksi' => $transactions->count(),
                        'total_qty' => $transactions->sum('qty'),
                        'total_nilai' => 'Rp ' . number_format((float) $items->sum('subtotal'), 0, ',', '.'),
                        'kredit' => $transactions->whereIn('status', ['BELUM LUNAS', 'JATUH TEMPO'])->count(),
                        'lunas' => $transactions->where('status', 'LUNAS')->count(),
                    ],
                    'transactions' => $transactions,
                    'last_transaction' => $transactions->first(),
                ];
            })
            ->sortByDesc(fn (array $group): string => (string) ($group['last_transaction']['waktu_raw'] ?? ''))
            ->values()
            ->all();
    }

    /**
     * Ambil detail transaksi untuk satu PT/CV.
     *
     * @return array<string, mixed>|null
     */
    private function getPtCvDetailForCashier(?int $userId, string $ptName): ?array
    {
        $group = collect($this->getPtCvGroupsForCashier($userId))
            ->first(function (array $group) use ($ptName): bool {
                return $this->normalizePtCvName((string) ($group['pt_name'] ?? '')) === $ptName;
            });

        if ($group === null) {
            return null;
        }

        return $group;
    }

    /**
     * Ambil data transaksi PT/CV berdasarkan customer_name pada sales.
     */
    private function getPtCvRowsForCashier(?int $userId)
    {
        $itemsAgg = DB::table('sale_items')
            ->selectRaw('sale_id, COUNT(*) as items_count, COALESCE(SUM(qty), 0) as qty, COALESCE(SUM(subtotal), 0) as subtotal')
            ->groupBy('sale_id');

        return DB::table('sales')
            ->leftJoinSub($itemsAgg, 'sale_items_agg', function ($join): void {
                $join->on('sale_items_agg.sale_id', '=', 'sales.id');
            })
            ->when($userId !== null, fn ($query) => $query->where('sales.user_id', $userId))
            ->whereNotNull('sales.customer_name')
            ->whereRaw("TRIM(sales.customer_name) <> ''")
            ->where(function ($query): void {
                $query->whereRaw("UPPER(TRIM(sales.customer_name)) LIKE 'PT %'")
                    ->orWhereRaw("UPPER(TRIM(sales.customer_name)) LIKE 'CV %'");
            })
            ->whereNull('sales.deleted_at')
            ->selectRaw('
                sales.id as sale_id,
                sales.invoice_number,
                sales.customer_name as pt_name,
                sales.payment_method,
                sales.total as sale_total,
                sales.credit_amount,
                sales.credit_due_date,
                sales.created_at,
                COALESCE(sale_items_agg.items_count, 0) as items_count,
                COALESCE(sale_items_agg.qty, 0) as qty,
                COALESCE(sale_items_agg.subtotal, 0) as subtotal
            ')
            ->orderByDesc('sales.id')
            ->get();
    }

    /**
     * Ubah satu baris hasil query menjadi format yang siap dipakai view.
     */
    private function mapPtCvTransactionRow(object $row): array
    {
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
            'waktu_raw' => $row->created_at ? Carbon::parse($row->created_at)->toDateTimeString() : '',
            'waktu' => $row->created_at ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-',
            'metode' => $method,
            'qty' => (int) ($row->qty ?? 0),
            'subtotal' => 'Rp ' . number_format((float) ($row->subtotal ?? 0), 0, ',', '.'),
            'credit_amount' => 'Rp ' . number_format($creditAmount, 0, ',', '.'),
            'credit_due_date' => $dueDate ? $dueDate->format('d M Y') : '-',
            'status' => $status,
        ];
    }

    /**
     * Normalisasi nama PT/CV supaya nama yang mirip tetap tergabung.
     */
    private function normalizePtCvName(?string $name): string
    {
        $normalized = strtoupper(trim((string) $name));
        return preg_replace('/\s+/', ' ', $normalized) ?: 'TANPA PT/CV';
    }

    /**
     * @return array<int, float>
     */
    private function getSaleInstallmentPaidMap(array $saleIds): array
    {
        if ($saleIds === [] || ! Schema::hasTable('sale_installments')) {
            return [];
        }

        return SaleInstallment::query()
            ->whereIn('sale_id', $saleIds)
            ->selectRaw('sale_id, SUM(amount) as total_amount')
            ->groupBy('sale_id')
            ->get()
            ->mapWithKeys(fn ($row): array => [(int) $row->sale_id => (float) $row->total_amount])
            ->all();
    }

    private function getSaleInstallmentPaidTotal(int $saleId): float
    {
        if (! Schema::hasTable('sale_installments')) {
            return 0.0;
        }

        return (float) SaleInstallment::query()
            ->where('sale_id', $saleId)
            ->sum('amount');
    }

    public function editHistory(Request $request, Sale $sale): View
    {
        if (! $this->canAccessSale($request, $sale) || ! $this->canManageAdminToko($request)) {
            abort(403, 'Anda tidak berhak mengubah transaksi ini.');
        }

        $sale->loadMissing(['items.productBatch', 'returns']);

        if ($sale->returns->isNotEmpty()) {
            return view('admin.transaksi.history-edit', [
                'user' => $request->user(),
                'sale' => $sale,
                'blockedByReturn' => true,
            ]);
        }

        return view('admin.transaksi.history-edit', [
            'user' => $request->user(),
            'sale' => $sale,
            'blockedByReturn' => false,
        ]);
    }

    public function updateHistory(Request $request, Sale $sale): RedirectResponse
    {
        if (! $this->canAccessSale($request, $sale) || ! $this->canManageAdminToko($request)) {
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

        AdminBesarCache::forgetToday();

        return redirect()
            ->route('cashier.history')
            ->with('success', "Transaksi {$sale->invoice_number} berhasil diperbarui.");
    }

    public function destroyHistory(Request $request, Sale $sale): RedirectResponse
    {
        if (! $this->canAccessSale($request, $sale) || ! $this->canManageAdminToko($request)) {
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

        AdminBesarCache::forgetToday();

        return redirect()
            ->route('cashier.history')
            ->with('success', "Transaksi {$sale->invoice_number} berhasil dihapus.");
    }

    public function returnForm(Request $request, Sale $sale): View
    {
        if (! $this->canAccessSale($request, $sale) || ! $this->canManageAdminToko($request)) {
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

        $replacementOptions = ProductBatch::query()
            ->with(['product:id,name,barcode'])
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function (ProductBatch $batch): array {
                $partNumber = strtoupper((string) ($batch->product?->barcode ?? '-'));
                $partName = strtoupper((string) ($batch->product?->name ?? '-'));

                return [
                    'product_id' => (int) $batch->product_id,
                    'batch_id' => (int) $batch->id,
                    'part_number' => $partNumber,
                    'part_name' => $partName,
                    'stock' => (int) $batch->stock,
                    'price' => (float) $batch->selling_price,
                    'label' => trim("{$partNumber} - {$partName}"),
                    'search_text' => strtoupper(trim("{$partNumber} {$partName} {$batch->batch_code}")),
                ];
            })
            ->values()
            ->all();

        return view('admin.transaksi.return-form', [
            'user' => $request->user(),
            'sale' => $sale,
            'returnedQtyMap' => $returnedQtyMap,
            'replacementOptions' => $replacementOptions,
        ]);
    }

    public function storeReturn(Request $request, Sale $sale): RedirectResponse
    {
        if (! $this->canAccessSale($request, $sale) || ! $this->canManageAdminToko($request)) {
            abort(403, 'Anda tidak berhak memproses retur transaksi ini.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'integer'],
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
            'items.*.replacement_product_id' => ['nullable', 'integer'],
            'items.*.replacement_batch_id' => ['nullable', 'integer'],
            'items.*.replacement_qty' => ['nullable', 'integer', 'min:0'],
            'items.*.replacement_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.replacement_lines' => ['nullable', 'array'],
            'items.*.replacement_lines.*.product_id' => ['nullable', 'integer'],
            'items.*.replacement_lines.*.batch_id' => ['nullable', 'integer'],
            'items.*.replacement_lines.*.qty' => ['nullable', 'integer', 'min:0'],
            'items.*.replacement_lines.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.replacement_lines.*.label' => ['nullable', 'string', 'max:255'],
            'extra_payment_amount' => ['nullable', 'string'],
            'return_reason' => ['required', 'in:barang_rusak,ganti_barang,pengembalian_dana,salah_kirim,lainnya'],
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
        $salesReturn->loadMissing(['sale.user:id,name', 'items.replacementBatch.product']);

        if (! $this->canAccessSale($request, $salesReturn->sale)) {
            abort(403, 'Anda tidak berhak melihat nota retur ini.');
        }

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.transaksi.return-receipt', [
                'salesReturn' => $salesReturn,
                'storeName' => config('app.name', 'Surya Duta Multindo'),
                'historyUrl' => route('cashier.history'),
                'saleUrl' => route('cashier.receipt', $salesReturn->sale),
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$salesReturn->return_number}.pdf");
        }

        return view('admin.transaksi.return-receipt', [
            'salesReturn' => $salesReturn,
            'storeName' => config('app.name', 'Surya Duta Multindo'),
            'historyUrl' => route('cashier.history'),
            'saleUrl' => route('cashier.receipt', $salesReturn->sale),
        ]);
    }

    public function receipt(Request $request, Sale $sale): View|Response
    {
        $fromReturn = $request->boolean('from_return') || $request->query('from') === 'return';
        $isAdminBesarContext = $request->routeIs('admin.admin-besar.*')
            || str_starts_with($request->path(), 'admin/admin-besar');

        $historyUrl = $isAdminBesarContext
            ? route('admin.admin-besar.index')
            : route('cashier.history');
        $historyLabel = $isAdminBesarContext ? 'Kembali ke Admin Besar' : 'Kembali ke History';
        $newTransactionUrl = $isAdminBesarContext
            ? route('admin.admin-besar.index')
            : route('cashier.dashboard');

        if (! $this->canAccessSale($request, $sale) && ! $fromReturn) {
            abort(403, 'Anda tidak berhak melihat nota ini.');
        }

        $sale->loadMissing([
            'items.product.brand',
            'user:id,name',
            'returns.items.replacementBatch.product',
            'installments.user:id,name',
        ]);

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.transaksi.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Surya Duta Multindo'),
                'historyUrl' => $historyUrl,
                'historyLabel' => $historyLabel,
                'newTransactionUrl' => $newTransactionUrl,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$sale->invoice_number}.pdf");
        }

        return response()
            ->view('admin.transaksi.receipt', [
                'sale' => $sale,
                'storeName' => config('app.name', 'Surya Duta Multindo'),
                'historyUrl' => $historyUrl,
                'historyLabel' => $historyLabel,
                'newTransactionUrl' => $newTransactionUrl,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}

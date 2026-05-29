<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierDraft;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Services\CheckoutService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CashierTransactionController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService)
    {
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
        $customerName = trim((string) $request->input('customer_name', ''));
        $cashierServiceName = trim((string) $request->input('cashier_service_name', ''));
        $cashierPhone = trim((string) $request->input('cashier_phone', ''));
        $shouldPrintReceipt = $request->boolean('print_receipt');

        try {
            $sale = $this->checkoutService->checkout($request->user(), [
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount,
                'customer_name' => $customerName !== '' ? $customerName : null,
                'cashier_service_name' => $cashierServiceName !== '' ? $cashierServiceName : null,
                'cashier_phone' => $cashierPhone !== '' ? $cashierPhone : null,
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
        $sales = Sale::query()
            ->withCount('items')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(15);

        return view('cashier.history', [
            'user' => $request->user(),
            'sales' => $sales,
        ]);
    }

    public function receipt(Request $request, Sale $sale): View|Response
    {
        if ((int) $sale->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak melihat nota ini.');
        }

        $sale->loadMissing(['items', 'user:id,name']);

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

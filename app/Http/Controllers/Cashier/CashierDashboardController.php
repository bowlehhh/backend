<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierDraft;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashierDashboardController extends Controller
{
    /**
     * Render halaman utama kasir.
     *
     * Alur utama:
     * - Hitung ringkasan transaksi harian berdasarkan jam reset yang bisa dikonfigurasi.
     * - Ambil daftar produk aktif dengan filter pencarian + kategori.
     * - Bentuk data keranjang dari session untuk ditampilkan di panel kanan.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now(config('app.timezone'));
        $resetHour = max(0, min(23, (int) env('CASHIER_DAILY_RESET_HOUR', 0)));
        // Window "hari operasional" kasir dimulai dari jam reset (bukan selalu 00:00).
        $resetStart = $now->copy()->startOfDay()->setHour($resetHour);
        if ($now->lt($resetStart)) {
            $resetStart->subDay();
        }
        $nextResetAt = $resetStart->copy()->addDay();

        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        $todaySalesQuery = Sale::query()
            ->where('user_id', $user?->id)
            ->whereBetween('created_at', [
                // created_at disimpan dalam UTC, jadi boundary ikut dikonversi ke UTC.
                $resetStart->copy()->utc(),
                $nextResetAt->copy()->utc(),
            ]);

        $totalTransactionsToday = (clone $todaySalesQuery)->count();

        $totalRevenueToday = (float) (clone $todaySalesQuery)->sum('total');
        $historyCount = Sale::query()
            ->where('user_id', $user?->id)
            ->count();

        $activeProducts = Product::query()->where('is_active', true)->count();
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $products = Product::query()
            ->with(['category', 'batches' => fn ($query) => $query->where('is_active', true)->latest('id')])
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhereHas('batches.supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($category !== '', function ($query) use ($category): void {
                $query->whereHas('category', fn ($q) => $q->where('slug', $category));
            })
            ->latest('id')
            ->limit(12)
            ->get();

        $searchSuggestions = Product::query()
            ->with([
                'batches' => fn ($query) => $query
                    ->where('is_active', true)
                    ->latest('id')
                    ->with('supplier:id,name'),
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'barcode'])
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'barcode' => $product->barcode,
                'supplier' => $product->batches->first()?->supplier?->name,
            ])
            ->values()
            ->all();

        $sessionCart = collect((array) $request->session()->get('cashier_cart', []));
        $cartItems = $sessionCart->values()->all();
        $subtotal = $sessionCart->sum(fn (array $item): float => (float) $item['price'] * (int) $item['qty']);
        $total = $subtotal;
        $draftCount = CashierDraft::query()
            ->where('user_id', $user?->id)
            ->count();

        $supplierCount = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('product_batches', 'product_batches.id', '=', 'sale_items.product_batch_id')
            ->where('sales.user_id', $user?->id)
            ->whereNull('sales.deleted_at')
            ->whereNotNull('product_batches.supplier_id')
            ->distinct()
            ->count('product_batches.supplier_id');

        return view('cashier.dashboard', [
            'user' => $user,
            'totalTransactionsToday' => $totalTransactionsToday,
            'totalRevenueToday' => $totalRevenueToday,
            'historyCount' => $historyCount,
            'activeProducts' => $activeProducts,
            'categories' => $categories,
            'products' => $products,
            'selectedCategory' => $category,
            'search' => $search,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'cartTotal' => $total,
            'draftCount' => $draftCount,
            'supplierCount' => $supplierCount,
            'searchSuggestions' => $searchSuggestions,
            'nextResetAtIso' => $nextResetAt->toIso8601String(),
        ]);
    }
}

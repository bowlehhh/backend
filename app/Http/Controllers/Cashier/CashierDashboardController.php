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
            ->with(['latestBatch.supplier:id,name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'barcode'])
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'barcode' => $product->barcode,
                'supplier' => $product->latestBatch?->supplier?->name,
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

        return view('cashier.dashboard', [
            'user' => $user,
            'totalTransactionsToday' => $totalTransactionsToday,
            'totalRevenueToday' => $totalRevenueToday,
            'activeProducts' => $activeProducts,
            'categories' => $categories,
            'products' => $products,
            'selectedCategory' => $category,
            'search' => $search,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'cartTotal' => $total,
            'draftCount' => $draftCount,
            'searchSuggestions' => $searchSuggestions,
            'nextResetAtIso' => $nextResetAt->toIso8601String(),
        ]);
    }
}

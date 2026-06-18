<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashierDraft;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
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
        $showAllProducts = $request->boolean('show_all');

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

        $productsQuery = Product::query()
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
            ->latest('id');

        $totalFilteredProducts = (clone $productsQuery)->count();

        if (! $showAllProducts) {
            $productsQuery->limit(12);
        }

        $products = $productsQuery->get();

        $rawCart = collect((array) $request->session()->get('cashier_cart', []))->values();
        $reservedQtyByBatch = [];
        foreach ($rawCart as $item) {
            $allocations = $item['stock_allocations'] ?? null;

            if (is_array($allocations) && $allocations !== []) {
                foreach ($allocations as $batchId => $qty) {
                    $batchKey = (int) $batchId;
                    $batchQty = max(0, (int) $qty);
                    if ($batchKey > 0 && $batchQty > 0) {
                        $reservedQtyByBatch[$batchKey] = ($reservedQtyByBatch[$batchKey] ?? 0) + $batchQty;
                    }
                }

                continue;
            }

            $batchKey = (int) ($item['product_batch_id'] ?? 0);
            $batchQty = max(0, (int) ($item['qty'] ?? 0));
            if ($batchKey > 0 && $batchQty > 0) {
                $reservedQtyByBatch[$batchKey] = ($reservedQtyByBatch[$batchKey] ?? 0) + $batchQty;
            }
        }

        $products = $products->map(function (Product $product) use ($request, $reservedQtyByBatch): Product {
            $batch = $product->batches->first();
            $batchId = (int) ($batch?->id ?? 0);
            $reservedQty = 0;

            if ($batchId > 0) {
                $reservedQty = (int) ($reservedQtyByBatch[$batchId] ?? 0);
            }

            $displayStock = max(0, (int) ($batch?->stock ?? 0) - $reservedQty);

            $product->setAttribute('display_stock', $displayStock);
            $product->setAttribute('batch_stock', (int) ($batch?->stock ?? 0));
            $product->setAttribute('available_stock', (int) ProductBatch::query()
                ->where('is_active', true)
                ->where('product_id', $product->id)
                ->sum('stock'));

            return $product;
        });

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

        $rawCart = collect((array) $request->session()->get('cashier_cart', []))->values();
        $rawCartProductIds = $rawCart
            ->pluck('product_id')
            ->filter()
            ->map(fn ($productId): int => (int) $productId)
            ->unique()
            ->values()
            ->all();

        $rawCartProducts = $rawCartProductIds !== []
            ? Product::query()->whereIn('id', $rawCartProductIds)->get()->keyBy('id')
            : collect();
        $rawCartBatches = $rawCart->pluck('product_batch_id')->filter()->map(fn ($batchId): int => (int) $batchId)->unique()->values()->all();
        $batchStockMap = $rawCartBatches !== []
            ? ProductBatch::query()
                ->with('product:id,barcode')
                ->whereIn('id', $rawCartBatches)
                ->get()
                ->keyBy('id')
            : collect();

        $rawCartPartNumbers = $rawCart
            ->map(function (array $item) use ($rawCartProducts): string {
                $product = $rawCartProducts->get((int) ($item['product_id'] ?? 0));
                $partNumber = strtoupper(trim((string) ($item['part_number'] ?? ($product?->barcode ?? ''))));

                if ($partNumber === '') {
                    $partNumber = 'PRODUCT-' . (int) ($item['product_id'] ?? 0);
                }

                return $partNumber;
            })
            ->filter()
            ->values();

        $availableStockMap = [];
        if ($rawCartPartNumbers->isNotEmpty()) {
            $availableStockMap = ProductBatch::query()
                ->with('product:id,barcode')
                ->where('is_active', true)
                ->where(function ($query) use ($rawCartPartNumbers): void {
                    foreach ($rawCartPartNumbers->unique()->all() as $partNumber) {
                        if (str_starts_with($partNumber, 'PRODUCT-')) {
                            $query->orWhere('product_id', (int) substr($partNumber, 8));
                        } else {
                            $query->orWhereHas('product', fn ($productQuery) => $productQuery->whereRaw('UPPER(TRIM(barcode)) = ?', [$partNumber]));
                        }
                    }
                })
                ->get()
                ->groupBy(fn (ProductBatch $batch): string => strtoupper(trim((string) ($batch->product?->barcode ?? ('PRODUCT-' . $batch->product_id)))))
                ->map(fn ($batches): int => (int) $batches->sum('stock'))
                ->all();
        }

        $normalizedCart = $rawCart
            ->map(function (array $item) use ($rawCartProducts, $batchStockMap, $availableStockMap): array {
                $product = $rawCartProducts->get((int) ($item['product_id'] ?? 0));
                $partNumber = strtoupper(trim((string) ($item['part_number'] ?? ($product?->barcode ?? ''))));
                $batchId = (int) ($item['product_batch_id'] ?? 0);
                $batch = $batchStockMap->get($batchId);

                if ($partNumber === '') {
                    $partNumber = 'PRODUCT-' . (int) ($item['product_id'] ?? 0);
                }

                $batchStock = (int) ($batch?->stock ?? 0);
                $availableStock = (int) ($availableStockMap[$partNumber] ?? 0);
                $mergeStock = (bool) ($item['merge_stock'] ?? false);
                $lineSubtotal = $mergeStock
                    ? (float) ($item['merged_subtotal'] ?? ((float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0)))
                    : ((float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0));
                $item['part_number'] = $partNumber;
                $item['product_name'] = (string) ($item['product_name'] ?? $product?->name ?? '-');
                $item['batch_stock'] = $batchStock;
                $item['available_stock'] = $availableStock;
                $item['merge_stock'] = $mergeStock;
                $item['merged_subtotal'] = $lineSubtotal;
                $item['price'] = $mergeStock && (int) ($item['qty'] ?? 0) > 0
                    ? ($lineSubtotal / max(1, (int) $item['qty']))
                    : (float) ($item['price'] ?? 0);
                $item['max_qty'] = $mergeStock ? $availableStock : $batchStock;
                $item['can_merge_stock'] = $availableStock > $batchStock;

                return $item;
            })
            ->values();

        $request->session()->put('cashier_cart', $normalizedCart
            ->keyBy(fn (array $item): string => (string) ($item['product_batch_id'] ?? $item['part_number']))
            ->all());

        $cartItems = $normalizedCart->all();
        $subtotal = $normalizedCart->sum(fn (array $item): float => (float) ($item['merged_subtotal'] ?? ((float) $item['price'] * (int) $item['qty'])));
        $total = $subtotal;

        $cartItems = collect($cartItems)->map(function (array $item) use ($availableStockMap): array {
            $partNumber = strtoupper(trim((string) ($item['part_number'] ?? '')));
            if ($partNumber === '') {
                $productId = (int) ($item['product_id'] ?? 0);
                $partNumber = $productId > 0 ? 'PRODUCT-' . $productId : '';
            }

            $mergeStock = (bool) ($item['merge_stock'] ?? false);
            $item['part_number'] = $partNumber;
            $item['available_stock'] = (int) ($availableStockMap[$partNumber] ?? 0);
            $item['max_qty'] = $mergeStock
                ? max(0, (int) ($availableStockMap[$partNumber] ?? 0))
                : max(0, (int) ($item['batch_stock'] ?? 0));
            $item['line_total'] = $mergeStock
                ? (float) ($item['merged_subtotal'] ?? ((float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0)))
                : ((float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0));

            return $item;
        })->all();

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

        return view('admin.transaksi.dashboard', [
            'user' => $user,
            'totalTransactionsToday' => $totalTransactionsToday,
            'totalRevenueToday' => $totalRevenueToday,
            'historyCount' => $historyCount,
            'activeProducts' => $activeProducts,
            'categories' => $categories,
            'products' => $products,
            'selectedCategory' => $category,
            'search' => $search,
            'showAllProducts' => $showAllProducts,
            'totalFilteredProducts' => $totalFilteredProducts,
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

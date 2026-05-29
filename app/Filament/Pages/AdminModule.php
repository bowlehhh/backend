<?php

namespace App\Filament\Pages;

use App\Models\ProductBatch;
use App\Models\Product;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminModule extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Modul Admin';

    protected static ?string $slug = 'admin-module';

    protected string $view = 'filament.pages.admin-module';

    public function getViewData(): array
    {
        $type = request()->query('type', 'batches');
        $searchKeyword = trim((string) request()->query('q', ''));
        $taxonomySort = (string) request()->query('sort', 'category');
        $taxonomyDir = strtolower((string) request()->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $taxonomyPage = max((int) request()->query('page', 1), 1);
        $taxonomyPerPage = 10;
        $categoryFilterId = (int) request()->query('category_id', 0);
        $brandFilterId = (int) request()->query('brand_id', 0);

        $taxonomyData = $this->getTaxonomyRows(
            $searchKeyword,
            $taxonomySort,
            $taxonomyDir,
            $taxonomyPage,
            $taxonomyPerPage,
        );

        $modules = [
            'batches' => ['label' => 'Batch Barang', 'icon' => 'layers'],
            'taxonomy' => ['label' => 'Kategori & Brand', 'icon' => 'category'],
            'reports' => ['label' => 'Laporan', 'icon' => 'analytics'],
            'users' => ['label' => 'User', 'icon' => 'group'],
        ];

        if (! array_key_exists($type, $modules)) {
            $type = 'batches';
        }

        return [
            'type' => $type,
            'modules' => $modules,
            'title' => $modules[$type]['label'],
            'icon' => $modules[$type]['icon'],
            'rows' => $type === 'taxonomy' ? ($taxonomyData['items'] ?? []) : $this->getRows($type, $searchKeyword),
            'userRows' => $this->getUserRows(),
            'searchKeyword' => $searchKeyword,
            'reportStats' => $this->getReportStats(),
            'reportTransactions' => $this->getReportTransactions(),
            'reportCashierTransactions' => $this->getReportCashierTransactions(),
            'taxonomyStats' => $this->getTaxonomyStats(),
            'taxonomyPagination' => $taxonomyData['pagination'] ?? [],
            'taxonomySort' => $taxonomySort,
            'taxonomyDir' => $taxonomyDir,
            'taxonomyProducts' => $this->getTaxonomyProducts($categoryFilterId, $brandFilterId),
            'taxonomySelectedCategoryId' => $categoryFilterId,
            'taxonomySelectedBrandId' => $brandFilterId,
            'editingUserId' => (int) request()->query('edit_user', 0),
        ];
    }

    private function getRows(string $type, string $searchKeyword = ''): array
    {
        return match ($type) {
            'batches' => $this->getBatchRows(),
            'taxonomy' => $this->getTaxonomyRows($searchKeyword)['items'] ?? [],
            'users' => $this->getUserRows(),
            default => [],
        };
    }

    private function getBatchRows(): array
    {
        if (! Schema::hasTable('product_batches')) {
            return [];
        }

        return ProductBatch::query()
            ->with(['product:id,name', 'supplier:id,name'])
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (ProductBatch $batch) => [
                'kode' => $batch->batch_code ?: '-',
                'barang' => $batch->product?->name ?: '-',
                'supplier' => $batch->supplier?->name ?: '-',
                'stok' => (int) $batch->stock,
                'harga_beli' => 'Rp ' . number_format((float) $batch->purchase_price, 0, ',', '.'),
            ])
            ->toArray();
    }

    private function getTaxonomyRows(
        string $searchKeyword = '',
        string $sort = 'category',
        string $dir = 'asc',
        int $page = 1,
        int $perPage = 10,
    ): array
    {
        if (! Schema::hasTable('products') || ! Schema::hasTable('categories') || ! Schema::hasTable('brands')) {
            return ['items' => [], 'pagination' => []];
        }

        $query = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->when(Schema::hasColumn('products', 'deleted_at'), fn ($q) => $q->whereNull('products.deleted_at'))
            ->when(Schema::hasColumn('categories', 'deleted_at'), fn ($q) => $q->whereNull('categories.deleted_at'))
            ->when(Schema::hasColumn('brands', 'deleted_at'), fn ($q) => $q->whereNull('brands.deleted_at'))
            ->when($searchKeyword !== '', function ($q) use ($searchKeyword) {
                $like = '%' . $searchKeyword . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('categories.name', 'like', $like)
                        ->orWhere('brands.name', 'like', $like)
                        ->orWhere('products.name', 'like', $like);
                });
            })
            ->selectRaw('categories.id as category_id, categories.name as kategori, categories.is_active as category_active, brands.id as brand_id, brands.name as brand, brands.is_active as brand_active, COUNT(products.id) as total_produk')
            ->groupBy('categories.id', 'categories.name', 'categories.is_active', 'brands.id', 'brands.name', 'brands.is_active');

        $sortMap = [
            'category' => 'kategori',
            'brand' => 'brand',
            'total_produk' => 'total_produk',
            'status' => 'category_active',
        ];
        $sortColumn = $sortMap[$sort] ?? 'kategori';

        $rows = $query->orderBy($sortColumn, $dir)->orderBy('brand', $dir)->get();

        $total = $rows->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min(max($page, 1), $lastPage);
        $offset = ($page - 1) * $perPage;
        $pagedRows = $rows->slice($offset, $perPage)->values();

        $items = $pagedRows
            ->map(fn ($row) => [
                'category_id' => (int) $row->category_id,
                'brand_id' => (int) $row->brand_id,
                'kategori' => $row->kategori,
                'brand' => $row->brand,
                'total_produk' => (int) $row->total_produk,
                'status' => ((bool) $row->category_active && (bool) $row->brand_active) ? 'Aktif' : 'Nonaktif',
                'is_active' => (bool) $row->category_active && (bool) $row->brand_active,
            ])->toArray();

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total > 0 ? ($offset + 1) : 0,
                'to' => min($offset + $perPage, $total),
                'has_prev' => $page > 1,
                'has_next' => $page < $lastPage,
                'prev_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $lastPage ? $page + 1 : null,
            ],
        ];
    }

    private function getTaxonomyStats(): array
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('brands') || ! Schema::hasTable('products')) {
            return [
                'total_categories' => 0,
                'total_brands' => 0,
                'total_products' => 0,
                'popular_category' => '-',
            ];
        }

        $categoryQuery = DB::table('categories');
        $brandQuery = DB::table('brands');
        $productQuery = DB::table('products');

        if (Schema::hasColumn('categories', 'deleted_at')) {
            $categoryQuery->whereNull('deleted_at');
        }
        if (Schema::hasColumn('brands', 'deleted_at')) {
            $brandQuery->whereNull('deleted_at');
        }
        if (Schema::hasColumn('products', 'deleted_at')) {
            $productQuery->whereNull('deleted_at');
        }

        $popularRows = DB::table('products')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->when(Schema::hasColumn('products', 'deleted_at'), fn ($q) => $q->whereNull('products.deleted_at'))
            ->when(Schema::hasColumn('categories', 'deleted_at'), fn ($q) => $q->whereNull('categories.deleted_at'))
            ->selectRaw('categories.name as name, COUNT(products.id) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(2)
            ->get();

        $popularCategory = '-';
        if ($popularRows->isNotEmpty()) {
            $first = $popularRows->get(0);
            $second = $popularRows->get(1);
            $isUniqueTop = ! $second || (int) $first->total > (int) $second->total;
            $popularCategory = $isUniqueTop ? (string) $first->name : '-';
        }

        return [
            'total_categories' => (int) $categoryQuery->count(),
            'total_brands' => (int) $brandQuery->count(),
            'total_products' => (int) $productQuery->count(),
            'popular_category' => $popularCategory,
        ];
    }

    private function getTaxonomyProducts(int $categoryId, int $brandId): array
    {
        if ($categoryId <= 0 || $brandId <= 0 || ! Schema::hasTable('products')) {
            return [];
        }

        return Product::query()
            ->when(Schema::hasColumn('products', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->where('category_id', $categoryId)
            ->where('brand_id', $brandId)
            ->latest('id')
            ->limit(50)
            ->get(['id', 'name', 'barcode', 'is_active'])
            ->map(fn (Product $product) => [
                'id' => (int) $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode ?: '-',
                'status' => $product->is_active ? 'Aktif' : 'Nonaktif',
            ])
            ->toArray();
    }

    private function getUserRows(): array
    {
        if (! Schema::hasTable('users')) {
            return [];
        }

        return User::query()
            ->latest('id')
            ->get()
            ->map(fn (User $user) => [
                'id' => (int) $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'role' => (string) $user->role,
                'status' => $user->is_active ? 'Aktif' : 'Nonaktif',
                'is_active' => (bool) $user->is_active,
            ])
            ->toArray();
    }

    private function getReportStats(): array
    {
        if (! Schema::hasTable('product_batches')) {
            return [
                'today_total' => 0,
                'month_total' => 0,
                'year_total' => 0,
                'today_count' => 0,
                'month_count' => 0,
                'year_count' => 0,
                'cashier_today_total' => 0,
                'cashier_today_count' => 0,
            ];
        }

        $now = Carbon::now();
        $query = ProductBatch::query();

        $sumExpr = 'purchase_price * stock';

        $todayTotal = (float) (clone $query)
            ->whereDate('created_at', $now->toDateString())
            ->sum(\DB::raw($sumExpr));
        $monthTotal = (float) (clone $query)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum(\DB::raw($sumExpr));
        $yearTotal = (float) (clone $query)
            ->whereYear('created_at', $now->year)
            ->sum(\DB::raw($sumExpr));

        $todayCount = (int) (clone $query)->whereDate('created_at', $now->toDateString())->count();
        $monthCount = (int) (clone $query)->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)->count();
        $yearCount = (int) (clone $query)->whereYear('created_at', $now->year)->count();

        $cashierTodayTotal = 0;
        $cashierTodayCount = 0;
        if (Schema::hasTable('sales')) {
            $salesQuery = DB::table('sales')
                ->whereDate('created_at', $now->toDateString());
            if (Schema::hasColumn('sales', 'deleted_at')) {
                $salesQuery->whereNull('deleted_at');
            }
            $cashierTodayTotal = (float) (clone $salesQuery)->sum('total');
            $cashierTodayCount = (int) (clone $salesQuery)->count();
        }

        return [
            'today_total' => $todayTotal,
            'month_total' => $monthTotal,
            'year_total' => $yearTotal,
            'today_count' => $todayCount,
            'month_count' => $monthCount,
            'year_count' => $yearCount,
            'cashier_today_total' => $cashierTodayTotal,
            'cashier_today_count' => $cashierTodayCount,
        ];
    }

    private function getReportCashierTransactions(): array
    {
        if (! Schema::hasTable('sales')) {
            return [];
        }

        $rows = DB::table('sales')
            ->leftJoin('users', 'users.id', '=', 'sales.user_id')
            ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($q) => $q->whereNull('sales.deleted_at'))
            ->orderByDesc('sales.id')
            ->limit(20)
            ->get([
                'sales.id',
                'sales.invoice_number',
                'sales.customer_name',
                'sales.total',
                'sales.created_at',
                'users.name as cashier_name',
            ]);

        return $rows->map(fn ($row) => [
            'sale_id' => (int) $row->id,
            'invoice_number' => $row->invoice_number ?: '-',
            'customer_name' => $row->customer_name ?: 'Pembeli Umum',
            'cashier_name' => $row->cashier_name ?: '-',
            'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-',
            'total' => 'Rp ' . number_format((float) ($row->total ?? 0), 0, ',', '.'),
        ])->toArray();
    }

    private function getReportTransactions(): array
    {
        if (! Schema::hasTable('product_batches')) {
            return [];
        }

        return ProductBatch::query()
            ->with(['product:id,name,barcode', 'supplier:id,name'])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(function (ProductBatch $batch): array {
                $total = (float) $batch->purchase_price * (int) $batch->stock;

                return [
                    'tanggal' => optional($batch->created_at)->format('d M Y H:i') ?: '-',
                    'supplier' => $batch->supplier?->name ?: '-',
                    'barang' => $batch->product?->name ?: '-',
                    'qty' => (int) $batch->stock,
                    'harga_satuan' => 'Rp ' . number_format((float) $batch->purchase_price, 0, ',', '.'),
                    'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'supplier_id' => $batch->supplier_id,
                ];
            })
            ->toArray();
    }
}

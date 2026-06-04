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
            'credits' => ['label' => 'Kredit', 'icon' => 'credit_card'],
            'supplier-transactions' => ['label' => 'Transaksi PT/CV', 'icon' => 'account_tree'],
            'taxonomy' => ['label' => 'Kategori & Merek', 'icon' => 'category'],
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
            'reportSupplierGroups' => $this->getReportSupplierGroups(),
            'ptCustomerGroups' => $this->getPtCustomerGroups(),
            'ptCustomerDetail' => $this->getPtCustomerDetail((string) request()->query('pt', '')),
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
            'batches' => $this->getBatchRows($searchKeyword),
            'credits' => $this->getCreditRows(),
            'supplier-transactions' => [],
            'taxonomy' => $this->getTaxonomyRows($searchKeyword)['items'] ?? [],
            'users' => $this->getUserRows(),
            default => [],
        };
    }

    private function getCreditRows(): array
    {
        if (! Schema::hasTable('product_batches')) {
            return [];
        }

        $query = ProductBatch::query()
            ->with(['product:id,name,barcode,unit,brand_id', 'product.brand:id,name', 'supplier:id,name'])
            ->where('payment_type', 'KREDIT')
            ->latest('id')
            ->limit(200);

        $hasInstallments = Schema::hasTable('credit_installments');

        return $query->get()->map(function (ProductBatch $batch) use ($hasInstallments): array {
            $qty = (int) ($batch->stock ?? 0);
            $unitPrice = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $subtotal = $qty * $unitPrice;
            $totalCredit = $subtotal + $expeditionCost;
            $installmentPaid = 0.0;

            if ($hasInstallments) {
                $installmentPaid = (float) DB::table('credit_installments')
                    ->where('product_batch_id', $batch->id)
                    ->sum('amount');
            }

            $paid = min($totalCredit, $downPayment + $installmentPaid);
            $remaining = max(0, $totalCredit - $paid);

            return [
                'batch_id' => (int) $batch->id,
                'supplier_id' => $batch->supplier_id ? (int) $batch->supplier_id : null,
                'supplier' => $batch->supplier?->name ?: '-',
                'part_number' => $batch->product?->barcode ?: '-',
                'part_name' => $batch->product?->name ?: '-',
                'merek' => $batch->product?->brand?->name ?: '-',
                'unit' => $batch->product?->unit ?: '-',
                'qty' => number_format($qty, 0, ',', '.'),
                'harga_beli' => 'Rp ' . number_format($unitPrice, 0, ',', '.'),
                'biaya_ekspedisi' => 'Rp ' . number_format($expeditionCost, 0, ',', '.'),
                'total_kredit' => 'Rp ' . number_format($totalCredit, 0, ',', '.'),
                'down_payment' => 'Rp ' . number_format($downPayment, 0, ',', '.'),
                'down_payment_value' => $downPayment,
                'sudah_dibayar' => 'Rp ' . number_format($paid, 0, ',', '.'),
                'sisa_kredit' => 'Rp ' . number_format($remaining, 0, ',', '.'),
                'total_kredit_value' => $totalCredit,
                'sudah_dibayar_value' => $paid,
                'sisa_kredit_value' => $remaining,
                'hari_kredit' => (int) ($batch->credit_days ?? 0) > 0 ? ((int) $batch->credit_days . ' hari') : '-',
                'hari_kredit_value' => (int) ($batch->credit_days ?? 0),
                'jatuh_tempo' => $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->format('d M Y') : '-',
                'jatuh_tempo_value' => $batch->credit_due_date?->toDateString(),
                'status' => $remaining <= 0
                    ? 'LUNAS'
                    : (($batch->credit_due_date && Carbon::parse($batch->credit_due_date)->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS'),
            ];
        })->toArray();
    }

    private function getBatchRows(string $searchKeyword = ''): array
    {
        if (! Schema::hasTable('product_batches')) {
            return [];
        }

        return ProductBatch::query()
            ->with(['product:id,name', 'supplier:id,name'])
            ->when($searchKeyword !== '', function ($q) use ($searchKeyword) {
                $like = '%' . $searchKeyword . '%';
                $q->where('batch_code', 'like', $like);
            })
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

        $sumExpr = Schema::hasColumn('product_batches', 'expedition_cost')
            ? '(purchase_price * stock) + expedition_cost'
            : 'purchase_price * stock';

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

        $query = DB::table('sales')
            ->leftJoin('users', 'users.id', '=', 'sales.user_id')
            ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($q) => $q->whereNull('sales.deleted_at'));

        if (
            Schema::hasTable('sales_returns')
            && Schema::hasColumn('sales_returns', 'sale_id')
            && Schema::hasColumn('sales_returns', 'refund_amount')
        ) {
            $returnsQuery = DB::table('sales_returns')
                ->selectRaw('sale_id, COALESCE(SUM(refund_amount), 0) as total_return_refund')
                ->groupBy('sale_id');

            $query->leftJoinSub($returnsQuery, 'returns_agg', fn ($join) => $join->on('returns_agg.sale_id', '=', 'sales.id'));
        }

        $selects = [
            'sales.id',
            'sales.invoice_number',
            'sales.customer_name',
            'sales.total',
            'sales.created_at',
            'sales.cashier_service_name',
            'users.name as cashier_user_name',
        ];

        if (Schema::hasColumn('sales', 'payment_method')) {
            $selects[] = 'sales.payment_method';
        } else {
            $selects[] = DB::raw("'cash' as payment_method");
        }

        if (Schema::hasColumn('sales', 'credit_amount')) {
            $selects[] = 'sales.credit_amount';
        } else {
            $selects[] = DB::raw('0 as credit_amount');
        }

        if (Schema::hasColumn('sales', 'credit_due_date')) {
            $selects[] = 'sales.credit_due_date';
        } else {
            $selects[] = DB::raw('NULL as credit_due_date');
        }

        if (
            Schema::hasTable('sales_returns')
            && Schema::hasColumn('sales_returns', 'sale_id')
            && Schema::hasColumn('sales_returns', 'refund_amount')
        ) {
            $selects[] = DB::raw('COALESCE(returns_agg.total_return_refund, 0) as total_return_refund');
        } else {
            $selects[] = DB::raw('0 as total_return_refund');
        }

        $rows = $query
            ->orderByDesc('sales.id')
            ->limit(20)
            ->get($selects);

        return $rows->map(fn ($row) => [
            'sale_id' => (int) $row->id,
            'invoice_number' => $row->invoice_number ?: '-',
            'customer_name' => $row->customer_name ?: 'Pembeli Umum',
            'cashier_name' => $this->resolveCashierDisplayName(
                (string) ($row->cashier_service_name ?? ''),
                (string) ($row->cashier_user_name ?? '')
            ),
            'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-',
            'payment_method' => strtoupper((string) ($row->payment_method ?? 'cash')),
            'credit_amount' => 'Rp ' . number_format((float) ($row->credit_amount ?? 0), 0, ',', '.'),
            'credit_due_date' => ! empty($row->credit_due_date) ? Carbon::parse($row->credit_due_date)->format('d M Y') : '-',
            'total_return_refund' => 'Rp ' . number_format((float) ($row->total_return_refund ?? 0), 0, ',', '.'),
            'total' => 'Rp ' . number_format((float) ($row->total ?? 0), 0, ',', '.'),
        ])->toArray();
    }

    private function resolveCashierDisplayName(string $serviceName, string $fallbackName): string
    {
        $serviceName = trim($serviceName);
        $fallbackName = trim($fallbackName);

        if ($serviceName === '') {
            return $fallbackName !== '' ? $fallbackName : '-';
        }

        $normalized = mb_strtolower($serviceName);
        $looksLikeCreditDays = (bool) preg_match('/^\d+\s*(hari|day|days)?$/u', $normalized) || str_contains($normalized, 'hari');

        if ($looksLikeCreditDays && $fallbackName !== '') {
            return $fallbackName;
        }

        return $serviceName;
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
                $expeditionCost = (float) ($batch->expedition_cost ?? 0);
                $total = ((float) $batch->purchase_price * (int) $batch->stock) + $expeditionCost;

                return [
                    'tanggal' => optional($batch->created_at)->format('d M Y H:i') ?: '-',
                    'supplier' => $batch->supplier?->name ?: '-',
                    'barang' => $batch->product?->name ?: '-',
                    'qty' => (int) $batch->stock,
                    'harga_satuan' => 'Rp ' . number_format((float) $batch->purchase_price, 0, ',', '.'),
                    'biaya_ekspedisi' => 'Rp ' . number_format($expeditionCost, 0, ',', '.'),
                    'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                    'supplier_id' => $batch->supplier_id,
                ];
            })
            ->toArray();
    }

    private function getReportSupplierGroups(): array
    {
        if (! Schema::hasTable('product_batches')) {
            return [];
        }

        $hasInstallments = Schema::hasTable('credit_installments');
        $installmentPaidMap = [];

        if ($hasInstallments) {
            $installmentPaidMap = DB::table('credit_installments')
                ->selectRaw('product_batch_id, COALESCE(SUM(amount), 0) as paid_total')
                ->groupBy('product_batch_id')
                ->pluck('paid_total', 'product_batch_id')
                ->map(fn ($value) => (float) $value)
                ->toArray();
        }

        $batches = ProductBatch::query()
            ->with('supplier:id,name')
            ->latest('created_at')
            ->get();

        $grouped = [];
        foreach ($batches as $batch) {
            $supplierId = (int) ($batch->supplier_id ?? 0);
            $supplierName = $batch->supplier?->name ?: 'Tanpa Supplier';

            if (! isset($grouped[$supplierId])) {
                $grouped[$supplierId] = [
                    'supplier_id' => $supplierId > 0 ? $supplierId : null,
                    'supplier' => $supplierName,
                    'total_transaksi' => 0,
                    'total_qty' => 0,
                    'total_modal_value' => 0.0,
                    'kredit_count' => 0,
                    'lunas_count' => 0,
                    'jatuh_tempo_count' => 0,
                    'last_purchase_at_ts' => 0,
                    'last_purchase_at' => '-',
                ];
            }

            $qty = (int) ($batch->stock ?? 0);
            $price = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $total = ($qty * $price) + $expeditionCost;
            $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $installmentPaid = (float) ($installmentPaidMap[$batch->id] ?? 0);
            $paid = min($total, $downPayment + $installmentPaid);
            $remaining = max(0, $total - $paid);

            $status = 'LUNAS';
            if ($paymentType === 'KREDIT') {
                if ($remaining <= 0) {
                    $status = 'LUNAS';
                } elseif ($batch->credit_due_date && Carbon::parse($batch->credit_due_date)->isPast()) {
                    $status = 'JATUH TEMPO';
                } else {
                    $status = 'BELUM LUNAS';
                }
            }

            $grouped[$supplierId]['total_transaksi']++;
            $grouped[$supplierId]['total_qty'] += $qty;
            $grouped[$supplierId]['total_modal_value'] += $total;
            if (in_array($status, ['BELUM LUNAS', 'JATUH TEMPO'], true)) {
                $grouped[$supplierId]['kredit_count']++;
            }
            if ($status === 'LUNAS') {
                $grouped[$supplierId]['lunas_count']++;
            }
            if ($status === 'JATUH TEMPO') {
                $grouped[$supplierId]['jatuh_tempo_count']++;
            }

            $ts = $batch->created_at?->timestamp ?? 0;
            if ($ts > $grouped[$supplierId]['last_purchase_at_ts']) {
                $grouped[$supplierId]['last_purchase_at_ts'] = $ts;
                $grouped[$supplierId]['last_purchase_at'] = $batch->created_at?->format('d M Y H:i') ?: '-';
            }
        }

        return collect($grouped)
            ->map(function (array $row) {
                $row['total_modal'] = 'Rp ' . number_format((float) $row['total_modal_value'], 0, ',', '.');
                unset($row['total_modal_value'], $row['last_purchase_at_ts']);
                return $row;
            })
            ->sortByDesc('total_transaksi')
            ->values()
            ->toArray();
    }

    private function getPtCustomerGroups(): array
    {
        $rows = $this->getPtCvRowsForAdmin();
        if ($rows->isEmpty()) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $name = $this->normalizePtCvName((string) ($row->pt_name ?? ''));
            if (! isset($grouped[$name])) {
                $grouped[$name] = [
                    'pt_name' => $name,
                    'total_transaksi' => 0,
                    'total_qty' => 0,
                    'total_nilai_value' => 0.0,
                    'kredit' => 0,
                    'jatuh_tempo' => 0,
                    'lunas' => 0,
                    'terakhir_beli_ts' => 0,
                    'terakhir_beli' => '-',
                ];
            }

            $method = strtoupper((string) ($row->payment_method ?? 'CASH'));
            $creditAmount = (float) ($row->credit_amount ?? 0);
            $dueDate = !empty($row->credit_due_date) ? Carbon::parse($row->credit_due_date) : null;
            $status = 'LUNAS';
            if ($method === 'CREDIT' && $creditAmount > 0) {
                $status = ($dueDate && $dueDate->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS';
            }

            $grouped[$name]['total_transaksi']++;
            $grouped[$name]['total_qty'] += (int) ($row->qty ?? 0);
            $grouped[$name]['total_nilai_value'] += (float) ($row->sale_total ?? 0);
            if (in_array($status, ['BELUM LUNAS', 'JATUH TEMPO'], true)) {
                $grouped[$name]['kredit']++;
            }
            if ($status === 'JATUH TEMPO') {
                $grouped[$name]['jatuh_tempo']++;
            }
            if ($status === 'LUNAS') {
                $grouped[$name]['lunas']++;
            }

            $ts = !empty($row->created_at) ? Carbon::parse($row->created_at)->timestamp : 0;
            if ($ts > $grouped[$name]['terakhir_beli_ts']) {
                $grouped[$name]['terakhir_beli_ts'] = $ts;
                $grouped[$name]['terakhir_beli'] = !empty($row->created_at) ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-';
            }
        }

        return collect($grouped)
            ->map(function (array $row) {
                $row['total_nilai'] = 'Rp ' . number_format((float) $row['total_nilai_value'], 0, ',', '.');
                unset($row['total_nilai_value'], $row['terakhir_beli_ts']);
                return $row;
            })
            ->sortByDesc('total_transaksi')
            ->values()
            ->toArray();
    }

    private function getPtCustomerDetail(string $ptName): array
    {
        $rawPtName = trim($ptName);
        if ($rawPtName === '') {
            return ['pt_name' => '', 'rows' => [], 'summary' => null];
        }

        $ptName = $this->normalizePtCvName($rawPtName);
        if ($ptName === 'TANPA PT/CV') {
            return ['pt_name' => '', 'rows' => [], 'summary' => null];
        }

        $mapped = $this->getPtCvRowsForAdmin()
            ->filter(function (object $row) use ($ptName): bool {
                return $this->normalizePtCvName((string) ($row->pt_name ?? '')) === $ptName;
            })
            ->map(function (object $row) {
            $method = strtoupper((string) ($row->payment_method ?? 'CASH'));
            $creditAmount = (float) ($row->credit_amount ?? 0);
            $dueDate = !empty($row->credit_due_date) ? Carbon::parse($row->credit_due_date) : null;
            $status = 'LUNAS';
            if ($method === 'CREDIT' && $creditAmount > 0) {
                $status = ($dueDate && $dueDate->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS';
            }

            return [
                'sale_id' => (int) $row->id,
                'invoice' => (string) ($row->invoice_number ?: '-'),
                'waktu' => !empty($row->created_at) ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-',
                'metode' => $method,
                'qty' => (int) ($row->qty ?? 0),
                'total' => 'Rp ' . number_format((float) ($row->sale_total ?? 0), 0, ',', '.'),
                'total_value' => (float) ($row->sale_total ?? 0),
                'kredit' => 'Rp ' . number_format($creditAmount, 0, ',', '.'),
                'jatuh_tempo' => $dueDate ? $dueDate->format('d M Y') : '-',
                'status' => $status,
            ];
        })->values();

        return [
            'pt_name' => $ptName,
            'rows' => $mapped->toArray(),
            'summary' => [
                'total_transaksi' => $mapped->count(),
                'total_qty' => $mapped->sum('qty'),
                'total_nilai' => 'Rp ' . number_format((float) $mapped->sum('total_value'), 0, ',', '.'),
            ],
        ];
    }

    private function getPtCvRowsForAdmin()
    {
        if (! Schema::hasTable('sales')) {
            return collect();
        }

        $itemsAgg = null;
        if (Schema::hasTable('sale_items')) {
            $itemsAgg = DB::table('sale_items')
                ->selectRaw('sale_id, COUNT(*) as items_count, COALESCE(SUM(qty), 0) as qty, COALESCE(SUM(subtotal), 0) as subtotal')
                ->groupBy('sale_id');
        }

        $query = DB::table('sales')
            ->whereNotNull('customer_name')
            ->whereRaw("TRIM(customer_name) <> ''")
            ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'));

        if ($itemsAgg !== null) {
            $query->leftJoinSub($itemsAgg, 'sale_items_agg', function ($join): void {
                $join->on('sale_items_agg.sale_id', '=', 'sales.id');
            });
        }

        return $query
            ->selectRaw('
                sales.id,
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

    private function normalizePtCvName(?string $name): string
    {
        $normalized = strtoupper(trim((string) $name));
        return preg_replace('/\s+/', ' ', $normalized) ?: 'TANPA PT/CV';
    }
}

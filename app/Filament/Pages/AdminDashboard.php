<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\OfflineBackupService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Ringkasan Dashboard';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.admin-dashboard';

    public $search = '';
    public $categoryFilter = '';
    public $brandFilter = '';
    public $perPage = 30;
    public $currentPage = 1;

    public function mount(): void
    {
        $this->search = request()->query('search', '');
        $this->categoryFilter = '';
        $this->brandFilter = '';
    }

    public function getViewData(): array
    {
        $productsData = $this->getProducts();

        return [
            'stats' => $this->getStats(),
            'recentSales' => $this->getRecentSales(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'products' => $productsData['items'] ?? [],
            'pagination' => $productsData['pagination'] ?? [],
            'categories' => $this->getCategories(),
            'brands' => $this->getBrands(),
            'categoryOptions' => $this->getCategoryOptions(),
            'brandOptions' => $this->getBrandOptions(),
            'supplierOptions' => $this->getSupplierOptions(),
            'search' => $this->search,
            'categoryFilter' => $this->categoryFilter,
            'brandFilter' => $this->brandFilter,
        ];
    }

    private function getRecentSales(): array
    {
        if (! Schema::hasTable('sales')) {
            return [];
        }

        $query = DB::table('sales')
            ->leftJoin('users', 'users.id', '=', 'sales.user_id')
            ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($q) => $q->whereNull('sales.deleted_at'))
            ->orderByDesc('sales.id');

        $rows = $query->limit(15)->get([
            'sales.id',
            'sales.invoice_number',
            'sales.customer_name',
            'sales.total',
            'sales.created_at',
            'users.name as cashier_name',
        ]);

        return $rows->map(fn ($row) => [
            'invoice_number' => $row->invoice_number ?: '-',
            'customer_name' => $row->customer_name ?: 'Pembeli Umum',
            'cashier_name' => $row->cashier_name ?: '-',
            'total' => 'Rp ' . number_format((float) ($row->total ?? 0), 0, ',', '.'),
            'created_at' => $row->created_at
                ? \Illuminate\Support\Carbon::parse($row->created_at)->format('d M Y H:i')
                : '-',
            'detail_url' => route('admin.sales.receipt', ['sale' => $row->id]),
        ])->toArray();
    }

    private function getStats(): array
    {
        $totalProducts = 0;
        $lowStock = 0;
        $stockValue = 0;
        $creditItems = 0;
        $creditWarnings = 0;

        if (Schema::hasTable('products')) {
            $totalProducts = DB::table('products')
                ->when(Schema::hasColumn('products', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->count();
        }

        if (Schema::hasTable('product_batches')) {
            $stockColumn = Schema::hasColumn('product_batches', 'stock') ? 'stock' : null;
            $purchaseColumn = Schema::hasColumn('product_batches', 'purchase_price') ? 'purchase_price' : null;

            if ($stockColumn) {
                $lowStock = DB::table('product_batches')
                    ->when(Schema::hasColumn('product_batches', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                    ->where($stockColumn, '<=', 10)
                    ->count();
            }

            if ($stockColumn && $purchaseColumn) {
                $expeditionExpr = Schema::hasColumn('product_batches', 'expedition_cost') ? ' + expedition_cost' : '';
                $stockValue = DB::table('product_batches')
                    ->when(Schema::hasColumn('product_batches', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                    ->selectRaw("SUM(({$stockColumn} * {$purchaseColumn}){$expeditionExpr}) as total")
                    ->value('total') ?? 0;
            }

            if (
                $stockColumn
                && $purchaseColumn
                && Schema::hasColumn('product_batches', 'payment_type')
                && Schema::hasColumn('product_batches', 'credit_due_date')
            ) {
                $paidByBatch = [];
                if (Schema::hasTable('credit_installments') && Schema::hasColumn('credit_installments', 'product_batch_id') && Schema::hasColumn('credit_installments', 'amount')) {
                    $paidByBatch = DB::table('credit_installments')
                        ->selectRaw('product_batch_id, SUM(amount) as paid_total')
                        ->groupBy('product_batch_id')
                        ->pluck('paid_total', 'product_batch_id')
                        ->map(fn ($v) => (float) $v)
                        ->toArray();
                }

                $creditSelects = ['id', $stockColumn . ' as stock', $purchaseColumn . ' as purchase_price', 'credit_due_date'];
                if (Schema::hasColumn('product_batches', 'expedition_cost')) {
                    $creditSelects[] = 'expedition_cost';
                } else {
                    $creditSelects[] = DB::raw('0 as expedition_cost');
                }
                if (Schema::hasColumn('product_batches', 'down_payment_amount')) {
                    $creditSelects[] = 'down_payment_amount';
                } else {
                    $creditSelects[] = DB::raw('0 as down_payment_amount');
                }

                $creditBatches = DB::table('product_batches')
                    ->when(Schema::hasColumn('product_batches', 'deleted_at'), fn ($query) => $query->whereNull('product_batches.deleted_at'))
                    ->whereRaw('UPPER(payment_type) = ?', ['KREDIT'])
                    ->get($creditSelects);

                $today = now()->startOfDay();
                foreach ($creditBatches as $batch) {
                    $expeditionCost = (float) ($batch->expedition_cost ?? 0);
                    $total = ((float) ($batch->stock ?? 0) * (float) ($batch->purchase_price ?? 0)) + $expeditionCost;
                    $downPayment = (float) ($batch->down_payment_amount ?? 0);
                    $paid = min($total, $downPayment + (float) ($paidByBatch[$batch->id] ?? 0));
                    $remaining = max(0, $total - $paid);

                    if ($remaining <= 0) {
                        continue;
                    }

                    $creditItems++;

                    if (!empty($batch->credit_due_date)) {
                        $due = \Illuminate\Support\Carbon::parse($batch->credit_due_date)->startOfDay();
                        $days = $today->diffInDays($due, false);
                        if ($days <= 3) {
                            $creditWarnings++;
                        }
                    }
                }
            }
        }

        return [
            [
                'label' => 'Total SKU',
                'value' => number_format($totalProducts, 0, ',', '.'),
                'description' => 'Data master aktif inventory',
                'icon' => 'inventory_2',
                'variant' => 'primary',
            ],
            [
                'label' => 'Stok Menipis',
                'value' => number_format($lowStock, 0, ',', '.'),
                'description' => 'Perlu restock segera',
                'icon' => 'warning',
                'variant' => 'warning',
            ],
            [
                'label' => 'Total Nilai Stok',
                'value' => 'Rp ' . number_format($stockValue, 0, ',', '.'),
                'description' => 'Estimasi nilai modal inventory',
                'icon' => 'trending_up',
                'variant' => 'secondary',
            ],
            [
                'label' => 'Barang Kredit',
                'value' => number_format($creditItems, 0, ',', '.'),
                'description' => 'Klik untuk lihat daftar kredit supplier',
                'icon' => 'credit_card',
                'variant' => 'info',
            ],
            [
                'label' => 'Warning Kredit',
                'value' => number_format($creditWarnings, 0, ',', '.'),
                'description' => 'Jatuh tempo <= 3 hari',
                'icon' => 'notifications_active',
                'variant' => 'danger',
            ],
        ];
    }

    private function getLowStockProducts(): array
    {
        if (! Schema::hasTable('product_batches') || ! Schema::hasTable('products')) {
            return [];
        }

        $nameColumn = Schema::hasColumn('products', 'name') ? 'name' : 'nama_barang';

        $query = DB::table('product_batches')
            ->join('products', 'products.id', '=', 'product_batches.product_id')
            ->when(Schema::hasTable('suppliers'), fn ($q) => $q->leftJoin('suppliers', 'suppliers.id', '=', 'product_batches.supplier_id'))
            ->when(Schema::hasColumn('product_batches', 'deleted_at'), fn ($q) => $q->whereNull('product_batches.deleted_at'))
            ->when(Schema::hasColumn('products', 'deleted_at'), fn ($q) => $q->whereNull('products.deleted_at'))
            ->where('product_batches.stock', '<=', 10)
            ->orderBy('product_batches.stock')
            ->orderByDesc('product_batches.updated_at');

        $selects = [
            'product_batches.id as batch_id',
            "products.{$nameColumn} as product_name",
            'product_batches.batch_code',
            'product_batches.stock',
            'product_batches.selling_price',
        ];

        if (Schema::hasColumn('suppliers', 'name')) {
            $selects[] = 'suppliers.name as supplier_name';
        }

        return $query->limit(100)->get($selects)->map(fn ($row) => [
            'batch_id' => (int) $row->batch_id,
            'product_name' => $row->product_name ?? '-',
            'batch_code' => $row->batch_code ?? '-',
            'stock' => (int) ($row->stock ?? 0),
            'supplier_name' => $row->supplier_name ?? '-',
            'selling_price' => 'Rp ' . number_format((float) ($row->selling_price ?? 0), 0, ',', '.'),
        ])->toArray();
    }

    private function getProducts(): array
    {
        if (! Schema::hasTable('products')) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $this->perPage,
                    'total' => 0,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0,
                    'has_prev' => false,
                    'has_next' => false,
                    'prev_page' => null,
                    'next_page' => null,
                ],
            ];
        }

        $nameColumn = Schema::hasColumn('products', 'name') ? 'name' : 'nama_barang';
        $barcodeColumn = Schema::hasColumn('products', 'barcode') ? 'barcode' : null;

        $query = DB::table('products')
            ->when(Schema::hasColumn('products', 'deleted_at'), fn ($query) => $query->whereNull('products.deleted_at'));

        if (Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id')) {
            $query->leftJoin('categories', 'categories.id', '=', 'products.category_id');
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id')) {
            $query->leftJoin('brands', 'brands.id', '=', 'products.brand_id');
        }

        $hasCategoryJoin = Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id');
        $hasBrandJoin = Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id');
        $categorySearchColumn = Schema::hasColumn('categories', 'name')
            ? 'categories.name'
            : (Schema::hasColumn('categories', 'nama') ? 'categories.nama' : null);
        $brandSearchColumn = Schema::hasColumn('brands', 'name')
            ? 'brands.name'
            : (Schema::hasColumn('brands', 'nama') ? 'brands.nama' : null);

        // Global search: nama barang, barcode, kategori, brand
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($nameColumn, $barcodeColumn, $search, $hasCategoryJoin, $hasBrandJoin, $categorySearchColumn, $brandSearchColumn) {
                $q->where("products.{$nameColumn}", 'like', $search);
                if ($barcodeColumn) {
                    $q->orWhere("products.{$barcodeColumn}", 'like', $search);
                }
                if ($hasCategoryJoin && $categorySearchColumn) {
                    $q->orWhere($categorySearchColumn, 'like', $search);
                }
                if ($hasBrandJoin && $brandSearchColumn) {
                    $q->orWhere($brandSearchColumn, 'like', $search);
                }
            });
        }

        $selects = [
            "products.id",
            "products.{$nameColumn} as product_name",
            'products.created_at as created_at',
        ];

        if ($barcodeColumn) {
            $selects[] = "products.{$barcodeColumn} as barcode";
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id')) {
            $categoryNameColumn = Schema::hasColumn('categories', 'name') ? 'name' : 'nama';
            $selects[] = "categories.{$categoryNameColumn} as category_name";
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id')) {
            $brandNameColumn = Schema::hasColumn('brands', 'name') ? 'name' : 'nama';
            $selects[] = "brands.{$brandNameColumn} as brand_name";
        }

        $page = max((int) request()->query('page', 1), 1);
        $this->currentPage = $page;

        $total = (clone $query)->count('products.id');
        $lastPage = max((int) ceil($total / $this->perPage), 1);

        if ($page > $lastPage) {
            $page = $lastPage;
            $this->currentPage = $lastPage;
        }

        $items = $query
            ->select($selects)
            ->latest('products.id')
            ->forPage($page, $this->perPage)
            ->get();

        if ($items->isEmpty()) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $this->perPage,
                    'total' => 0,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0,
                    'has_prev' => false,
                    'has_next' => false,
                    'prev_page' => null,
                    'next_page' => null,
                ],
            ];
        }

        $mappedItems = $items->map(function ($item) {
            $details = $this->getProductDetails((int) $item->id);
            $stock = (int) ($details['stock'] ?? 0);

            return [
                'id' => $item->id,
                'name' => $item->product_name ?? '-',
                'sku' => $item->barcode ?: 'SKU-' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                'barcode' => $item->barcode,
                'created_at' => $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y H:i') : '-',
                'unit' => $details['unit'] ?? null,
                'weight' => $details['weight'] ?? null,
                'weight_unit' => $details['weight_unit'] ?? 'kg',
                'category' => $item->category_name ?? '-',
                'category_id' => $details['category_id'] ?? null,
                'brand' => $item->brand_name ?? '-',
                'brand_id' => $details['brand_id'] ?? null,
                'stock' => $stock,
                'purchase_price' => $this->formatRupiah($details['purchase_price'] ?? 0),
                'purchase_price_value' => (float) ($details['purchase_price'] ?? 0),
                'expedition_cost' => $this->formatRupiah($details['expedition_cost'] ?? 0),
                'expedition_cost_value' => (float) ($details['expedition_cost'] ?? 0),
                'down_payment_amount' => $this->formatRupiah($details['down_payment_amount'] ?? 0),
                'down_payment_amount_value' => (float) ($details['down_payment_amount'] ?? 0),
                'selling_price' => $this->formatRupiah($details['selling_price'] ?? 0),
                'selling_price_value' => (float) ($details['selling_price'] ?? 0),
                'supplier_id' => $details['supplier_id'] ?? null,
                'supplier' => $details['supplier_name'] ?? '-',
                'supplier_name' => $details['supplier_name'] ?? '',
                'supplier_branch' => $details['supplier_branch'] ?? '',
                'supplier_phone' => $details['supplier_phone'] ?? '',
                'supplier_address' => $details['supplier_address'] ?? '',
                'supplier_note' => $details['supplier_note'] ?? '',
                'batch_id' => $details['batch_id'] ?? null,
                'batch_code' => $details['batch_code'] ?? null,
                'condition' => $details['condition'] ?? null,
                'processed_by' => $details['processed_by'] ?? null,
                'payment_type' => $details['payment_type'] ?? 'LUNAS',
                'credit_days' => $details['credit_days'] ?? null,
                'credit_due_date' => $details['credit_due_date'] ?? null,
                'expired_at' => $details['expired_at'] ?? null,
                'description' => $details['description'] ?? null,
                'image_url' => $details['image_url'] ?? null,
                'is_active' => (bool) ($details['is_active'] ?? true),
            ];
        })->toArray();

        $from = $total > 0 ? (($page - 1) * $this->perPage) + 1 : 0;
        $to = min($page * $this->perPage, $total);

        return [
            'items' => $mappedItems,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $this->perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
                'has_prev' => $page > 1,
                'has_next' => $page < $lastPage,
                'prev_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $lastPage ? $page + 1 : null,
            ],
        ];
    }

    private function getProductDetails(int $productId): array
    {
        $product = Product::query()
            ->with([
                'latestBatch.supplier',
            ])
            ->find($productId);

        if (! $product) {
            return [];
        }

        $batch = $product->latestBatch;

        return [
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'description' => $product->description,
            'unit' => $product->unit,
            'weight' => $product->weight,
            'weight_unit' => $product->weight_unit ?: 'kg',
            'created_at' => $product->created_at?->format('d M Y H:i'),
            'image_url' => $product->image_path ? '/storage/' . ltrim($product->image_path, '/') : null,
            'is_active' => $product->is_active,
            'stock' => (int) ($batch?->stock ?? 0),
            'purchase_price' => (float) ($batch?->purchase_price ?? 0),
            'expedition_cost' => (float) ($batch?->expedition_cost ?? 0),
            'down_payment_amount' => (float) ($batch?->down_payment_amount ?? 0),
            'selling_price' => (float) ($batch?->selling_price ?? 0),
            'supplier_id' => $batch?->supplier_id,
            'supplier_name' => $batch?->supplier?->name,
            'supplier_branch' => $batch?->supplier?->branch,
            'supplier_phone' => $batch?->supplier?->phone,
            'supplier_address' => $batch?->supplier?->address,
            'supplier_note' => $batch?->supplier?->note,
            'batch_id' => $batch?->id,
            'batch_code' => $batch?->batch_code,
            'condition' => $batch?->condition,
            'processed_by' => $batch?->processed_by,
            'payment_type' => $batch?->payment_type ?? 'LUNAS',
            'credit_days' => $batch?->credit_days,
            'credit_due_date' => $batch?->credit_due_date?->toDateString(),
            'expired_at' => $batch?->expired_at?->toDateString(),
        ];
    }

    private function getCategories(): array
    {
        if (! Schema::hasTable('categories')) {
            return ['Elektronik', 'Peralatan Rumah', 'Pakaian', 'Makanan & Minuman'];
        }

        $nameColumn = Schema::hasColumn('categories', 'name') ? 'name' : 'nama';

        return DB::table('categories')
            ->when(Schema::hasColumn('categories', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->limit(10)
            ->pluck($nameColumn)
            ->toArray();
    }

    private function getCategoryOptions(): array
    {
        if (! Schema::hasTable('categories')) {
            return [];
        }

        $nameColumn = Schema::hasColumn('categories', 'name') ? 'name' : 'nama';

        return DB::table('categories')
            ->select(['id', "{$nameColumn} as name"])
            ->when(Schema::hasColumn('categories', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->orderBy($nameColumn)
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'name' => $item->name,
            ])
            ->toArray();
    }

    private function getBrands(): array
    {
        if (! Schema::hasTable('brands')) {
            return ['Sony', 'Samsung', 'Apple', 'Logitech'];
        }

        $nameColumn = Schema::hasColumn('brands', 'name') ? 'name' : 'nama';

        return DB::table('brands')
            ->when(Schema::hasColumn('brands', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->limit(10)
            ->pluck($nameColumn)
            ->toArray();
    }

    private function getBrandOptions(): array
    {
        if (! Schema::hasTable('brands')) {
            return [];
        }

        $nameColumn = Schema::hasColumn('brands', 'name') ? 'name' : 'nama';

        return DB::table('brands')
            ->select(['id', "{$nameColumn} as name"])
            ->when(Schema::hasColumn('brands', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->orderBy($nameColumn)
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'name' => $item->name,
            ])
            ->toArray();
    }

    private function getSupplierOptions(): array
    {
        if (! Schema::hasTable('suppliers')) {
            return [];
        }

        $nameColumn = Schema::hasColumn('suppliers', 'name') ? 'name' : 'nama';

        return DB::table('suppliers')
            ->select(['id', "{$nameColumn} as name"])
            ->when(Schema::hasColumn('suppliers', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->orderBy($nameColumn)
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'name' => $item->name,
            ])
            ->toArray();
    }

    private function formatRupiah(int|float|null $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    public function deleteProduct(int $id): void
    {
        if (Schema::hasTable('products')) {
            $product = Product::query()->find($id);
            $deleted = false;

            if ($product) {
                $deleted = (bool) $product->delete();
            }

            if ($deleted) {
                Notification::make()
                    ->title('Produk berhasil dihapus')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Produk tidak ditemukan')
                    ->danger()
                    ->send();
            }
        }
    }

    public function createOfflineBackup(OfflineBackupService $backupService): void
    {
        try {
            $result = $backupService->createBackup();
            $notePdfCount = (int) ($result['note_pdf_count'] ?? 0);

            Notification::make()
                ->title('Backup Excel berhasil dibuat')
                ->body(
                    'Disimpan di Desktop: ' . $result['folder_path']
                    . ($notePdfCount > 0
                        ? " | {$notePdfCount} PDF nota tersimpan di folder nota-pdf"
                        : '')
                )
                ->success()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Backup gagal dibuat')
                ->body('Silakan cek log Laravel untuk detail error.')
                ->danger()
                ->send();
        }
    }

    public function updateSearch(string $search): void
    {
        $this->search = $search;
    }

    public function updateCategoryFilter(string $category): void
    {
        $this->categoryFilter = $category;
    }

    public function updateBrandFilter(string $brand): void
    {
        $this->brandFilter = $brand;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->brandFilter = '';
    }
}

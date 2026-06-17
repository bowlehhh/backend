<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDashboardProductRequest;
use App\Http\Requests\Admin\UpdateDashboardProductRequest;
use App\Models\AdminActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Support\AdminBesarCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminDashboardProductController extends Controller
{
    public function store(StoreDashboardProductRequest $request): JsonResponse
    {
        $product = DB::transaction(function () use ($request): Product {
            $payload = $request->validated();
            $category = $this->resolveCategory($payload);
            $brand = $this->resolveBrand($payload);
            $supplier = $this->resolveSupplier($payload);
            $name = trim((string) ($payload['name'] ?? '')) ?: 'Barang Baru';
            $purchasePrice = (float) ($payload['purchase_price'] ?? 0);
            $sellingPrice = (float) ($payload['selling_price'] ?? 0);
            $stock = (int) ($payload['stock'] ?? 0);

            $product = Product::create([
                'category_id' => $category?->id,
                'brand_id' => $brand->id,
                'name' => $name,
                'slug' => $this->makeUniqueSlug(($payload['slug'] ?? '') ?: $name),
                'barcode' => ($payload['barcode'] ?? '') ?: null,
                'unit' => ($payload['unit'] ?? '') ?: null,
                'weight' => ($payload['weight'] ?? null) !== null ? (float) $payload['weight'] : null,
                'weight_unit' => Schema::hasColumn('products', 'weight_unit') ? $this->resolveWeightUnit($payload) : null,
                'description' => ($payload['description'] ?? '') ?: null,
                'image_path' => $this->storeProductImage($request),
                'is_active' => (bool) $payload['is_active'],
            ]);

            $paymentType = strtoupper((string) ($payload['payment_type'] ?? 'LUNAS'));
            if (! in_array($paymentType, ['LUNAS', 'KREDIT'], true)) {
                $paymentType = 'LUNAS';
            }

            $creditDays = $paymentType === 'KREDIT' ? (int) ($payload['credit_days'] ?? 0) : null;
            $creditDueDate = $paymentType === 'KREDIT' ? ($payload['credit_due_date'] ?: null) : null;
            $totalPurchase = ($purchasePrice * $stock) + ((float) ($payload['expedition_cost'] ?? 0));
            $downPaymentAmount = $paymentType === 'KREDIT'
                ? min($totalPurchase, max(0, (float) ($payload['down_payment_amount'] ?? 0)))
                : 0.0;

            $batch = ProductBatch::create([
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'batch_code' => ($payload['batch_code'] ?? '') ?: $this->generateBatchCode($product),
                'supplier_invoice_number' => Schema::hasColumn('product_batches', 'supplier_invoice_number')
                    ? (($payload['supplier_invoice_number'] ?? '') ?: null)
                    : null,
                'condition' => ($payload['condition'] ?? '') ?: null,
                'processed_by' => Schema::hasColumn('product_batches', 'processed_by')
                    ? ($this->resolveProcessedBy($payload, $request->user()?->name) ?: null)
                    : null,
                'purchase_price' => $purchasePrice,
                'expedition_cost' => (float) ($payload['expedition_cost'] ?? 0),
                'down_payment_amount' => $downPaymentAmount,
                'selling_price' => $sellingPrice,
                'stock' => $stock,
                'payment_type' => $paymentType,
                'credit_days' => $creditDays,
                'credit_due_date' => $creditDueDate,
                'expired_at' => ($payload['expired_at'] ?? '') ?: null,
                'is_active' => true,
            ]);

            if ($stock > 0) {
                StockHistory::create([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'user_id' => $request->user()->id,
                    'type' => StockHistory::TYPE_IN,
                    'qty' => $stock,
                    'stock_before' => 0,
                    'stock_after' => $stock,
                    'reference' => $batch->display_inventory_code,
                    'description' => 'Initial stock added from admin dashboard.',
                ]);
            }

            AdminActivityLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'product_created',
                'subject_type' => Product::class,
                'subject_id' => $product->id,
                'title' => 'Tambah Barang',
                'description' => 'Menambahkan barang baru dan stok awal dari dashboard admin.',
                'meta' => [
                    'product_name' => $product->name,
                    'batch_code' => $batch->display_inventory_code,
                    'supplier_name' => $supplier->name,
                    'stock' => $stock,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                ],
            ]);

            return $product->fresh(['category', 'brand', 'latestBatch.supplier']);
        });

        AdminBesarCache::forgetToday();

        return response()->json([
            'message' => 'Barang berhasil ditambahkan.',
            'product' => $this->makeProductPayload($product),
        ], JsonResponse::HTTP_CREATED);
    }

    public function update(UpdateDashboardProductRequest $request, Product $product): JsonResponse
    {
        $product = DB::transaction(function () use ($request, $product): Product {
            $payload = $request->validated();
            $supplier = $this->resolveSupplier($payload);

            $product->update([
                'category_id' => $this->resolveCategory($payload)?->id,
                'brand_id' => $this->resolveBrand($payload)?->id ?? $product->brand_id,
                'name' => trim((string) ($payload['name'] ?? '')) ?: $product->name,
                'slug' => $this->makeUniqueSlug(($payload['slug'] ?? '') ?: (trim((string) ($payload['name'] ?? '')) ?: $product->name), $product->id),
                'barcode' => ($payload['barcode'] ?? '') ?: null,
                'unit' => ($payload['unit'] ?? '') ?: null,
                'weight' => ($payload['weight'] ?? null) !== null ? (float) $payload['weight'] : null,
                'weight_unit' => Schema::hasColumn('products', 'weight_unit') ? $this->resolveWeightUnit($payload) : null,
                'description' => ($payload['description'] ?? '') ?: null,
                'is_active' => (bool) $payload['is_active'],
            ]);

            if ($request->hasFile('image')) {
                $newImagePath = $this->storeProductImage($request);

                if ($newImagePath) {
                    if (! empty($product->image_path)) {
                        Storage::disk('public')->delete($product->image_path);
                    }

                    $product->update(['image_path' => $newImagePath]);
                }
            }

            $batch = $product->latestBatch()->first();

            if (! $batch) {
                $batch = new ProductBatch([
                    'product_id' => $product->id,
                    'stock' => 0,
                    'is_active' => true,
                ]);
            }

            $stockBefore = (int) $batch->stock;
            $stockAfter = (int) ($payload['stock'] ?? 0);
            $paymentType = strtoupper((string) ($payload['payment_type'] ?? 'LUNAS'));
            if (! in_array($paymentType, ['LUNAS', 'KREDIT'], true)) {
                $paymentType = 'LUNAS';
            }
            $creditDays = $paymentType === 'KREDIT' ? (int) ($payload['credit_days'] ?? 0) : null;
            $creditDueDate = $paymentType === 'KREDIT' ? ($payload['credit_due_date'] ?: null) : null;
            $totalPurchase = ((float) ($payload['purchase_price'] ?? 0)) * $stockAfter + ((float) ($payload['expedition_cost'] ?? 0));
            $downPaymentAmount = $paymentType === 'KREDIT'
                ? min($totalPurchase, max(0, (float) ($payload['down_payment_amount'] ?? 0)))
                : 0.0;

            $batch->fill([
                'supplier_id' => $supplier->id,
                'batch_code' => ($payload['batch_code'] ?? '') ?: ($batch->batch_code ?: $this->generateBatchCode($product)),
                'supplier_invoice_number' => Schema::hasColumn('product_batches', 'supplier_invoice_number')
                    ? (($payload['supplier_invoice_number'] ?? '') ?: null)
                    : null,
                'condition' => ($payload['condition'] ?? '') ?: null,
                'processed_by' => Schema::hasColumn('product_batches', 'processed_by')
                    ? ($this->resolveProcessedBy($payload, $request->user()?->name) ?: null)
                    : null,
                'purchase_price' => (float) ($payload['purchase_price'] ?? 0),
                'expedition_cost' => $payload['expedition_cost'] ?? 0,
                'down_payment_amount' => $downPaymentAmount,
                'selling_price' => (float) ($payload['selling_price'] ?? 0),
                'stock' => $stockAfter,
                'payment_type' => $paymentType,
                'credit_days' => $creditDays,
                'credit_due_date' => $creditDueDate,
                'expired_at' => ($payload['expired_at'] ?? '') ?: null,
                'is_active' => true,
            ]);
            $batch->product_id = $product->id;
            $batch->save();

            if ($stockBefore !== $stockAfter) {
                StockHistory::create([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'user_id' => $request->user()->id,
                    'type' => StockHistory::TYPE_ADJUST,
                    'qty' => abs($stockAfter - $stockBefore),
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference' => $batch->display_inventory_code,
                    'description' => 'Stock adjusted from admin dashboard.',
                ]);
            }

            AdminActivityLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'product_updated',
                'subject_type' => Product::class,
                'subject_id' => $product->id,
                'title' => 'Edit Barang',
                'description' => 'Memperbarui data barang dari dashboard admin.',
                'meta' => [
                    'product_name' => $product->name,
                    'batch_code' => $batch->display_inventory_code,
                    'supplier_name' => $supplier->name,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'purchase_price' => (float) $payload['purchase_price'],
                    'selling_price' => (float) $payload['selling_price'],
                ],
            ]);

            return $product->fresh(['category', 'brand', 'latestBatch.supplier']);
        });

        AdminBesarCache::forgetToday();

        return response()->json([
            'message' => 'Barang berhasil diperbarui.',
            'product' => $this->makeProductPayload($product),
        ]);
    }

    private function makeUniqueSlug(string $name, ?int $ignoreProductId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'barang';
        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::query()
                ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function generateBatchCode(Product $product): string
    {
        return sprintf('INV-%s-%04d', now()->format('YmdHis'), $product->id);
    }

    private function makeProductPayload(Product $product): array
    {
        $batch = $product->latestBatch;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'created_at' => $product->created_at?->format('d M Y H:i'),
            'sku' => $product->barcode ?: 'SKU-' . str_pad((string) $product->id, 4, '0', STR_PAD_LEFT),
            'barcode' => $product->barcode,
            'unit' => $product->unit,
            'weight' => $product->weight,
            'weight_unit' => $product->weight_unit ?: 'kg',
            'description' => $product->description,
            'image_url' => $product->image_path ? '/storage/' . ltrim($product->image_path, '/') : null,
            'category' => $product->category?->name ?? '-',
            'category_id' => $product->category_id,
            'brand' => $product->brand?->name ?? '-',
            'brand_id' => $product->brand_id,
            'stock' => (int) ($batch?->stock ?? 0),
            'purchase_price' => $this->formatRupiah($batch?->purchase_price),
            'purchase_price_value' => (float) ($batch?->purchase_price ?? 0),
            'expedition_cost' => $this->formatRupiah($batch?->expedition_cost),
            'expedition_cost_value' => (float) ($batch?->expedition_cost ?? 0),
            'down_payment_amount' => $this->formatRupiah($batch?->down_payment_amount),
            'down_payment_amount_value' => (float) ($batch?->down_payment_amount ?? 0),
            'selling_price' => $this->formatRupiah($batch?->selling_price),
            'selling_price_value' => (float) ($batch?->selling_price ?? 0),
            'supplier_id' => $batch?->supplier_id,
            'supplier_name' => $batch?->supplier?->name ?? '-',
            'supplier_branch' => $batch?->supplier?->branch,
            'supplier_phone' => $batch?->supplier?->phone,
            'supplier_address' => $batch?->supplier?->address,
            'supplier_note' => $batch?->supplier?->note,
            'batch_id' => $batch?->id,
            'batch_code' => $batch?->display_inventory_code,
            'supplier_invoice_number' => $batch?->supplier_invoice_number,
            'condition' => $batch?->condition,
            'processed_by' => $batch?->processed_by,
            'payment_type' => $batch?->payment_type ?? 'LUNAS',
            'credit_days' => $batch?->credit_days,
            'credit_due_date' => $batch?->credit_due_date?->toDateString(),
            'expired_at' => $batch?->expired_at?->toDateString(),
            'is_active' => (bool) $product->is_active,
        ];
    }

    private function formatRupiah(int|float|string|null $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function resolveWeightUnit(array $payload): ?string
    {
        $unit = strtolower(trim((string) ($payload['weight_unit'] ?? 'kg')));

        if ($unit === '' || $unit === 'kg' || $unit === 'gram' || $unit === 'ton' || $unit === 'lb' || $unit === 'oz') {
            return $unit === '' ? 'kg' : $unit;
        }

        if ($unit === 'other') {
            $custom = strtolower(trim((string) ($payload['weight_unit_custom'] ?? '')));

            return $custom !== '' ? $custom : 'kg';
        }

        return $unit;
    }

    private function resolveProcessedBy(array $payload, ?string $fallbackName = null): string
    {
        $name = trim((string) ($payload['processed_by'] ?? ''));

        if ($name === '') {
            $name = trim((string) ($fallbackName ?? ''));
        }

        return $name !== '' ? $name : 'Admin POS';
    }

    private function storeProductImage(StoreDashboardProductRequest|UpdateDashboardProductRequest $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $disk = Storage::disk('public');
        $disk->makeDirectory('products');

        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
        $fileName = 'product-' . now()->format('YmdHis') . '-' . Str::random(10) . '.' . strtolower($extension);

        return $file->storeAs('products', $fileName, 'public');
    }

    private function resolveCategory(array $payload): Category
    {
        if (! empty($payload['category_id'])) {
            $category = Category::query()->find((int) $payload['category_id']);

            if ($category) {
                return $category;
            }
        }

        $name = trim((string) ($payload['category'] ?? ''));

        if ($name === '') {
            $name = 'Tanpa Kategori';
        }

        return $this->resolveNamedModel(Category::class, $name);
    }

    private function resolveBrand(array $payload): Brand
    {
        if (! empty($payload['brand_id'])) {
            $brand = Brand::query()->find((int) $payload['brand_id']);

            if ($brand) {
                return $brand;
            }
        }

        $name = trim((string) ($payload['brand'] ?? ''));

        if ($name === '') {
            $name = 'Tanpa Merek';
        }

        return $this->resolveNamedModel(Brand::class, $name);
    }

    private function resolveSupplier(array $payload): Supplier
    {
        $name = trim((string) ($payload['supplier_name'] ?? ''));
        if ($name === '') {
            $name = 'Tanpa Supplier';
        }
        $normalized = $this->normalizeSupplierKey($name);

        $supplier = null;

        if (! empty($payload['supplier_id'])) {
            $supplier = Supplier::query()
                ->withTrashed()
                ->find((int) $payload['supplier_id']);
        }

        if (! $supplier) {
            $supplier = Supplier::query()
                ->withTrashed()
                ->get()
                ->first(fn (Supplier $candidate) => $this->normalizeSupplierKey((string) $candidate->name) === $normalized);
        }

        if ($supplier) {
            if (method_exists($supplier, 'trashed') && $supplier->trashed()) {
                $supplier->restore();
            }

            $updates = [];
            if (Schema::hasColumn('suppliers', 'branch')) {
                $updates['branch'] = $payload['supplier_branch'] ?? $supplier->branch;
            }
            $updates['phone'] = $payload['supplier_phone'] ?? $supplier->phone;
            $updates['address'] = $payload['supplier_address'] ?? $supplier->address;
            if (Schema::hasColumn('suppliers', 'note')) {
                $updates['note'] = $payload['supplier_note'] ?? $supplier->note;
            }
            $supplier->update($updates);

            return $supplier;
        }

        $data = [
            'name' => $name,
            'phone' => $payload['supplier_phone'] ?? null,
            'email' => null,
            'address' => $payload['supplier_address'] ?? null,
            'is_active' => true,
        ];

        if (Schema::hasColumn('suppliers', 'branch')) {
            $data['branch'] = $payload['supplier_branch'] ?? null;
        }

        if (Schema::hasColumn('suppliers', 'note')) {
            $data['note'] = $payload['supplier_note'] ?? null;
        }

        return Supplier::create($data);
    }

    private function normalizeSupplierKey(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @param class-string<TModel> $modelClass
     * @return TModel
     */
    private function resolveNamedModel(string $modelClass, string $name, bool $hasSlug = true)
    {
        $normalized = Str::lower(trim($name));
        $query = $modelClass::query();

        $model = $query
            ->withTrashed()
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->first();

        if ($model) {
            if (method_exists($model, 'trashed') && $model->trashed()) {
                $model->restore();
            }

            return $model;
        }

        $data = [
            'name' => trim($name),
            'is_active' => true,
        ];

        if ($hasSlug) {
            $baseSlug = Str::slug($name) ?: 'item';
            $slug = $baseSlug;
            $counter = 1;

            while (
                $query->withTrashed()
                    ->where('slug', $slug)
                    ->exists()
            ) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $data['slug'] = $slug;
        }

        return $query->create($data);
    }
}

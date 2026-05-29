<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDashboardProductRequest;
use App\Http\Requests\Admin\UpdateDashboardProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockHistory;
use App\Models\Supplier;
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
            $category = $this->resolveCategory($payload['category']);
            $brand = $this->resolveBrand($payload['brand']);
            $supplier = $this->resolveSupplier($payload);

            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $payload['name'],
                'slug' => $this->makeUniqueSlug($payload['slug'] ?: $payload['name']),
                'barcode' => $payload['barcode'] ?: null,
                'description' => $payload['description'] ?: null,
                'image_path' => $this->storeProductImage($request),
                'is_active' => (bool) $payload['is_active'],
            ]);

            $batch = ProductBatch::create([
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'batch_code' => $payload['batch_code'] ?: $this->generateBatchCode($product),
                'purchase_price' => $payload['purchase_price'],
                'selling_price' => $payload['selling_price'],
                'stock' => $payload['stock'],
                'expired_at' => $payload['expired_at'] ?: null,
                'is_active' => true,
            ]);

            if ((int) $payload['stock'] > 0) {
                StockHistory::create([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'user_id' => $request->user()->id,
                    'type' => StockHistory::TYPE_IN,
                    'qty' => (int) $payload['stock'],
                    'stock_before' => 0,
                    'stock_after' => (int) $payload['stock'],
                    'reference' => $batch->batch_code,
                    'description' => 'Initial stock added from admin dashboard.',
                ]);
            }

            return $product->fresh(['category', 'brand', 'latestBatch.supplier']);
        });

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
                // Kategori & brand dikunci di edit produk dashboard.
                // Perubahan kategori/brand hanya boleh lewat modul Kategori & Brand.
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'name' => $payload['name'],
                'slug' => $this->makeUniqueSlug($payload['slug'] ?: $payload['name'], $product->id),
                'barcode' => $payload['barcode'] ?: null,
                'description' => $payload['description'] ?: null,
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
            $stockAfter = (int) $payload['stock'];

            $batch->fill([
                'supplier_id' => $supplier->id,
                'batch_code' => $payload['batch_code'] ?: ($batch->batch_code ?: $this->generateBatchCode($product)),
                'purchase_price' => $payload['purchase_price'],
                'selling_price' => $payload['selling_price'],
                'stock' => $stockAfter,
                'expired_at' => $payload['expired_at'] ?: null,
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
                    'reference' => $batch->batch_code,
                    'description' => 'Stock adjusted from admin dashboard.',
                ]);
            }

            return $product->fresh(['category', 'brand', 'latestBatch.supplier']);
        });

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
        return sprintf('BATCH-%s-%04d', now()->format('YmdHis'), $product->id);
    }

    private function makeProductPayload(Product $product): array
    {
        $batch = $product->latestBatch;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->barcode ?: 'SKU-' . str_pad((string) $product->id, 4, '0', STR_PAD_LEFT),
            'barcode' => $product->barcode,
            'description' => $product->description,
            'image_url' => $product->image_path ? '/storage/' . ltrim($product->image_path, '/') : null,
            'category' => $product->category?->name ?? '-',
            'category_id' => $product->category_id,
            'brand' => $product->brand?->name ?? '-',
            'brand_id' => $product->brand_id,
            'stock' => (int) ($batch?->stock ?? 0),
            'purchase_price' => $this->formatRupiah($batch?->purchase_price),
            'purchase_price_value' => (float) ($batch?->purchase_price ?? 0),
            'selling_price' => $this->formatRupiah($batch?->selling_price),
            'selling_price_value' => (float) ($batch?->selling_price ?? 0),
            'supplier_id' => $batch?->supplier_id,
            'supplier_name' => $batch?->supplier?->name ?? '-',
            'supplier_branch' => $batch?->supplier?->branch,
            'supplier_phone' => $batch?->supplier?->phone,
            'supplier_address' => $batch?->supplier?->address,
            'supplier_note' => $batch?->supplier?->note,
            'batch_id' => $batch?->id,
            'batch_code' => $batch?->batch_code,
            'expired_at' => $batch?->expired_at?->toDateString(),
            'is_active' => (bool) $product->is_active,
        ];
    }

    private function formatRupiah(int|float|string|null $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
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

    private function resolveCategory(string $name): Category
    {
        return $this->resolveNamedModel(Category::class, $name);
    }

    private function resolveBrand(string $name): Brand
    {
        return $this->resolveNamedModel(Brand::class, $name);
    }

    private function resolveSupplier(array $payload): Supplier
    {
        $name = trim($payload['supplier_name']);
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
                ->whereRaw("REPLACE(REPLACE(REPLACE(LOWER(name), ' ', ''), '.', ''), ',', '') = ?", [$normalized])
                ->first();
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
            ->replaceMatches('/[\s\.,]+/', '')
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

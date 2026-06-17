<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Product $record */
        $category = $this->resolveCategory($data['category_name'] ?? '');
        $brand = $this->resolveBrand($data['brand_name'] ?? '');

        $record->update([
            'name' => $data['name'],
            'slug' => $this->makeUniqueProductSlug($data['slug'] ?: $data['name'], $record->id),
            'barcode' => $data['barcode'] ?: null,
            'description' => $data['description'] ?: null,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $batch = $record->latestBatch()->first();

        if (! $batch) {
            $batch = new ProductBatch([
                'product_id' => $record->id,
                'batch_code' => sprintf('INV-%s-%04d', now()->format('YmdHis'), $record->id),
                'purchase_price' => 0,
                'selling_price' => 0,
                'stock' => 0,
                'is_active' => true,
            ]);
        }

        // On edit barang, keep the current supplier relation unless supplier data is explicitly provided.
        if (! empty($data['supplier_name'])) {
            $supplier = $this->resolveSupplier($data);
            $batch->supplier_id = $supplier->id;
        } elseif (! $batch->supplier_id) {
            $batch->supplier_id = Supplier::query()->value('id');
        }
        $batch->product_id = $record->id;
        $batch->stock = (int) ($data['stock'] ?? 0);
        $batch->save();

        return $record;
    }

    private function resolveCategory(string $name): Category
    {
        return $this->resolveNamedModel(Category::class, $name);
    }

    private function resolveBrand(string $name): Brand
    {
        return $this->resolveNamedModel(Brand::class, $name);
    }

    private function resolveSupplier(array $data): Supplier
    {
        $name = trim((string) ($data['supplier_name'] ?? ''));
        $normalized = $this->normalizeSupplierKey($name);

        $supplier = Supplier::query()
            ->withTrashed()
            ->whereRaw("REPLACE(REPLACE(REPLACE(LOWER(name), ' ', ''), '.', ''), ',', '') = ?", [$normalized])
            ->first();

        if ($supplier) {
            if (method_exists($supplier, 'trashed') && $supplier->trashed()) {
                $supplier->restore();
            }

            $updates = [
                'phone' => Arr::get($data, 'supplier_phone') ?? $supplier->phone,
                'address' => Arr::get($data, 'supplier_address') ?? $supplier->address,
            ];
            if (Schema::hasColumn('suppliers', 'branch')) {
                $updates['branch'] = Arr::get($data, 'supplier_branch') ?: Arr::get($data, 'name') ?: $supplier->branch;
            }
            if (Schema::hasColumn('suppliers', 'note')) {
                $updates['note'] = Arr::get($data, 'supplier_note') ?? $supplier->note;
            }
            $supplier->update($updates);

            return $supplier;
        }

        $supplierData = [
            'name' => $name,
            'phone' => Arr::get($data, 'supplier_phone'),
            'address' => Arr::get($data, 'supplier_address'),
            'is_active' => true,
        ];

        if (Schema::hasColumn('suppliers', 'branch')) {
            $supplierData['branch'] = Arr::get($data, 'supplier_branch') ?: Arr::get($data, 'name');
        }

        if (Schema::hasColumn('suppliers', 'note')) {
            $supplierData['note'] = Arr::get($data, 'supplier_note');
        }

        return Supplier::create($supplierData);
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
    private function resolveNamedModel(string $modelClass, string $name)
    {
        $trimmed = trim($name);
        $normalized = Str::lower($trimmed);
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

        $baseSlug = Str::slug($trimmed) ?: 'item';
        $slug = $baseSlug;
        $counter = 1;

        while ($query->withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $query->create([
            'name' => $trimmed,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeUniqueProductSlug(string $name, int $ignoreId): string
    {
        $baseSlug = Str::slug($name) ?: 'barang';
        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::query()
                ->where('id', '!=', $ignoreId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

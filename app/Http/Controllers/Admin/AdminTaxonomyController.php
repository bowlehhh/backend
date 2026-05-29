<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTaxonomyController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'category_name' => ['required', 'string', 'max:255'],
            'category_slug' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['required', 'string', 'max:255'],
            'brand_slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = (bool) ($payload['is_active'] ?? true);

        $this->resolveOrCreateCategory(
            trim($payload['category_name']),
            trim((string) ($payload['category_slug'] ?? '')),
            $isActive,
        );

        $this->resolveOrCreateBrand(
            trim($payload['brand_name']),
            trim((string) ($payload['brand_slug'] ?? '')),
            $isActive,
        );

        return redirect()->to($this->taxonomyUrl($request))->with('success', 'Kategori & brand berhasil disimpan.');
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'category_name' => ['required', 'string', 'max:255'],
            'category_slug' => ['nullable', 'string', 'max:255'],
            'brand_name' => ['required', 'string', 'max:255'],
            'brand_slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = Category::query()->findOrFail((int) $payload['category_id']);
        $brand = Brand::query()->findOrFail((int) $payload['brand_id']);
        $isActive = (bool) ($payload['is_active'] ?? true);

        $category->update([
            'name' => trim($payload['category_name']),
            'slug' => $this->makeUniqueSlug(Category::class, trim((string) ($payload['category_slug'] ?? '')), trim($payload['category_name']), $category->id),
            'is_active' => $isActive,
        ]);

        $brand->update([
            'name' => trim($payload['brand_name']),
            'slug' => $this->makeUniqueSlug(Brand::class, trim((string) ($payload['brand_slug'] ?? '')), trim($payload['brand_name']), $brand->id),
            'is_active' => $isActive,
        ]);

        return redirect()->to($this->taxonomyUrl($request))->with('success', 'Data kategori & brand berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
        ]);

        $category = Category::query()->findOrFail((int) $payload['category_id']);
        $brand = Brand::query()->findOrFail((int) $payload['brand_id']);

        $linkedCount = Product::query()
            ->where('category_id', $category->id)
            ->where('brand_id', $brand->id)
            ->count();

        if ($linkedCount > 0) {
            $category->update(['is_active' => false]);
            $brand->update(['is_active' => false]);

            return redirect()->to($this->taxonomyUrl($request))->with('success', 'Data dipakai produk, status diubah menjadi nonaktif.');
        }

        $category->delete();
        $brand->delete();

        return redirect()->to($this->taxonomyUrl($request))->with('success', 'Data kategori & brand berhasil dihapus.');
    }

    private function resolveOrCreateCategory(string $name, string $slugInput, bool $isActive): Category
    {
        $normalized = Str::lower($name);
        $category = Category::query()->withTrashed()->whereRaw('LOWER(name) = ?', [$normalized])->first();

        if ($category) {
            if (method_exists($category, 'trashed') && $category->trashed()) {
                $category->restore();
            }

            $category->update([
                'is_active' => $isActive,
                'slug' => $this->makeUniqueSlug(Category::class, $slugInput, $name, $category->id),
            ]);

            return $category;
        }

        return Category::query()->create([
            'name' => $name,
            'slug' => $this->makeUniqueSlug(Category::class, $slugInput, $name),
            'is_active' => $isActive,
        ]);
    }

    private function resolveOrCreateBrand(string $name, string $slugInput, bool $isActive): Brand
    {
        $normalized = Str::lower($name);
        $brand = Brand::query()->withTrashed()->whereRaw('LOWER(name) = ?', [$normalized])->first();

        if ($brand) {
            if (method_exists($brand, 'trashed') && $brand->trashed()) {
                $brand->restore();
            }

            $brand->update([
                'is_active' => $isActive,
                'slug' => $this->makeUniqueSlug(Brand::class, $slugInput, $name, $brand->id),
            ]);

            return $brand;
        }

        return Brand::query()->create([
            'name' => $name,
            'slug' => $this->makeUniqueSlug(Brand::class, $slugInput, $name),
            'is_active' => $isActive,
        ]);
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    private function makeUniqueSlug(string $modelClass, string $slugInput, string $fallbackName, ?int $ignoreId = null): string
    {
        $base = Str::slug($slugInput !== '' ? $slugInput : $fallbackName) ?: 'item';
        $slug = $base;
        $counter = 1;

        while (
            $modelClass::query()
                ->withTrashed()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function taxonomyUrl(Request $request): string
    {
        $query = [
            'type' => 'taxonomy',
            'q' => (string) $request->input('q', ''),
            'sort' => (string) $request->input('sort', 'category'),
            'dir' => (string) $request->input('dir', 'asc'),
            'page' => (string) $request->input('page', '1'),
        ];

        return url('/admin/admin-module?' . http_build_query(array_filter($query, fn ($value) => $value !== '')));
    }
}

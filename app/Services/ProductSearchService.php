<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductSearchService
{
    public function search(?string $term, int $limit = 20): Collection
    {
        $keywords = collect(preg_split('/\s+/', trim((string) $term)) ?: [])
            ->filter()
            ->values();

        return Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'batches' => fn ($query) => $query
                    ->with('supplier:id,name')
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('expired_at')
                    ->orderBy('id'),
            ])
            ->where('is_active', true)
            ->whereHas('batches', fn (Builder $query) => $query
                ->where('is_active', true)
                ->where('stock', '>', 0))
            ->when($keywords->isNotEmpty(), function (Builder $query) use ($keywords): void {
                $keywords->each(function (string $keyword) use ($query): void {
                    $query->where(function (Builder $nested) use ($keyword): void {
                        $nested
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('barcode', 'like', "%{$keyword}%")
                            ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', "%{$keyword}%"));
                    });
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}

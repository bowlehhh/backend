<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'barcode',
        'unit',
        'weight',
        'weight_unit',
        'description',
        'image_path',
        'is_active',
    ];

    protected $appends = [
        'total_stock',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight' => 'decimal:2',
        ];
    }

    protected function barcode(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->normalizeBarcode($value),
            set: fn (?string $value) => $this->normalizeBarcode($value),
        );
    }

    protected function unit(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->normalizeUnit($value),
            set: fn (?string $value) => $this->normalizeUnit($value),
        );
    }

    protected function weightUnit(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->normalizeWeightUnit($value),
            set: fn (?string $value) => $this->normalizeWeightUnit($value),
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            if ($product->isForceDeleting()) {
                $product->batches()->withTrashed()->get()->each->forceDelete();

                return;
            }

            $product->batches()->delete();
        });

        static::restoring(function (Product $product): void {
            $product->batches()->withTrashed()->restore();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function latestBatch(): HasOne
    {
        return $this->hasOne(ProductBatch::class)->latestOfMany();
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('batches')) {
            return (int) $this->batches
                ->where('is_active', true)
                ->sum('stock');
        }

        return (int) $this->batches()
            ->where('is_active', true)
            ->sum('stock');
    }

    private function normalizeBarcode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeUnit(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeWeightUnit(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));

        return $normalized === '' ? null : $normalized;
    }
}

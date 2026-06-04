<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ProductBatch;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sale_item_id',
        'product_id',
        'product_batch_id',
        'product_name',
        'price',
        'qty_sold',
        'qty_return',
        'subtotal_return',
        'replacement_product_id',
        'replacement_batch_id',
        'replacement_qty',
        'replacement_price',
        'replacement_subtotal',
        'price_difference',
        'replacement_details',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'subtotal_return' => 'decimal:2',
            'replacement_price' => 'decimal:2',
            'replacement_subtotal' => 'decimal:2',
            'price_difference' => 'decimal:2',
            'replacement_details' => 'array',
        ];
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function replacementBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'replacement_batch_id');
    }

    public function getQtyAttribute(): int
    {
        return (int) ($this->qty_return ?? 0);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->subtotal_return ?? 0);
    }

    public function getReplacementSubtotalResolvedAttribute(): float
    {
        if (is_array($this->replacement_details) && $this->replacement_details !== []) {
            return collect($this->replacement_details)
                ->sum(fn (array $detail): float => (float) ($detail['subtotal'] ?? 0));
        }

        if ($this->replacement_subtotal !== null) {
            return (float) $this->replacement_subtotal;
        }

        $replacementPrice = (float) ($this->replacement_price ?? $this->replacementBatch?->selling_price ?? 0);

        return $replacementPrice * (int) ($this->replacement_qty ?? 0);
    }

    public function getPriceDifferenceResolvedAttribute(): float
    {
        if (is_array($this->replacement_details) && $this->replacement_details !== []) {
            return $this->replacement_subtotal_resolved - (float) $this->subtotal_return;
        }

        if ($this->price_difference !== null) {
            return (float) $this->price_difference;
        }

        return $this->replacement_subtotal_resolved - (float) $this->subtotal_return;
    }

    public function getReplacementDetailsResolvedAttribute(): array
    {
        if (is_array($this->replacement_details) && $this->replacement_details !== []) {
            return $this->replacement_details;
        }

        if ($this->replacement_batch_id) {
            return [[
                'product_id' => $this->replacement_product_id,
                'batch_id' => $this->replacement_batch_id,
                'label' => $this->replacementBatch?->product?->name ?? '-',
                'qty' => (int) ($this->replacement_qty ?? 0),
                'price' => (float) ($this->replacement_price ?? $this->replacementBatch?->selling_price ?? 0),
                'subtotal' => (float) $this->replacement_subtotal_resolved,
            ]];
        }

        return [];
    }
}

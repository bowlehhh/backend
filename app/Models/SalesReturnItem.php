<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'subtotal_return' => 'decimal:2',
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

    public function getQtyAttribute(): int
    {
        return (int) ($this->qty_return ?? 0);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->subtotal_return ?? 0);
    }
}

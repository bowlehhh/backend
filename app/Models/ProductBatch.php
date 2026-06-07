<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'batch_code',
        'condition',
        'processed_by',
        'purchase_price',
        'expedition_cost',
        'down_payment_amount',
        'selling_price',
        'stock',
        'payment_type',
        'credit_days',
        'credit_due_date',
        'expired_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'expedition_cost' => 'decimal:2',
            'down_payment_amount' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'credit_days' => 'integer',
            'credit_due_date' => 'date',
            'expired_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'product_batch_id');
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class, 'product_batch_id');
    }

    public function salesReturnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'product_batch_id');
    }

    public function creditInstallments(): HasMany
    {
        return $this->hasMany(CreditInstallment::class, 'product_batch_id');
    }
}

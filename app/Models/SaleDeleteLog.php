<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDeleteLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'invoice_number',
        'deleted_by_user_id',
        'payment_method',
        'total',
        'credit_amount',
        'items_count',
        'delete_note',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'snapshot' => 'array',
        ];
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}


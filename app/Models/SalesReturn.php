<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'user_id',
        'return_number',
        'invoice_number',
        'return_type',
        'reason',
        'reason_other',
        'return_total',
        'refund_amount',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'return_total' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'returned_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    public function getTotalRefundAttribute(): float
    {
        return (float) ($this->refund_amount ?? $this->return_total ?? 0);
    }

    public function getNotesAttribute(): ?string
    {
        $note = trim((string) ($this->reason_other ?? ''));

        if ($note !== '') {
            return $note;
        }

        $reason = trim((string) ($this->reason ?? ''));

        return $reason !== '' ? $reason : null;
    }
}

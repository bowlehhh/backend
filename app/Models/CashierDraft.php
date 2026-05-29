<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'items',
        'items_count',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'items_count' => 'integer',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

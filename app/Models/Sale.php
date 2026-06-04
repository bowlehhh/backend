<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'cashier_service_name',
        'cashier_phone',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'credit_amount',
        'credit_days',
        'credit_due_date',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'credit_days' => 'integer',
            'credit_due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(SaleInstallment::class);
    }

    public function returnedItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            SalesReturnItem::class,
            SalesReturn::class,
            'sale_id',
            'sales_return_id',
            'id',
            'id',
        );
    }

    public function editLogs(): HasMany
    {
        return $this->hasMany(SaleEditLog::class);
    }

    public function deleteLogs(): HasMany
    {
        return $this->hasMany(SaleDeleteLog::class);
    }

    public function getCashierDisplayNameAttribute(): string
    {
        $serviceName = trim((string) ($this->cashier_service_name ?? ''));
        $fallbackName = trim((string) ($this->user?->name ?? ''));

        if ($serviceName === '') {
            return $fallbackName !== '' ? $fallbackName : '-';
        }

        $normalized = mb_strtolower($serviceName);
        $looksLikeCreditDays = (bool) preg_match('/^\d+\s*(hari|day|days)?$/u', $normalized) || str_contains($normalized, 'hari');

        if ($looksLikeCreditDays && $fallbackName !== '') {
            return $fallbackName;
        }

        return $serviceName;
    }
}

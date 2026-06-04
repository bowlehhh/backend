<?php

namespace App\Services;

use App\Models\Sale;

class InvoiceService
{
    public function makePayload(Sale $sale): array
    {
        $sale->loadMissing(['user:id,name', 'items']);

        return [
            'store_name' => config('app.name'),
            'invoice_number' => $sale->invoice_number,
            'transaction_date' => $sale->created_at?->format('Y-m-d H:i:s'),
            'cashier_name' => $sale->user?->name,
            'payment_method' => $sale->payment_method,
            'paid_amount' => (float) $sale->paid_amount,
            'down_payment_amount' => (float) $sale->paid_amount,
            'change_amount' => (float) $sale->change_amount,
            'total' => (float) $sale->total,
            'credit_amount' => (float) ($sale->credit_amount ?? 0),
            'remaining_credit_amount' => (float) ($sale->credit_amount ?? 0),
            'credit_due_date' => $sale->credit_due_date?->toDateString(),
            'items' => $sale->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'qty' => $item->qty,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
        ];
    }
}

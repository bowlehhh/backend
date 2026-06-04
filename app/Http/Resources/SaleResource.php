<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'cashier_service_name' => $this->cashier_service_name,
            'cashier_phone' => $this->cashier_phone,
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method,
            'paid_amount' => (float) $this->paid_amount,
            'down_payment_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'credit_amount' => (float) ($this->credit_amount ?? 0),
            'remaining_credit_amount' => (float) ($this->credit_amount ?? 0),
            'credit_days' => $this->credit_days,
            'credit_due_date' => $this->credit_due_date?->toDateString(),
            'is_credit' => strtolower((string) $this->payment_method) === 'credit',
            'created_at' => $this->created_at?->toISOString(),
            'cashier' => $this->user?->name,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_batch_id' => $item->product_batch_id,
                'product_name' => $item->product_name,
                'price' => (float) $item->price,
                'qty' => $item->qty,
                'subtotal' => (float) $item->subtotal,
            ])),
        ];
    }
}

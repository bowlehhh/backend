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
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
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

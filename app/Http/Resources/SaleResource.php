<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    private function normalizeBrandName(?string $value): string
    {
        $brand = trim((string) $value);

        if ($brand === '' || mb_strtolower($brand) === 'tanpa merek') {
            return '';
        }

        return $brand;
    }

    public function toArray(Request $request): array
    {
        $groupedItems = $this->relationLoaded('items') ? $this->items
            ->groupBy(function ($item): string {
                if ((bool) ($item->merge_stock ?? false)) {
                    return (int) ($item->product_id ?? 0) > 0
                        ? 'MERGED-PRODUCT-' . (int) $item->product_id
                        : 'MERGED-' . strtoupper(trim((string) ($item->part_number ?? $item->product?->barcode ?? 'PRODUCT-' . ($item->product_id ?? 0))));
                }

                return 'ITEM-' . (int) ($item->id ?? 0);
            })
            ->map(function ($items) {
                $first = $items->first();
                $brandName = $items
                    ->map(fn ($item): string => $this->normalizeBrandName($item->productBatch?->product?->brand?->name ?? $item->product?->brand?->name))
                    ->filter()
                    ->unique()
                    ->implode('/');
                $uniquePrices = $items
                    ->pluck('price')
                    ->map(fn ($price) => (float) $price)
                    ->unique()
                    ->values();
                $priceBreakdown = $items
                    ->groupBy(fn ($item) => number_format((float) $item->price, 2, '.', ''))
                    ->map(function ($priceItems, $priceKey) {
                        $qty = (int) $priceItems->sum('qty');
                        $price = (float) $priceKey;

                        return [
                            'price' => $price,
                            'qty' => $qty,
                            'subtotal' => $price * $qty,
                        ];
                    })
                    ->sortBy('price')
                    ->values();

                return [
                    'product_name' => (string) ($first->product_name ?: $first->product?->name ?: '-'),
                    'brand_name' => $brandName,
                    'part_number' => (string) ($first->part_number ?: $first->product?->barcode ?: '-'),
                    'unit' => (string) ($first->product?->unit ?: '-'),
                    'qty' => (int) $items->sum('qty'),
                    'price' => $uniquePrices->count() === 1 ? (float) $uniquePrices->first() : null,
                    'subtotal' => (float) $items->sum('subtotal'),
                    'has_mixed_price' => $uniquePrices->count() > 1,
                    'price_breakdown' => $priceBreakdown,
                ];
            })
            ->values() : [];

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'cashier_service_name' => $this->cashier_service_name,
            'cashier_phone' => $this->cashier_phone,
            'discount_percent' => (float) ((($groupedItems->sum('subtotal') ?: 0) > 0)
                ? (((float) ($this->discount_amount ?? 0) / (float) $groupedItems->sum('subtotal')) * 100)
                : 0),
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'subtotal_before_discount' => (float) $groupedItems->sum('subtotal'),
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
                'part_number' => $item->part_number ?? $item->product?->barcode,
                'brand_name' => $this->normalizeBrandName($item->productBatch?->product?->brand?->name ?? $item->product?->brand?->name),
                'merge_stock' => (bool) ($item->merge_stock ?? false),
                'price' => (float) $item->price,
                'qty' => $item->qty,
                'subtotal' => (float) $item->subtotal,
            ])),
            'grouped_items' => $groupedItems,
        ];
    }
}

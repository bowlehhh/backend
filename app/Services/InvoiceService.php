<?php

namespace App\Services;

use App\Models\Sale;

class InvoiceService
{
    private function normalizeBrandName(?string $value): string
    {
        $brand = trim((string) $value);

        if ($brand === '' || mb_strtolower($brand) === 'tanpa merek') {
            return '';
        }

        return $brand;
    }

    public function makePayload(Sale $sale): array
    {
        $sale->loadMissing([
            'user:id,name',
            'items.product:id,name,barcode,unit,brand_id',
            'items.product.brand:id,name',
            'items.productBatch.product.brand:id,name',
        ]);

        $groupedItems = $sale->items
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
            ->values();

        return [
            'discount_percent' => (float) (($groupedItems->sum('subtotal') ?: 0) > 0
                ? (((float) ($sale->discount_amount ?? 0) / (float) $groupedItems->sum('subtotal')) * 100)
                : 0),
            'store_name' => config('app.name'),
            'invoice_number' => $sale->invoice_number,
            'transaction_date' => $sale->created_at?->format('Y-m-d H:i:s'),
            'cashier_name' => $sale->user?->name,
            'payment_method' => $sale->payment_method,
            'paid_amount' => (float) $sale->paid_amount,
            'down_payment_amount' => (float) $sale->paid_amount,
            'change_amount' => (float) $sale->change_amount,
            'discount_amount' => (float) ($sale->discount_amount ?? 0),
            'subtotal_before_discount' => (float) $groupedItems->sum('subtotal'),
            'total' => (float) $sale->total,
            'credit_amount' => (float) ($sale->credit_amount ?? 0),
            'remaining_credit_amount' => (float) ($sale->credit_amount ?? 0),
            'credit_due_date' => $sale->credit_due_date?->toDateString(),
            'items' => $sale->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'part_number' => $item->part_number ?? $item->product?->barcode,
                'brand_name' => $this->normalizeBrandName($item->productBatch?->product?->brand?->name ?? $item->product?->brand?->name),
                'merge_stock' => (bool) ($item->merge_stock ?? false),
                'qty' => $item->qty,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
            'grouped_items' => $groupedItems,
        ];
    }
}

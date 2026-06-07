<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'category' => $this->category?->name,
            'brand' => $this->brand?->name,
            'total_stock' => $this->total_stock,
            'batches' => $this->whenLoaded('batches', fn () => $this->batches
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->values()
                ->map(fn ($batch) => [
                    'id' => $batch->id,
                    'batch_code' => $batch->batch_code,
                    'condition' => $batch->condition,
                    'selling_price' => (float) $batch->selling_price,
                    'stock' => $batch->stock,
                    'expired_at' => $batch->expired_at?->toDateString(),
                    'supplier' => $batch->supplier?->name,
                ])),
        ];
    }
}

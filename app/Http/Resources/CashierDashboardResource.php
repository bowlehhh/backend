<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashierDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'today_sales_total' => (float) ($this['today_sales_total'] ?? 0),
            'today_transactions_count' => (int) ($this['today_transactions_count'] ?? 0),
            'today_revenue_total' => (float) ($this['today_revenue_total'] ?? 0),
            'recent_transactions' => SaleResource::collection($this['recent_transactions'] ?? collect()),
        ];
    }
}

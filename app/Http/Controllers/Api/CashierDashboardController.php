<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CashierDashboardResource;
use App\Models\Sale;
use Illuminate\Http\Request;

class CashierDashboardController extends Controller
{
    public function show(Request $request): CashierDashboardResource
    {
        $todaySales = Sale::query()
            ->with('user:id,name')
            ->whereDate('created_at', today());

        $recentTransactions = (clone $todaySales)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return new CashierDashboardResource([
            'today_sales_total' => (clone $todaySales)->sum('total'),
            'today_transactions_count' => (clone $todaySales)->count(),
            'today_revenue_total' => (clone $todaySales)->sum('paid_amount'),
            'recent_transactions' => $recentTransactions,
        ]);
    }
}

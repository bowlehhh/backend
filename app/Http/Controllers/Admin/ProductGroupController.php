<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductGroupController extends Controller
{
    public function show(Request $request, Product $product): View|Response
    {
        return response()
            ->view('admin.product-group-detail', $this->buildPayload($product))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function receipt(Request $request, Product $product): View|Response
    {
        return response()
            ->view('admin.product-group-receipt', $this->buildPayload($product))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function buildPayload(Product $product): array
    {
        if (! Schema::hasTable('products')) {
            abort(404);
        }

        $product->loadMissing([
            'category:id,name',
            'brand:id,name',
            'batches' => fn ($query) => $query->with('supplier:id,name')->latest('created_at'),
            'saleItems' => fn ($query) => $query->with([
                'sale' => fn ($saleQuery) => $saleQuery
                    ->with('user:id,name')
                    ->withSum('returns as total_return_refund', 'refund_amount')
                    ->withCount('returns as return_count'),
            ])->latest('id'),
        ]);

        $purchaseRows = [];
        $salesRows = [];
        $latestTs = 0;
        $hasInstallments = Schema::hasTable('credit_installments');

        $installmentPaidMap = [];
        if ($hasInstallments) {
            $installmentPaidMap = DB::table('credit_installments')
                ->selectRaw('product_batch_id, COALESCE(SUM(amount), 0) as paid_total')
                ->whereIn('product_batch_id', $product->batches->pluck('id')->all())
                ->groupBy('product_batch_id')
                ->pluck('paid_total', 'product_batch_id')
                ->map(fn ($value) => (float) $value)
                ->toArray();
        }

        foreach ($product->batches as $batch) {
            $qty = (int) ($batch->stock ?? 0);
            $purchasePrice = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $total = ($qty * $purchasePrice) + $expeditionCost;
            $installmentPaid = (float) ($installmentPaidMap[$batch->id] ?? 0);
            $paid = min($total, $downPayment + $installmentPaid);
            $remaining = max(0, $total - $paid);
            $status = $this->resolveBatchPaymentStatus($batch, $remaining);
            $createdAt = $batch->created_at ? Carbon::parse($batch->created_at) : null;
            $ts = $createdAt?->timestamp ?? 0;

            $purchaseRows[] = [
                'batch_id' => (int) $batch->id,
                'tanggal' => $createdAt ? $createdAt->format('d M Y H:i') : '-',
                'month_key' => $createdAt?->format('Y-m') ?? 'tanpa-tanggal',
                'month_label' => $createdAt ? $this->formatMonthLabel($createdAt) : 'Tanpa Tanggal',
                'tanggal_ts' => $ts,
                'supplier' => $batch->supplier?->name ?: '-',
                'processed_by' => trim((string) ($batch->processed_by ?? '')) ?: '-',
                'condition' => trim((string) ($batch->condition ?? '')) ?: '-',
                'qty' => $qty,
                'harga_beli' => 'Rp ' . number_format($purchasePrice, 0, ',', '.'),
                'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                'total_value' => $total,
                'down_payment' => 'Rp ' . number_format($downPayment, 0, ',', '.'),
                'sisa_kredit' => 'Rp ' . number_format($remaining, 0, ',', '.'),
                'jatuh_tempo' => $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->format('d M Y') : '-',
                'status' => $status,
            ];

            if ($ts > $latestTs) {
                $latestTs = $ts;
            }
        }

        $groupedSales = $product->saleItems->groupBy('sale_id');
        foreach ($groupedSales as $saleItems) {
            $sale = $saleItems->first()?->sale;
            if (! $sale) {
                continue;
            }

            $qty = (int) $saleItems->sum('qty');
            $subtotal = (float) $saleItems->sum('subtotal');
            $paymentMethod = strtoupper((string) ($sale->payment_method ?? 'CASH'));
            $creditAmount = (float) ($sale->credit_amount ?? 0);
            $dueDate = $sale->credit_due_date ? Carbon::parse($sale->credit_due_date) : null;
            $status = $this->resolveSaleStatus($paymentMethod, $creditAmount, $dueDate);
            $createdAt = $sale->created_at ? Carbon::parse($sale->created_at) : null;
            $ts = $createdAt?->timestamp ?? 0;

            $salesRows[] = [
                'sale_id' => (int) $sale->id,
                'invoice' => (string) ($sale->invoice_number ?: '-'),
                'tanggal' => $createdAt ? $createdAt->format('d M Y H:i') : '-',
                'month_key' => $createdAt?->format('Y-m') ?? 'tanpa-tanggal',
                'month_label' => $createdAt ? $this->formatMonthLabel($createdAt) : 'Tanpa Tanggal',
                'tanggal_ts' => $ts,
                'customer' => trim((string) ($sale->customer_name ?? '')) ?: 'Pembeli Umum',
                'cashier' => trim((string) ($sale->cashierDisplayName ?? $sale->user?->name ?? '')) ?: '-',
                'payment_method' => $paymentMethod,
                'qty' => $qty,
                'total' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                'total_value' => $subtotal,
                'kredit' => 'Rp ' . number_format($creditAmount, 0, ',', '.'),
                'jatuh_tempo' => $dueDate ? $dueDate->format('d M Y') : '-',
                'status' => $status,
                'return_count' => (int) ($sale->return_count ?? 0),
                'return_value' => (float) ($sale->total_return_refund ?? 0),
            ];

            if ($ts > $latestTs) {
                $latestTs = $ts;
            }
        }

        usort($purchaseRows, fn (array $a, array $b): int => ($b['tanggal_ts'] ?? 0) <=> ($a['tanggal_ts'] ?? 0));
        usort($salesRows, fn (array $a, array $b): int => ($b['tanggal_ts'] ?? 0) <=> ($a['tanggal_ts'] ?? 0));

        $purchaseMonthGroups = collect($purchaseRows)
            ->groupBy('month_key')
            ->map(function ($rows, string $monthKey) {
                $monthRows = collect($rows)->values();

                return [
                    'month_key' => $monthKey,
                    'month_label' => (string) ($monthRows->first()['month_label'] ?? 'Tanpa Tanggal'),
                    'summary' => [
                        'count' => $monthRows->count(),
                        'value' => 'Rp ' . number_format((float) $monthRows->sum(fn (array $row) => (float) $row['total_value']), 0, ',', '.'),
                        'lunas' => $monthRows->filter(fn (array $row) => $row['status'] === 'LUNAS')->count(),
                        'utang' => $monthRows->filter(fn (array $row) => in_array($row['status'], ['BELUM LUNAS', 'JATUH TEMPO'], true))->count(),
                    ],
                    'rows' => $monthRows->map(function (array $row): array {
                        unset($row['tanggal_ts'], $row['month_key'], $row['month_label']);
                        return $row;
                    })->toArray(),
                ];
            })
            ->sortKeysDesc()
            ->values()
            ->toArray();

        $salesMonthGroups = collect($salesRows)
            ->groupBy('month_key')
            ->map(function ($rows, string $monthKey) {
                $monthRows = collect($rows)->values();

                return [
                    'month_key' => $monthKey,
                    'month_label' => (string) ($monthRows->first()['month_label'] ?? 'Tanpa Tanggal'),
                    'summary' => [
                        'count' => $monthRows->count(),
                        'value' => 'Rp ' . number_format((float) $monthRows->sum(fn (array $row) => (float) $row['total_value']), 0, ',', '.'),
                        'credit' => $monthRows->filter(fn (array $row) => in_array($row['payment_method'], ['CREDIT', 'KREDIT'], true))->count(),
                        'lunas' => $monthRows->filter(fn (array $row) => $row['status'] === 'LUNAS')->count(),
                        'retur' => $monthRows->sum(fn (array $row) => (int) ($row['return_count'] ?? 0)),
                    ],
                    'rows' => $monthRows->map(function (array $row): array {
                        unset($row['tanggal_ts'], $row['month_key'], $row['month_label']);
                        return $row;
                    })->toArray(),
                ];
            })
            ->sortKeysDesc()
            ->values()
            ->toArray();

        $purchaseSummary = [
            'count' => count($purchaseRows),
            'value' => 'Rp ' . number_format((float) array_sum(array_map(fn (array $row) => (float) $row['total_value'], $purchaseRows)), 0, ',', '.'),
            'lunas' => count(array_filter($purchaseRows, fn (array $row) => $row['status'] === 'LUNAS')),
            'utang' => count(array_filter($purchaseRows, fn (array $row) => in_array($row['status'], ['BELUM LUNAS', 'JATUH TEMPO'], true))),
        ];
        $salesSummary = [
            'count' => count($salesRows),
            'value' => 'Rp ' . number_format((float) array_sum(array_map(fn (array $row) => (float) $row['total_value'], $salesRows)), 0, ',', '.'),
            'credit' => count(array_filter($salesRows, fn (array $row) => in_array($row['payment_method'], ['CREDIT', 'KREDIT'], true))),
            'lunas' => count(array_filter($salesRows, fn (array $row) => $row['status'] === 'LUNAS')),
            'retur' => array_sum(array_map(fn (array $row) => (int) ($row['return_count'] ?? 0), $salesRows)),
        ];

        return [
            'product' => $product,
            'purchaseRows' => $purchaseRows,
            'salesRows' => $salesRows,
            'purchaseMonthGroups' => $purchaseMonthGroups,
            'salesMonthGroups' => $salesMonthGroups,
            'purchaseSummary' => $purchaseSummary,
            'salesSummary' => $salesSummary,
            'latestActivityAt' => $latestTs ? Carbon::createFromTimestamp($latestTs) : null,
            'printedAt' => now(),
        ];
    }

    private function formatMonthLabel(Carbon $date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[(int) $date->format('n')] ?? $date->format('F')) . ' ' . $date->format('Y');
    }

    private function resolveBatchPaymentStatus(ProductBatch $batch, float $remaining): string
    {
        if ($remaining <= 0) {
            return 'LUNAS';
        }

        if ($batch->credit_due_date && Carbon::parse($batch->credit_due_date)->isPast()) {
            return 'JATUH TEMPO';
        }

        return 'BELUM LUNAS';
    }

    private function resolveSaleStatus(string $paymentMethod, float $creditAmount, ?Carbon $dueDate = null): string
    {
        if ($paymentMethod !== 'CREDIT') {
            return 'LUNAS';
        }

        if ($creditAmount <= 0) {
            return 'LUNAS';
        }

        if ($dueDate && $dueDate->isPast()) {
            return 'JATUH TEMPO';
        }

        return 'BELUM LUNAS';
    }
}

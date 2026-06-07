<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductGroupReportService
{
    public function build(): array
    {
        if (! Schema::hasTable('products')) {
            return [
                'summary' => [
                    'total_products' => 0,
                    'purchase_count' => 0,
                    'sales_count' => 0,
                    'purchase_installment_count' => 0,
                    'purchase_value' => 'Rp 0',
                    'purchase_installment_value' => 'Rp 0',
                    'sales_value' => 'Rp 0',
                    'purchase_kredit' => 0,
                    'purchase_lunas' => 0,
                    'sales_kredit' => 0,
                    'sales_lunas' => 0,
                ],
                'groups' => [],
            ];
        }

        $productIds = collect();
        if (Schema::hasTable('product_batches')) {
            $productIds = $productIds->merge(DB::table('product_batches')->distinct()->pluck('product_id'));
        }
        if (Schema::hasTable('sale_items')) {
            $productIds = $productIds->merge(DB::table('sale_items')->distinct()->pluck('product_id'));
        }

        $productIds = $productIds
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return [
                'summary' => [
                    'total_products' => 0,
                    'purchase_count' => 0,
                    'sales_count' => 0,
                    'purchase_installment_count' => 0,
                    'purchase_value' => 'Rp 0',
                    'purchase_installment_value' => 'Rp 0',
                    'sales_value' => 'Rp 0',
                    'purchase_kredit' => 0,
                    'purchase_lunas' => 0,
                    'sales_kredit' => 0,
                    'sales_lunas' => 0,
                ],
                'groups' => [],
            ];
        }

        $hasInstallments = Schema::hasTable('credit_installments');
        $installmentPaidMap = [];

        if ($hasInstallments) {
            $installmentPaidMap = DB::table('credit_installments')
                ->selectRaw('product_batch_id, COALESCE(SUM(amount), 0) as paid_total')
                ->groupBy('product_batch_id')
                ->pluck('paid_total', 'product_batch_id')
                ->map(fn ($value) => (float) $value)
                ->toArray();
        }

        $products = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'batches' => fn ($query) => $query->with([
                    'supplier:id,name',
                    'creditInstallments' => fn ($installmentQuery) => $installmentQuery
                        ->with('user:id,name')
                        ->orderBy('paid_at')
                        ->orderBy('id'),
                ])->latest('created_at'),
                'saleItems' => fn ($query) => $query->with([
                    'sale' => fn ($saleQuery) => $saleQuery
                        ->with('user:id,name')
                        ->withSum('returns as total_return_refund', 'refund_amount')
                        ->withCount('returns as return_count'),
                ])->latest('id'),
            ])
            ->whereIn('id', $productIds->all())
            ->get(['id', 'name', 'barcode', 'unit', 'category_id', 'brand_id']);

        $overall = [
            'total_products' => 0,
            'purchase_count' => 0,
            'sales_count' => 0,
            'purchase_value' => 0.0,
            'purchase_installment_count' => 0,
            'purchase_installment_value' => 0.0,
            'sales_value' => 0.0,
            'purchase_kredit' => 0,
            'purchase_lunas' => 0,
            'sales_kredit' => 0,
            'sales_lunas' => 0,
        ];

        $groups = [];

        foreach ($products as $product) {
            $purchaseRows = [];
            $purchaseInstallmentRows = [];
            $salesRows = [];
            $latestTs = 0;

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

                $runningPaid = $downPayment;
                $installments = $batch->creditInstallments ?? collect();
                foreach ($installments as $index => $installment) {
                    $amount = (float) ($installment->amount ?? 0);
                    $runningPaid += $amount;
                    $remainingAfter = max(0, $total - $runningPaid);
                    $paidAt = $installment->paid_at ? Carbon::parse($installment->paid_at) : ($installment->created_at ? Carbon::parse($installment->created_at) : null);
                    $installmentTs = $paidAt?->timestamp ?? $ts;

                    $purchaseInstallmentRows[] = [
                        'batch_id' => (int) $batch->id,
                        'part_number' => strtoupper((string) ($product->barcode ?? '-')),
                        'part_name' => $product->name ?: '-',
                        'supplier' => $batch->supplier?->name ?: '-',
                        'tanggal' => $paidAt ? $paidAt->format('d M Y H:i') : '-',
                        'month_key' => $paidAt?->format('Y-m') ?? 'tanpa-tanggal',
                        'month_label' => $paidAt ? $this->formatMonthLabel($paidAt) : 'Tanpa Tanggal',
                        'tanggal_ts' => $installmentTs,
                        'nomor_cicilan' => $index + 1,
                        'nominal' => 'Rp ' . number_format($amount, 0, ',', '.'),
                        'nominal_value' => $amount,
                        'diproses_oleh' => trim((string) ($installment->processed_by ?? '')) ?: ($installment->user?->name ?? '-'),
                        'catatan' => trim((string) ($installment->note ?? '')) ?: '-',
                        'sisa_setelah_cicilan' => 'Rp ' . number_format($remainingAfter, 0, ',', '.'),
                        'status' => $remainingAfter <= 0 ? 'LUNAS' : 'BELUM LUNAS',
                    ];

                    $overall['purchase_installment_count']++;
                    $overall['purchase_installment_value'] += $amount;
                }

                $overall['purchase_count']++;
                $overall['purchase_value'] += $total;
                if ($status === 'LUNAS') {
                    $overall['purchase_lunas']++;
                } else {
                    $overall['purchase_kredit']++;
                }

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

                $overall['sales_count']++;
                $overall['sales_value'] += $subtotal;
                if ($status === 'LUNAS') {
                    $overall['sales_lunas']++;
                } else {
                    $overall['sales_kredit']++;
                }

                if ($ts > $latestTs) {
                    $latestTs = $ts;
                }
            }

            usort($purchaseRows, fn (array $a, array $b): int => ($b['tanggal_ts'] ?? 0) <=> ($a['tanggal_ts'] ?? 0));
            usort($purchaseInstallmentRows, fn (array $a, array $b): int => ($b['tanggal_ts'] ?? 0) <=> ($a['tanggal_ts'] ?? 0));
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
                            'qty' => $monthRows->sum(fn (array $row) => (int) $row['qty']),
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

            $purchaseInstallmentMonthGroups = collect($purchaseInstallmentRows)
                ->groupBy('month_key')
                ->map(function ($rows, string $monthKey) {
                    $monthRows = collect($rows)->values();

                    return [
                        'month_key' => $monthKey,
                        'month_label' => (string) ($monthRows->first()['month_label'] ?? 'Tanpa Tanggal'),
                        'summary' => [
                            'count' => $monthRows->count(),
                            'value' => 'Rp ' . number_format((float) $monthRows->sum(fn (array $row) => (float) $row['nominal_value']), 0, ',', '.'),
                            'lunas' => $monthRows->filter(fn (array $row) => $row['status'] === 'LUNAS')->count(),
                            'utang' => $monthRows->filter(fn (array $row) => $row['status'] !== 'LUNAS')->count(),
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
                    $returnCount = $monthRows->sum(fn (array $row) => (int) ($row['return_count'] ?? 0));
                    $returnValue = $monthRows->sum(fn (array $row) => (float) ($row['return_value'] ?? 0));

                    return [
                        'month_key' => $monthKey,
                        'month_label' => (string) ($monthRows->first()['month_label'] ?? 'Tanpa Tanggal'),
                        'summary' => [
                            'count' => $monthRows->count(),
                            'qty' => $monthRows->sum(fn (array $row) => (int) $row['qty']),
                            'value' => 'Rp ' . number_format((float) $monthRows->sum(fn (array $row) => (float) $row['total_value']), 0, ',', '.'),
                            'credit' => $monthRows->filter(fn (array $row) => in_array($row['payment_method'], ['CREDIT', 'KREDIT'], true))->count(),
                            'lunas' => $monthRows->filter(fn (array $row) => $row['status'] === 'LUNAS')->count(),
                            'retur' => $returnCount,
                            'retur_value' => 'Rp ' . number_format((float) $returnValue, 0, ',', '.'),
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

            $purchaseRows = array_map(function (array $row): array {
                unset($row['tanggal_ts']);
                return $row;
            }, $purchaseRows);
            $purchaseInstallmentRows = array_map(function (array $row): array {
                unset($row['tanggal_ts']);
                return $row;
            }, $purchaseInstallmentRows);
            $salesRows = array_map(function (array $row): array {
                unset($row['tanggal_ts']);
                return $row;
            }, $salesRows);

            $groups[] = [
                'product_id' => (int) $product->id,
                'part_number' => strtoupper((string) ($product->barcode ?? '-')),
                'part_name' => $product->name ?: '-',
                'category' => $product->category?->name ?: '-',
                'brand' => $product->brand?->name ?: '-',
                'unit' => $product->unit ?: '-',
                'total_stock' => (int) $product->batches->where('is_active', true)->sum('stock'),
                'last_activity_ts' => $latestTs,
                'summary' => [
                    'purchase_batches' => count($purchaseRows),
                    'purchase_installments' => count($purchaseInstallmentRows),
                    'sales_invoices' => count($salesRows),
                    'purchase_qty' => array_sum(array_map(fn (array $row) => (int) $row['qty'], $purchaseRows)),
                    'sales_qty' => array_sum(array_map(fn (array $row) => (int) $row['qty'], $salesRows)),
                    'purchase_value' => 'Rp ' . number_format((float) array_sum(array_map(fn (array $row) => (float) $row['total_value'], $purchaseRows)), 0, ',', '.'),
                    'purchase_installment_value' => 'Rp ' . number_format((float) array_sum(array_map(fn (array $row) => (float) $row['nominal_value'], $purchaseInstallmentRows)), 0, ',', '.'),
                    'sales_value' => 'Rp ' . number_format((float) array_sum(array_map(fn (array $row) => (float) $row['total_value'], $salesRows)), 0, ',', '.'),
                    'purchase_kredit' => count(array_filter($purchaseRows, fn (array $row) => in_array($row['status'], ['BELUM LUNAS', 'JATUH TEMPO'], true))),
                    'purchase_lunas' => count(array_filter($purchaseRows, fn (array $row) => $row['status'] === 'LUNAS')),
                    'sales_kredit' => count(array_filter($salesRows, fn (array $row) => in_array($row['status'], ['BELUM LUNAS', 'JATUH TEMPO'], true))),
                    'sales_lunas' => count(array_filter($salesRows, fn (array $row) => $row['status'] === 'LUNAS')),
                ],
                'purchase_rows' => $purchaseRows,
                'purchase_month_groups' => $purchaseMonthGroups,
                'purchase_installment_rows' => $purchaseInstallmentRows,
                'purchase_installment_month_groups' => $purchaseInstallmentMonthGroups,
                'sales_rows' => $salesRows,
                'sales_month_groups' => $salesMonthGroups,
            ];

            $overall['total_products']++;
        }

        usort($groups, fn (array $a, array $b): int => ($b['last_activity_ts'] <=> $a['last_activity_ts']));

        return [
            'summary' => [
                'total_products' => $overall['total_products'],
                'purchase_count' => $overall['purchase_count'],
                'sales_count' => $overall['sales_count'],
                'purchase_installment_count' => $overall['purchase_installment_count'],
                'purchase_value' => 'Rp ' . number_format((float) $overall['purchase_value'], 0, ',', '.'),
                'purchase_installment_value' => 'Rp ' . number_format((float) $overall['purchase_installment_value'], 0, ',', '.'),
                'sales_value' => 'Rp ' . number_format((float) $overall['sales_value'], 0, ',', '.'),
                'purchase_kredit' => $overall['purchase_kredit'],
                'purchase_lunas' => $overall['purchase_lunas'],
                'sales_kredit' => $overall['sales_kredit'],
                'sales_lunas' => $overall['sales_lunas'],
            ],
            'groups' => $groups,
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

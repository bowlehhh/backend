<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Supplier;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    protected string $view = 'filament.resources.supplier-resource.pages.view-supplier';

    public function getViewData(): array
    {
        /** @var Supplier $supplier */
        $supplier = $this->record->load([
            'productBatches.product.category',
            'productBatches.product.brand',
        ]);

        $batches = $supplier->productBatches
            ->filter(fn ($batch) => $batch->product)
            ->sortByDesc(fn ($batch) => $batch->created_at?->timestamp ?? 0)
            ->values();

        $hasInstallments = Schema::hasTable('credit_installments');

        $batchRows = $batches->map(function ($batch) use ($hasInstallments) {
            $qty = (int) ($batch->stock ?? 0);
            $price = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $goodsSubtotal = $qty * $price;
            $subtotal = $goodsSubtotal + $expeditionCost;
            $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $installmentPaid = 0.0;
            $remaining = 0.0;
            $paid = $subtotal;

            if ($paymentType === 'KREDIT') {
                if ($hasInstallments) {
                    $installmentPaid = (float) DB::table('credit_installments')
                        ->where('product_batch_id', $batch->id)
                        ->sum('amount');
                }

                $paid = min($subtotal, $downPayment + $installmentPaid);
                $remaining = max(0, $subtotal - $paid);
            }

            $dueDate = $batch->credit_due_date ? Carbon::parse($batch->credit_due_date) : null;
            $status = 'LUNAS';
            $warning = false;
            if ($paymentType === 'KREDIT') {
                if ($remaining <= 0) {
                    $status = 'LUNAS';
                } elseif ($dueDate && $dueDate->isPast()) {
                    $status = 'JATUH TEMPO';
                    $warning = true;
                } else {
                    $status = 'BELUM LUNAS';
                    if ($dueDate) {
                        $daysToDue = Carbon::now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);
                        $warning = $daysToDue <= 3;
                    }
                }
            }

            return [
                'batch_id' => (int) $batch->id,
                'invoice_group_key' => $this->makeInvoiceGroupKey(
                    $batch->supplier_invoice_number,
                    (int) $batch->id
                ),
                'supplier_invoice_number' => trim((string) ($batch->supplier_invoice_number ?? '')),
                'waktu' => $batch->created_at ? $batch->created_at->format('d M Y H:i') : '-',
                'purchase_date' => $batch->purchase_date?->format('d M Y') ?? '-',
                'purchase_date_value' => $batch->purchase_date,
                'part_number' => strtoupper((string) ($batch->product?->barcode ?? '-')),
                'part_name' => strtoupper((string) ($batch->product?->name ?? '-')),
                'processed_by' => trim((string) ($batch->processed_by ?? '')) ?: '-',
                'condition' => $batch->condition ?: '-',
                'merek' => $batch->product?->brand?->name ?? '-',
                'kategori' => $batch->product?->category?->name ?? '-',
                'unit' => strtoupper((string) ($batch->product?->unit ?? '-')),
                'berat' => $batch->product?->weight !== null
                    ? rtrim(rtrim((string) $batch->product->weight, '0'), '.') . ' ' . strtolower((string) ($batch->product?->weight_unit ?: 'kg'))
                    : '-',
                'qty' => $qty,
                'stok' => $qty,
                'harga_beli' => 'Rp ' . number_format($price, 0, ',', '.'),
                'biaya_ekspedisi' => 'Rp ' . number_format($expeditionCost, 0, ',', '.'),
                'subtotal' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                'subtotal_value' => $subtotal,
                'payment_type' => $paymentType,
                'credit_days' => (int) ($batch->credit_days ?? 0),
                'credit_due_date' => $dueDate?->format('d M Y') ?? '-',
                'credit_due_date_value' => $dueDate,
                'status' => $status,
                'is_warning' => $warning,
                'sudah_dibayar' => 'Rp ' . number_format($paid, 0, ',', '.'),
                'total_dibayar' => 'Rp ' . number_format($paid, 0, ',', '.'),
                'total_dibayar_value' => $paid,
                'sisa_kredit' => $paymentType === 'KREDIT' ? 'Rp ' . number_format($remaining, 0, ',', '.') : '-',
                'sisa_kredit_value' => $remaining,
            ];
        })->values();

        $purchaseRows = $batchRows
            ->groupBy(function (array $row): string {
                return ($row['purchase_date_value'] ?? null) instanceof Carbon
                    ? $row['purchase_date_value']->toDateString()
                    : 'Tanpa Tanggal';
            })
            ->map(function (Collection $rows, string $groupKey) use ($supplier) {
                $first = $rows->sortByDesc('batch_id')->first();
                $invoiceNumbers = $rows->pluck('supplier_invoice_number')
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->unique()
                    ->values();
                $paymentTypes = $rows->pluck('payment_type')->filter()->unique()->values();
                $isMixedPayment = $paymentTypes->count() > 1;
                $paymentType = $isMixedPayment
                    ? 'CAMPURAN'
                    : (string) ($paymentTypes->first() ?: 'LUNAS');
                $creditRows = $rows->filter(fn (array $row) => ($row['payment_type'] ?? 'LUNAS') === 'KREDIT');
                $latestDueDate = $creditRows
                    ->pluck('credit_due_date_value')
                    ->filter()
                    ->sortByDesc(fn (Carbon $date) => $date->timestamp)
                    ->first();
                $hasOverdue = $creditRows->contains(fn (array $row) => ($row['status'] ?? '') === 'JATUH TEMPO');
                $hasUnpaidCredit = $creditRows->contains(fn (array $row) => (float) ($row['sisa_kredit_value'] ?? 0) > 0);
                $status = $hasOverdue ? 'JATUH TEMPO' : ($hasUnpaidCredit ? 'BELUM LUNAS' : 'LUNAS');
                $isWarning = $rows->contains(fn (array $row) => (bool) ($row['is_warning'] ?? false));
                $items = $rows->map(fn (array $row) => trim((string) ($row['part_name'] ?? '')))
                    ->filter()
                    ->unique()
                    ->values();
                $partNumbers = $rows->map(fn (array $row) => trim((string) ($row['part_number'] ?? '')))
                    ->filter()
                    ->unique()
                    ->values();
                $waktu = $rows->pluck('waktu')->filter()->sortDesc()->first() ?: '-';
                $purchaseDateText = $groupKey !== 'Tanpa Tanggal'
                    ? Carbon::parse($groupKey)->format('j/n/Y')
                    : 'Tanpa Tanggal';
                $purchaseDateSort = $groupKey !== 'Tanpa Tanggal'
                    ? Carbon::parse($groupKey)->timestamp
                    : 0;

                return [
                    'group_key' => $groupKey,
                    'purchase_date_sort' => $purchaseDateSort,
                    'invoice_label' => $invoiceNumbers->isNotEmpty()
                        ? $invoiceNumbers->implode(', ')
                        : 'Tanpa Invoice',
                    'receipt_url' => route('admin.suppliers.invoice-recap', [
                        'supplier' => $supplier->id,
                    ]),
                    'waktu' => $waktu,
                    'purchase_date' => $purchaseDateText,
                    'part_number' => $partNumbers->implode(', '),
                    'part_name' => $items->implode(', '),
                    'processed_by' => $rows->pluck('processed_by')->filter()->unique()->implode(', '),
                    'condition' => $rows->pluck('condition')->filter()->unique()->implode(', '),
                    'merek' => $rows->pluck('merek')->filter()->unique()->implode(', '),
                    'kategori' => $rows->pluck('kategori')->filter()->unique()->implode(', '),
                    'unit' => $rows->pluck('unit')->filter()->unique()->implode(', '),
                    'berat' => $rows->pluck('berat')->filter()->unique()->implode(', '),
                    'qty' => (int) $rows->sum('qty'),
                    'stok' => (int) $rows->sum('stok'),
                    'harga_beli' => $rows->count() === 1 ? (string) ($first['harga_beli'] ?? 'Rp 0') : $rows->count() . ' barang',
                    'subtotal' => 'Rp ' . number_format((float) $rows->sum('subtotal_value'), 0, ',', '.'),
                    'payment_type' => $paymentType,
                    'credit_days' => $latestDueDate ? max(0, Carbon::now()->startOfDay()->diffInDays($latestDueDate->copy()->startOfDay(), false)) : 0,
                    'credit_due_date' => $latestDueDate?->format('d M Y') ?? '-',
                    'status' => $status,
                    'is_warning' => $isWarning,
                    'total_dibayar' => 'Rp ' . number_format((float) $rows->sum('total_dibayar_value'), 0, ',', '.'),
                    'sisa_kredit' => 'Rp ' . number_format((float) $rows->sum('sisa_kredit_value'), 0, ',', '.'),
                    'items_count' => (int) $rows->count(),
                    'batch_ids' => $rows->pluck('batch_id')->all(),
                ];
            })
            ->sortByDesc(fn (array $row) => $row['purchase_date_sort'] ?? 0)
            ->values();

        $totalTransactions = (int) $purchaseRows->count();
        $totalQty = (int) $batchRows->sum('qty');
        $totalModal = (float) $batches->sum(fn ($batch) => (((float) ($batch->purchase_price ?? 0)) * ((int) ($batch->stock ?? 0))) + ((float) ($batch->expedition_cost ?? 0)));
        $lastPurchaseAt = $batches->first()?->created_at?->format('d M Y H:i') ?? '-';
        $kreditCount = $purchaseRows->filter(fn (array $row) => in_array($row['status'], ['BELUM LUNAS', 'JATUH TEMPO'], true))->count();
        $lunasCount = $purchaseRows->where('status', 'LUNAS')->count();
        $warningCount = $purchaseRows->where('is_warning', true)->count();
        $invoiceNumbers = $batchRows
            ->pluck('supplier_invoice_number')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $dueDateGroups = $batchRows
            ->filter(fn (array $row) => ($row['payment_type'] ?? 'LUNAS') === 'KREDIT' && ($row['credit_due_date'] ?? '-') !== '-')
            ->groupBy('credit_due_date')
            ->map(function (Collection $rows, string $date): array {
                return [
                    'date' => $date,
                    'count' => $rows->count(),
                    'items' => $rows->map(fn (array $row): array => [
                        'invoice' => $row['supplier_invoice_number'] !== '' ? $row['supplier_invoice_number'] : '-',
                        'part_number' => $row['part_number'] ?? '-',
                        'part_name' => $row['part_name'] ?? '-',
                        'qty' => (int) ($row['qty'] ?? 0),
                        'status' => $row['status'] ?? 'BELUM LUNAS',
                    ])->values()->all(),
                ];
            })
            ->sortKeys()
            ->values()
            ->all();

        $purchaseDateGroups = $batchRows
            ->filter(fn (array $row) => ($row['purchase_date'] ?? '-') !== '-')
            ->groupBy('purchase_date')
            ->map(function (Collection $rows, string $date): array {
                return [
                    'date' => Carbon::parse($date)->format('j/n/Y'),
                    'date_value' => $date,
                    'count' => $rows->count(),
                    'items' => $rows->map(fn (array $row): array => [
                        'invoice' => $row['supplier_invoice_number'] !== '' ? $row['supplier_invoice_number'] : '-',
                        'part_number' => $row['part_number'] ?? '-',
                        'part_name' => $row['part_name'] ?? '-',
                        'qty' => (int) ($row['qty'] ?? 0),
                        'payment_type' => $row['payment_type'] ?? 'LUNAS',
                    ])->values()->all(),
                ];
            })
            ->sortKeysDesc()
            ->values()
            ->all();

        $noPurchaseDateRows = $batchRows
            ->filter(fn (array $row) => ($row['purchase_date'] ?? '-') === '-')
            ->values();

        if ($noPurchaseDateRows->isNotEmpty()) {
            $purchaseDateGroups[] = [
                'date' => 'Tanpa Tanggal Beli',
                'date_value' => 'none',
                'count' => $noPurchaseDateRows->count(),
                'items' => $noPurchaseDateRows->map(fn (array $row): array => [
                    'invoice' => $row['supplier_invoice_number'] !== '' ? $row['supplier_invoice_number'] : '-',
                    'part_number' => $row['part_number'] ?? '-',
                    'part_name' => $row['part_name'] ?? '-',
                    'qty' => (int) ($row['qty'] ?? 0),
                    'payment_type' => $row['payment_type'] ?? 'LUNAS',
                ])->all(),
            ];
        }

        $purchaseItems = $batchRows
            ->filter(fn (array $row) => ($row['purchase_date'] ?? '-') !== '-')
            ->sortByDesc(fn (array $row) => [
                $row['purchase_date'] ?? '',
                $row['waktu'] ?? '',
                $row['batch_id'] ?? 0,
            ])
            ->values()
            ->map(fn (array $row): array => [
                'purchase_date' => $row['purchase_date'] ?? '-',
                'invoice' => $row['supplier_invoice_number'] !== '' ? $row['supplier_invoice_number'] : '-',
                'part_number' => $row['part_number'] ?? '-',
                'part_name' => $row['part_name'] ?? '-',
                'qty' => (int) ($row['qty'] ?? 0),
                'payment_type' => $row['payment_type'] ?? 'LUNAS',
                'status' => $row['status'] ?? 'LUNAS',
            ])
            ->all();

        return [
            'supplier' => $supplier,
            'purchaseRows' => $purchaseRows,
            'focusBatchId' => (int) request()->query('batch_id', 0),
            'invoiceNumbers' => $invoiceNumbers,
            'dueDateGroups' => $dueDateGroups,
            'purchaseDateGroups' => $purchaseDateGroups,
            'purchaseItems' => $purchaseItems,
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_products' => $batches->unique('product_id')->count(),
                'total_qty' => $totalQty,
                'total_modal' => 'Rp ' . number_format($totalModal, 0, ',', '.'),
                'last_purchase_at' => $lastPurchaseAt,
                'kredit_count' => $kreditCount,
                'lunas_count' => $lunasCount,
                'warning_count' => $warningCount,
                'purchase_date_count' => count($purchaseDateGroups),
            ],
            'branch' => $supplier->branch,
            'note' => $supplier->note,
        ];
    }

    private function makeInvoiceGroupKey(?string $invoiceNumber, int $batchId): string
    {
        $invoiceNumber = trim((string) $invoiceNumber);

        if ($invoiceNumber !== '') {
            return 'invoice:' . $invoiceNumber;
        }

        return 'batch:' . $batchId;
    }
}

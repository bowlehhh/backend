<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Supplier;
use Filament\Resources\Pages\ViewRecord;
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

        $purchaseRows = $batches->map(function ($batch) use ($hasInstallments) {
            $qty = (int) ($batch->stock ?? 0);
            $price = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $goodsSubtotal = $qty * $price;
            $subtotal = $goodsSubtotal + $expeditionCost;
            $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
            $downPayment = 0.0;
            $installmentPaid = 0.0;
            $remaining = 0.0;

            if ($paymentType === 'KREDIT') {
                $downPayment = (float) ($batch->down_payment_amount ?? 0);
                if ($hasInstallments) {
                    $installmentPaid = (float) DB::table('credit_installments')
                        ->where('product_batch_id', $batch->id)
                        ->sum('amount');
                }

                $paid = min($subtotal, $downPayment + $installmentPaid);
                $remaining = max(0, $subtotal - $paid);
            } else {
                $paid = 0.0;
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
                'receipt_url' => route('admin.credits.receipt', ['batch' => $batch->id]),
                'waktu' => $batch->created_at ? $batch->created_at->format('d M Y H:i') : '-',
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
                'payment_type' => $paymentType,
                'credit_days' => (int) ($batch->credit_days ?? 0),
                'credit_due_date' => $dueDate?->format('d M Y') ?? '-',
                'status' => $status,
                'is_warning' => $warning,
                'down_payment' => $paymentType === 'KREDIT' ? 'Rp ' . number_format($downPayment, 0, ',', '.') : '-',
                'down_payment_value' => $downPayment,
                'sudah_dibayar' => $paymentType === 'KREDIT' ? 'Rp ' . number_format($paid, 0, ',', '.') : '-',
                'total_dibayar' => $paymentType === 'KREDIT' ? 'Rp ' . number_format($paid, 0, ',', '.') : '-',
                'sisa_kredit' => $paymentType === 'KREDIT' ? 'Rp ' . number_format($remaining, 0, ',', '.') : '-',
                'sisa_kredit_value' => $remaining,
            ];
        })->values();

        $totalTransactions = (int) $purchaseRows->count();
        $totalQty = (int) $purchaseRows->sum('qty');
        $totalModal = (float) $batches->sum(fn ($batch) => (((float) ($batch->purchase_price ?? 0)) * ((int) ($batch->stock ?? 0))) + ((float) ($batch->expedition_cost ?? 0)));
        $lastPurchaseAt = $batches->first()?->created_at?->format('d M Y H:i') ?? '-';
        $kreditCount = $purchaseRows->filter(fn (array $row) => $row['payment_type'] === 'KREDIT' && (float) ($row['sisa_kredit_value'] ?? 0) > 0)->count();
        $lunasCount = $purchaseRows->where('status', 'LUNAS')->count();
        $warningCount = $purchaseRows->where('is_warning', true)->count();

        return [
            'supplier' => $supplier,
            'purchaseRows' => $purchaseRows,
            'focusBatchId' => (int) request()->query('batch_id', 0),
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_products' => $batches->unique('product_id')->count(),
                'total_qty' => $totalQty,
                'total_modal' => 'Rp ' . number_format($totalModal, 0, ',', '.'),
                'last_purchase_at' => $lastPurchaseAt,
                'kredit_count' => $kreditCount,
                'lunas_count' => $lunasCount,
                'warning_count' => $warningCount,
            ],
            'branch' => $supplier->branch,
            'note' => $supplier->note,
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSupplierInvoiceRecapController extends Controller
{
    public function show(Request $request, Supplier $supplier): View|Response
    {
        $groupKey = trim((string) $request->query('group', ''));
        $purchaseDateParam = trim((string) $request->query('date', ''));
        $isNoDateRecap = in_array(strtolower($purchaseDateParam), ['none', 'tanpa', 'without'], true);
        $selectedPurchaseDate = null;

        if ($purchaseDateParam !== '' && ! $isNoDateRecap) {
            try {
                $selectedPurchaseDate = Carbon::parse($purchaseDateParam)->toDateString();
            } catch (\Throwable) {
                $selectedPurchaseDate = null;
            }
        }

        $supplier->load([
            'productBatches.product.brand',
            'productBatches.product.category',
        ]);

        $batchQuery = $supplier->productBatches
            ->filter(fn ($batch) => $batch->product)
            ->sortBy(fn ($batch) => $batch->purchase_date?->timestamp ?? $batch->created_at?->timestamp ?? 0);

        $matchingBatches = $batchQuery->values();
        if ($selectedPurchaseDate !== null) {
            $matchingBatches = $matchingBatches
                ->filter(fn ($batch) => $batch->purchase_date?->toDateString() === $selectedPurchaseDate)
                ->values();
        } elseif ($isNoDateRecap) {
            $matchingBatches = $matchingBatches
                ->filter(fn ($batch) => $batch->purchase_date === null)
                ->values();
        } elseif ($groupKey !== '') {
            $matchingBatches = $matchingBatches
                ->filter(fn ($batch) => $this->makeInvoiceGroupKey($batch->supplier_invoice_number, (int) $batch->id) === $groupKey)
                ->values();
        }

        abort_if($matchingBatches->isEmpty(), 404);

        $installmentPaidMap = [];
        if (Schema::hasTable('credit_installments')) {
            $installmentPaidMap = DB::table('credit_installments')
                ->selectRaw('product_batch_id, COALESCE(SUM(amount), 0) as paid_total')
                ->whereIn('product_batch_id', $matchingBatches->pluck('id')->all())
                ->groupBy('product_batch_id')
                ->pluck('paid_total', 'product_batch_id')
                ->map(fn ($value) => (float) $value)
                ->toArray();
        }

        $items = $matchingBatches->map(function ($batch) use ($installmentPaidMap): array {
            $qty = (int) ($batch->stock ?? 0);
            $price = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $goodsSubtotal = $qty * $price;
            $subtotal = $goodsSubtotal + $expeditionCost;
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $installmentPaid = (float) ($installmentPaidMap[$batch->id] ?? 0);
            $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
            $paid = $paymentType === 'KREDIT'
                ? min($subtotal, $downPayment + $installmentPaid)
                : $subtotal;
            $remaining = max(0, $subtotal - $paid);
            $dueDate = $batch->credit_due_date ? Carbon::parse($batch->credit_due_date) : null;
            $status = $remaining <= 0
                ? 'LUNAS'
                : (($dueDate && $dueDate->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS');

            return [
                'batch_id' => (int) $batch->id,
                'purchase_date' => $batch->purchase_date?->format('d M Y') ?? ($batch->created_at?->format('d M Y') ?? '-'),
                'part_number' => strtoupper((string) ($batch->product?->barcode ?? '-')),
                'part_name' => strtoupper((string) ($batch->product?->name ?? '-')),
                'processed_by' => trim((string) ($batch->processed_by ?? '')) ?: '-',
                'condition' => trim((string) ($batch->condition ?? '')) ?: '-',
                'brand' => $batch->product?->brand?->name ?? '-',
                'category' => $batch->product?->category?->name ?? '-',
                'unit' => strtoupper((string) ($batch->product?->unit ?? '-')),
                'qty' => $qty,
                'purchase_price' => $price,
                'expedition_cost' => $expeditionCost,
                'subtotal' => $subtotal,
                'paid' => $paid,
                'remaining' => $remaining,
                'payment_type' => $paymentType,
                'credit_due_date' => $dueDate?->format('d M Y') ?? '-',
                'status' => $status,
            ];
        })->values();

        $isFullSupplierRecap = $groupKey === '' && $selectedPurchaseDate === null && ! $isNoDateRecap;
        $invoiceNumber = trim((string) ($matchingBatches->first()->supplier_invoice_number ?? ''));
        $recapLabel = $selectedPurchaseDate !== null
            ? 'Tanggal Beli: ' . Carbon::parse($selectedPurchaseDate)->format('j/n/Y')
            : ($isNoDateRecap
                ? 'Tanpa Tanggal Beli'
                : ($isFullSupplierRecap
                    ? 'SEMUA PEMBELIAN SUPPLIER'
                    : ($invoiceNumber !== '' ? $invoiceNumber : 'Tanpa Invoice')));
        $paymentTypes = $items->pluck('payment_type')->unique()->values();
        $overallPaymentType = $paymentTypes->count() > 1 ? 'CAMPURAN' : (string) ($paymentTypes->first() ?: 'LUNAS');
        $hasOverdue = $items->contains(fn (array $item) => $item['status'] === 'JATUH TEMPO');
        $hasRemaining = $items->contains(fn (array $item) => (float) ($item['remaining'] ?? 0) > 0);
        $overallStatus = $hasOverdue ? 'JATUH TEMPO' : ($hasRemaining ? 'BELUM LUNAS' : 'LUNAS');
        $showCreditColumns = $overallPaymentType === 'KREDIT' || $overallPaymentType === 'CAMPURAN' || $hasRemaining;
        $showExpeditionColumn = $showCreditColumns;

        $purchaseDates = $matchingBatches
            ->map(fn ($batch) => $batch->purchase_date ?: $batch->created_at)
            ->filter();

        $viewData = [
            'supplier' => $supplier,
            'items' => $items,
            'invoiceNumber' => $isFullSupplierRecap
                ? 'SEMUA PEMBELIAN SUPPLIER'
                : ($invoiceNumber !== '' ? $invoiceNumber : 'Tanpa Invoice'),
            'recapLabel' => $recapLabel,
            'isFullSupplierRecap' => $isFullSupplierRecap,
            'printedAt' => now(),
            'summary' => [
                'items_count' => (int) $items->count(),
                'qty_total' => (int) $items->sum('qty'),
                'subtotal' => (float) $items->sum('subtotal'),
                'paid' => (float) $items->sum('paid'),
                'remaining' => (float) $items->sum('remaining'),
                'payment_type' => $overallPaymentType,
                'status' => $overallStatus,
                'purchase_date_start' => $purchaseDates->sortBy(fn ($date) => $date->timestamp)->first(),
                'purchase_date_end' => $purchaseDates->sortByDesc(fn ($date) => $date->timestamp)->first(),
                'show_credit_columns' => $showCreditColumns,
                'show_expedition_column' => $showExpeditionColumn,
            ],
            'pdf' => $request->boolean('pdf'),
        ];

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('admin.supplier-invoice-recap', $viewData)->setPaper('a4', 'portrait');
            return $pdf->download('rekap-invoice-supplier-' . $supplier->id . '.pdf');
        }

        return response()->view('admin.supplier-invoice-recap', $viewData)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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

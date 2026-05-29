<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Supplier;
use Filament\Resources\Pages\ViewRecord;

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
            ->values();

        $purchaseHistory = $batches
            ->groupBy(fn ($batch) => optional($batch->created_at)->toDateString() ?: 'unknown')
            ->map(function ($dailyBatches, $date) {
                $transactions = $dailyBatches->count();
                $totalQty = (int) $dailyBatches->sum('stock');
                $totalModal = (float) $dailyBatches->sum(fn ($batch) => ((float) $batch->purchase_price) * ((int) $batch->stock));

                return [
                    'date_key' => $date,
                    'date_label' => $date === 'unknown'
                        ? '-'
                        : optional($dailyBatches->first()->created_at)->format('d M Y'),
                    'transactions' => $transactions,
                    'total_qty' => $totalQty,
                    'total_modal' => $totalModal,
                    'batch_codes' => $dailyBatches
                        ->pluck('batch_code')
                        ->filter()
                        ->implode(', '),
                ];
            })
            ->sortByDesc('date_key')
            ->values();

        return [
            'supplier' => $supplier,
            'productBatches' => $batches,
            'totalProducts' => $batches->unique('product_id')->count(),
            'totalStock' => (int) $batches->sum('stock'),
            'totalTransactions' => (int) $batches->count(),
            'purchaseHistory' => $purchaseHistory,
            'branch' => $supplier->branch,
            'note' => $supplier->note,
        ];
    }
}

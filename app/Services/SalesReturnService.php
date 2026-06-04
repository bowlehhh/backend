<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    public function create(User $cashier, Sale $sale, array $payload): SalesReturn
    {
        return DB::transaction(function () use ($cashier, $sale, $payload): SalesReturn {
            $requested = collect((array) ($payload['items'] ?? []))
                ->map(fn (array $item): array => [
                    'sale_item_id' => (int) ($item['sale_item_id'] ?? 0),
                    'qty' => max(0, (int) ($item['qty'] ?? 0)),
                ])
                ->filter(fn (array $item): bool => $item['qty'] > 0)
                ->values();

            if ($requested->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['Pilih minimal satu item untuk diretur.'],
                ]);
            }

            $requestedItemIds = $requested->pluck('sale_item_id')->all();

            $saleItems = SaleItem::query()
                ->where('sale_id', $sale->id)
                ->whereIn('id', $requestedItemIds)
                ->with('productBatch')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $alreadyReturnedMap = SalesReturnItem::query()
                ->selectRaw('sales_return_items.sale_item_id, SUM(sales_return_items.qty_return) AS total_qty')
                ->join('sales_returns', 'sales_returns.id', '=', 'sales_return_items.sales_return_id')
                ->where('sales_returns.sale_id', $sale->id)
                ->whereIn('sales_return_items.sale_item_id', $requestedItemIds)
                ->groupBy('sales_return_items.sale_item_id')
                ->get()
                ->mapWithKeys(fn ($row): array => [(int) $row->sale_item_id => (int) $row->total_qty]);

            $returnNumber = $this->generateReturnNumber();
            $returnItems = [];
            $totalRefund = 0.0;

            foreach ($requested as $requestedItem) {
                $saleItemId = (int) $requestedItem['sale_item_id'];
                $returnQty = (int) $requestedItem['qty'];
                $saleItem = $saleItems->get($saleItemId);

                if (! $saleItem) {
                    throw ValidationException::withMessages([
                        'items' => ['Item transaksi tidak valid untuk diretur.'],
                    ]);
                }

                $alreadyReturnedQty = (int) ($alreadyReturnedMap[$saleItemId] ?? 0);
                $availableToReturn = max(0, (int) $saleItem->qty - $alreadyReturnedQty);

                if ($returnQty > $availableToReturn) {
                    throw ValidationException::withMessages([
                        'items' => ["Qty retur {$saleItem->product_name} melebihi batas. Maksimal: {$availableToReturn}."],
                    ]);
                }

                $batch = $saleItem->productBatch;

                if (! $batch) {
                    throw ValidationException::withMessages([
                        'items' => ["Batch {$saleItem->product_name} tidak ditemukan."],
                    ]);
                }

                $subtotal = (float) $saleItem->price * $returnQty;
                $totalRefund += $subtotal;

                $stockBefore = (int) $batch->stock;
                $stockAfter = $stockBefore + $returnQty;
                $batch->update(['stock' => $stockAfter]);

                StockHistory::create([
                    'product_id' => $saleItem->product_id,
                    'product_batch_id' => $saleItem->product_batch_id,
                    'user_id' => $cashier->id,
                    'type' => StockHistory::TYPE_IN,
                    'qty' => $returnQty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference' => $returnNumber,
                    'description' => "Stock returned from sales return {$sale->invoice_number}.",
                ]);

                $returnItems[] = [
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_batch_id' => $saleItem->product_batch_id,
                    'product_name' => $saleItem->product_name,
                    'price' => (float) $saleItem->price,
                    'qty_sold' => (int) $saleItem->qty,
                    'qty_return' => $returnQty,
                    'subtotal_return' => $subtotal,
                ];
            }

            $note = trim((string) ($payload['notes'] ?? ''));

            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'user_id' => $cashier->id,
                'return_number' => $returnNumber,
                'invoice_number' => (string) $sale->invoice_number,
                'return_type' => 'refund',
                'reason' => $note !== '' ? 'Lainnya' : 'Retur penjualan',
                'reason_other' => $note !== '' ? $note : null,
                'return_total' => $totalRefund,
                'refund_amount' => $totalRefund,
                'returned_at' => now(),
            ]);

            $salesReturn->items()->createMany($returnItems);

            if (strtolower((string) $sale->payment_method) === 'credit') {
                $currentCredit = (float) ($sale->credit_amount ?? 0);
                $remainingCredit = max(0, $currentCredit - $totalRefund);

                $sale->fill([
                    'credit_amount' => $remainingCredit,
                    'credit_days' => $remainingCredit > 0 && $sale->credit_due_date
                        ? max(0, Carbon::today()->diffInDays(Carbon::parse($sale->credit_due_date), false))
                        : null,
                    'credit_due_date' => $remainingCredit > 0 ? $sale->credit_due_date : null,
                ])->save();
            }

            return $salesReturn->load(['sale.user:id,name', 'items']);
        });
    }

    private function generateReturnNumber(): string
    {
        $datePart = Carbon::now()->format('Ymd');
        $prefix = "RET-{$datePart}-";

        $lastReturnNumber = SalesReturn::query()
            ->where('return_number', 'like', "{$prefix}%")
            ->latest('id')
            ->value('return_number');

        $nextNumber = 1;

        if ($lastReturnNumber) {
            $nextNumber = ((int) substr($lastReturnNumber, -4)) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }
}

<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductBatch;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockHistory;
use App\Models\User;
use App\Support\AdminBesarCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    public function create(User $cashier, Sale $sale, array $payload): SalesReturn
    {
        $salesReturn = DB::transaction(function () use ($cashier, $sale, $payload): SalesReturn {
            $reason = (string) ($payload['return_reason'] ?? 'barang_rusak');
            $requested = collect((array) ($payload['items'] ?? []))
                ->map(fn (array $item): array => [
                    'sale_item_id' => (int) ($item['sale_item_id'] ?? 0),
                    'qty' => max(0, (int) ($item['qty'] ?? 0)),
                    'replacement_product_id' => (int) ($item['replacement_product_id'] ?? 0),
                    'replacement_batch_id' => (int) ($item['replacement_batch_id'] ?? 0),
                    'replacement_qty' => max(0, (int) ($item['replacement_qty'] ?? 0)),
                    'replacement_lines' => collect((array) ($item['replacement_lines'] ?? []))
                        ->map(function (array $line): array {
                            $replacementQty = max(0, (int) ($line['qty'] ?? 0));
                            $replacementPrice = isset($line['price']) ? (float) preg_replace('/[^\d.]/', '', (string) $line['price']) : 0.0;

                            return [
                                'product_id' => (int) ($line['product_id'] ?? 0),
                                'batch_id' => (int) ($line['batch_id'] ?? 0),
                                'qty' => $replacementQty,
                                'price' => $replacementPrice,
                                'label' => trim((string) ($line['label'] ?? '')),
                            ];
                        })
                        ->filter(fn (array $line): bool => $line['qty'] > 0 || $line['product_id'] > 0 || $line['batch_id'] > 0)
                        ->values()
                        ->all(),
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
            $exchangeTotal = 0.0;
            $priceDifferenceTotal = 0.0;
            $extraPaymentAmount = max(0, (float) preg_replace('/[^\d]/', '', (string) ($payload['extra_payment_amount'] ?? 0)));
            $extraPaymentChangeAmount = 0.0;

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

                $replacementProductId = (int) ($requestedItem['replacement_product_id'] ?? 0);
                $replacementBatchId = (int) ($requestedItem['replacement_batch_id'] ?? 0);
                $replacementQty = max(0, (int) ($requestedItem['replacement_qty'] ?? 0));
                $subtotal = (float) $saleItem->price * $returnQty;
                $replacementPrice = null;
                $replacementSubtotal = 0.0;
                $priceDifference = 0.0;
                $replacementDetails = [];

                if ($reason === 'ganti_barang') {
                    $replacementLines = collect((array) ($requestedItem['replacement_lines'] ?? []));

                    if ($replacementLines->isEmpty() && $replacementProductId > 0 && $replacementBatchId > 0) {
                        $replacementLines = collect([[
                            'product_id' => $replacementProductId,
                            'batch_id' => $replacementBatchId,
                            'qty' => $replacementQty > 0 ? $replacementQty : $returnQty,
                            'price' => 0,
                            'label' => '',
                        ]]);
                    }

                    if ($replacementLines->isEmpty()) {
                        throw ValidationException::withMessages([
                            'items' => ["Pilih barang pengganti untuk item {$saleItem->product_name}."],
                        ]);
                    }

                    foreach ($replacementLines as $lineIndex => $line) {
                        $lineProductId = (int) ($line['product_id'] ?? 0);
                        $lineBatchId = (int) ($line['batch_id'] ?? 0);
                        $lineQty = max(0, (int) ($line['qty'] ?? 0));

                        if ($lineProductId <= 0 || $lineBatchId <= 0) {
                            throw ValidationException::withMessages([
                                'items' => ["Pilih barang pengganti untuk item {$saleItem->product_name}."],
                            ]);
                        }

                        if ($lineQty <= 0) {
                            throw ValidationException::withMessages([
                                'items' => ["Qty barang pengganti untuk {$saleItem->product_name} harus lebih dari 0."],
                            ]);
                        }

                        $replacementBatch = ProductBatch::query()
                            ->with('product:id,name,barcode')
                            ->whereKey($lineBatchId)
                            ->lockForUpdate()
                            ->first();

                        if (! $replacementBatch) {
                            throw ValidationException::withMessages([
                                'items' => ["Barang pengganti untuk item {$saleItem->product_name} tidak ditemukan."],
                            ]);
                        }

                        if ((int) $replacementBatch->product_id !== $lineProductId) {
                            throw ValidationException::withMessages([
                                'items' => ["Barang pengganti untuk item {$saleItem->product_name} tidak valid."],
                            ]);
                        }

                        if ((int) $replacementBatch->stock < $lineQty) {
                            throw ValidationException::withMessages([
                                'items' => ["Stok barang pengganti untuk {$saleItem->product_name} tidak mencukupi."],
                            ]);
                        }

                        $linePrice = (float) $replacementBatch->selling_price;
                        $lineSubtotal = $linePrice * $lineQty;
                        $replacementSubtotal += $lineSubtotal;
                        $replacementDetails[] = [
                            'product_id' => $lineProductId,
                            'batch_id' => $lineBatchId,
                            'label' => trim((string) ($line['label'] ?? $replacementBatch->product?->name ?? '-')),
                            'part_number' => strtoupper((string) ($replacementBatch->product?->barcode ?? '-')),
                            'part_name' => strtoupper((string) ($replacementBatch->product?->name ?? '-')),
                            'qty' => $lineQty,
                            'price' => $linePrice,
                            'subtotal' => $lineSubtotal,
                        ];

                        $replacementStockBefore = (int) $replacementBatch->stock;
                        $replacementStockAfter = max(0, $replacementStockBefore - $lineQty);
                        $replacementBatch->update(['stock' => $replacementStockAfter]);

                        StockHistory::create([
                            'product_id' => $lineProductId,
                            'product_batch_id' => $lineBatchId,
                            'user_id' => $cashier->id,
                            'type' => StockHistory::TYPE_OUT,
                            'qty' => $lineQty,
                            'stock_before' => $replacementStockBefore,
                            'stock_after' => $replacementStockAfter,
                            'reference' => $returnNumber,
                            'description' => "Stock replacement used for sales return {$sale->invoice_number}.",
                        ]);
                    }

                    $exchangeTotal += $replacementSubtotal;
                    $priceDifference = $replacementSubtotal - $subtotal;
                    $priceDifferenceTotal += $priceDifference;
                }

                $batch = $saleItem->productBatch;

                if (! $batch) {
                    throw ValidationException::withMessages([
                        'items' => ["Batch {$saleItem->product_name} tidak ditemukan."],
                    ]);
                }

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
                    'replacement_product_id' => $reason === 'ganti_barang' ? ($replacementDetails[0]['product_id'] ?? null) : null,
                    'replacement_batch_id' => $reason === 'ganti_barang' ? ($replacementDetails[0]['batch_id'] ?? null) : null,
                    'replacement_qty' => $reason === 'ganti_barang' ? array_sum(array_map(fn (array $line): int => (int) ($line['qty'] ?? 0), $replacementDetails)) : null,
                    'replacement_price' => $reason === 'ganti_barang' ? ($replacementDetails[0]['price'] ?? null) : null,
                    'replacement_subtotal' => $reason === 'ganti_barang' ? $replacementSubtotal : null,
                    'price_difference' => $reason === 'ganti_barang' ? $priceDifference : null,
                    'replacement_details' => $reason === 'ganti_barang' ? $replacementDetails : null,
                ];
            }

            $note = trim((string) ($payload['notes'] ?? ''));
            $reason = (string) ($payload['return_reason'] ?? 'lainnya');
            $reasonLabel = match ($reason) {
                'barang_rusak' => 'Barang rusak',
                'ganti_barang' => 'Ganti barang',
                'pengembalian_dana' => 'Pengembalian dana',
                'salah_kirim' => 'Salah kirim',
                default => 'Lainnya',
            };

            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'user_id' => $cashier->id,
                'return_number' => $returnNumber,
                'invoice_number' => (string) $sale->invoice_number,
                'return_type' => $reason === 'ganti_barang' ? 'exchange' : 'refund',
                'reason' => $reasonLabel,
                'reason_other' => $note !== '' ? $note : null,
                'return_total' => $totalRefund,
                'refund_amount' => $reason === 'ganti_barang' ? 0 : $totalRefund,
                'exchange_total' => $exchangeTotal,
                'price_difference_total' => $reason === 'ganti_barang' ? $priceDifferenceTotal : (-1 * $totalRefund),
                'extra_payment_amount' => $reason === 'ganti_barang' ? $extraPaymentAmount : 0,
                'extra_payment_change_amount' => $reason === 'ganti_barang' ? $extraPaymentChangeAmount : 0,
                'returned_at' => now(),
            ]);

            $salesReturn->items()->createMany($returnItems);

            if (strtolower((string) $sale->payment_method) === 'credit') {
                $currentCredit = (float) ($sale->credit_amount ?? 0);
                $creditAdjustment = $reason === 'ganti_barang'
                    ? $priceDifferenceTotal
                    : (-1 * $totalRefund);
                $remainingCreditBeforeExtraPayment = max(0, $currentCredit + $creditAdjustment);
                $remainingCredit = max(0, $remainingCreditBeforeExtraPayment - $extraPaymentAmount);
                $extraPaymentChangeAmount = max(0, $extraPaymentAmount - $remainingCreditBeforeExtraPayment);
                $salesReturn->update([
                    'extra_payment_amount' => $extraPaymentAmount,
                    'extra_payment_change_amount' => $extraPaymentChangeAmount,
                ]);

                $sale->fill([
                    'credit_amount' => $remainingCredit,
                    'credit_days' => $remainingCredit > 0 && $sale->credit_due_date
                        ? max(0, Carbon::today()->diffInDays(Carbon::parse($sale->credit_due_date), false))
                        : null,
                    'credit_due_date' => $remainingCredit > 0 ? $sale->credit_due_date : null,
                ])->save();
            } elseif ($reason === 'ganti_barang') {
                $extraPaymentChangeAmount = max(0, $extraPaymentAmount - max(0, $priceDifferenceTotal));
                $salesReturn->update([
                    'extra_payment_amount' => $extraPaymentAmount,
                    'extra_payment_change_amount' => $extraPaymentChangeAmount,
                ]);
            }

            return $salesReturn->load(['sale.user:id,name', 'items']);
        });

        AdminBesarCache::forgetToday();
        return $salesReturn;
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

<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Menyelesaikan checkout dalam satu DB transaction.
     *
     * Tujuan:
     * - Mencegah inkonsistensi stok/penjualan saat terjadi error di tengah proses.
     * - Menjamin sale, sale_items, dan stock_histories selalu sinkron.
     */
    public function checkout(User $cashier, array $payload): Sale
    {
        return DB::transaction(function () use ($cashier, $payload): Sale {
            $itemsPayload = collect($payload['items']);
            $batchIds = $itemsPayload->pluck('product_batch_id')->all();

            $batches = ProductBatch::query()
                ->with('product:id,name,is_active')
                ->whereIn('id', $batchIds)
                ->where('is_active', true)
                // Lock row batch agar dua kasir tidak mengurangi stok bersamaan secara race condition.
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $saleItems = [];
            $total = 0.0;

            foreach ($itemsPayload as $item) {
                $batch = $batches->get($item['product_batch_id']);

                if (! $batch || ! $batch->product || ! $batch->product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more selected batches are unavailable.'],
                    ]);
                }

                if ((int) $batch->product_id !== (int) $item['product_id']) {
                    throw ValidationException::withMessages([
                        'items' => ['Product and batch mismatch detected.'],
                    ]);
                }

                if ($batch->stock < $item['qty']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock for {$batch->product->name} is not sufficient."],
                    ]);
                }

                $unitPrice = isset($item['price']) ? (float) $item['price'] : (float) $batch->selling_price;
                if ($unitPrice < 0) {
                    throw ValidationException::withMessages([
                        'items' => ['Item price must not be negative.'],
                    ]);
                }

                $subtotal = $unitPrice * (int) $item['qty'];
                $total += $subtotal;

                $saleItems[] = [
                    'product_id' => $batch->product_id,
                    'product_batch_id' => $batch->id,
                    'product_name' => $batch->product->name,
                    'price' => $unitPrice,
                    'qty' => (int) $item['qty'],
                    'subtotal' => $subtotal,
                ];
            }

            $paymentMethod = strtolower((string) ($payload['payment_method'] ?? 'cash'));
            $paidAmount = isset($payload['paid_amount']) ? (float) $payload['paid_amount'] : 0.0;
            $creditAmount = 0.0;
            $creditDueDate = null;
            $creditDays = null;
            $changeAmount = 0.0;

            if (! in_array($paymentMethod, ['cash', 'transfer', 'qris', 'debit', 'credit'], true)) {
                throw ValidationException::withMessages([
                    'payment_method' => ['Payment method is invalid.'],
                ]);
            }

            if ($paymentMethod === 'cash') {
                if ($paidAmount < $total) {
                    throw ValidationException::withMessages([
                        'paid_amount' => ['Paid amount must be equal to or greater than the total.'],
                    ]);
                }

                $changeAmount = max(0, $paidAmount - $total);
            } elseif ($paymentMethod === 'credit') {
                // Kredit dapat memakai DP parsial. Sisa akan disimpan di credit_amount.
                $paidAmount = min($total, max(0, $paidAmount));
                $creditAmount = max(0, $total - $paidAmount);

                if ($creditAmount > 0) {
                    $dueDateInput = $payload['credit_due_date'] ?? null;

                    if ($dueDateInput === null || trim((string) $dueDateInput) === '') {
                        throw ValidationException::withMessages([
                            'credit_due_date' => ['Credit due date is required when payment is not fully paid.'],
                        ]);
                    }

                    try {
                        $creditDueDate = Carbon::parse((string) $dueDateInput)->startOfDay();
                    } catch (\Throwable) {
                        throw ValidationException::withMessages([
                            'credit_due_date' => ['Credit due date is invalid.'],
                        ]);
                    }

                    if ($creditDueDate->lt(Carbon::today())) {
                        throw ValidationException::withMessages([
                            'credit_due_date' => ['Credit due date must be today or a future date.'],
                        ]);
                    }

                    $creditDays = Carbon::today()->diffInDays($creditDueDate);
                }
            } else {
                $paidAmount = $paidAmount > 0 ? $paidAmount : $total;
                $changeAmount = max(0, $paidAmount - $total);
            }

            $sale = Sale::create([
                'user_id' => $cashier->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_name' => $payload['customer_name'] ?? null,
                'customer_phone' => $payload['customer_phone'] ?? null,
                'cashier_service_name' => $payload['cashier_service_name'] ?? null,
                'cashier_phone' => $payload['cashier_phone'] ?? null,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'credit_amount' => $creditAmount,
                'credit_days' => $creditDays,
                'credit_due_date' => $creditDueDate?->toDateString(),
            ]);

            $sale->items()->createMany($saleItems);

            foreach ($saleItems as $item) {
                $batch = $batches->get($item['product_batch_id']);
                $stockBefore = $batch->stock;
                $stockAfter = $stockBefore - $item['qty'];

                $batch->update([
                    'stock' => $stockAfter,
                ]);

                StockHistory::create([
                    'product_id' => $item['product_id'],
                    'product_batch_id' => $item['product_batch_id'],
                    'user_id' => $cashier->id,
                    'type' => StockHistory::TYPE_OUT,
                    'qty' => $item['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference' => $sale->invoice_number,
                    'description' => 'Stock reduced from POS checkout.',
                ]);
            }

            return $sale->load(['user:id,name', 'items']);
        });
    }

    private function generateInvoiceNumber(): string
    {
        // Format: INV-YYYYMMDD-0001, reset counter per hari.
        $datePart = now()->format('Ymd');
        $prefix = "INV-{$datePart}-";

        $lastInvoice = Sale::query()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->latest('id')
            ->value('invoice_number');

        $nextNumber = 1;

        if ($lastInvoice) {
            $nextNumber = ((int) substr($lastInvoice, -4)) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextNumber);
    }
}

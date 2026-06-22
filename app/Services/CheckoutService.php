<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\StockHistory;
use App\Models\User;
use App\Support\AdminBesarCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    private function productLookupKeys(Product $product): array
    {
        $keys = [];
        $barcodeKey = $this->normalizePartNumberKey($product->barcode, null);

        if ($barcodeKey !== '') {
            $keys[] = $barcodeKey;
        }

        if ($product->id !== null) {
            $keys[] = $this->normalizePartNumberKey(null, (int) $product->id);
        }

        return array_values(array_unique(array_filter($keys)));
    }

    private function findFallbackBatchForProduct($batches)
    {
        return $batches
            ->filter(fn (ProductBatch $batch): bool => (int) $batch->stock > 0)
            ->sortBy('id')
            ->first();
    }

    private function normalizePartNumberKey(?string $value, ?int $productId = null): string
    {
        $normalized = strtoupper(trim((string) $value));

        if ($normalized !== '') {
            return $normalized;
        }

        return $productId !== null && $productId > 0 ? 'PRODUCT-' . $productId : '';
    }

    private function productPartNumberKey(Product $product): string
    {
        return $this->productLookupKeys($product)[0] ?? '';
    }

    private function normalizeStockAllocations(array $item): array
    {
        $allocations = $item['stock_allocations'] ?? null;
        $normalized = [];

        if (is_array($allocations)) {
            foreach ($allocations as $batchId => $qty) {
                $batchKey = (int) $batchId;
                $batchQty = max(0, (int) $qty);
                if ($batchKey > 0 && $batchQty > 0) {
                    $normalized[$batchKey] = ($normalized[$batchKey] ?? 0) + $batchQty;
                }
            }
        }

        if ($normalized === []) {
            $batchId = (int) ($item['product_batch_id'] ?? 0);
            $qty = max(0, (int) ($item['qty'] ?? 0));
            if ($batchId > 0 && $qty > 0) {
                $normalized[$batchId] = $qty;
            }
        }

        return $normalized;
    }

    /**
     * Menyelesaikan checkout dalam satu DB transaction.
     *
     * Tujuan:
     * - Mencegah inkonsistensi stok/penjualan saat terjadi error di tengah proses.
     * - Menjamin sale, sale_items, dan stock_histories selalu sinkron.
     */
    public function checkout(User $cashier, array $payload): Sale
    {
        $invoicePeriod = now()->format('Ym');
        $this->acquireInvoiceNumberLock($invoicePeriod);
        $sale = null;

        try {
            $sale = DB::transaction(function () use ($cashier, $payload, $invoicePeriod): Sale {
                $itemsPayload = collect($payload['items']);
                $partNumbers = $itemsPayload
                    ->map(fn (array $item): string => $this->normalizePartNumberKey(
                        $item['part_number'] ?? null,
                        isset($item['product_id']) ? (int) $item['product_id'] : null,
                    ))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $productQuery = Product::query()
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->where(function ($query) use ($partNumbers): void {
                        foreach ($partNumbers as $partNumber) {
                            if (str_starts_with($partNumber, 'PRODUCT-')) {
                                $query->orWhere('id', (int) substr($partNumber, 8));
                            } else {
                                $query->orWhereRaw('UPPER(TRIM(barcode)) = ?', [$partNumber]);
                            }
                        }
                    });

                $products = $productQuery->get();
                $productsByPart = $products
                    ->flatMap(function (Product $product): array {
                        $entries = [];

                        foreach ($this->productLookupKeys($product) as $key) {
                            $entries[] = [$key, $product];
                        }

                        return $entries;
                    })
                    ->groupBy(fn (array $entry): string => $entry[0])
                    ->map(fn ($entries) => $entries->pluck(1)->unique('id')->values());
                $productIds = $products->pluck('id')->all();

                $productBatches = ProductBatch::query()
                    ->with('product:id,name,is_active,barcode,unit')
                    ->whereIn('product_id', $productIds)
                    ->where('is_active', true)
                    ->orderBy('product_id')
                    ->orderBy('id')
                    // Lock row batch agar dua kasir tidak mengurangi stok bersamaan secara race condition.
                    ->lockForUpdate()
                    ->get();
                $batchesByPart = $productBatches
                    ->flatMap(function (ProductBatch $batch): array {
                        $entries = [];

                        foreach ($this->productLookupKeys($batch->product) as $key) {
                            $entries[] = [$key, $batch];
                        }

                        return $entries;
                    })
                    ->groupBy(fn (array $entry): string => $entry[0])
                    ->map(fn ($entries) => $entries->pluck(1)->unique('id')->values());
                $batchesById = $batchesByPart->flatten()->keyBy('id');
                $remainingStockByBatch = $batchesById
                    ->mapWithKeys(fn (ProductBatch $batch): array => [(int) $batch->id => (int) $batch->stock])
                    ->all();

                $saleItems = [];
                $total = 0.0;

                foreach ($itemsPayload as $item) {
                    $partNumber = $this->normalizePartNumberKey(
                        $item['part_number'] ?? null,
                        isset($item['product_id']) ? (int) $item['product_id'] : null,
                    );
                    $qty = (int) ($item['qty'] ?? 0);
                    $mergeStock = (bool) ($item['merge_stock'] ?? false);
                    $product = $productsByPart->get($partNumber)?->first();
                    $batches = $batchesByPart->get($partNumber, collect());
                    $allocations = $this->normalizeStockAllocations($item);
                    $primaryBatch = $batchesById->get((int) ($item['product_batch_id'] ?? 0));
                    $fallbackBatch = $this->findFallbackBatchForProduct($batches);
                    $useRequestedAllocations = false;

                    if (! $product) {
                        throw ValidationException::withMessages([
                            'items' => ['Ada barang di keranjang yang sudah tidak tersedia.'],
                        ]);
                    }

                    if ($qty < 1) {
                        throw ValidationException::withMessages([
                            'items' => ['Qty barang minimal 1.'],
                        ]);
                    }

                    if ($mergeStock && $allocations !== []) {
                        $batchIds = array_keys($allocations);
                        $allocatedBatches = $batchesById->only($batchIds);

                        $useRequestedAllocations = $allocatedBatches->count() === count($batchIds);
                        $availableStock = $useRequestedAllocations
                            ? (int) collect($allocations)->sum(fn ($allocationQty, $batchId): int => min(
                                max(0, (int) $allocationQty),
                                max(0, (int) ($remainingStockByBatch[(int) $batchId] ?? 0)),
                            ))
                            : (int) $batches->sum(fn (ProductBatch $batch): int => max(0, (int) ($remainingStockByBatch[(int) $batch->id] ?? 0)));

                    } elseif (! $primaryBatch || (int) $primaryBatch->product_id !== (int) $product->id) {
                        if (! $fallbackBatch) {
                            throw ValidationException::withMessages([
                                'items' => ['Batch barang tidak tersedia.'],
                            ]);
                        }

                        $primaryBatch = $fallbackBatch;
                    }

                    $availableStock = $mergeStock
                        ? (int) ($useRequestedAllocations
                            ? collect($allocations)->sum(fn ($allocationQty, $batchId): int => min(
                                max(0, (int) $allocationQty),
                                max(0, (int) ($remainingStockByBatch[(int) $batchId] ?? 0)),
                            ))
                            : $batches->sum(fn (ProductBatch $batch): int => max(0, (int) ($remainingStockByBatch[(int) $batch->id] ?? 0))))
                        : max(0, (int) ($remainingStockByBatch[(int) $primaryBatch->id] ?? 0));
                    $unitPrice = array_key_exists('price', $item)
                        ? (float) $item['price']
                        : (float) ($batches->first()?->selling_price ?? 0);
                    if ($unitPrice < 0) {
                        throw ValidationException::withMessages([
                            'items' => ['Harga barang tidak valid.'],
                        ]);
                    }

                    $remainingQty = $qty;
                    $subtotal = $unitPrice * $qty;
                    $total += $subtotal;

                    if (! $mergeStock) {
                        $batchId = (int) $primaryBatch->id;
                        $saleItems[] = [
                            'product_id' => $product->id,
                            'product_batch_id' => $batchId,
                            'product_name' => $product->name,
                            'part_number' => $partNumber,
                            'merge_stock' => false,
                            'price' => $unitPrice,
                            'qty' => $remainingQty,
                            'subtotal' => $subtotal,
                        ];
                        $remainingStockByBatch[$batchId] = (int) ($remainingStockByBatch[$batchId] ?? 0) - $remainingQty;
                        $remainingQty = 0;
                    } else {
                        if ($allocations !== [] && $useRequestedAllocations) {
                            foreach ($allocations as $batchId => $takeQty) {
                                if ($remainingQty <= 0) {
                                    break;
                                }

                                $takeQty = min($remainingQty, (int) $takeQty);
                                if ($takeQty <= 0) {
                                    continue;
                                }

                                $batch = $batchesById->get((int) $batchId);
                                if (! $batch || (int) $batch->product_id !== (int) $product->id) {
                                    $batch = $batches->first(fn (ProductBatch $candidate): bool => max(0, (int) ($remainingStockByBatch[(int) $candidate->id] ?? 0)) > 0);
                                }
                                if (! $batch) {
                                    continue;
                                }

                                $availableInBatch = max(0, (int) ($remainingStockByBatch[(int) $batch->id] ?? 0));
                                $takeQty = min($remainingQty, $takeQty, $availableInBatch);
                                if ($takeQty <= 0) {
                                    continue;
                                }

                                $remainingQty -= $takeQty;
                                $batchPrice = (float) $batch->selling_price;
                                $batchSubtotal = $batchPrice * $takeQty;

                                $saleItems[] = [
                                    'product_id' => $product->id,
                                    'product_batch_id' => $batch->id,
                                    'product_name' => $product->name,
                                    'part_number' => $partNumber,
                                    'merge_stock' => true,
                                    'price' => $batchPrice,
                                    'qty' => $takeQty,
                                    'subtotal' => $batchSubtotal,
                                ];
                                $remainingStockByBatch[(int) $batch->id] = $availableInBatch - $takeQty;
                            }
                        } else {
                            foreach ($batches as $batch) {
                                if ($remainingQty <= 0) {
                                    break;
                                }

                                $availableInBatch = max(0, (int) ($remainingStockByBatch[(int) $batch->id] ?? 0));
                                $takeQty = min($remainingQty, $availableInBatch);
                                if ($takeQty <= 0) {
                                    continue;
                                }

                                $remainingQty -= $takeQty;
                                $batchPrice = (float) $batch->selling_price;
                                $batchSubtotal = $batchPrice * $takeQty;

                                $saleItems[] = [
                                    'product_id' => $product->id,
                                    'product_batch_id' => $batch->id,
                                    'product_name' => $product->name,
                                    'part_number' => $partNumber,
                                    'merge_stock' => true,
                                    'price' => $batchPrice,
                                    'qty' => $takeQty,
                                    'subtotal' => $batchSubtotal,
                                ];
                                $remainingStockByBatch[(int) $batch->id] = $availableInBatch - $takeQty;
                            }
                        }

                        if ($remainingQty > 0) {
                            $overflowBatch = $primaryBatch ?? $fallbackBatch ?? $batches->first();

                            if (! $overflowBatch) {
                                throw ValidationException::withMessages([
                                    'items' => ['Batch barang tidak tersedia.'],
                                ]);
                            }

                            $overflowPrice = (float) $overflowBatch->selling_price;
                            $overflowSubtotal = $overflowPrice * $remainingQty;

                            $saleItems[] = [
                                'product_id' => $product->id,
                                'product_batch_id' => $overflowBatch->id,
                                'product_name' => $product->name,
                                'part_number' => $partNumber,
                                'merge_stock' => true,
                                'price' => $overflowPrice,
                                'qty' => $remainingQty,
                                'subtotal' => $overflowSubtotal,
                            ];

                            $remainingStockByBatch[(int) $overflowBatch->id] = (int) ($remainingStockByBatch[(int) $overflowBatch->id] ?? 0) - $remainingQty;
                            $remainingQty = 0;
                        }
                    }
                }

                $discountPercent = isset($payload['discount_amount']) ? max(0, min(100, (float) $payload['discount_amount'])) : 0.0;
                $discountAmount = $total > 0 ? round(($total * $discountPercent) / 100, 2) : 0.0;
                $discountAmount = min($total, $discountAmount);
                $totalAfterDiscount = max(0, $total - $discountAmount);

                $paymentMethod = strtolower((string) ($payload['payment_method'] ?? 'cash'));
                $paidAmount = isset($payload['paid_amount']) ? (float) $payload['paid_amount'] : 0.0;
                $creditAmount = 0.0;
                $creditDueDate = null;
                $creditDays = null;
                $changeAmount = 0.0;

                if (! in_array($paymentMethod, ['cash', 'transfer', 'qris', 'debit', 'credit'], true)) {
                    throw ValidationException::withMessages([
                        'payment_method' => ['Metode pembayaran tidak valid.'],
                    ]);
                }

                if ($paymentMethod === 'cash') {
                    if ($paidAmount < $totalAfterDiscount) {
                        throw ValidationException::withMessages([
                            'paid_amount' => ['Jumlah bayar harus sama atau lebih besar dari total.'],
                        ]);
                    }

                    $changeAmount = max(0, $paidAmount - $totalAfterDiscount);
                } elseif ($paymentMethod === 'credit') {
                    // Kredit dapat memakai DP parsial. Sisa akan disimpan di credit_amount.
                    $paidAmount = min($totalAfterDiscount, max(0, $paidAmount));
                    $creditAmount = max(0, $totalAfterDiscount - $paidAmount);

                    if ($creditAmount > 0) {
                        $dueDateInput = $payload['credit_due_date'] ?? null;
                        $creditDaysInput = isset($payload['credit_days']) ? (int) $payload['credit_days'] : null;

                        if ($dueDateInput !== null && trim((string) $dueDateInput) !== '') {
                            try {
                                $creditDueDate = Carbon::parse((string) $dueDateInput)->startOfDay();
                            } catch (\Throwable) {
                                throw ValidationException::withMessages([
                                    'credit_due_date' => ['Credit due date is invalid.'],
                                ]);
                            }
                        } elseif ($creditDaysInput !== null && $creditDaysInput > 0) {
                            $creditDueDate = Carbon::today()->addDays($creditDaysInput)->startOfDay();
                        } else {
                            $creditDaysInput = 30;
                            $creditDueDate = Carbon::today()->addDays($creditDaysInput)->startOfDay();
                        }

                        if ($creditDueDate->lt(Carbon::today())) {
                            throw ValidationException::withMessages([
                                'credit_due_date' => ['Credit due date must be today or a future date.'],
                            ]);
                        }

                        $creditDays = Carbon::today()->diffInDays($creditDueDate);
                    }
                } else {
                    $paidAmount = $paidAmount > 0 ? $paidAmount : $totalAfterDiscount;
                    $changeAmount = max(0, $paidAmount - $totalAfterDiscount);
                }

                $sale = Sale::create([
                    'user_id' => $cashier->id,
                    'invoice_number' => $this->generateInvoiceNumber($invoicePeriod),
                    'customer_name' => $payload['customer_name'] ?? null,
                    'customer_phone' => $payload['customer_phone'] ?? null,
                    'po_number' => $payload['po_number'] ?? null,
                    'site_name' => $payload['site_name'] ?? null,
                    'cashier_service_name' => $payload['cashier_service_name'] ?? null,
                    'cashier_phone' => $payload['cashier_phone'] ?? null,
                    'total' => $totalAfterDiscount,
                    'discount_amount' => $discountAmount,
                    'payment_method' => $paymentMethod,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'credit_amount' => $creditAmount,
                    'credit_days' => $creditDays,
                    'credit_due_date' => $creditDueDate?->toDateString(),
                ]);

                $sale->items()->createMany($saleItems);

                foreach ($saleItems as $item) {
                    $batch = $batchesById->get($item['product_batch_id']);
                    if (! $batch) {
                        continue;
                    }

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
        } finally {
            $this->releaseInvoiceNumberLock($invoicePeriod);
        }

        AdminBesarCache::forgetToday();
        return $sale;
    }

    private function acquireInvoiceNumberLock(string $invoicePeriod): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $lockName = 'sales_invoice_number_' . $invoicePeriod;
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS lock_acquired', [$lockName]);

        if (! $result || (int) ($result->lock_acquired ?? 0) !== 1) {
            throw ValidationException::withMessages([
                'invoice_number' => ['Unable to reserve invoice number. Please try again.'],
            ]);
        }
    }

    private function releaseInvoiceNumberLock(string $invoicePeriod): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $lockName = 'sales_invoice_number_' . $invoicePeriod;

        try {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS lock_released', [$lockName]);
        } catch (\Throwable) {
            // Abaikan error release lock agar checkout tidak ikut gagal.
        }
    }

    private function generateInvoiceNumber(string $invoicePeriod): string
    {
        // Lanjutkan nomor bulanan dari invoice format baru maupun format lama harian
        // seperti INV-YYYYMMDD-0001 agar urutan bulan tidak kembali ke 0001.
        $prefix = "INV-{$invoicePeriod}-";

        $nextNumber = Sale::withTrashed()
            ->where('invoice_number', 'like', "INV-{$invoicePeriod}%")
            ->lockForUpdate()
            ->pluck('invoice_number')
            ->map(function (string $invoiceNumber) use ($invoicePeriod): int {
                if (! preg_match('/^INV-' . preg_quote($invoicePeriod, '/') . '(?:\d{2})?-(\d+)$/', $invoiceNumber, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() + 1;

        return sprintf('%s%04d', $prefix, $nextNumber);
    }
}

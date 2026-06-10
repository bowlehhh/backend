<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota <?php echo e($sale->invoice_number); ?></title>
    <style>
        :root { --sheet-width: 176mm; --sheet-border: 1.2px; --sheet-pad: 7px; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 0; background: #f3f4f6; }
        .wrap { width: var(--sheet-width); margin: 12px auto; background: #fff; border: var(--sheet-border) solid #111827; padding: var(--sheet-pad); box-sizing: border-box; }
        .header-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 8px; }
        .header-table td { border: 0; padding: 0; vertical-align: top; }
        .header-title { width: 100%; text-align: center; }
        .invoice-title { border: 1.2px solid #111827; padding: 4px 12px; font-size: 24px; font-weight: 800; letter-spacing: 0.6px; text-align: center; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #111827; table-layout: fixed; }
        .meta-table td { border: 1px solid #111827; padding: 4px 5px; font-size: 11px; }
        .meta-table .label { font-weight: 700; background: #f9fafb; width: 15%; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #111827; table-layout: fixed; }
        th, td { border: 1px solid #111827; padding: 2px 3px; font-size: 10px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; text-align: center; }
        td.num { text-align: right; }
        .desc { min-height: 0; line-height: 1.15; }
        .item-table th, .item-table td { overflow-wrap: anywhere; word-break: break-word; }
        .item-table .col-no { width: 4.5%; }
        .item-table .col-name { width: 23%; }
        .item-table .col-part { width: 18%; }
        .item-table .col-qty { width: 5.5%; }
        .item-table .col-unit { width: 5.5%; }
        .item-table .col-price { width: 17%; }
        .item-table .col-total { width: 26.5%; }
        .totals { margin-top: 0; width: 100%; border-collapse: collapse; }
        .totals td { border: 1px solid #111827; padding: 4px 5px; font-size: 10px; }
        .totals .label { text-align: right; font-weight: 700; background: #f9fafb; }
        .totals .grand td { font-size: 14px; font-weight: 800; }
        .foot-table { width: 100%; border-collapse: collapse; margin-top: 6px; table-layout: fixed; }
        .foot-table td { vertical-align: top; font-size: 10px; }
        .foot-table .note-col { width: 72%; padding-right: 10px; line-height: 1.3; }
        .foot-table .sign-col { width: 28%; text-align: center; }
        .sign-box { min-height: 72px; }
        .sign-box .sign-bottom { margin-top: 30px; }
        .actions { margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap; }
        .btn { border: 1px solid #0f766e; background: #0f766e; color: #fff; padding: 7px 10px; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 11px; }
        .btn.secondary { background: #fff; color: #0f766e; }
        @page { size: A4 landscape; margin: 2.5mm; }
        @media print {
            body { background: #fff; }
            .wrap { width: auto; margin: 0; border: var(--sheet-border) solid #111827; padding: 2.5mm; transform: none; transform-origin: initial; }
            .actions { display: none; }
            .header-table, .meta-table, .item-table, .totals, .foot-table { page-break-inside: auto; }
            .item-table thead { display: table-header-group; }
            .item-table tr { break-inside: avoid; page-break-inside: avoid; }
            .item-table th, .item-table td { padding-top: 1px; padding-bottom: 1px; font-size: 7.5px; line-height: 1.05; }
            .meta-table td, .totals td { padding-top: 2px; padding-bottom: 2px; font-size: 8px; line-height: 1.05; }
            .header-table td { padding: 0; }
            .foot-table, .return-history, .installment-history, .footer-note-print { display: none !important; }
            .invoice-title { font-size: 18px; padding: 2px 8px; }
            .meta-table .label { width: 13%; }
            .item-table .col-no { width: 3.5%; }
            .item-table .col-name { width: 21%; }
            .item-table .col-part { width: 15%; }
            .item-table .col-qty { width: 5%; }
            .item-table .col-unit { width: 5%; }
            .item-table .col-price { width: 18%; }
            .item-table .col-total { width: 32.5%; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<?php
    $isCredit = strtolower((string) $sale->payment_method) === 'credit';
    $downPayment = (float) ($sale->paid_amount ?? 0);
    $installmentPaid = (float) ($sale->installments?->sum('amount') ?? 0);
    $installmentReceived = (float) ($sale->installments?->sum('received_amount') ?? 0);
    $installmentChange = (float) ($sale->installments?->sum('change_amount') ?? 0);
    $installments = collect($sale->installments ?? [])->sortBy([
        ['paid_at', 'asc'],
        ['id', 'asc'],
    ])->values();
    $lastInstallment = $installments->last();
    $lastInstallmentApplied = (float) ($lastInstallment->amount ?? 0);
    $lastInstallmentReceived = (float) ($lastInstallment->received_amount ?? $lastInstallmentApplied);
    $lastInstallmentChange = (float) ($lastInstallment->change_amount ?? 0);
    $creditOutstanding = (float) ($sale->credit_amount ?? 0);
    $creditOutstandingBeforeLastInstallment = $lastInstallment ? ($creditOutstanding + $lastInstallmentApplied) : $creditOutstanding;
    $creditDays = (int) ($sale->credit_days ?? 0);
    $totalReturned = (float) ($sale->returns?->sum('return_total') ?? 0);
    $totalRefundActual = (float) ($sale->returns?->sum('refund_amount') ?? 0);
    $exchangeTotal = (float) ($sale->returns?->sum('exchange_total') ?? 0);
    $priceDifferenceTotal = (float) ($sale->returns?->sum('price_difference_total') ?? 0);
    $extraPaymentTotal = (float) ($sale->returns?->sum('extra_payment_amount') ?? 0);
    $extraPaymentChangeTotal = (float) ($sale->returns?->sum('extra_payment_change_amount') ?? 0);
    $settlementRemainingTotal = $priceDifferenceTotal > 0 ? max(0, $priceDifferenceTotal - $extraPaymentTotal) : 0;
    $refundRemainingTotal = $priceDifferenceTotal < 0 ? max(0, abs($priceDifferenceTotal) - $extraPaymentTotal) : 0;
    $returnItemsBySaleItem = collect($sale->returns ?? [])
        ->flatMap(fn ($return) => $return->items ?? [])
        ->groupBy('sale_item_id');
    $returnRecords = collect($sale->returns ?? [])->sortBy([
        ['returned_at', 'asc'],
        ['id', 'asc'],
    ])->values();
    $hasReturnItems = $returnItemsBySaleItem->isNotEmpty();
    $displayItems = collect($sale->items ?? [])
        ->groupBy(fn ($item) => strtoupper(trim((string) ($item->part_number ?? $item->product?->barcode ?? 'PRODUCT-' . ($item->product_id ?? 0)))))
        ->map(function ($items) {
            $first = $items->first();
            $uniquePrices = $items
                ->pluck('price')
                ->map(fn ($price) => (float) $price)
                ->unique()
                ->values();
            $priceBreakdown = $items
                ->groupBy(fn ($item) => number_format((float) $item->price, 2, '.', ''))
                ->map(function ($priceItems, $priceKey) {
                    $qty = (int) $priceItems->sum('qty');
                    $price = (float) $priceKey;

                    return [
                        'price' => $price,
                        'qty' => $qty,
                        'subtotal' => $price * $qty,
                    ];
                })
                ->sortBy('price')
                ->values();

            return [
                'product_name' => (string) ($first->product_name ?: $first->product?->name ?: '-'),
                'part_number' => (string) ($first->part_number ?: $first->product?->barcode ?: '-'),
                'unit' => (string) ($first->product?->unit ?: '-'),
                'qty' => (int) $items->sum('qty'),
                'subtotal' => (float) $items->sum('subtotal'),
                'price' => $uniquePrices->count() === 1 ? (float) $uniquePrices->first() : null,
                'has_mixed_price' => $uniquePrices->count() > 1,
                'price_breakdown' => $priceBreakdown,
            ];
        })
        ->values();
    $paymentStatus = ($isCredit && $creditOutstanding > 0) ? 'BELUM LUNAS' : 'LUNAS';
    $finalGrandTotal = $isCredit ? $creditOutstanding : (float) $sale->total;
    $remainingAfterEntry = max(0, (float) $sale->total - $downPayment);
    $cashierDisplayName = $sale->cashier_display_name;
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div style="width: var(--sheet-width); margin: 12px auto 0; background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px 14px; box-sizing: border-box; font-size: 13px; font-weight: 700;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<div class="wrap">
    <table class="header-table">
        <tr>
            <td class="header-title">
                <div class="invoice-title">INVOICE PENJUALAN</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="label">No. Invoice</td>
            <td><?php echo e($sale->invoice_number); ?></td>
            <td class="label">Tanggal</td>
            <td><?php echo e($sale->created_at?->format('d M Y H:i')); ?></td>
        </tr>
        <tr>
            <td class="label">Pembeli</td>
            <td><?php echo e($sale->customer_name ?: '-'); ?></td>
            <td class="label">Pelayan</td>
            <td><?php echo e($sale->cashier_display_name); ?></td>
        </tr>
        <tr>
            <td class="label">Metode Bayar</td>
            <td><?php echo e(strtoupper($sale->payment_method)); ?></td>
            <td class="label">Jatuh Tempo</td>
            <td><?php echo e($sale->credit_due_date?->format('d M Y') ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Tempo Kredit</td>
            <td><?php echo e($creditDays > 0 ? ($creditDays . ' hari') : '-'); ?></td>
            <td class="label">Status</td>
            <td><?php echo e($paymentStatus); ?></td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-name">Part Name</th>
            <th class="col-part">Part Number</th>
            <th class="col-qty">Qty</th>
            <th class="col-unit">Unit</th>
            <th class="col-price">Harga</th>
            <th class="col-total">Nilai</th>
        </tr>
        </thead>
        <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $displayItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td class="col-no" style="text-align:center;"><?php echo e($idx + 1); ?></td>
                <td class="desc col-name"><strong><?php echo e($item['product_name']); ?></strong></td>
                <td class="desc col-part"><span style="font-size: 9px; color: #64748b;"><?php echo e($item['part_number']); ?></span></td>
                <td class="col-qty" style="text-align:center;"><?php echo e($item['qty']); ?></td>
                <td class="col-unit" style="text-align:center;"><?php echo e($item['unit']); ?></td>
                <td class="num col-price">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['has_mixed_price']): ?>
                        Rp <?php echo e(number_format((float) $item['subtotal'], 0, ',', '.')); ?>

                    <?php else: ?>
                        Rp <?php echo e(number_format((float) ($item['price'] ?? 0), 0, ',', '.')); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="num col-total">Rp <?php echo e(number_format((float) $item['subtotal'], 0, ',', '.')); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasReturnItems): ?>
        <table style="margin-top: 6px; font-size: 9px;" class="return-history">
            <thead>
            <tr>
                <th colspan="5">Track Record Retur</th>
            </tr>
            <tr>
                <th style="width: 18%;">No Retur / Tanggal</th>
                <th style="width: 24%;">Produk</th>
                <th style="width: 12%;">Qty Retur</th>
                <th style="width: 28%;">Barang Pengganti</th>
                <th style="width: 18%;">Selisih</th>
            </tr>
            </thead>
            <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $returnRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $returnRecord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $returnRecord->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $returnItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $replacementDetails = $returnItem->replacementDetailsResolved ?? [];
                        $partNumber = (string) ($returnItem->productBatch?->product?->barcode ?? $returnItem->product?->barcode ?? '-');
                        $partName = (string) ($returnItem->product_name ?: $returnItem->product?->name ?: '-');
                        $replacementQtyTotal = (int) collect($replacementDetails)->sum(fn (array $replacement): int => (int) ($replacement['qty'] ?? 0));
                        $returnDifference = (float) $returnItem->priceDifferenceResolved;
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;"><?php echo e($returnRecord->return_number); ?></div>
                            <div style="color:#64748b;"><?php echo e($returnRecord->created_at?->format('d M Y H:i')); ?></div>
                        </td>
                        <td>
                            <div style="font-weight:700;"><?php echo e($partName); ?></div>
                            <div style="color:#64748b;">Part No: <?php echo e($partNumber); ?></div>
                        </td>
                        <td class="num">
                            <div style="font-weight:700;"><?php echo e((int) $returnItem->qty); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($replacementQtyTotal > 0): ?>
                                <div style="font-size: 9px; color: #0f766e;">Ganti <?php echo e($replacementQtyTotal); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($replacementDetails)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $replacementDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $replacementLineIndex => $replacement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div style="margin-bottom: 2px;">
                                        <?php echo e($replacementLineIndex + 1); ?>. <?php echo e($replacement['part_name'] ?? ($replacement['label'] ?? '-')); ?>

                                        [<?php echo e($replacement['part_number'] ?? ($replacement['label'] ?? '-')); ?>]
                                        x<?php echo e((int) ($replacement['qty'] ?? 0)); ?>

                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="num">
                            <?php echo e($returnDifference > 0 ? 'Rp ' . number_format($returnDifference, 0, ',', '.') . ' +' : 'Rp ' . number_format(abs($returnDifference), 0, ',', '.') . ' -'); ?>

                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="totals">
        <tr><td class="label">SUBTOTAL</td><td class="num">Rp <?php echo e(number_format((float) $sale->total, 0, ',', '.')); ?></td></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCredit): ?>
            <tr><td class="label">DP / UANG MUKA</td><td class="num">Rp <?php echo e(number_format($downPayment, 0, ',', '.')); ?></td></tr>
            <tr><td class="label">PEMBAYARAN TERAKHIR</td><td class="num">Rp <?php echo e(number_format($lastInstallmentReceived, 0, ',', '.')); ?></td></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastInstallment): ?>
                <tr><td class="label">SISA SEBELUM BAYAR TERAKHIR</td><td class="num">Rp <?php echo e(number_format($creditOutstandingBeforeLastInstallment, 0, ',', '.')); ?></td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>
            <tr><td class="label">BAYAR</td><td class="num">Rp <?php echo e(number_format($downPayment, 0, ',', '.')); ?></td></tr>
            <tr><td class="label">KEMBALIAN</td><td class="num">Rp <?php echo e(number_format((float) $sale->change_amount, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <tr><td class="label">TOTAL RETUR BARANG</td><td class="num">Rp <?php echo e(number_format($totalReturned, 0, ',', '.')); ?></td></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalRefundActual > 0): ?>
            <tr><td class="label">REFUND AKTUAL</td><td class="num">Rp <?php echo e(number_format($totalRefundActual, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exchangeTotal > 0): ?>
            <tr><td class="label">NILAI BARANG PENGGANTI</td><td class="num">Rp <?php echo e(number_format($exchangeTotal, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($priceDifferenceTotal !== 0.0): ?>
            <tr>
                <td class="label"><?php echo e($priceDifferenceTotal > 0 ? 'SELISIH TAMBAHAN' : 'SELISIH REFUND'); ?></td>
                <td class="num">Rp <?php echo e(number_format(abs($priceDifferenceTotal), 0, ',', '.')); ?></td>
            </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraPaymentTotal > 0): ?>
            <tr><td class="label">UANG SELISIH DIBAYAR</td><td class="num">Rp <?php echo e(number_format($extraPaymentTotal, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settlementRemainingTotal > 0): ?>
            <tr><td class="label">SELISIH MASUK KE KREDIT</td><td class="num">Rp <?php echo e(number_format($settlementRemainingTotal, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($refundRemainingTotal > 0): ?>
            <tr><td class="label">REFUND BELUM DIBAYAR</td><td class="num">Rp <?php echo e(number_format($refundRemainingTotal, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraPaymentChangeTotal > 0): ?>
            <tr><td class="label">KEMBALIAN SELISIH</td><td class="num">Rp <?php echo e(number_format($extraPaymentChangeTotal, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCredit): ?>
            <tr class="grand"><td class="label">SISA AKHIR CICILAN</td><td class="num">Rp <?php echo e(number_format($finalGrandTotal, 0, ',', '.')); ?></td></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastInstallmentChange > 0): ?>
                <tr><td class="label">UANG SISA / KEMBALIAN</td><td class="num">Rp <?php echo e(number_format($lastInstallmentChange, 0, ',', '.')); ?></td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>
            <tr class="grand"><td class="label">GRAND TOTAL</td><td class="num">Rp <?php echo e(number_format($finalGrandTotal, 0, ',', '.')); ?></td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCredit && ($sale->installments?->isNotEmpty() ?? false)): ?>
        <table style="margin-top: 6px;" class="installment-history">
            <thead>
            <tr>
                <th colspan="4">Riwayat Cicilan Kredit</th>
            </tr>
            <tr>
                <th style="width: 34%;">Tanggal</th>
                <th style="width: 22%;">Nominal</th>
                <th style="width: 22%;">Sisa Sesudah</th>
                <th style="width: 22%;">Admin</th>
            </tr>
            </thead>
            <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $installments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $installment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $remainingAfterEntry = max(0, $remainingAfterEntry - (float) $installment->amount);
                ?>
                <tr>
                    <td><?php echo e($installment->paid_at?->format('d M Y H:i') ?: '-'); ?></td>
                    <td class="num">Rp <?php echo e(number_format((float) $installment->amount, 0, ',', '.')); ?></td>
                    <td class="num">Rp <?php echo e(number_format($remainingAfterEntry, 0, ',', '.')); ?></td>
                    <td><?php echo e($cashierDisplayName); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <table class="foot-table">
        <tr>
            <td class="note-col footer-note-print">
                <strong>Catatan:</strong> Simpan faktur ini sebagai bukti transaksi resmi.
            </td>
            <td class="sign-col">
                <div class="sign-box">
                    <div><?php echo e($sale->created_at?->format('d M Y')); ?></div>
                    <div class="sign-bottom">
                        <strong><?php echo e($cashierDisplayName); ?></strong><br>
                        <?php echo e($sale->cashier_phone ?: 'Admin'); ?>

                    </div>
                </div>
            </td>
        </tr>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! (($pdf ?? false) || request()->boolean('pdf'))): ?>
        <div class="actions">
            <button type="button" class="btn" onclick="window.print()">Print Nota</button>
            <a href="<?php echo e($historyUrl ?? route('cashier.history')); ?>" class="btn secondary"><?php echo e($historyLabel ?? 'Kembali ke History'); ?></a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($showNewTransactionButton ?? true) === true): ?>
                <a href="<?php echo e($newTransactionUrl ?? route('cashier.dashboard')); ?>" class="btn secondary">Transaksi Baru</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->boolean('print')): ?>
    <script>
        window.addEventListener('load', function () {
            const originalTitle = document.title;
            document.title = '';
            window.print();
            setTimeout(function () {
                document.title = originalTitle;
            }, 300);
        });
    </script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($installmentChange > 0): ?>
<div style="margin-top:12px;padding:8px 10px;border:1px solid #fde68a;background:#fffbeb;color:#92400e;font-size:12px;font-weight:600;">
    Uang sisa / kembalian: Rp <?php echo e(number_format($installmentChange, 0, ',', '.')); ?>

</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</body>
</html>
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/cashier/receipt.blade.php ENDPATH**/ ?>
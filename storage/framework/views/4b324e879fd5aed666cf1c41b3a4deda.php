<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Cicilan Kredit</title>
    <style>
        body { font-family: Arial, sans-serif; color:#111; background:#fff; }
        .sheet { max-width: 900px; margin: 20px auto; border: 2px solid #111827; padding: 16px; }
        .row { display:flex; justify-content:space-between; gap:16px; }
        .title { font-size: 34px; font-weight: 800; margin:0; }
        .sub { margin: 4px 0; }
        table { width:100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #111827; padding: 8px; text-align:left; }
        .num { text-align:right; }
        .section-title { margin-top: 16px; font-size: 18px; font-weight: 700; }
        .actions { margin-top: 14px; display:flex; gap:10px; }
        .btn { border:1px solid #006948; color:#006948; text-decoration:none; padding:8px 14px; border-radius:8px; font-weight:600; }
        .btn.fill { background:#006948; color:#fff; }
        @media print { .actions { display:none; } .sheet { margin:0; border-width:2px; } }
    </style>
</head>
<body>
<?php
    $qty = (int) ($batch->stock ?? 0);
    $price = (float) ($batch->purchase_price ?? 0);
    $subtotal = $qty * $price;
    $expeditionCost = (float) ($batch->expedition_cost ?? 0);
    $downPayment = (float) ($downPayment ?? 0);
    $installmentPaid = (float) ($installmentPaid ?? 0);
    $paymentHistory = $paymentHistory ?? [];
    $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $dueDate = $batch->credit_due_date;
    $dueDateText = $dueDate ? $dueDate->format('d') . ' ' . $monthNames[(int) $dueDate->format('n')] . ' ' . $dueDate->format('Y') : '-';
    $dueDateDisplay = $dueDate
        ? (((int) ($batch->credit_days ?? 0) > 0 ? ((int) $batch->credit_days . ' hari ') : '') . '(' . $dueDateText . ')')
        : '-';
?>
<div class="sheet">
    <div class="row">
        <div>
            <h1 class="title">NOTA CICILAN KREDIT</h1>
            <p class="sub"><strong>Supplier:</strong> <?php echo e($batch->supplier?->name ?? '-'); ?></p>
            <p class="sub"><strong>Part Number:</strong> <?php echo e($batch->product?->barcode ?? '-'); ?></p>
            <p class="sub"><strong>Part Name:</strong> <?php echo e($batch->product?->name ?? '-'); ?></p>
        </div>
        <div>
            <p class="sub"><strong>Cicilan Ke:</strong> <?php echo e($installmentNumber ?? 1); ?></p>
            <p class="sub"><strong>Tanggal Bayar:</strong> <?php echo e(optional($installment->paid_at)->format('d M Y') ?? '-'); ?></p>
            <p class="sub"><strong>Jatuh Tempo:</strong> <?php echo e($dueDateDisplay); ?></p>
            <p class="sub"><strong>Jam Input:</strong> <?php echo e(optional($installment->created_at)->format('H:i:s') ?? '-'); ?></p>
            <p class="sub"><strong>Diproses Oleh:</strong> <?php echo e($installment->processed_by ?? $installment->user?->name ?? '-'); ?></p>
        </div>
    </div>

    <table>
        <tr><th>Subtotal Barang</th><td class="num">Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?></td></tr>
        <tr><th>Biaya Ekspedisi</th><td class="num">Rp <?php echo e(number_format($expeditionCost, 0, ',', '.')); ?></td></tr>
        <tr><th>Total Kredit</th><td class="num">Rp <?php echo e(number_format((float) $totalCredit, 0, ',', '.')); ?></td></tr>
        <tr><th>DP / Uang Muka</th><td class="num">Rp <?php echo e(number_format($downPayment, 0, ',', '.')); ?></td></tr>
        <tr><th>Total Cicilan</th><td class="num">Rp <?php echo e(number_format($installmentPaid, 0, ',', '.')); ?></td></tr>
        <tr><th>Nominal Cicilan Ini</th><td class="num">Rp <?php echo e(number_format((float) $installment->amount, 0, ',', '.')); ?></td></tr>
        <tr><th>Total Sudah Dibayar</th><td class="num">Rp <?php echo e(number_format((float) $totalPaid, 0, ',', '.')); ?></td></tr>
        <tr><th>Sisa Kredit</th><td class="num">Rp <?php echo e(number_format((float) $remainingCredit, 0, ',', '.')); ?></td></tr>
        <tr><th>Catatan</th><td><?php echo e($installment->note ?: '-'); ?></td></tr>
    </table>

    <div class="section-title">Riwayat Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Nominal</th>
                <th>Diproses Oleh</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php $paymentRowNumber = 0; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($paymentHistory ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $isDownPayment = ($payment['type'] ?? '') === 'DP / Uang Muka';
                    if (! $isDownPayment) {
                        $paymentRowNumber++;
                    }
                ?>
                <tr>
                    <td><?php echo e($isDownPayment ? 'DP' : $paymentRowNumber); ?></td>
                    <td><?php echo e($payment['type'] ?? '-'); ?></td>
                    <td><?php echo e($payment['date'] ?? '-'); ?></td>
                    <td><?php echo e($payment['time'] ?? '-'); ?></td>
                    <td class="num">Rp <?php echo e(number_format((float) ($payment['amount'] ?? 0), 0, ',', '.')); ?></td>
                    <td><?php echo e($payment['processed_by'] ?? $payment['user'] ?? '-'); ?></td>
                    <td><?php echo e($payment['note'] ?? '-'); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="7" class="center">Belum ada riwayat pembayaran.</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! ($pdf ?? false)): ?>
        <div class="actions">
            <button class="btn fill" onclick="window.print()">Print Nota</button>
            <a class="btn" href="<?php echo e(route('admin.credits.installment.receipt', ['batch' => $batch->id, 'installment' => $installment->id, 'pdf' => 1])); ?>">Download PDF</a>
            <a class="btn" href="<?php echo e(url('/admin/products')); ?>">Kembali ke Barang</a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views/admin/credit-installment-receipt.blade.php ENDPATH**/ ?>
<?php
    $storeName = config('app.name', 'Toko Pak Paul');
    $supplierName = $batch->supplier?->name ?? '-';
    $partNumber = strtoupper((string) ($batch->product?->barcode ?? '-'));
    $partName = strtoupper((string) ($batch->product?->name ?? '-'));
    $unit = strtoupper((string) ($batch->product?->unit ?? '-'));
    $qty = (int) ($batch->stock ?? 0);
    $price = (float) ($batch->purchase_price ?? 0);
    $expeditionCost = (float) ($batch->expedition_cost ?? 0);
    $subtotal = $qty * $price;
    $total = (float) $totalCredit;
    $downPayment = (float) ($downPayment ?? 0);
    $installmentPaid = (float) ($installmentPaid ?? 0);
    $paid = (float) $totalPaid;
    $remaining = (float) $remainingCredit;
    $paymentHistory = $paymentHistory ?? [];
    $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $dueDate = $batch->credit_due_date;
    $dueDateText = $dueDate ? $dueDate->format('d') . ' ' . $monthNames[(int) $dueDate->format('n')] . ' ' . $dueDate->format('Y') : '-';
    $dueDateDisplay = $dueDate
        ? (((int) ($batch->credit_days ?? 0) > 0 ? ((int) $batch->credit_days . ' hari ') : '') . '(' . $dueDateText . ')')
        : '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Kredit Batch #<?php echo e($batch->id); ?></title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #eef2f7; font-family: Arial, Helvetica, sans-serif; color: #0f172a; }
        .paper { max-width: 960px; margin: 16px auto; background: #fff; border: 2px solid #0f172a; padding: 16px; }
        .head { display: flex; justify-content: space-between; gap: 16px; }
        .title { font-size: 28px; font-weight: 800; margin: 0; }
        .sub { margin: 6px 0 0; font-size: 13px; }
        .meta { text-align: right; font-size: 14px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #0f172a; padding: 8px; font-size: 14px; vertical-align: top; }
        th { background: #f1f5f9; text-transform: uppercase; font-size: 12px; letter-spacing: .02em; }
        .num { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .section-title { margin-top: 16px; font-size: 18px; font-weight: 700; }
        .actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border: 1px solid #0b7a5a; color: #0b7a5a; padding: 10px 14px; border-radius: 10px; text-decoration: none; font-weight: 700; background: #fff; }
        .btn.primary { background: #0b7a5a; color: #fff; }
        @media print {
            body { background: #fff; }
            .paper { margin: 0; max-width: none; border-width: 1px; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="paper">
        <div class="head">
            <div>
                <h1 class="title"><?php echo e(strtoupper($storeName)); ?></h1>
                <p class="sub">NOTA KREDIT SUPPLIER</p>
                <p class="sub">Supplier: <strong><?php echo e($supplierName); ?></strong></p>
            </div>
            <div class="meta">
                <div>Tanggal Cetak: <?php echo e($printedAt->format('d M Y H:i')); ?></div>
                <div>Batch ID: #<?php echo e($batch->id); ?></div>
                <div>Jatuh Tempo: <?php echo e($dueDateDisplay); ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Part Number</th>
                    <th>Part Name</th>
                    <th class="center">Unit</th>
                    <th class="center">Qty</th>
                    <th class="num">Harga Beli</th>
                    <th class="num">Subtotal</th>
                    <th class="num">Biaya Ekspedisi</th>
                    <th class="num">Total Kredit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo e($partNumber); ?></td>
                    <td><?php echo e($partName); ?></td>
                    <td class="center"><?php echo e($unit); ?></td>
                    <td class="center"><?php echo e(number_format($qty, 0, ',', '.')); ?></td>
                    <td class="num">Rp <?php echo e(number_format($price, 0, ',', '.')); ?></td>
                    <td class="num">Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?></td>
                    <td class="num">Rp <?php echo e(number_format($expeditionCost, 0, ',', '.')); ?></td>
                    <td class="num">Rp <?php echo e(number_format($total, 0, ',', '.')); ?></td>
                </tr>
            </tbody>
        </table>

        <table>
            <tbody>
                <tr><td><strong>DP / Uang Muka</strong></td><td class="num"><strong>Rp <?php echo e(number_format($downPayment, 0, ',', '.')); ?></strong></td></tr>
                <tr><td><strong>Total Cicilan</strong></td><td class="num"><strong>Rp <?php echo e(number_format($installmentPaid, 0, ',', '.')); ?></strong></td></tr>
                <tr><td><strong>Total Dibayar</strong></td><td class="num"><strong>Rp <?php echo e(number_format($paid, 0, ',', '.')); ?></strong></td></tr>
                <tr><td><strong>Sisa Kredit</strong></td><td class="num"><strong>Rp <?php echo e(number_format($remaining, 0, ',', '.')); ?></strong></td></tr>
                <tr><td><strong>Status</strong></td><td class="num"><strong><?php echo e($remaining <= 0 ? 'LUNAS' : 'BELUM LUNAS'); ?></strong></td></tr>
            </tbody>
        </table>

        <div class="section-title">Riwayat Pembayaran</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Tanggal Bayar</th>
                    <th>Jam</th>
                    <th class="num">Nominal</th>
                    <th>Diproses Oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php $paymentRowNumber = 0; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $paymentHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $isDownPayment = ($payment['type'] ?? '') === 'DP / Uang Muka';
                        if (! $isDownPayment) {
                            $paymentRowNumber++;
                        }
                    ?>
                    <tr>
                        <td class="center"><?php echo e($isDownPayment ? 'DP' : $paymentRowNumber); ?></td>
                        <td><?php echo e($payment['type'] ?? '-'); ?></td>
                        <td><?php echo e($payment['date'] ?? '-'); ?></td>
                        <td><?php echo e($payment['time'] ?? '-'); ?></td>
                        <td class="num">Rp <?php echo e(number_format((float) ($payment['amount'] ?? 0), 0, ',', '.')); ?></td>
                        <td><?php echo e($payment['user'] ?? '-'); ?></td>
                        <td><?php echo e($payment['note'] ?? '-'); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="7" class="center">Belum ada riwayat pembayaran.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <div class="actions">
            <button type="button" class="btn primary" onclick="window.print()">Print Nota</button>
            <a class="btn" href="<?php echo e(url('/admin/admin-module?type=credits')); ?>">Kembali ke Kredit</a>
            <a class="btn" href="<?php echo e(route('admin.credits.receipt', ['batch' => $batch->id, 'pdf' => 1])); ?>">Download PDF</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views/admin/credit-receipt.blade.php ENDPATH**/ ?>
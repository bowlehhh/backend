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
        .actions { margin-top: 14px; display:flex; gap:10px; }
        .btn { border:1px solid #006948; color:#006948; text-decoration:none; padding:8px 14px; border-radius:8px; font-weight:600; }
        .btn.fill { background:#006948; color:#fff; }
        @media print { .actions { display:none; } .sheet { margin:0; border-width:2px; } }
    </style>
</head>
<body>
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
            <p class="sub"><strong>Jam Input:</strong> <?php echo e(optional($installment->created_at)->format('H:i:s') ?? '-'); ?></p>
            <p class="sub"><strong>Kasir:</strong> <?php echo e($installment->user?->name ?? '-'); ?></p>
        </div>
    </div>

    <table>
        <tr><th>Total Kredit</th><td class="num">Rp <?php echo e(number_format((float) $totalCredit, 0, ',', '.')); ?></td></tr>
        <tr><th>Nominal Cicilan Ini</th><td class="num">Rp <?php echo e(number_format((float) $installment->amount, 0, ',', '.')); ?></td></tr>
        <tr><th>Total Sudah Dibayar</th><td class="num">Rp <?php echo e(number_format((float) $totalPaid, 0, ',', '.')); ?></td></tr>
        <tr><th>Sisa Kredit</th><td class="num">Rp <?php echo e(number_format((float) $remainingCredit, 0, ',', '.')); ?></td></tr>
        <tr><th>Catatan</th><td><?php echo e($installment->note ?: '-'); ?></td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Nominal</th>
                <th>Kasir</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($allInstallments ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($idx + 1); ?></td>
                    <td><?php echo e(optional($row->paid_at)->format('d M Y') ?? '-'); ?></td>
                    <td><?php echo e(optional($row->created_at)->format('H:i:s') ?? '-'); ?></td>
                    <td class="num">Rp <?php echo e(number_format((float) $row->amount, 0, ',', '.')); ?></td>
                    <td><?php echo e($row->user?->name ?? '-'); ?></td>
                    <td><?php echo e($row->note ?: '-'); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>

    <div class="actions">
        <button class="btn fill" onclick="window.print()">Print Nota</button>
        <a class="btn" href="<?php echo e(route('admin.credits.installment.receipt', ['batch' => $batch->id, 'installment' => $installment->id, 'pdf' => 1])); ?>">Download PDF</a>
        <a class="btn" href="<?php echo e(route('admin.credits.detail', ['batch' => $batch->id])); ?>">Kembali</a>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views/admin/credit-installment-receipt.blade.php ENDPATH**/ ?>
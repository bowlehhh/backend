<?php
    $totalCredit = (float) ($totalCredit ?? 0);
    $downPayment = (float) ($downPayment ?? 0);
    $installmentPaid = (float) ($installmentPaid ?? 0);
    $totalPaid = (float) ($totalPaid ?? 0);
    $remainingCredit = (float) ($remainingCredit ?? 0);
    $paymentHistory = $paymentHistory ?? [];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kredit - <?php echo e($batch->product?->barcode ?? '-'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hanken Grotesk', sans-serif; }
        .card-shadow { box-shadow: 0 2px 6px rgba(17, 24, 39, 0.06); }
        .credit-input {
            width: 100%;
            border: 1.5px solid #b7c5be;
            border-radius: 0.75rem;
            background: #ffffff;
            color: #191c1e;
            padding: 0.75rem 0.9rem;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .credit-input:focus {
            border-color: #006948;
            box-shadow: 0 0 0 3px rgba(0, 105, 72, 0.14);
        }
        .credit-input::placeholder { color: #7b8b84; }
    </style>
</head>
<body class="bg-[#f4f7f6] text-[#191c1e]">
<main class="mx-auto max-w-7xl p-4 md:p-6">
    <div class="mb-5 rounded-2xl border border-[#d4dbd7] bg-white px-5 py-4 card-shadow">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Detail Kredit</h1>
                <p class="mt-1 text-[#52615a]">Pantau cicilan kredit, status pembayaran, dan cetak nota cicilan per transaksi.</p>
            </div>
            <a href="<?php echo e(url('/admin/admin-module?type=credits')); ?>" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Kembali ke Kredit</a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white overflow-hidden card-shadow">
        <div class="border-b border-[#d4dbd7] px-5 py-3 bg-[#f8fbfa]">
            <h2 class="text-lg font-bold text-[#193429]">Informasi Kredit</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-5">
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Supplier</p><p class="mt-1 text-2xl font-semibold"><?php echo e($batch->supplier?->name ?? '-'); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Part Number</p><p class="mt-1 text-2xl font-semibold"><?php echo e($batch->product?->barcode ?? '-'); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Part Name</p><p class="mt-1 text-2xl font-semibold"><?php echo e($batch->product?->name ?? '-'); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Unit</p><p class="mt-1 text-2xl font-semibold"><?php echo e($batch->product?->unit ?? '-'); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Qty</p><p class="mt-1 text-xl font-semibold"><?php echo e(number_format((int) ($batch->stock ?? 0), 0, ',', '.')); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Jatuh Tempo</p><p class="mt-1 text-xl font-semibold"><?php echo e($batch->credit_due_date?->format('d M Y') ?? '-'); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Total Kredit</p><p class="mt-1 text-xl font-bold text-[#0f4e3a]">Rp <?php echo e(number_format($totalCredit, 0, ',', '.')); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">DP / Uang Muka</p><p class="mt-1 text-xl font-bold text-[#1d4ed8]">Rp <?php echo e(number_format($downPayment, 0, ',', '.')); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Total Cicilan</p><p class="mt-1 text-xl font-bold text-[#0f4e3a]">Rp <?php echo e(number_format($installmentPaid, 0, ',', '.')); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Total Dibayar</p><p class="mt-1 text-xl font-bold text-[#0f8a54]">Rp <?php echo e(number_format($totalPaid, 0, ',', '.')); ?></p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Sisa Kredit</p><p class="mt-1 text-xl font-bold <?php echo e($remainingCredit > 0 ? 'text-[#b42318]' : 'text-[#0f8a54]'); ?>">Rp <?php echo e(number_format($remainingCredit, 0, ',', '.')); ?></p></div>
        </div>
    </section>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white p-5 card-shadow">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Input Cicilan</h2>
                <p class="text-sm text-[#52615a]">Isi nominal, tanggal bayar, lalu simpan. Riwayat otomatis tercatat dengan jam dan kasir.</p>
            </div>
            <div class="rounded-xl bg-[#eef7f3] px-4 py-2 text-sm text-[#006948] font-semibold">
                DP: Rp <?php echo e(number_format($downPayment, 0, ',', '.')); ?> | Sisa saat ini: Rp <?php echo e(number_format($remainingCredit, 0, ',', '.')); ?>

            </div>
        </div>
        <form method="POST" action="<?php echo e(route('admin.credits.installment', ['batch' => $batch->id])); ?>" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="redirect_to" value="detail">
            <div class="md:col-span-4">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#52615a]">Nominal Cicilan</label>
                <input type="text" name="amount" required placeholder="Contoh: 500000" class="credit-input">
            </div>
            <div class="md:col-span-3">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#52615a]">Tanggal Bayar</label>
                <input type="date" name="paid_at" value="<?php echo e(now()->toDateString()); ?>" class="credit-input">
            </div>
            <div class="md:col-span-5">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#52615a]">Catatan</label>
                <input type="text" name="note" placeholder="Opsional" class="credit-input">
            </div>
            <button type="submit" class="rounded-xl bg-[#006948] px-4 py-2.5 text-white font-semibold hover:bg-[#00563b] md:col-span-3">Simpan Cicilan</button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remainingCredit > 0): ?>
                <button type="submit" form="settleForm" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-emerald-700 font-semibold hover:bg-emerald-100 md:col-span-3">Lunaskan Sekarang</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </form>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remainingCredit > 0): ?>
            <form id="settleForm" method="POST" action="<?php echo e(route('admin.credits.settle', ['batch' => $batch->id])); ?>" class="hidden">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="detail">
                <button type="submit" onclick="return confirm('Lunaskan semua sisa kredit sekarang?')"></button>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    <section class="rounded-2xl border border-[#d4dbd7] bg-white overflow-hidden card-shadow">
        <div class="flex items-center justify-between border-b border-[#d4dbd7] px-5 py-4">
            <div>
                <h2 class="text-2xl font-bold">Riwayat Pembayaran Kredit</h2>
                <p class="text-sm text-[#52615a]">DP dan cicilan tercatat terpisah supaya alurnya jelas di nota.</p>
            </div>
            <a href="<?php echo e(route('admin.credits.receipt', ['batch' => $batch->id])); ?>" target="_blank" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Cetak Ringkasan</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm md:text-[15px]">
                <thead class="bg-[#eceef0] text-[#3d4a42]">
                    <tr>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jam</th>
                        <th class="px-4 py-3 text-left">Nominal</th>
                        <th class="px-4 py-3 text-left">Kasir</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $paymentHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-[#f8fbfa]">
                            <td class="px-4 py-3 font-semibold"><?php echo e($payment['type'] ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($payment['date'] ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($payment['time'] ?? '-'); ?></td>
                            <td class="px-4 py-3 font-semibold">Rp <?php echo e(number_format((float) ($payment['amount'] ?? 0), 0, ',', '.')); ?></td>
                            <td class="px-4 py-3"><?php echo e($payment['user'] ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($payment['note'] ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($payment['receipt_url'])): ?>
                                    <a href="<?php echo e($payment['receipt_url']); ?>" target="_blank" class="rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-xs font-semibold text-[#006948] hover:bg-[#eef7f3]">Cetak Nota</a>
                                <?php else: ?>
                                    <span class="text-xs text-[#52615a]">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="7" class="px-4 py-8 text-center text-[#52615a]">Belum ada riwayat pembayaran.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script>
    (function () {
        const amountInput = document.querySelector('input[name="amount"]');
        if (!amountInput) return;

        const formatRibuan = (value) => {
            const digits = String(value || '').replace(/\D/g, '');
            if (!digits) return '';
            return new Intl.NumberFormat('id-ID').format(Number(digits));
        };

        amountInput.addEventListener('input', () => {
            amountInput.value = formatRibuan(amountInput.value);
        });
    })();
</script>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views\admin\credit-detail.blade.php ENDPATH**/ ?>
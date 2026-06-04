<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cicilan - <?php echo e($sale->invoice_number); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<main class="mx-auto max-w-5xl p-4 lg:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Cicilan Transaksi</h1>
            <p class="text-sm text-slate-500">Invoice: <?php echo e($sale->invoice_number); ?> | Kasir: <?php echo e($sale->cashier_display_name); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('cashier.receipt', $sale)); ?>" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Lihat Nota</a>
            <a href="<?php echo e(route('cashier.history')); ?>" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke History</a>
        </div>
    </div>

    <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4">
        <div>
            <p class="text-xs uppercase text-slate-500">Total Transaksi</p>
            <p class="font-semibold">Rp <?php echo e(number_format((float) $sale->total, 0, ',', '.')); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">DP / Uang Muka</p>
            <p class="font-semibold">Rp <?php echo e(number_format((float) $sale->paid_amount, 0, ',', '.')); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Total Cicilan</p>
            <p class="font-semibold">Rp <?php echo e(number_format((float) $installmentPaid, 0, ',', '.')); ?></p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Sisa Kredit</p>
            <p class="font-semibold text-amber-700">Rp <?php echo e(number_format((float) $remainingCredit, 0, ',', '.')); ?></p>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid gap-4 lg:grid-cols-[1fr_380px]">
        <form method="POST" action="<?php echo e(route('cashier.history.installment.store', $sale)); ?>" class="rounded-2xl border border-slate-200 bg-white">
            <?php echo csrf_field(); ?>
            <div class="border-b border-slate-200 px-4 py-4">
                <h2 class="text-lg font-bold">Input Cicilan</h2>
                <p class="text-sm text-slate-500">Masukkan nominal cicilan yang dibayar customer sekarang.</p>
            </div>
            <div class="space-y-4 p-4">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700" for="amount">Nominal Cicilan</label>
                    <input id="amount" name="amount" type="text" inputmode="numeric" value="<?php echo e(old('amount', '0')); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm" data-rupiah-input />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700" for="note">Catatan</label>
                    <textarea id="note" name="note" rows="3" maxlength="1000" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Catatan tambahan bila ada"><?php echo e(old('note')); ?></textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-2">
                    <a href="<?php echo e(route('cashier.history')); ?>" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600">Simpan Cicilan</button>
                </div>
            </div>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-4">
                <h2 class="text-lg font-bold">Riwayat Cicilan</h2>
                <p class="text-sm text-slate-500">Semua cicilan yang sudah dibayar untuk invoice ini.</p>
            </div>
            <div class="max-h-[520px] overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Nominal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Kasir</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="border-t border-slate-100 <?php echo e(session('last_installment_id') && (int) session('last_installment_id') === (int) $row->id ? 'bg-emerald-50' : ''); ?>">
                                <td class="px-4 py-3"><?php echo e($row->paid_at?->format('d M Y H:i') ?: '-'); ?></td>
                                <td class="px-4 py-3 font-semibold">Rp <?php echo e(number_format((float) $row->amount, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3"><?php echo e($sale->cashier_display_name); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="<?php echo e(route('cashier.history.installment.receipt', ['sale' => $sale->id, 'installment' => $row->id])); ?>" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                                        <a href="<?php echo e(route('cashier.history.installment.receipt', ['sale' => $sale->id, 'installment' => $row->id, 'pdf' => 1])); ?>" target="_blank" rel="noopener" class="rounded-lg border border-emerald-700 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Print</a>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada cicilan untuk transaksi ini.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script>
    const amountInput = document.querySelector('[data-rupiah-input]');

    const formatRupiah = (value) => {
        const digits = String(value || '').replace(/[^\d]/g, '');
        if (digits === '') {
            return '';
        }
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    const syncRupiahInput = () => {
        if (!amountInput) {
            return;
        }

        const caretAtEnd = amountInput.selectionStart === amountInput.value.length;
        const formatted = formatRupiah(amountInput.value);
        amountInput.value = formatted;

        if (caretAtEnd) {
            amountInput.setSelectionRange(formatted.length, formatted.length);
        }
    };

    amountInput?.addEventListener('input', syncRupiahInput);
    amountInput?.addEventListener('blur', syncRupiahInput);
    syncRupiahInput();
</script>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views\cashier\installment-form.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail PT/CV - Toko Pak Paul</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[260px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <h1 class="text-3xl font-extrabold text-emerald-700">Toko Pak Paul</h1>
            <p class="text-xs text-slate-500">Kasir Terminal - Station 01</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="<?php echo e(route('cashier.dashboard')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span><span class="font-semibold">Register</span>
            </a>
            <a href="<?php echo e(route('cashier.history')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">history</span><span class="font-semibold">History</span>
            </a>
            <a href="<?php echo e(route('cashier.history.supplier')); ?>" class="flex items-center gap-3 rounded-xl bg-indigo-500 px-3 py-2 text-white">
                <span class="material-symbols-outlined">account_tree</span><span class="font-semibold">PT/CV</span>
            </a>
            <a href="<?php echo e(route('cashier.drafts')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">draft</span><span class="font-semibold">Draft</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-200">
            <form method="POST" action="<?php echo e(route('logout')); ?>" onsubmit="return confirm('Yakin ingin logout dari akun ini?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <span class="material-symbols-outlined">lock_clock</span><span class="font-semibold">Close Shift</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold">Detail PT/CV</h2>
                <p class="text-sm text-slate-500"><?php echo e($user?->name); ?> - Kasir</p>
            </div>
            <a href="<?php echo e(route('cashier.history.supplier')); ?>" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke PT/CV</a>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-4 md:px-5">
                <h3 class="text-2xl font-extrabold text-slate-900"><?php echo e($group['pt_name']); ?></h3>
                <p class="mt-1 text-sm text-slate-500">Semua transaksi dengan nama PT/CV yang sama akan masuk ke halaman ini.</p>
            </div>

            <div class="grid gap-3 border-b border-slate-200 bg-slate-50 p-4 md:grid-cols-5">
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi</p>
                    <p class="mt-1 text-lg font-bold text-slate-900"><?php echo e(number_format((int) $group['summary']['total_transaksi'])); ?> kali</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</p>
                    <p class="mt-1 text-lg font-bold text-slate-900"><?php echo e(number_format((int) $group['summary']['total_qty'])); ?></p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Nilai</p>
                    <p class="mt-1 text-lg font-bold text-slate-900"><?php echo e($group['summary']['total_nilai']); ?></p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kredit</p>
                    <p class="mt-1 text-lg font-bold text-amber-600"><?php echo e(number_format((int) $group['summary']['kredit'])); ?></p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lunas</p>
                    <p class="mt-1 text-lg font-bold text-emerald-600"><?php echo e(number_format((int) $group['summary']['lunas'])); ?></p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Metode</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Qty</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Subtotal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Kredit</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['transactions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $statusClass = $trx['status'] === 'LUNAS'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($trx['status'] === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                        ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold"><?php echo e($trx['invoice_number']); ?></td>
                            <td class="px-4 py-3"><?php echo e($trx['waktu']); ?></td>
                            <td class="px-4 py-3"><?php echo e($trx['metode']); ?></td>
                            <td class="px-4 py-3"><?php echo e(number_format((int) $trx['qty'])); ?></td>
                            <td class="px-4 py-3"><?php echo e($trx['subtotal']); ?></td>
                            <td class="px-4 py-3"><?php echo e($trx['credit_amount']); ?></td>
                            <td class="px-4 py-3"><?php echo e($trx['credit_due_date']); ?></td>
                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($trx['status']); ?></span></td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="<?php echo e(route('cashier.receipt', ['sale' => $trx['sale_id']])); ?>" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="<?php echo e(route('cashier.receipt', ['sale' => $trx['sale_id'], 'pdf' => 1])); ?>" target="_blank" rel="noopener" class="rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Nota</a>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi untuk PT/CV ini.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views\cashier\history-supplier-detail.blade.php ENDPATH**/ ?>
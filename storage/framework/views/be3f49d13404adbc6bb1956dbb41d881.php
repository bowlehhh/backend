<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>History PT/CV - Toko Pak Paul</title>
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
            <p class="text-xs text-slate-500">Admin Transaksi - Station 01</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="<?php echo e(route('cashier.dashboard')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span><span class="font-semibold">Transaksi</span>
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
        <div class="px-4 pb-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                <p class="px-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Master Data</p>
                <div class="mt-2 space-y-1">
                    <a href="<?php echo e(url('/admin/products')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">inventory_2</span><span class="font-semibold">Barang</span>
                    </a>
                    <a href="<?php echo e(url('/admin/suppliers')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">local_shipping</span><span class="font-semibold">Supplier</span>
                    </a>
                    <a href="<?php echo e(url('/admin/admin-module?type=product-groups')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">inventory_2</span><span class="font-semibold">Kelompok Barang</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="p-4 border-t border-slate-200">
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="js-logout-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <span class="material-symbols-outlined">lock_clock</span><span class="font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold">Kelompok Transaksi PT/CV</h2>
                <p class="text-sm text-slate-500"><?php echo e($user?->name); ?> - Admin</p>
            </div>
            <a href="<?php echo e(route('cashier.dashboard')); ?>" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Transaksi</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <h3 class="text-2xl font-extrabold text-slate-900"><?php echo e($group['pt_name']); ?></h3>
                        <p class="mt-1 text-sm text-slate-500">Riwayat transaksi PT/CV ini dikumpulkan otomatis berdasarkan nama yang sama.</p>
                    </div>
                    <a href="<?php echo e(route('cashier.history.supplier.detail', ['pt' => $group['pt_name']])); ?>" class="inline-flex items-center justify-center rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                        Detail
                    </a>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-5">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi</p>
                        <p class="mt-1 text-lg font-bold text-slate-900"><?php echo e(number_format((int) $group['summary']['total_transaksi'])); ?> kali</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</p>
                        <p class="mt-1 text-lg font-bold text-slate-900"><?php echo e(number_format((int) $group['summary']['total_qty'])); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Nilai</p>
                        <p class="mt-1 text-lg font-bold text-slate-900"><?php echo e($group['summary']['total_nilai']); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kredit</p>
                        <p class="mt-1 text-lg font-bold text-amber-600"><?php echo e(number_format((int) $group['summary']['kredit'])); ?></p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lunas</p>
                        <p class="mt-1 text-lg font-bold text-emerald-600"><?php echo e(number_format((int) $group['summary']['lunas'])); ?></p>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-8 text-center text-slate-500">
                Belum ada transaksi untuk dikelompokkan per PT/CV.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </main>
</div>
<?php echo $__env->make('cashier.partials.logout-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/cashier/history-supplier.blade.php ENDPATH**/ ?>
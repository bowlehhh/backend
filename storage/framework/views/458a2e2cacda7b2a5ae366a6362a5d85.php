<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail PT/CV - Admin Besar</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[260px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <h1 class="text-3xl font-extrabold text-emerald-700">Surya Duta Multindo</h1>
            <p class="text-xs text-slate-500">Admin Besar</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="<?php echo e(route('admin.admin-besar.index')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">dashboard</span><span class="font-semibold">Admin Besar</span>
            </a>
            <a href="<?php echo e(route('admin.admin-besar.history')); ?>" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">history</span><span class="font-semibold">History</span>
            </a>
            <a href="<?php echo e(route('admin.admin-besar.history.supplier')); ?>" class="flex items-center gap-3 rounded-xl bg-emerald-700 px-3 py-2 text-white">
                <span class="material-symbols-outlined">account_tree</span><span class="font-semibold">PT/CV</span>
            </a>
        </nav>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold">Detail PT/CV</h2>
                <p class="text-sm text-slate-500"><?php echo e($user?->name); ?> - Admin Besar</p>
            </div>
            <a href="<?php echo e(route('admin.admin-besar.index')); ?>" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Admin Besar</a>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-4 md:px-5">
                <h3 class="text-2xl font-extrabold text-slate-900"><?php echo e($group['pt_name']); ?></h3>
                <p class="mt-1 text-sm text-slate-500">Invoice per PT/CV tampil di sini dengan subtotal masing-masing.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Subtotal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Metode</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $statusClass = $row['status'] === 'LUNAS'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($row['status'] === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                        ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold"><?php echo e($row['invoice']); ?></td>
                            <td class="px-4 py-3"><?php echo e($row['waktu']); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($row['subtotal']); ?></td>
                            <td class="px-4 py-3 uppercase"><?php echo e($row['metode']); ?></td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($row['status']); ?></span></td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?php echo e(route('admin.admin-besar.receipt', $row['sale_id'])); ?>" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi untuk PT/CV ini.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/admin/admin-besar/history-supplier-detail.blade.php ENDPATH**/ ?>
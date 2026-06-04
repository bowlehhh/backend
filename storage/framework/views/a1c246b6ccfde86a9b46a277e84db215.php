<?php
    $viewData = $this->getViewData();
    $supplier = $viewData['supplier'];
    $summary = $viewData['summary'] ?? [];
    $rows = $viewData['purchaseRows'] ?? [];
?>

<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <style>
      .sf-wrap { font-family: 'Hanken Grotesk', sans-serif; width: 100%; max-width: 100%; margin: 0; }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; display: inline-block; vertical-align: middle; }
      .table-container::-webkit-scrollbar { height: 8px; }
      .table-container::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 10px; }
      html.sf-dashboard-page, .sf-dashboard-page, .sf-dashboard-page body, .sf-dashboard-page .fi-body, .sf-dashboard-page .fi-layout, .sf-dashboard-page .fi-main, .sf-dashboard-page .fi-main-ctn { background: #f7f9fb !important; min-height: 100% !important; }
      .sf-dashboard-page .fi-sidebar, .sf-dashboard-page .fi-topbar, .sf-dashboard-page .fi-topbar-ctn, .sf-dashboard-page .fi-layout-sidebar-toggle-btn-ctn, .sf-dashboard-page .fi-header { display: none !important; }
      .sf-dashboard-page .fi-main, .sf-dashboard-page .fi-main-ctn, .sf-dashboard-page .fi-page, .sf-dashboard-page .fi-page-content { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
      .sf-layout { display: grid; grid-template-columns: 220px minmax(0, 1fr); min-height: calc(100vh - 64px); }
      .sf-sidebar { position: sticky; top: 64px; width: 220px; height: calc(100vh - 64px); border-right: 1px solid #d4dbd7; overflow-y: auto; z-index: 20; }
      .sf-content { min-width: 0; width: 100%; min-height: calc(100vh - 64px); overflow-x: hidden; overflow-y: visible; }
      .sf-nav-item { font-size: 13px; }
      .sf-table th { font-size: 12px; letter-spacing: .02em; }
      .sf-table td { font-size: 13px; }
      @media (max-width: 1279px) {
        .sf-layout { grid-template-columns: 1fr; min-height: auto; }
        .sf-sidebar { position: static; width: 100%; height: auto; border-right: 0; border-bottom: 1px solid #d4dbd7; }
        .sf-content { min-height: auto; overflow: visible; }
      }
      @media (max-width: 767px) {
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-content { padding: 12px !important; }
        .sf-table th, .sf-table td { font-size: 13px !important; white-space: nowrap; }
      }
    </style>

    <div class="sf-wrap bg-[#f7f9fb] text-[#191c1e] antialiased min-h-screen overflow-x-hidden">
      <header class="bg-white border-b border-[#d4dbd7] shadow-sm flex justify-between items-center px-5 h-14 w-full sticky top-0 z-50">
        <div class="flex items-center gap-4">
          <span class="text-[34px] font-bold text-[#006948]">Toko Pak Paul</span>
        </div>
        <div class="relative flex items-center gap-4">
          <span class="material-symbols-outlined p-1 rounded-full hover:bg-[#eceef0] transition-colors">notifications</span>
          <span class="material-symbols-outlined p-1 rounded-full hover:bg-[#eceef0] transition-colors">settings</span>
          <button type="button" class="h-8 w-8 rounded-full bg-[#006948] text-white flex items-center justify-center text-xs font-bold">AP</button>
        </div>
      </header>

      <div class="sf-layout">
        <aside class="sf-sidebar hidden lg:flex flex-col w-full p-3 bg-white">
          <div class="mb-3 rounded-lg border border-[#d4dbd7] bg-[#f2f4f6] p-2.5">
            <div class="flex items-center gap-2">
              <div class="h-8 w-8 rounded-lg bg-[#006948] text-white flex items-center justify-center">
                <span class="material-symbols-outlined text-sm">inventory</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-[#006948]">Admin Panel</p>
                <p class="text-[10px] uppercase tracking-wide text-[#52615a]">Management Mode</p>
              </div>
            </div>
          </div>
          <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/products')); ?>">
              <span class="material-symbols-outlined">inventory_2</span><span>Barang</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 bg-[#006948] text-white rounded-lg px-2.5 py-2 font-medium" href="<?php echo e(url('/admin/suppliers')); ?>">
              <span class="material-symbols-outlined">local_shipping</span><span>Supplier</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/admin-module?type=credits')); ?>">
              <span class="material-symbols-outlined">credit_card</span><span>Kredit</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/admin-module?type=supplier-transactions')); ?>">
              <span class="material-symbols-outlined">account_tree</span><span>Transaksi PT</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/admin-module?type=batches')); ?>">
              <span class="material-symbols-outlined">layers</span><span>Batch Barang</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/admin-module?type=taxonomy')); ?>">
              <span class="material-symbols-outlined">category</span><span>Kategori & Merek</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/admin-module?type=reports')); ?>">
              <span class="material-symbols-outlined">analytics</span><span>Laporan</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/admin-module?type=users')); ?>">
              <span class="material-symbols-outlined">group</span><span>User</span>
            </a>
          </nav>
          <div class="pt-3 border-t border-[#d4dbd7]">
            <form method="POST" action="<?php echo e(route('logout')); ?>" onsubmit="return confirm('Yakin ingin logout dari akun ini?')">
              <?php echo csrf_field(); ?>
              <button type="submit" class="sf-nav-item w-full flex items-center gap-3 text-[#ba1a1a] px-2.5 py-2 hover:bg-[#ffdad6] transition-all rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span><span>Logout</span>
              </button>
            </form>
          </div>
        </aside>

        <main class="sf-content min-h-screen p-3 md:p-4 space-y-4">
          <div class="rounded-xl border border-[#d4dbd7] bg-white p-4">
            <h1 class="text-[28px] leading-tight font-semibold text-[#191c1e]"><?php echo e($supplier->name); ?></h1>
            <p class="mt-1 text-sm text-[#52615a]">Detail supplier dan seluruh riwayat pembelian (kredit + lunas).</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="rounded-xl border border-[#d4dbd7] bg-white p-4">
              <p class="text-xs uppercase tracking-wide text-[#52615a]">Barang Kredit</p>
              <p class="mt-1 text-2xl font-semibold text-[#1e40af]"><?php echo e(number_format((int) ($summary['kredit_count'] ?? 0), 0, ',', '.')); ?></p>
              <p class="text-xs text-[#52615a]">Belum lunas</p>
            </div>
            <div class="rounded-xl border border-[#f1d4d4] bg-[#fff6f6] p-4">
              <p class="text-xs uppercase tracking-wide text-[#8a1c1c]">Warning Jatuh Tempo</p>
              <p class="mt-1 text-2xl font-semibold text-[#ba1a1a]"><?php echo e(number_format((int) ($summary['warning_count'] ?? 0), 0, ',', '.')); ?></p>
              <p class="text-xs text-[#8a1c1c]">Jatuh tempo / <= 3 hari</p>
            </div>
          </div>

          <div class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden">
            <div class="px-4 py-3 border-b border-[#d4dbd7]">
              <h2 class="text-lg font-semibold text-[#191c1e]">Ringkasan Supplier</h2>
              <p class="text-sm text-[#52615a]">Frekuensi pembelian dari PT ini dan total nilainya.</p>
            </div>
            <div class="overflow-x-auto table-container">
              <table class="sf-table w-full min-w-[760px] text-left border-collapse">
                <thead>
                  <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Total Transaksi</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Jenis Barang</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Total Qty</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Total Modal</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Terakhir Beli</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Kredit</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Lunas</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-[#e4e8e6]">
                    <td class="px-4 py-2.5 font-semibold"><?php echo e((int) ($summary['total_transactions'] ?? 0)); ?> kali</td>
                    <td class="px-4 py-2.5 font-semibold"><?php echo e((int) ($summary['total_products'] ?? 0)); ?> item</td>
                    <td class="px-4 py-2.5 font-semibold"><?php echo e(number_format((int) ($summary['total_qty'] ?? 0), 0, ',', '.')); ?></td>
                    <td class="px-4 py-2.5 font-semibold"><?php echo e($summary['total_modal'] ?? 'Rp 0'); ?></td>
                    <td class="px-4 py-2.5"><?php echo e($summary['last_purchase_at'] ?? '-'); ?></td>
                    <td class="px-4 py-2.5"><?php echo e((int) ($summary['kredit_count'] ?? 0)); ?></td>
                    <td class="px-4 py-2.5"><?php echo e((int) ($summary['lunas_count'] ?? 0)); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div id="riwayat-pembelian" class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden">
            <div class="px-4 py-3 border-b border-[#d4dbd7]">
              <h2 class="text-lg font-semibold text-[#191c1e]">Riwayat Pembelian Supplier</h2>
              <p class="text-sm text-[#52615a]">Seluruh riwayat pembelian dari supplier ini (kredit dan lunas).</p>
            </div>
            <div class="overflow-x-auto table-container">
              <table class="sf-table w-full min-w-[1400px] text-left border-collapse">
                <thead>
                  <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Waktu</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Part Number</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Part Name</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Merek</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Kategori</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Unit</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Berat</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Qty</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Stok</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Harga Beli</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Subtotal</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Metode</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">DP / Uang Muka</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Total Dibayar</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                      $statusClass = ($row['status'] ?? '') === 'LUNAS'
                        ? 'bg-emerald-100 text-emerald-700'
                        : ((($row['status'] ?? '') === 'JATUH TEMPO') ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                    ?>
                    <tr class="border-b border-[#e4e8e6]">
                      <td class="px-4 py-2.5"><?php echo e($row['waktu']); ?></td>
                      <td class="px-4 py-2.5 font-semibold"><?php echo e($row['part_number']); ?></td>
                      <td class="px-4 py-2.5 font-semibold"><?php echo e($row['part_name']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['merek']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['kategori']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['unit']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['berat']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e(number_format((int) $row['qty'], 0, ',', '.')); ?></td>
                      <td class="px-4 py-2.5"><?php echo e(number_format((int) $row['stok'], 0, ',', '.')); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['harga_beli']); ?></td>
                      <td class="px-4 py-2.5 font-semibold"><?php echo e($row['subtotal']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['payment_type']); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['down_payment'] ?? '-'); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['total_dibayar'] ?? $row['sudah_dibayar'] ?? '-'); ?></td>
                      <td class="px-4 py-2.5"><?php echo e($row['payment_type'] === 'KREDIT' ? ($row['credit_days'] . ' hari (' . $row['credit_due_date'] . ')') : '-'); ?></td>
                      <td class="px-4 py-2.5"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold <?php echo e($statusClass); ?>"><?php echo e($row['status']); ?></span></td>
                    </tr>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                      <td colspan="16" class="px-4 py-8 text-center text-[#52615a]">Belum ada riwayat pembelian untuk supplier ini.</td>
                    </tr>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\backend\resources\views/filament/resources/supplier-resource/pages/view-supplier.blade.php ENDPATH**/ ?>
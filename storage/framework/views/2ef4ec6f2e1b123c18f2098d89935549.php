<?php
    $viewData = $this->getViewData();
    $stats = $viewData['stats'] ?? ['total' => 0, 'active' => 0, 'total_stock' => 0];
    $suppliers = $viewData['suppliers'] ?? [];
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

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

    <style>
      .sf-wrap { font-family: 'Hanken Grotesk', sans-serif; }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
      html.sf-dashboard-page, .sf-dashboard-page, .sf-dashboard-page body {
        background: #f7f9fb !important;
        min-height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
      }
      .sf-dashboard-page .fi-body,
      .sf-dashboard-page .fi-layout,
      .sf-dashboard-page .fi-main,
      .sf-dashboard-page .fi-main-ctn,
      .sf-dashboard-page .fi-page,
      .sf-dashboard-page .fi-page-content {
        background: #f7f9fb !important;
        min-height: 100% !important;
        height: auto !important;
        overflow-y: visible !important;
        overflow-x: hidden !important;
      }
      .sf-dashboard-page .fi-sidebar, .sf-dashboard-page .fi-topbar, .sf-dashboard-page .fi-header { display: none !important; }
      .sf-dashboard-page .fi-main, .sf-dashboard-page .fi-page, .sf-dashboard-page .fi-page-content, .sf-dashboard-page .fi-main-ctn { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
      .sf-layout { display: grid; grid-template-columns: 220px minmax(0, 1fr); min-height: calc(100vh - 58px); }
      .sf-sidebar { position: sticky; top: 58px; align-self: start; height: calc(100vh - 58px); border-right: 1px solid #d4dbd7; overflow: hidden; }
      .sf-main-scroll { min-height: calc(100vh - 58px); overflow: visible; }
      .sf-nav-item { font-size: 13px; }
      .sf-wrap .custom-shadow { box-shadow: 0 1px 3px rgba(0,0,0,.03); }
      .sf-wrap .rounded-xl { border-radius: 10px !important; }
      .sf-wrap .rounded-2xl { border-radius: 12px !important; }
      .sf-wrap .p-6 { padding: 1rem !important; }
      .sf-wrap .p-5 { padding: .9rem !important; }
      .sf-wrap .p-4 { padding: .8rem !important; }
      .sf-wrap .px-5 { padding-left: 1rem !important; padding-right: 1rem !important; }
      .sf-wrap .px-6 { padding-left: 1rem !important; padding-right: 1rem !important; }
      .sf-wrap .py-4 { padding-top: .7rem !important; padding-bottom: .7rem !important; }
      .sf-wrap .h-16 { height: 58px !important; }
      .sf-wrap .text-4xl { font-size: 30px !important; line-height: 36px !important; }
      .sf-wrap .text-5xl { font-size: 30px !important; line-height: 36px !important; }
      .sf-wrap table th,
      .sf-wrap table td { padding-top: .65rem !important; padding-bottom: .65rem !important; }
      @media (max-width: 1279px) {
        .sf-layout { grid-template-columns: 1fr; min-height: auto; display: block; }
        .sf-sidebar { display: none; }
        .sf-main-scroll { height: auto; overflow: visible; }
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-main-scroll { padding: 12px !important; }
        .sf-main-scroll h1 { font-size: 28px !important; line-height: 34px !important; }
        .sf-main-scroll .text-5xl { font-size: 30px !important; line-height: 36px !important; }
        .sf-main-scroll table th, .sf-main-scroll table td { white-space: nowrap; font-size: 13px !important; }
      }
    </style>

    <div class="sf-wrap bg-[#f7f9fb] text-[#191c1e] min-h-screen w-screen max-w-none ml-[calc(50%-50vw)] mr-[calc(50%-50vw)] overflow-x-hidden">
      <header class="bg-white border-b border-[#d4dbd7] flex justify-between items-center px-6 h-16 sticky top-0 z-20">
        <div class="flex items-center gap-4">
          <span class="text-xl font-bold text-[#006948]">Surya Duta Multindo</span>
        </div>
      </header>

      <div class="sf-layout">
        <aside class="sf-sidebar lg:flex flex-col w-full p-4 pb-6 bg-white hidden">
          <div class="mb-4 rounded-lg border border-[#d4dbd7] bg-[#f2f4f6] p-3">
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
          <nav class="flex-1 min-h-0 flex flex-col space-y-1 overflow-y-auto">
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="<?php echo e(url('/admin/products')); ?>">
              <span class="material-symbols-outlined">inventory_2</span>
              <span>Barang</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 bg-[#006948] text-white rounded-lg px-3 py-2 font-medium" href="<?php echo e(url('/admin/suppliers')); ?>">
              <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">local_shipping</span>
              <span>Supplier</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=credits')); ?>">
              <span class="material-symbols-outlined">credit_card</span><span>Kredit &amp; Utang Saya</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=supplier-transactions')); ?>">
              <span class="material-symbols-outlined">account_tree</span><span>Transaksi PT</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=reports')); ?>">
              <span class="material-symbols-outlined">analytics</span><span>Laporan</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=users')); ?>">
              <span class="material-symbols-outlined">group</span><span>User</span>
            </a>
            <a class="sf-nav-item w-full mt-auto flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=product-groups')); ?>">
              <span class="material-symbols-outlined">inventory_2</span><span>Kelompok Barang</span>
            </a>
          </nav>
          <div class="mt-4 pt-3 pb-5 border-t border-[#d4dbd7]">
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="js-admin-logout-form">
              <?php echo csrf_field(); ?>
              <button type="submit" class="sf-nav-item w-full flex items-center gap-3 text-[#ba1a1a] px-3 py-2 hover:bg-[#ffdad6] transition-all rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
              </button>
            </form>
          </div>
        </aside>

        <main class="sf-main-scroll p-4 md:p-6">
          <div class="flex items-center justify-between mb-5 md:mb-6">
            <div>
              <h1 class="text-4xl font-semibold text-[#191c1e]">Daftar Supplier</h1>
              <p class="text-[#52615a] text-base">Kelola data supplier dengan tampilan yang konsisten.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5 mb-6">
            <div class="bg-[#ffffff] border border-[#b8c5be] rounded-xl p-5 md:p-6 custom-shadow">
              <p class="text-base text-[#415149] font-medium">Total Supplier</p>
              <p class="text-[34px] md:text-[40px] leading-tight font-extrabold text-[#006948] mt-2"><?php echo e(number_format($stats['total'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-[#ffffff] border border-[#b8c5be] rounded-xl p-5 md:p-6 custom-shadow">
              <p class="text-base text-[#415149] font-medium">Supplier Aktif</p>
              <p class="text-[34px] md:text-[40px] leading-tight font-extrabold text-[#825100] mt-2"><?php echo e(number_format($stats['active'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-[#ffffff] border border-[#b8c5be] rounded-xl p-5 md:p-6 custom-shadow">
              <p class="text-base text-[#415149] font-medium">Total Stok Supplier</p>
              <p class="text-[34px] md:text-[40px] leading-tight font-extrabold text-[#4648d4] mt-2"><?php echo e(number_format($stats['total_stock'] ?? 0, 0, ',', '.')); ?></p>
            </div>
          </div>

          <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden">
            <table class="w-full text-left">
              <thead class="bg-[#eceef0] text-[#3d4a42] text-sm uppercase">
                <tr>
                  <th class="px-5 py-4">Nama Supplier</th>
                  <th class="px-5 py-4">Tipe Supplier</th>
                  <th class="px-5 py-4">Alamat Supplier</th>
                  <th class="px-5 py-4">Telepon</th>
                  <th class="px-5 py-4">Total Barang</th>
                  <th class="px-5 py-4">Total Stok</th>
                  <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                  <tr class="border-t border-[#e4e8e6]">
                    <td class="px-5 py-4 font-semibold"><?php echo e($supplier['name'] ?: '-'); ?></td>
                    <td class="px-5 py-4"><?php echo e($supplier['type'] ?: '-'); ?></td>
                    <td class="px-5 py-4"><?php echo e($supplier['address'] ?: '-'); ?></td>
                    <td class="px-5 py-4"><?php echo e($supplier['phone'] ?: '-'); ?></td>
                    <td class="px-5 py-4"><?php echo e($supplier['product_count']); ?></td>
                    <td class="px-5 py-4"><?php echo e($supplier['stock_total']); ?></td>
                    <td class="px-5 py-4 text-right">
                      <a class="text-[#006948]" href="<?php echo e(url('/admin/suppliers/' . $supplier['id'])); ?>">Detail</a>
                    </td>
                  </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                  <tr><td colspan="7" class="px-5 py-8 text-center text-[#52615a]">Belum ada supplier.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');
    </script>

    <?php echo $__env->make('filament.partials.logout-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/filament/pages/admin-suppliers.blade.php ENDPATH**/ ?>
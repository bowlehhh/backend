<?php
    $type = $type ?? 'batches';
    $title = $title ?? 'Modul';
    $icon = $icon ?? 'layers';
    $rows = $rows ?? [];
    $userRows = $userRows ?? [];
    $editingUserId = $editingUserId ?? 0;
    $searchKeyword = $searchKeyword ?? '';
    $taxonomyStats = $taxonomyStats ?? ['total_categories' => 0, 'total_brands' => 0, 'total_products' => 0, 'popular_category' => '-'];
    $taxonomyPagination = $taxonomyPagination ?? ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'from' => 0, 'to' => 0, 'has_prev' => false, 'has_next' => false, 'prev_page' => null, 'next_page' => null];
    $taxonomySort = $taxonomySort ?? 'category';
    $taxonomyDir = $taxonomyDir ?? 'asc';
    $taxonomyProducts = $taxonomyProducts ?? [];
    $taxonomySelectedCategoryId = $taxonomySelectedCategoryId ?? 0;
    $taxonomySelectedBrandId = $taxonomySelectedBrandId ?? 0;
    $reportStats = $reportStats ?? ['today_total' => 0, 'month_total' => 0, 'year_total' => 0, 'today_count' => 0, 'month_count' => 0, 'year_count' => 0];
    $reportTransactions = $reportTransactions ?? [];
    $reportCashierTransactions = $reportCashierTransactions ?? [];
    $ptCustomerGroups = $ptCustomerGroups ?? [];
    $ptCustomerDetail = $ptCustomerDetail ?? ['pt_name' => '', 'rows' => [], 'summary' => null];
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
      .sf-wrap { font-family: 'Hanken Grotesk', sans-serif; }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
      /* Hard override: always hide native Filament shell on this custom page */
      .fi-sidebar,
      .fi-topbar,
      .fi-header,
      .fi-page-header,
      .fi-breadcrumbs {
        display: none !important;
      }
      .fi-main,
      .fi-page,
      .fi-page-content,
      .fi-main-ctn {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      html.sf-dashboard-page,
      .sf-dashboard-page,
      .sf-dashboard-page body,
      .sf-dashboard-page .fi-body,
      .sf-dashboard-page .fi-layout,
      .sf-dashboard-page .fi-main,
      .sf-dashboard-page .fi-main-ctn {
        background: #f7f9fb !important;
        min-height: 100% !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
      }
      .sf-dashboard-page .fi-sidebar,
      .sf-dashboard-page .fi-topbar,
      .sf-dashboard-page .fi-header { display: none !important; }
      .sf-dashboard-page .fi-main,
      .sf-dashboard-page .fi-page,
      .sf-dashboard-page .fi-page-content,
      .sf-dashboard-page .fi-main-ctn {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100% !important;
        overflow: visible !important;
      }
      html.sf-dashboard-page,
      .sf-dashboard-page,
      .sf-dashboard-page body,
      .sf-dashboard-page .fi-body { min-height: 100% !important; overflow-x: hidden !important; overflow-y: auto !important; }
      .sf-layout {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        gap: 0;
        min-height: calc(100vh - 64px);
      }
      .sf-sidebar {
        position: sticky;
        top: 64px;
        height: calc(100vh - 64px);
        border-right: 1px solid #d4dbd7;
        overflow-y: auto;
      }
      .sf-main-scroll {
        min-height: calc(100vh - 64px);
        overflow: visible;
      }
      .sf-nav-item { font-size: 14px; }
      .sf-sidebar-collapsed .sf-layout { grid-template-columns: 76px minmax(0, 1fr); }
      .sf-sidebar-collapsed .sf-sidebar .nav-label,
      .sf-sidebar-collapsed .sf-sidebar .brand-title,
      .sf-sidebar-collapsed .sf-sidebar .brand-subtitle { display: none; }
      .sf-sidebar-collapsed .sf-sidebar .sf-nav-item { justify-content: center; }
      .sf-sidebar-collapsed .sf-sidebar .sf-nav-item span.material-symbols-outlined { margin-right: 0; }
      .sf-sidebar-collapsed .sf-sidebar .admin-panel-card { padding-left: 10px; padding-right: 10px; }
      .custom-shadow { box-shadow: 0 2px 4px rgba(0, 0, 0, .04); }
      .table-sort-link { display: inline-flex; align-items: center; gap: 6px; color: inherit; text-decoration: none; }
      .table-sort-link:hover { color: #006948; }
      .user-delete-modal-backdrop { background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); }
      @media (max-width: 1279px) {
        .sf-layout { grid-template-columns: 1fr; }
        .sf-sidebar { display: none !important; }
        .sf-main-scroll { height: auto; overflow: visible; }
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-main-scroll { padding: 12px !important; }
        .sf-main-scroll h1 { font-size: 28px !important; line-height: 34px !important; }
        .sf-main-scroll .px-6 { padding-left: 12px !important; padding-right: 12px !important; }
        .sf-main-scroll .py-4 { padding-top: 10px !important; padding-bottom: 10px !important; }
        .sf-main-scroll table th,
        .sf-main-scroll table td { font-size: 13px !important; white-space: nowrap; }
      }
    </style>

    <div class="sf-wrap bg-[#f7f9fb] text-[#191c1e] antialiased min-h-screen w-screen max-w-none ml-[calc(50%-50vw)] mr-[calc(50%-50vw)] overflow-x-hidden">
      <header class="bg-white border-b border-[#d4dbd7] shadow-sm flex justify-between items-center px-6 h-16 w-full sticky top-0 z-20">
        <div class="flex items-center gap-4"><span class="text-xl font-bold text-[#006948]">Toko Pak Paul</span></div>
        <button id="toggleSidebarBtn" type="button" class="hidden lg:inline-flex items-center gap-1 rounded-lg border border-[#bccac0] px-3 py-2 text-sm text-[#3d4a42] hover:bg-[#f1f4f2]">
          <span class="material-symbols-outlined text-base">left_panel_close</span>
          <span>Sidebar</span>
        </button>
      </header>

      <div class="sf-layout">
        <aside class="sf-sidebar hidden lg:flex flex-col w-full p-4 pb-6 bg-white">
          <div class="admin-panel-card mb-4 rounded-lg border border-[#d4dbd7] bg-[#f2f4f6] p-3">
            <div class="flex items-center gap-2">
              <div class="h-8 w-8 rounded-lg bg-[#006948] text-white flex items-center justify-center"><span class="material-symbols-outlined text-sm">inventory</span></div>
              <div>
                <p class="brand-title text-sm font-semibold text-[#006948]">Admin Panel</p>
                <p class="brand-subtitle text-[10px] uppercase tracking-wide text-[#52615a]">Management Mode</p>
              </div>
            </div>
          </div>
          <nav class="flex-1 min-h-0 space-y-1 overflow-y-auto">
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="<?php echo e(url('/admin/products')); ?>"><span class="material-symbols-outlined">inventory_2</span><span class="nav-label">Barang</span></a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="<?php echo e(url('/admin/suppliers')); ?>"><span class="material-symbols-outlined">local_shipping</span><span class="nav-label">Supplier</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium <?php echo e($type === 'credits' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]'); ?>" href="<?php echo e(url('/admin/admin-module?type=credits')); ?>"><span class="material-symbols-outlined">credit_card</span><span class="nav-label">Kredit</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium <?php echo e($type === 'supplier-transactions' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]'); ?>" href="<?php echo e(url('/admin/admin-module?type=supplier-transactions')); ?>"><span class="material-symbols-outlined">account_tree</span><span class="nav-label">Transaksi PT</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium <?php echo e($type === 'batches' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]'); ?>" href="<?php echo e(url('/admin/admin-module?type=batches')); ?>"><span class="material-symbols-outlined">layers</span><span class="nav-label">Batch Barang</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium <?php echo e($type === 'taxonomy' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]'); ?>" href="<?php echo e(url('/admin/admin-module?type=taxonomy')); ?>"><span class="material-symbols-outlined">category</span><span class="nav-label">Kategori & Merek</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium <?php echo e($type === 'reports' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]'); ?>" href="<?php echo e(url('/admin/admin-module?type=reports')); ?>"><span class="material-symbols-outlined">analytics</span><span class="nav-label">Laporan</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium <?php echo e($type === 'users' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]'); ?>" href="<?php echo e(url('/admin/admin-module?type=users')); ?>"><span class="material-symbols-outlined">group</span><span class="nav-label">User</span></a>
          </nav>
          <div class="mt-4 pt-3 pb-5 border-t border-[#d4dbd7]">
            <form method="POST" action="<?php echo e(route('logout')); ?>" onsubmit="return confirm('Yakin ingin logout dari akun ini?')">
              <?php echo csrf_field(); ?>
              <button type="submit" class="sf-nav-item w-full flex items-center gap-3 text-[#ba1a1a] px-3 py-2 hover:bg-[#ffdad6] rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span>
                <span class="nav-label">Logout</span>
              </button>
            </form>
          </div>
        </aside>

        <main class="sf-main-scroll p-4 md:p-6">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type !== 'taxonomy'): ?>
            <div class="flex items-end justify-between gap-4 mb-6">
              <div>
                <h1 class="text-[40px] leading-[48px] font-semibold text-[#191c1e]"><?php echo e($title); ?></h1>
                <p class="text-[#52615a]">Kelola data <?php echo e(strtolower($title)); ?> dengan tampilan konsisten seperti halaman barang.</p>
              </div>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'reports'): ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#e6fff3]"><span class="material-symbols-outlined text-[#006948]">today</span></div><span class="text-[#52615a] font-medium">Pengeluaran Hari Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#006948]">Rp <?php echo e(number_format((float) ($reportStats['today_total'] ?? 0), 0, ',', '.')); ?></div>
                <p class="text-sm text-[#52615a] mt-2"><?php echo e(number_format((int) ($reportStats['today_count'] ?? 0), 0, ',', '.')); ?> transaksi pembelian</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#ececff]"><span class="material-symbols-outlined text-[#4648d4]">calendar_month</span></div><span class="text-[#52615a] font-medium">Pengeluaran Bulan Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#4648d4]">Rp <?php echo e(number_format((float) ($reportStats['month_total'] ?? 0), 0, ',', '.')); ?></div>
                <p class="text-sm text-[#52615a] mt-2"><?php echo e(number_format((int) ($reportStats['month_count'] ?? 0), 0, ',', '.')); ?> transaksi pembelian</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#fff2df]"><span class="material-symbols-outlined text-[#825100]">calendar_today</span></div><span class="text-[#52615a] font-medium">Pengeluaran Tahun Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#825100]">Rp <?php echo e(number_format((float) ($reportStats['year_total'] ?? 0), 0, ',', '.')); ?></div>
                <p class="text-sm text-[#52615a] mt-2"><?php echo e(number_format((int) ($reportStats['year_count'] ?? 0), 0, ',', '.')); ?> transaksi pembelian</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#e8efff]"><span class="material-symbols-outlined text-[#1e40af]">point_of_sale</span></div><span class="text-[#52615a] font-medium">Transaksi Kasir Hari Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#1e40af]">Rp <?php echo e(number_format((float) ($reportStats['cashier_today_total'] ?? 0), 0, ',', '.')); ?></div>
                <p class="text-sm text-[#52615a] mt-2"><?php echo e(number_format((int) ($reportStats['cashier_today_count'] ?? 0), 0, ',', '.')); ?> transaksi kasir</p>
              </div>
            </div>

            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">List Transaksi Pembelian</h2>
                <p class="text-sm text-[#52615a]">Klik detail untuk masuk ke riwayat transaksi supplier terkait.</p>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Supplier</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Barang</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Qty</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Harga Satuan</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 text-[15px]"><?php echo e($trx['tanggal']); ?></td>
                        <td class="px-6 py-4 text-[15px] font-semibold"><?php echo e($trx['supplier']); ?></td>
                        <td class="px-6 py-4 text-[15px]"><?php echo e($trx['barang']); ?></td>
                        <td class="px-6 py-4 text-[15px]"><?php echo e(number_format((int) $trx['qty'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4 text-[15px]"><?php echo e($trx['harga_satuan']); ?></td>
                        <td class="px-6 py-4 text-[15px] font-semibold"><?php echo e($trx['total']); ?></td>
                        <td class="px-6 py-4 text-right">
                          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($trx['supplier_id'])): ?>
                            <a href="<?php echo e(url('/admin/suppliers/' . $trx['supplier_id'])); ?>#riwayat-pembelian" class="inline-flex items-center gap-1 rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">
                              <span class="material-symbols-outlined text-base">open_in_new</span>
                              <span>Detail</span>
                            </a>
                          <?php else: ?>
                            <span class="text-sm text-[#52615a]">-</span>
                          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="7" class="px-6 py-10 text-center text-[#52615a]">Belum ada transaksi pembelian.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="mt-6 bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">Kelompok PT: Kredit & Lunas</h2>
                <p class="text-sm text-[#52615a]">Ringkasan transaksi per PT/supplier, dipisahkan status kredit dan lunas.</p>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">PT / Supplier</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total Transaksi</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total Qty</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total Modal</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Kredit</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Lunas</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Terakhir Beli</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportSupplierGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 font-semibold"><?php echo e($group['supplier']); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['total_transaksi'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['total_qty'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4 font-semibold"><?php echo e($group['total_modal']); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['kredit_count'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['jatuh_tempo_count'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['lunas_count'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e($group['last_purchase_at']); ?></td>
                        <td class="px-6 py-4 text-right">
                          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($group['supplier_id'])): ?>
                            <a href="<?php echo e(url('/admin/suppliers/' . $group['supplier_id'])); ?>#riwayat-pembelian" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                          <?php else: ?>
                            <span class="text-sm text-[#52615a]">-</span>
                          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kelompok PT.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="mt-6 bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">List Transaksi Kasir Terbaru</h2>
                <p class="text-sm text-[#52615a]">Data transaksi kasir terbaru untuk monitoring harian.</p>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Invoice</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Pembeli</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Kasir</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Metode</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Waktu</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Total</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Sisa Kredit</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Total Retur</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportCashierTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 font-semibold"><?php echo e($trx['invoice_number']); ?></td>
                        <td class="px-6 py-4"><?php echo e($trx['customer_name']); ?></td>
                        <td class="px-6 py-4"><?php echo e($trx['cashier_name']); ?></td>
                        <td class="px-6 py-4 uppercase"><?php echo e($trx['payment_method']); ?></td>
                        <td class="px-6 py-4"><?php echo e($trx['created_at']); ?></td>
                        <td class="px-6 py-4 text-right font-semibold"><?php echo e($trx['total']); ?></td>
                        <td class="px-6 py-4 text-right">
                          <div><?php echo e($trx['credit_amount']); ?></div>
                          <div class="text-xs text-[#52615a]">Tempo: <?php echo e($trx['credit_due_date']); ?></div>
                        </td>
                        <td class="px-6 py-4 text-right"><?php echo e($trx['total_return_refund']); ?></td>
                        <td class="px-6 py-4 text-right">
                          <a href="<?php echo e(route('admin.sales.receipt', ['sale' => $trx['sale_id']])); ?>" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada transaksi kasir.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php elseif($type === 'supplier-transactions'): ?>
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">Kelompok Transaksi PT (Kredit & Lunas)</h2>
                <p class="text-sm text-[#52615a]">Data ini khusus dari transaksi kasir ke customer PT/CV.</p>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">PT / CV</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total Transaksi</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total Qty</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Total Nilai Belanja</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Kredit</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Lunas</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Terakhir Beli</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ptCustomerGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 font-semibold"><?php echo e($group['pt_name']); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['total_transaksi'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['total_qty'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4 font-semibold"><?php echo e($group['total_nilai']); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['kredit'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['jatuh_tempo'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e(number_format((int) $group['lunas'], 0, ',', '.')); ?></td>
                        <td class="px-6 py-4"><?php echo e($group['terakhir_beli']); ?></td>
                        <td class="px-6 py-4 text-right">
                          <a href="<?php echo e(url('/admin/admin-module?type=supplier-transactions&pt=' . urlencode($group['pt_name']))); ?>" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kelompok PT.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ptCustomerDetail['pt_name'])): ?>
              <div class="mt-6 bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
                <div class="px-6 py-4 border-b border-[#d4dbd7]">
                  <h2 class="text-lg font-semibold text-[#191c1e]">Detail Riwayat PT: <?php echo e($ptCustomerDetail['pt_name']); ?></h2>
                  <p class="text-sm text-[#52615a]">Riwayat pembelian PT/CV ini dari transaksi kasir, lengkap dengan nota.</p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ptCustomerDetail['summary'])): ?>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 px-6 py-4 border-b border-[#d4dbd7] bg-[#f8faf9]">
                    <div><p class="text-xs uppercase text-[#52615a]">Total Transaksi</p><p class="text-xl font-semibold text-[#191c1e]"><?php echo e(number_format((int) $ptCustomerDetail['summary']['total_transaksi'], 0, ',', '.')); ?> kali</p></div>
                    <div><p class="text-xs uppercase text-[#52615a]">Total Qty</p><p class="text-xl font-semibold text-[#191c1e]"><?php echo e(number_format((int) $ptCustomerDetail['summary']['total_qty'], 0, ',', '.')); ?></p></div>
                    <div><p class="text-xs uppercase text-[#52615a]">Total Nilai</p><p class="text-xl font-semibold text-[#191c1e]"><?php echo e($ptCustomerDetail['summary']['total_nilai']); ?></p></div>
                  </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="overflow-x-auto">
                  <table class="w-full text-left border-collapse">
                    <thead>
                      <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Kredit</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e4e8e6]">
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ptCustomerDetail['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-[#f6f8f7] transition-colors">
                          <td class="px-6 py-4 font-semibold"><?php echo e($row['invoice']); ?></td>
                          <td class="px-6 py-4"><?php echo e($row['waktu']); ?></td>
                          <td class="px-6 py-4"><?php echo e($row['metode']); ?></td>
                          <td class="px-6 py-4"><?php echo e(number_format((int) $row['qty'], 0, ',', '.')); ?></td>
                          <td class="px-6 py-4 font-semibold"><?php echo e($row['total']); ?></td>
                          <td class="px-6 py-4"><?php echo e($row['kredit']); ?></td>
                          <td class="px-6 py-4"><?php echo e($row['jatuh_tempo']); ?></td>
                          <td class="px-6 py-4">
                            <?php
                              $s = $row['status'] ?? 'LUNAS';
                              $statusClass = $s === 'LUNAS'
                                  ? 'bg-emerald-100 text-emerald-700'
                                  : ($s === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                            ?>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($s); ?></span>
                          </td>
                          <td class="px-6 py-4 text-right">
                            <a href="<?php echo e(route('admin.sales.receipt', ['sale' => $row['sale_id']])); ?>" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Lihat Nota</a>
                          </td>
                        </tr>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada riwayat transaksi PT ini.</td></tr>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php elseif($type === 'credits'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo e(session('success')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs md:text-sm">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Supplier</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Part Number</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Part Name</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Merek</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Unit</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Qty</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Harga Beli</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Total Kredit</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">DP / Uang Muka</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Total Dibayar</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Sisa Kredit</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Jatuh Tempo</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider whitespace-nowrap">Status</th>
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider text-right whitespace-nowrap">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-3 py-2.5 text-[13px] font-semibold whitespace-nowrap"><?php echo e($row['supplier']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['part_number']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['part_name']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['merek'] ?? '-'); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['unit']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['qty']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['harga_beli']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] font-semibold whitespace-nowrap"><?php echo e($row['total_kredit']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['down_payment'] ?? 'Rp 0'); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['sudah_dibayar']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] font-semibold whitespace-nowrap"><?php echo e($row['sisa_kredit']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap"><?php echo e($row['jatuh_tempo']); ?></td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">
                          <?php
                            $status = $row['status'] ?? 'BELUM LUNAS';
                            $statusClass = $status === 'LUNAS'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($status === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                          ?>
                          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusClass); ?>"><?php echo e($status); ?></span>
                        </td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                          <div class="flex min-w-max flex-row flex-nowrap items-center justify-end gap-1.5">
                            <a href="<?php echo e(route('admin.credits.detail', ['batch' => $row['batch_id']])); ?>" class="inline-flex h-8 items-center rounded-lg border border-[#bccac0] bg-white px-2.5 py-1 text-xs text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                            <a href="<?php echo e(route('admin.credits.receipt', ['batch' => $row['batch_id']])); ?>" target="_blank" rel="noopener" class="inline-flex h-8 items-center rounded-lg border border-[#bccac0] bg-white px-2.5 py-1 text-xs text-[#006948] hover:bg-[#f1f4f2]">Nota</a>
                          </div>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="14" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kredit.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php elseif($type === 'taxonomy'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo e(session('success')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mb-6">
              <h1 class="text-[40px] leading-[48px] font-semibold text-[#191c1e]">Kategori & Brand</h1>
              <p class="text-[#52615a]">Kelola kategori dan brand produk toko.</p>
            </div>

            <form id="taxonomySearchForm" method="GET" action="<?php echo e(url('/admin/admin-module')); ?>" class="mb-4">
              <input type="hidden" name="type" value="taxonomy">
              <input type="hidden" name="sort" value="<?php echo e($taxonomySort); ?>">
              <input type="hidden" name="dir" value="<?php echo e($taxonomyDir); ?>">
              <div class="bg-white border border-[#d4dbd7] rounded-xl p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto_auto] gap-3 items-center">
                <input id="taxonomySearchInput" type="text" name="q" value="<?php echo e($searchKeyword); ?>" placeholder="Cari kategori / brand" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
                <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-white">Cari</button>
                <a href="<?php echo e(url('/admin/admin-module?type=taxonomy')); ?>" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42] text-center">Reset</a>
              </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-4 custom-shadow"><p class="text-xs uppercase text-[#52615a]">Total Kategori</p><p class="mt-1 text-2xl font-semibold text-[#006948]"><?php echo e(number_format((int) $taxonomyStats['total_categories'], 0, ',', '.')); ?></p></div>
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-4 custom-shadow"><p class="text-xs uppercase text-[#52615a]">Total Brand</p><p class="mt-1 text-2xl font-semibold text-[#006948]"><?php echo e(number_format((int) $taxonomyStats['total_brands'], 0, ',', '.')); ?></p></div>
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-4 custom-shadow"><p class="text-xs uppercase text-[#52615a]">Total Produk</p><p class="mt-1 text-2xl font-semibold text-[#006948]"><?php echo e(number_format((int) $taxonomyStats['total_products'], 0, ',', '.')); ?></p></div>
            </div>

            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <?php
                        $dirCategory = $taxonomySort === 'category' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                        $dirBrand = $taxonomySort === 'brand' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                        $dirTotal = $taxonomySort === 'total_produk' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                        $dirStatus = $taxonomySort === 'status' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                      ?>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=category&dir=' . $dirCategory)); ?>">Nama Kategori</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=brand&dir=' . $dirBrand)); ?>">Nama Brand</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=total_produk&dir=' . $dirTotal)); ?>">Total Produk</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=status&dir=' . $dirStatus)); ?>">Status</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 text-[15px] font-medium"><?php echo e($row['kategori']); ?></td>
                        <td class="px-6 py-4 text-[15px]"><?php echo e($row['brand']); ?></td>
                        <td class="px-6 py-4 text-[15px]">
                          <a href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=' . $taxonomySort . '&dir=' . $taxonomyDir . '&category_id=' . $row['category_id'] . '&brand_id=' . $row['brand_id'])); ?>" class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 text-xs font-semibold text-[#006948]"><?php echo e(number_format((int) $row['total_produk'], 0, ',', '.')); ?></a>
                        </td>
                        <td class="px-6 py-4 text-[15px]">
                          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo e($row['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'); ?>"><?php echo e($row['status']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                          <div class="inline-flex items-center gap-2">
                            <button
                              type="button"
                              class="rounded-lg border border-[#bccac0] px-2.5 py-1 text-xs text-[#3d4a42] hover:bg-[#f1f4f2]"
                              data-tax-edit='<?php echo json_encode($row, 15, 512) ?>'>Edit</button>
                            <button
                              type="button"
                              class="rounded-lg border border-red-200 px-2.5 py-1 text-xs text-red-700 hover:bg-red-50"
                              data-tax-delete='<?php echo json_encode($row, 15, 512) ?>'>Hapus</button>
                          </div>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="5" class="px-6 py-12 text-center text-[#52615a]">Belum ada data kategori-brand. Klik <strong>+ Tambah Data</strong> untuk mulai.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-t border-[#d4dbd7] px-5 py-3 text-sm text-[#52615a]">
                <p>Menampilkan <?php echo e($taxonomyPagination['from']); ?>-<?php echo e($taxonomyPagination['to']); ?> dari <?php echo e($taxonomyPagination['total']); ?> data</p>
                <div class="flex items-center gap-2">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($taxonomyPagination['has_prev']): ?>
                    <a href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=' . $taxonomySort . '&dir=' . $taxonomyDir . '&page=' . $taxonomyPagination['prev_page'])); ?>" class="rounded-lg border border-[#bccac0] px-3 py-1.5">Prev</a>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <span class="rounded-lg bg-[#f1f4f2] px-3 py-1.5"><?php echo e($taxonomyPagination['current_page']); ?> / <?php echo e($taxonomyPagination['last_page']); ?></span>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($taxonomyPagination['has_next']): ?>
                    <a href="<?php echo e(url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=' . $taxonomySort . '&dir=' . $taxonomyDir . '&page=' . $taxonomyPagination['next_page'])); ?>" class="rounded-lg border border-[#bccac0] px-3 py-1.5">Next</a>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
              </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($taxonomyProducts)): ?>
              <div class="mt-4 rounded-xl border border-[#d4dbd7] bg-white custom-shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-[#d4dbd7]">
                  <h3 class="text-lg font-semibold text-[#191c1e]">Daftar Produk Terkait</h3>
                  <p class="text-sm text-[#52615a]">Kategori ID <?php echo e($taxonomySelectedCategoryId); ?> & Brand ID <?php echo e($taxonomySelectedBrandId); ?></p>
                </div>
                <div class="overflow-x-auto">
                  <table class="w-full text-left border-collapse">
                    <thead>
                      <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                        <th class="px-6 py-3 font-medium uppercase tracking-wider">Nama Produk</th>
                        <th class="px-6 py-3 font-medium uppercase tracking-wider">Barcode</th>
                        <th class="px-6 py-3 font-medium uppercase tracking-wider">Status</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e4e8e6]">
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $taxonomyProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-[#f6f8f7]">
                          <td class="px-6 py-3"><?php echo e($product['name']); ?></td>
                          <td class="px-6 py-3"><?php echo e($product['barcode']); ?></td>
                          <td class="px-6 py-3"><?php echo e($product['status']); ?></td>
                        </tr>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php elseif($type === 'users'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo e(session('success')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mb-6 rounded-xl border border-[#d4dbd7] bg-white p-5 custom-shadow">
              <h2 class="mb-4 text-lg font-semibold text-[#191c1e]">Tambah Akun Baru</h2>
              <form method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>
                <div>
                  <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Nama</label>
                  <input type="text" name="name" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Email</label>
                  <input type="email" name="email" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Role</label>
                  <select name="role" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                    <option value="cashier">Cashier</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div class="flex items-end">
                  <label class="inline-flex items-center gap-2 rounded-lg border border-[#d4dbd7] px-3 py-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-[#bccac0] text-[#006948] focus:ring-[#006948]/20">
                    <span class="text-sm text-[#3d4a42]">Akun aktif</span>
                  </label>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Password</label>
                  <input type="password" name="password" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Konfirmasi Password</label>
                  <input type="password" name="password_confirmation" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                </div>
                <div class="md:col-span-2 flex justify-end">
                  <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-sm font-medium text-white">Tambah Akun</button>
                </div>
              </form>
            </div>

            <div class="space-y-4">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $userRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="rounded-xl border border-[#d4dbd7] bg-white p-5 custom-shadow">
                  <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                      <h3 class="text-base font-semibold text-[#191c1e]"><?php echo e($userRow['nama']); ?></h3>
                      <p class="text-sm text-[#52615a]"><?php echo e($userRow['email']); ?></p>
                    </div>
                    <form method="POST" action="<?php echo e(route('admin.users.destroy', $userRow['id'])); ?>" onsubmit="return openDeleteUserModal(this)">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>
                      <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">Hapus</button>
                    </form>
                  </div>

                  <form method="POST" action="<?php echo e(route('admin.users.update', $userRow['id'])); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Nama</label>
                      <input type="text" name="name" value="<?php echo e($userRow['nama']); ?>" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Email</label>
                      <input type="email" name="email" value="<?php echo e($userRow['email']); ?>" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Role</label>
                      <select name="role" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                        <option value="cashier" <?php if($userRow['role'] === 'cashier'): echo 'selected'; endif; ?>>Cashier</option>
                        <option value="admin" <?php if($userRow['role'] === 'admin'): echo 'selected'; endif; ?>>Admin</option>
                      </select>
                    </div>
                    <div class="flex items-end">
                      <label class="inline-flex items-center gap-2 rounded-lg border border-[#d4dbd7] px-3 py-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" <?php if($userRow['is_active']): echo 'checked'; endif; ?> class="rounded border-[#bccac0] text-[#006948] focus:ring-[#006948]/20">
                        <span class="text-sm text-[#3d4a42]">Akun aktif</span>
                      </label>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Password Baru (opsional)</label>
                      <input type="password" name="password" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Konfirmasi Password Baru</label>
                      <input type="password" name="password_confirmation" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                      <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-sm font-medium text-white">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-6 py-10 text-center text-[#52615a]">Belum ada user.</div>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'batches'): ?>
              <form method="GET" action="<?php echo e(url('/admin/admin-module')); ?>" class="mb-4">
                <input type="hidden" name="type" value="batches">
                <div class="bg-white border border-[#d4dbd7] rounded-xl p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto_auto] gap-3 items-center">
                  <input type="text" name="q" value="<?php echo e($searchKeyword); ?>" placeholder="Cari kode batch... (contoh: BATCH-008 atau 202605)" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
                  <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-white">Cari</button>
                  <a href="<?php echo e(url('/admin/admin-module?type=batches')); ?>" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42] text-center">Reset</a>
                </div>
              </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7] flex items-center justify-end">
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Print Tabel</button>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse print-source-table">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($rows)): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_keys($rows[0]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                          <th class="px-6 py-4 font-medium uppercase tracking-wider"><?php echo e(str_replace('_', ' ', $key)); ?></th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                          <td class="px-6 py-4 text-[15px]"><?php echo e($value); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr><td colspan="10" class="px-6 py-10 text-center text-[#52615a]">Belum ada data.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </main>
      </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'credits'): ?>
      <div id="creditInstallmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 px-4">
        <div class="w-full max-w-xl rounded-xl border border-[#d4dbd7] bg-white p-6 shadow-2xl">
          <h3 class="text-xl font-semibold text-[#191c1e]">Bayar Cicilan Kredit</h3>
          <p id="creditInstallmentInfo" class="mt-1 text-sm text-[#52615a]">Isi nominal cicilan untuk kredit yang dipilih.</p>
          <form id="creditInstallmentForm" method="POST" class="mt-4 space-y-4">
            <?php echo csrf_field(); ?>
            <div>
              <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Nominal Cicilan</label>
              <input id="creditInstallmentAmount" type="text" name="amount" inputmode="numeric" placeholder="Contoh: 500000" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
              <p id="creditInstallmentHint" class="mt-1 text-xs text-[#52615a]">Sisa kredit: Rp 0</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Tanggal Bayar</label>
                <input id="creditInstallmentPaidAt" type="date" name="paid_at" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Catatan</label>
                <input type="text" name="note" placeholder="Opsional" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
              </div>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" id="closeCreditInstallmentModalBtn" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42]">Batal</button>
              <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-white">Simpan Cicilan</button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'taxonomy'): ?>
      <div id="taxonomyModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/50 px-4">
        <div class="w-full max-w-2xl rounded-xl border border-[#d4dbd7] bg-white p-6 shadow-2xl">
          <h3 id="taxonomyModalTitle" class="text-xl font-semibold text-[#191c1e]">Tambah Data</h3>
          <form id="taxonomyForm" method="POST" action="<?php echo e(route('admin.taxonomy.store')); ?>" class="mt-4 space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="q" value="<?php echo e($searchKeyword); ?>">
            <input type="hidden" name="sort" value="<?php echo e($taxonomySort); ?>">
            <input type="hidden" name="dir" value="<?php echo e($taxonomyDir); ?>">
            <input type="hidden" name="page" value="<?php echo e($taxonomyPagination['current_page']); ?>">
            <input type="hidden" id="taxonomyCategoryId" name="category_id" value="">
            <input type="hidden" id="taxonomyBrandId" name="brand_id" value="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Nama Kategori</label>
                <input id="taxonomyCategoryName" type="text" name="category_name" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Slug Kategori</label>
                <input id="taxonomyCategorySlug" type="text" name="category_slug" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Nama Brand</label>
                <input id="taxonomyBrandName" type="text" name="brand_name" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
              </div>
              <div>
                <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Slug Brand</label>
                <input id="taxonomyBrandSlug" type="text" name="brand_slug" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
              </div>
            </div>
            <label class="inline-flex items-center gap-2 rounded-lg border border-[#d4dbd7] px-3 py-2">
              <input type="hidden" name="is_active" value="0">
              <input id="taxonomyIsActive" type="checkbox" name="is_active" value="1" checked class="rounded border-[#bccac0] text-[#006948] focus:ring-[#006948]/20">
              <span class="text-sm text-[#3d4a42]">Status aktif</span>
            </label>
            <div class="flex justify-end gap-2">
              <button type="button" id="closeTaxonomyModalBtn" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42]">Batal</button>
              <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-white">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <div id="taxonomyDeleteModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/50 px-4">
        <div class="w-full max-w-lg rounded-xl border border-[#d4dbd7] bg-white p-6 shadow-2xl">
          <h3 class="text-xl font-semibold text-[#191c1e]">Hapus Data</h3>
          <p id="taxonomyDeleteMessage" class="mt-2 text-sm text-[#52615a]">Data akan dihapus.</p>
          <form id="taxonomyDeleteForm" method="POST" action="<?php echo e(route('admin.taxonomy.destroy')); ?>" class="mt-5">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <input type="hidden" name="category_id" id="taxonomyDeleteCategoryId" value="">
            <input type="hidden" name="brand_id" id="taxonomyDeleteBrandId" value="">
            <input type="hidden" name="q" value="<?php echo e($searchKeyword); ?>">
            <input type="hidden" name="sort" value="<?php echo e($taxonomySort); ?>">
            <input type="hidden" name="dir" value="<?php echo e($taxonomyDir); ?>">
            <input type="hidden" name="page" value="<?php echo e($taxonomyPagination['current_page']); ?>">
            <div class="flex justify-end gap-2">
              <button type="button" id="closeTaxonomyDeleteBtn" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42]">Batal</button>
              <button type="submit" class="rounded-lg bg-[#ba1a1a] px-4 py-2 text-white">Ya, Hapus</button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div id="deleteUserModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 user-delete-modal-backdrop">
      <div class="w-full max-w-2xl rounded-2xl border border-[#d4dbd7] bg-white p-7 shadow-2xl">
        <div class="flex items-start gap-4">
          <div class="mt-0.5 inline-flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-700">
            <span class="material-symbols-outlined">warning</span>
          </div>
          <div class="flex-1">
            <h3 class="text-2xl font-semibold text-[#191c1e]">Hapus Akun User</h3>
            <p id="deleteUserModalMessage" class="mt-2 text-base text-[#52615a]">Akun ini akan dihapus permanen. Lanjutkan?</p>
          </div>
        </div>
        <div class="mt-7 flex justify-end gap-3">
          <button type="button" onclick="closeDeleteUserModal()" class="rounded-xl border border-[#bccac0] px-5 py-2.5 text-sm font-medium text-[#3d4a42] hover:bg-[#f1f4f2]">Batal</button>
          <button type="button" onclick="submitDeleteUser()" class="rounded-xl bg-[#ba1a1a] px-5 py-2.5 text-sm font-medium text-white hover:brightness-95">Ya, Hapus</button>
        </div>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');

      let pendingDeleteUserForm = null;
      function openDeleteUserModal(form) {
        pendingDeleteUserForm = form;
        const row = form.closest('.rounded-xl');
        const nameEl = row?.querySelector('h3');
        const name = (nameEl?.textContent || 'akun ini').trim();
        const message = document.getElementById('deleteUserModalMessage');
        if (message) {
          message.textContent = `Akun "${name}" akan dihapus permanen dan tidak bisa dikembalikan.`;
        }
        const modal = document.getElementById('deleteUserModal');
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
        return false;
      }
      function closeDeleteUserModal() {
        const modal = document.getElementById('deleteUserModal');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        pendingDeleteUserForm = null;
      }
      function submitDeleteUser() {
        if (pendingDeleteUserForm) {
          pendingDeleteUserForm.submit();
        }
      }

      const wrap = document.querySelector('.sf-wrap');
      const sidebarToggleBtn = document.getElementById('toggleSidebarBtn');
      sidebarToggleBtn?.addEventListener('click', () => {
        wrap?.classList.toggle('sf-sidebar-collapsed');
      });

      <?php if($type === 'credits'): ?>
      const creditInstallmentModal = document.getElementById('creditInstallmentModal');
      const creditInstallmentForm = document.getElementById('creditInstallmentForm');
      const creditInstallmentInfo = document.getElementById('creditInstallmentInfo');
      const creditInstallmentHint = document.getElementById('creditInstallmentHint');
      const creditInstallmentAmount = document.getElementById('creditInstallmentAmount');
      const creditInstallmentPaidAt = document.getElementById('creditInstallmentPaidAt');
      const closeCreditInstallmentModalBtn = document.getElementById('closeCreditInstallmentModalBtn');

      const formatRupiahInput = (value) => {
        const digits = String(value ?? '').replace(/[^\d]/g, '');
        if (!digits) return '';
        return Number(digits).toLocaleString('id-ID');
      };

      const openCreditInstallmentModal = (row) => {
        if (!creditInstallmentModal || !creditInstallmentForm) return;
        creditInstallmentForm.action = `/admin/credits/${row.batch_id}/installment`;
        creditInstallmentInfo.textContent = `${row.supplier || '-'} - ${row.part_number || '-'} (${row.part_name || '-'})`;
        creditInstallmentHint.textContent = `DP: ${row.down_payment || 'Rp 0'} | Sisa kredit: ${row.sisa_kredit || 'Rp 0'}`;
        creditInstallmentAmount.value = '';
        if (creditInstallmentPaidAt) {
          creditInstallmentPaidAt.value = new Date().toISOString().slice(0, 10);
        }
        creditInstallmentModal.classList.remove('hidden');
        creditInstallmentModal.classList.add('flex');
      };

      const closeCreditInstallmentModal = () => {
        creditInstallmentModal?.classList.add('hidden');
        creditInstallmentModal?.classList.remove('flex');
      };

      document.querySelectorAll('[data-credit-installment]').forEach((button) => {
        button.addEventListener('click', () => {
          const row = JSON.parse(button.getAttribute('data-credit-installment') || '{}');
          openCreditInstallmentModal(row);
        });
      });

      creditInstallmentAmount?.addEventListener('input', () => {
        creditInstallmentAmount.value = formatRupiahInput(creditInstallmentAmount.value);
      });

      closeCreditInstallmentModalBtn?.addEventListener('click', closeCreditInstallmentModal);
      creditInstallmentModal?.addEventListener('click', (event) => {
        if (event.target === creditInstallmentModal) closeCreditInstallmentModal();
      });
      <?php endif; ?>

      <?php if($type === 'taxonomy'): ?>
      const taxonomySearchInput = document.getElementById('taxonomySearchInput');
      const taxonomySearchForm = document.getElementById('taxonomySearchForm');
      let taxonomyDebounceTimer = null;
      taxonomySearchInput?.addEventListener('input', () => {
        clearTimeout(taxonomyDebounceTimer);
        taxonomyDebounceTimer = setTimeout(() => {
          taxonomySearchForm?.submit();
        }, 350);
      });

      const slugify = (value) => String(value || '').toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
      const taxonomyModal = document.getElementById('taxonomyModal');
      const taxonomyDeleteModal = document.getElementById('taxonomyDeleteModal');
      const taxonomyForm = document.getElementById('taxonomyForm');
      const taxonomyModalTitle = document.getElementById('taxonomyModalTitle');
      const taxonomyCategoryId = document.getElementById('taxonomyCategoryId');
      const taxonomyBrandId = document.getElementById('taxonomyBrandId');
      const taxonomyCategoryName = document.getElementById('taxonomyCategoryName');
      const taxonomyCategorySlug = document.getElementById('taxonomyCategorySlug');
      const taxonomyBrandName = document.getElementById('taxonomyBrandName');
      const taxonomyBrandSlug = document.getElementById('taxonomyBrandSlug');
      const taxonomyIsActive = document.getElementById('taxonomyIsActive');
      const taxonomyMethodInput = taxonomyForm?.querySelector('input[name="_method"]');

      document.getElementById('openTaxonomyCreateModalBtn')?.addEventListener('click', () => {
        if (!taxonomyForm) return;
        taxonomyModalTitle.textContent = 'Tambah Data';
        taxonomyForm.action = '<?php echo e(route('admin.taxonomy.store')); ?>';
        if (taxonomyMethodInput) taxonomyMethodInput.value = 'POST';
        taxonomyCategoryId.value = '';
        taxonomyBrandId.value = '';
        taxonomyCategoryName.value = '';
        taxonomyCategorySlug.value = '';
        taxonomyBrandName.value = '';
        taxonomyBrandSlug.value = '';
        taxonomyIsActive.checked = true;
        taxonomyModal?.classList.remove('hidden');
        taxonomyModal?.classList.add('flex');
      });

      document.getElementById('closeTaxonomyModalBtn')?.addEventListener('click', () => {
        taxonomyModal?.classList.add('hidden');
        taxonomyModal?.classList.remove('flex');
      });

      taxonomyCategoryName?.addEventListener('input', () => {
        if (taxonomyCategorySlug) {
          taxonomyCategorySlug.value = slugify(taxonomyCategoryName.value);
        }
      });
      taxonomyBrandName?.addEventListener('input', () => {
        if (taxonomyBrandSlug) {
          taxonomyBrandSlug.value = slugify(taxonomyBrandName.value);
        }
      });

      document.querySelectorAll('[data-tax-edit]').forEach((button) => {
        button.addEventListener('click', () => {
          const row = JSON.parse(button.getAttribute('data-tax-edit') || '{}');
          taxonomyModalTitle.textContent = 'Edit Data';
          taxonomyForm.action = '<?php echo e(route('admin.taxonomy.update')); ?>';
          if (taxonomyMethodInput) taxonomyMethodInput.value = 'PUT';
          taxonomyCategoryId.value = row.category_id || '';
          taxonomyBrandId.value = row.brand_id || '';
          taxonomyCategoryName.value = row.kategori || '';
          taxonomyCategorySlug.value = slugify(row.kategori || '');
          taxonomyBrandName.value = row.brand || '';
          taxonomyBrandSlug.value = slugify(row.brand || '');
          taxonomyIsActive.checked = !!row.is_active;
          taxonomyModal?.classList.remove('hidden');
          taxonomyModal?.classList.add('flex');
        });
      });

      const taxonomyDeleteCategoryId = document.getElementById('taxonomyDeleteCategoryId');
      const taxonomyDeleteBrandId = document.getElementById('taxonomyDeleteBrandId');
      const taxonomyDeleteMessage = document.getElementById('taxonomyDeleteMessage');
      document.querySelectorAll('[data-tax-delete]').forEach((button) => {
        button.addEventListener('click', () => {
          const row = JSON.parse(button.getAttribute('data-tax-delete') || '{}');
          taxonomyDeleteCategoryId.value = row.category_id || '';
          taxonomyDeleteBrandId.value = row.brand_id || '';
          taxonomyDeleteMessage.textContent = `Hapus pasangan ${row.kategori || '-'} / ${row.brand || '-'}?`;
          taxonomyDeleteModal?.classList.remove('hidden');
          taxonomyDeleteModal?.classList.add('flex');
        });
      });

      document.getElementById('closeTaxonomyDeleteBtn')?.addEventListener('click', () => {
        taxonomyDeleteModal?.classList.add('hidden');
        taxonomyDeleteModal?.classList.remove('flex');
      });
      <?php endif; ?>
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
<?php /**PATH C:\laragon\www\backend\resources\views/filament/pages/admin-module.blade.php ENDPATH**/ ?>
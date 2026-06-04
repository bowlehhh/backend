<?php
    $viewData = $this->getViewData();

    $stats = $viewData['stats'] ?? [];
    $products = $viewData['products'] ?? [];
    $categories = $viewData['categories'] ?? [];
    $brands = $viewData['brands'] ?? [];
    $categoryOptions = $viewData['categoryOptions'] ?? [];
    $brandOptions = $viewData['brandOptions'] ?? [];
    $supplierOptions = $viewData['supplierOptions'] ?? [];
    $lowStockProducts = $viewData['lowStockProducts'] ?? [];
    $recentSales = $viewData['recentSales'] ?? [];
    $pagination = $viewData['pagination'] ?? [
        'current_page' => 1,
        'total' => count($products),
        'from' => count($products) > 0 ? 1 : 0,
        'to' => count($products),
        'last_page' => 1,
        'has_prev' => false,
        'has_next' => false,
        'prev_page' => null,
        'next_page' => null,
        'per_page' => count($products) ?: 10,
    ];
    $supplierTypeOptions = [
        'Distributor Resmi',
        'Grosir',
        'Cabang Jakarta',
        'Cabang Surabaya',
        'Supplier Lokal',
        'Supplier Pusat',
        'Importir',
    ];
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
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              'on-secondary-fixed-variant': '#2f2ebe',
              'error-container': '#ffdad6',
              'inverse-surface': '#2d3133',
              'on-secondary-fixed': '#07006c',
              'on-secondary': '#ffffff',
              'on-surface-variant': '#3d4a42',
              'surface-container-low': '#f2f4f6',
              'on-primary-fixed': '#002114',
              'on-tertiary-fixed-variant': '#653e00',
              'surface-variant': '#e0e3e5',
              primary: '#006948',
              'secondary-fixed': '#e1e0ff',
              'on-primary-container': '#f5fff7',
              background: '#f7f9fb',
              'primary-fixed': '#85f8c4',
              secondary: '#4648d4',
              'on-tertiary-container': '#fffbff',
              outline: '#6d7a72',
              'primary-container': '#00855d',
              'secondary-container': '#6063ee',
              'inverse-primary': '#68dba9',
              'on-tertiary-fixed': '#2a1700',
              'surface-container-high': '#e6e8ea',
              error: '#ba1a1a',
              'tertiary-fixed': '#ffddb8',
              'surface-bright': '#f7f9fb',
              'surface-container-lowest': '#ffffff',
              'on-primary-fixed-variant': '#005137',
              'surface-tint': '#006c4a',
              'on-tertiary': '#ffffff',
              surface: '#f7f9fb',
              'on-surface': '#191c1e',
              'tertiary-container': '#a36700',
              'surface-dim': '#d8dadc',
              'surface-container-highest': '#e0e3e5',
              'on-secondary-container': '#fffbff',
              'outline-variant': '#bccac0',
              'on-error-container': '#93000a',
              'primary-fixed-dim': '#68dba9',
              'secondary-fixed-dim': '#c0c1ff',
              'inverse-on-surface': '#eff1f3',
              'tertiary-fixed-dim': '#ffb95f',
              'on-background': '#191c1e',
              'on-primary': '#ffffff',
              'surface-container': '#eceef0',
              tertiary: '#825100',
              'on-error': '#ffffff',
            },
            fontFamily: { display: ['Hanken Grotesk', 'sans-serif'] },
            fontSize: {
              display: ['48px', { lineHeight: '56px', letterSpacing: '-0.02em', fontWeight: '700' }],
              'headline-lg': ['32px', { lineHeight: '40px', letterSpacing: '-0.01em', fontWeight: '600' }],
              'headline-md': ['20px', { lineHeight: '28px', fontWeight: '600' }],
              'body-md': ['16px', { lineHeight: '24px', fontWeight: '400' }],
              'label-md': ['14px', { lineHeight: '20px', letterSpacing: '0.01em', fontWeight: '500' }],
              'label-sm': ['12px', { lineHeight: '16px', fontWeight: '600' }],
            },
          },
        },
      };
    </script>

    <style>
      :root {
        --sf-topbar-h: 58px;
        --sf-sidebar-w: 220px;
      }
      .sf-wrap {
        font-family: 'Hanken Grotesk', sans-serif;
        width: 100vw;
        max-width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        font-size: 14px;
      }
      .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        display: inline-block;
        vertical-align: middle;
      }
      .table-container::-webkit-scrollbar { height: 8px; }
      .table-container::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 10px; }
      .custom-shadow { box-shadow: 0 2px 4px rgba(0,0,0,.04); }
      html.sf-dashboard-page,
      .sf-dashboard-page,
      .sf-dashboard-page body,
      .sf-dashboard-page .fi-body,
      .sf-dashboard-page .fi-layout,
      .sf-dashboard-page .fi-main,
      .sf-dashboard-page .fi-main-ctn {
        background: #f7f9fb !important;
        height: 100% !important;
        overflow: hidden !important;
      }
      .sf-dashboard-page .fi-sidebar,
      .sf-dashboard-page .fi-topbar,
      .sf-dashboard-page .fi-topbar-ctn,
      .sf-dashboard-page .fi-layout-sidebar-toggle-btn-ctn { display: none !important; }
      .fi-topbar,
      .fi-topbar-ctn,
      .fi-layout-sidebar-toggle-btn-ctn { display: none !important; }
      .sf-dashboard-page .fi-main {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      .sf-dashboard-page .fi-main-ctn {
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
      }
      .sf-dashboard-page .fi-header { display: none !important; }
      .sf-dashboard-page .fi-page,
      .sf-dashboard-page .fi-page-content {
        max-width: none !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      .sf-dashboard-page .fi-page-header-main-ctn,
      .sf-dashboard-page .fi-page-main {
        padding: 0 !important;
        gap: 0 !important;
      }
      .sf-shell { width: 100%; max-width: 100%; margin: 0; padding: 0; }
      .sf-layout { display: block; }
      .sf-sidebar {
        position: fixed;
        left: 0;
        top: var(--sf-topbar-h);
        width: var(--sf-sidebar-w);
        height: calc(100vh - var(--sf-topbar-h));
        border-right: 1px solid #d4dbd7;
        overflow: hidden;
        z-index: 30;
      }
      .sf-content {
        min-width: 0;
        width: calc(100% - var(--sf-sidebar-w));
        margin-left: var(--sf-sidebar-w);
        height: calc(100vh - var(--sf-topbar-h));
        overflow-y: auto;
      }
      .sf-nav-item { font-size: 13px; }
      .sf-nav-item .material-symbols-outlined { font-size: 17px; }
      .sf-title { font-size: 30px; line-height: 36px; }
      .sf-value { font-size: 30px; line-height: 36px; letter-spacing: -0.02em; }
      .sf-wrap .custom-shadow { box-shadow: 0 1px 3px rgba(0,0,0,.03); }
      .sf-wrap .rounded-xl { border-radius: 10px !important; }
      .sf-wrap .rounded-2xl { border-radius: 12px !important; }
      .sf-wrap .p-6 { padding: 1rem !important; }
      .sf-wrap .p-4 { padding: .8rem !important; }
      .sf-wrap .px-6 { padding-left: 1rem !important; padding-right: 1rem !important; }
      .sf-wrap .py-4 { padding-top: .7rem !important; padding-bottom: .7rem !important; }
      .sf-wrap .h-16 { height: var(--sf-topbar-h) !important; }
      .sf-wrap input,
      .sf-wrap select,
      .sf-wrap textarea { min-height: 40px; }
      .sf-wrap table th,
      .sf-wrap table td { padding-top: .65rem !important; padding-bottom: .65rem !important; }
      .sf-wrap .text-\[34px\] { font-size: 28px !important; line-height: 34px !important; }
      .sf-wrap .text-\[40px\] { font-size: 32px !important; line-height: 38px !important; }
      .sf-modal-panel { max-height: calc(100vh - 2rem); overflow: hidden; display: flex; flex-direction: column; }
      .sf-modal-form { overflow-y: auto; -webkit-overflow-scrolling: touch; }
      .sf-modal-form::-webkit-scrollbar { width: 8px; }
      .sf-modal-form::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 10px; }
      .sf-part-number {
        display: block;
        font-size: 18px;
        line-height: 24px;
        font-weight: 700;
        color: #191c1e;
      }
      .sf-product-name {
        display: block;
        margin-top: 2px;
        font-size: 13px;
        line-height: 18px;
        font-weight: 500;
        color: #3d4a42;
      }
      @media (max-width: 767px) {
        .sf-wrap #productTable td p.sf-part-number {
          font-size: 16px !important;
          line-height: 22px !important;
          font-weight: 700 !important;
        }
        .sf-wrap #productTable td p.sf-product-name {
          font-size: 11px !important;
          line-height: 16px !important;
          font-weight: 500 !important;
        }
      }
      @media (max-width: 1279px) {
        .sf-layout { display: block; }
        .sf-sidebar { position: static; width: 100%; height: auto; }
        .sf-content { margin-left: 0; height: auto; overflow: visible; }
      }
      @media (max-width: 767px) {
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-wrap .sf-content { padding: 12px !important; }
        .sf-wrap .sf-title { font-size: 24px !important; line-height: 30px !important; }
        .sf-wrap .sf-value { font-size: 26px !important; line-height: 32px !important; }
        .sf-wrap .custom-shadow { box-shadow: 0 1px 2px rgba(0,0,0,.03); }
        .sf-wrap .sf-header-actions .material-symbols-outlined { font-size: 20px; }
        .sf-wrap .sf-header-actions > div { width: 30px !important; height: 30px !important; font-size: 10px !important; }
        .sf-wrap .sf-top-search { display: none !important; }
        .sf-wrap .sf-header-actions { gap: 8px; }
        .sf-wrap .sf-toolbar { flex-direction: column; align-items: stretch; }
        .sf-wrap .sf-toolbar .sf-export { width: 100%; }
        .sf-wrap .sf-toolbar .sf-export label { text-align: left !important; margin-right: 0 !important; }
        .sf-wrap .sf-toolbar .sf-export > div { justify-content: flex-start; }
        .sf-wrap #globalSearchInput { font-size: 14px; }
        .sf-wrap #productTable th,
        .sf-wrap #productTable td { padding: 10px 12px !important; font-size: 13px !important; }
        .sf-wrap #productTable td .h-12.w-12 { width: 2.25rem; height: 2.25rem; }
        .sf-wrap #productTable td p.font-bold { font-size: 13px; }
        .sf-wrap #productTable td p.text-xs { font-size: 11px; }
        .sf-wrap .sf-modal-panel { width: 100%; max-width: 100%; }
        .sf-wrap .sf-modal-panel .px-6 { padding-left: 1rem !important; padding-right: 1rem !important; }
        .sf-wrap .sf-modal-panel .py-5 { padding-top: .9rem !important; padding-bottom: .9rem !important; }
        .sf-wrap .sf-modal-panel .text-headline-md { font-size: 18px !important; line-height: 24px !important; }
        .sf-wrap .sf-export { margin-left: 0 !important; align-items: flex-start !important; }
        .sf-wrap .sf-modal-panel { max-height: calc(100vh - 1rem); border-radius: 1rem; }
        .sf-wrap .sf-modal-form { padding-left: 1rem; padding-right: 1rem; padding-bottom: 1rem; }
      }
    </style>

    <div class="sf-wrap bg-background text-on-surface antialiased h-screen overflow-x-hidden overflow-y-hidden">
      <header class="bg-surface-container-lowest text-primary border-b border-outline-variant shadow-sm flex justify-between items-center px-6 h-16 w-full sticky top-0 z-50">
        <div class="flex items-center gap-4">
          <span class="font-display text-headline-md font-bold text-primary">Toko Pak Paul</span>
        </div>
        <div class="sf-header-actions relative flex items-center gap-4">
          <?php
            $lowStockCount = count($lowStockProducts ?? []);
          ?>
          <button id="headerNotificationsBtn" type="button" class="relative material-symbols-outlined p-1 rounded-full hover:bg-surface-container transition-colors" title="Notifikasi">notifications
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lowStockCount > 0): ?>
              <span class="absolute -right-1 -top-1 inline-flex min-h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white"><?php echo e($lowStockCount > 99 ? '99+' : $lowStockCount); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </button>
          <button id="headerSettingsBtn" type="button" class="material-symbols-outlined p-1 rounded-full hover:bg-surface-container transition-colors" title="Pengaturan Dashboard">settings</button>
          <button id="headerProfileBtn" type="button" class="h-8 w-8 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold hover:brightness-95">AP</button>

          <div id="notificationsPopup" class="absolute right-0 top-11 z-50 hidden w-[320px] rounded-xl border border-outline-variant bg-surface-container-lowest p-3 shadow-xl">
            <div class="mb-2 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-on-surface">Notifikasi Stok</h3>
              <button type="button" class="rounded p-1 hover:bg-surface-container" data-close-popup="notificationsPopup"><span class="material-symbols-outlined text-sm">close</span></button>
            </div>
            <div id="notificationsPopupList" class="max-h-64 space-y-2 overflow-y-auto"></div>
          </div>

          <div id="settingsPopup" class="absolute right-0 top-11 z-50 hidden w-[300px] rounded-xl border border-outline-variant bg-surface-container-lowest p-3 shadow-xl">
            <div class="mb-2 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-on-surface">Pengaturan Dashboard</h3>
              <button type="button" class="rounded p-1 hover:bg-surface-container" data-close-popup="settingsPopup"><span class="material-symbols-outlined text-sm">close</span></button>
            </div>
            <div class="space-y-2">
              <button type="button" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-left text-sm text-on-surface hover:bg-surface-container" data-settings-action="reset-filters">Reset Semua Filter</button>
              <button type="button" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-left text-sm text-on-surface hover:bg-surface-container" data-settings-action="open-products">Buka Halaman Data Barang</button>
            </div>
          </div>

          <div id="profilePopup" class="absolute right-0 top-11 z-50 hidden w-[250px] rounded-xl border border-outline-variant bg-surface-container-lowest p-3 shadow-xl">
            <div class="mb-3 border-b border-outline-variant pb-2">
              <p class="text-sm font-semibold text-on-surface">Admin Panel</p>
              <p class="text-xs text-on-surface-variant">Akses manajemen toko</p>
            </div>
            <div class="space-y-2">
              <a href="<?php echo e(url('/admin/admin-dashboard')); ?>" class="flex items-center gap-2 rounded-lg border border-outline-variant px-3 py-2 text-sm text-on-surface hover:bg-surface-container"><span class="material-symbols-outlined text-base">dashboard</span>Dashboard</a>
              <button type="button" onclick="confirmLogout()" class="flex w-full items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50"><span class="material-symbols-outlined text-base">logout</span>Logout</button>
            </div>
          </div>
        </div>
      </header>

      <div class="sf-shell">
        <div class="sf-layout">
          <aside class="sf-sidebar hidden lg:flex flex-col w-full p-4 bg-surface">
            <div class="mb-4 rounded-lg border border-outline-variant bg-surface-container-low p-3">
              <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-lg bg-primary text-on-primary flex items-center justify-center">
                  <span class="material-symbols-outlined text-sm">inventory</span>
                </div>
                <div>
                  <p class="text-sm font-semibold text-primary">Admin Panel</p>
                  <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Management Mode</p>
                </div>
              </div>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
              <a class="sf-nav-item flex items-center gap-3 bg-primary text-on-primary rounded-lg px-3 py-2 font-medium" href="<?php echo e(url('/admin/products')); ?>">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">inventory_2</span>
                <span>Barang</span>
              </a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/suppliers')); ?>"><span class="material-symbols-outlined">local_shipping</span><span>Supplier</span></a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=credits')); ?>"><span class="material-symbols-outlined">credit_card</span><span>Kredit</span></a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=supplier-transactions')); ?>"><span class="material-symbols-outlined">account_tree</span><span>Transaksi PT</span></a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=batches')); ?>"><span class="material-symbols-outlined">layers</span><span>Batch Barang</span></a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=taxonomy')); ?>"><span class="material-symbols-outlined">category</span><span>Kategori & Merek</span></a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=reports')); ?>"><span class="material-symbols-outlined">analytics</span><span>Laporan</span></a>
              <a class="sf-nav-item w-full flex items-center gap-3 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="<?php echo e(url('/admin/admin-module?type=users')); ?>"><span class="material-symbols-outlined">group</span><span>User</span></a>
            </nav>
            <div class="pt-3 border-t border-outline-variant">
              <button type="button" onclick="confirmLogout()" class="sf-nav-item w-full flex items-center gap-3 text-error px-3 py-2 hover:bg-error-container/20 transition-all rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
              </button>
            </div>
          </aside>

          <main class="sf-content min-h-screen p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-3 md:gap-4 mb-5 md:mb-6">
              <div>
                <h1 class="sf-title font-display text-headline-lg text-on-surface mb-1">Daftar Barang</h1>
                <p class="text-on-surface-variant text-body-md">Kelola seluruh stok inventaris Anda di satu tempat.</p>
              </div>
              <button type="button" onclick="openCreateModal()" class="w-full md:w-auto bg-primary text-on-primary px-5 md:px-8 py-3 rounded-xl font-medium flex items-center justify-center gap-2 hover:brightness-90 transition-all active:scale-95">
                <span class="material-symbols-outlined">add</span>Tambah Barang
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-6">
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                  $variant = $stat['variant'] ?? 'secondary';
                  $variantClass = match ($variant) {
                    'primary' => 'text-primary bg-primary-container/20',
                    'warning' => 'text-tertiary bg-tertiary-container/20',
                    'secondary' => 'text-secondary bg-secondary-container/20',
                    'info' => 'text-[#2563eb] bg-[#dbeafe]',
                    'danger' => 'text-error bg-error-container/30',
                    default => 'text-secondary bg-secondary-container/20',
                  };
                  $isLowStockCard = ($variant === 'warning');
                ?>
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant custom-shadow <?php echo e($isLowStockCard ? 'cursor-pointer hover:shadow-md transition-shadow' : ''); ?>" <?php if($isLowStockCard): ?> onclick="openLowStockModal()" <?php endif; ?>>
                  <div class="flex items-center gap-3 mb-2">
                    <div class="p-1 rounded-lg <?php echo e(explode(' ', $variantClass)[1]); ?>">
                      <span class="material-symbols-outlined <?php echo e(explode(' ', $variantClass)[0]); ?>"><?php echo e($stat['icon'] ?? 'inventory_2'); ?></span>
                    </div>
                    <span class="text-on-surface-variant font-medium"><?php echo e($stat['label'] ?? '-'); ?></span>
                  </div>
                  <div class="sf-value <?php echo e(explode(' ', $variantClass)[0]); ?> font-bold leading-tight"><?php echo e($stat['value'] ?? '0'); ?></div>
                  <p class="text-sm mt-1 font-medium <?php echo e(explode(' ', $variantClass)[0]); ?>"><?php echo e($stat['description'] ?? '-'); ?></p>
                </div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <div class="sf-toolbar bg-surface-container-lowest p-3 md:p-4 rounded-t-xl border-x border-t border-outline-variant flex flex-wrap items-center gap-3 md:gap-4">
              <div class="flex flex-col gap-1 min-w-0 flex-1">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase ml-1">Search</label>
                <input id="globalSearchInput" type="text" value="<?php echo e($viewData['search'] ?? ''); ?>" placeholder="Cari part number, nama barang, kategori, brand, barcode..." class="bg-surface border border-outline-variant rounded-lg px-4 py-2 text-label-md focus:ring-primary focus:border-primary" oninput="clearTimeout(deleteTimeout); deleteTimeout = setTimeout(applyFilters, 500)">
              </div>
              <div class="sf-export flex flex-col gap-1 ml-auto">
                <label class="text-[11px] font-bold text-on-surface-variant uppercase text-right mr-1">Export</label>
                <div class="flex gap-2">
                  <button type="button" class="p-2 bg-surface border border-outline-variant rounded-lg hover:bg-surface-container-high transition-colors" onclick="exportToCSV()"><span class="material-symbols-outlined text-on-surface-variant">download</span></button>
                  <button type="button" class="p-2 bg-surface border border-outline-variant rounded-lg hover:bg-surface-container-high transition-colors" onclick="window.print()"><span class="material-symbols-outlined text-on-surface-variant">print</span></button>
                </div>
              </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-b-xl overflow-hidden custom-shadow">
              <div class="overflow-x-auto table-container">
                <table class="w-full text-left border-collapse" id="productTable">
                  <thead>
                    <tr class="bg-surface-container text-on-surface-variant border-b border-outline-variant">
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Part Number</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Kategori</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Merek</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Berat</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Unit</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Stok</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Harga Beli</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Harga Jual</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider">Biaya Ekspedisi</th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-outline-variant" id="productTableBody">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                      <?php
                        $stock = (int) ($product['stock'] ?? 0);
                        $low = $stock <= 5;
                        $unit = trim((string) ($product['unit'] ?? '')) ?: '-';
                        $stockUnit = $unit !== '-' ? $unit : 'Unit';
                        $weight = $product['weight'] ?? null;
                        $weightDisplay = ($weight !== null && $weight !== '')
                            ? rtrim(rtrim(number_format((float) $weight, 2, ',', '.'), '0'), ',') . ' Kg'
                            : '-';
                        $expeditionCost = $product['expedition_cost_value'] ?? $product['expedition_cost'] ?? $product['shipping_cost'] ?? null;
                        $expeditionCostDisplay = ($expeditionCost !== null && $expeditionCost !== '')
                            ? 'Rp ' . number_format((float) $expeditionCost, 0, ',', '.')
                            : 'Rp 0';
                      ?>
                      <tr class="hover:bg-surface-container-low transition-colors group" data-product-id="<?php echo e((int) ($product['id'] ?? 0)); ?>">
                        <td class="px-6 py-4">
                          <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-lg bg-surface flex-shrink-0 border border-outline-variant overflow-hidden flex items-center justify-center">
                              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($product['image_url'])): ?>
                                <button type="button" class="h-full w-full" onclick='openImagePreview(<?php echo json_encode($product["image_url"], 15, 512) ?>, <?php echo json_encode($product["name"] ?? "Foto barang", 15, 512) ?>)'>
                                  <img src="<?php echo e($product['image_url']); ?>" alt="<?php echo e($product['name'] ?? 'Foto barang'); ?>" class="h-full w-full object-cover">
                                </button>
                              <?php else: ?>
                                <span class="material-symbols-outlined text-primary">inventory_2</span>
                              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                              <p class="sf-part-number"><?php echo e($product['sku'] ?? '-'); ?></p>
                              <p class="sf-product-name"><?php echo e($product['name'] ?? '-'); ?></p>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($product['category'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($product['brand'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($weightDisplay); ?></td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($unit); ?></td>
                        <td class="px-6 py-4">
                          <span class="px-3 py-1 rounded-full <?php echo e($low ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'); ?> text-label-sm font-semibold inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($low ? 'bg-red-500' : 'bg-emerald-500'); ?>"></span><?php echo e($stock); ?> <?php echo e($stockUnit); ?>

                          </span>
                        </td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($product['purchase_price'] ?? 'Rp 0'); ?></td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($product['selling_price'] ?? 'Rp 0'); ?></td>
                        <td class="px-6 py-4 text-body-md"><?php echo e($expeditionCostDisplay); ?></td>
                        <td class="px-6 py-4 text-right">
                          <div class="flex justify-end gap-2">
                            <button type="button" class="p-1 hover:bg-surface-container rounded-lg text-primary transition-colors" onclick='openEditModal(<?php echo json_encode($product, 15, 512) ?>)'><span class="material-symbols-outlined">edit</span></button>
                            <button type="button" class="p-1 hover:bg-error-container/20 rounded-lg text-error transition-colors" onclick='deleteProduct(<?php echo e((int) ($product['id'] ?? 0)); ?>, <?php echo json_encode($product['name'] ?? "-", 15, 512) ?>)'><span class="material-symbols-outlined">delete</span></button>
                          </div>
                        </td>
                      </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                      <tr id="emptyProductRow">
                        <td colspan="10" class="px-6 py-10 text-center text-on-surface-variant">Belum ada barang.</td>
                      </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div class="px-6 py-4 border-t border-outline-variant flex items-center justify-between bg-surface-container-lowest">
                <p class="text-label-sm text-on-surface-variant" id="productCountText">Menampilkan <?php echo e($pagination['from'] ?? 0); ?>-<?php echo e($pagination['to'] ?? 0); ?> dari <?php echo e($pagination['total'] ?? count($products)); ?> barang</p>
                <div class="flex items-center gap-1">
                  <button type="button" class="p-1 rounded hover:bg-surface-container transition-colors disabled:opacity-30" <?php echo e(empty($pagination['has_prev']) ? 'disabled' : ''); ?> onclick="goToPage(<?php echo e((int) ($pagination['prev_page'] ?? 1)); ?>)"><span class="material-symbols-outlined">chevron_left</span></button>
                  <?php
                    $currentPage = (int) ($pagination['current_page'] ?? 1);
                    $lastPage = (int) ($pagination['last_page'] ?? 1);
                    $startPage = max($currentPage - 1, 1);
                    $endPage = min($startPage + 2, $lastPage);
                    $startPage = max($endPage - 2, 1);
                  ?>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($page = $startPage; $page <= $endPage; $page++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" class="w-8 h-8 rounded text-label-sm flex items-center justify-center transition-colors <?php echo e($page === $currentPage ? 'bg-primary text-on-primary' : 'hover:bg-surface-container'); ?>" onclick="goToPage(<?php echo e($page); ?>)"><?php echo e($page); ?></button>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($endPage < $lastPage): ?>
                    <span class="px-1 text-on-surface-variant">...</span>
                    <button type="button" class="w-8 h-8 rounded hover:bg-surface-container text-label-sm flex items-center justify-center transition-colors" onclick="goToPage(<?php echo e($lastPage); ?>)"><?php echo e($lastPage); ?></button>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <button type="button" class="p-1 rounded hover:bg-surface-container transition-colors disabled:opacity-30" <?php echo e(empty($pagination['has_next']) ? 'disabled' : ''); ?> onclick="goToPage(<?php echo e((int) ($pagination['next_page'] ?? $currentPage)); ?>)"><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>

    <div id="dashboardToast" class="fixed right-6 top-6 z-50 hidden max-w-sm rounded-xl border bg-surface-container-lowest px-4 py-3 text-sm shadow-lg"></div>
    <div id="dashboardLoadingModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/45 px-4">
      <div class="w-full max-w-sm rounded-2xl border border-outline-variant bg-surface-container-lowest px-6 py-5 text-center shadow-2xl">
        <div class="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-4 border-outline-variant border-t-primary"></div>
        <p id="dashboardLoadingText" class="text-sm font-medium text-on-surface">Memproses data...</p>
      </div>
    </div>
    <div id="dashboardConfirmModal" class="fixed inset-0 z-[61] hidden items-center justify-center bg-slate-950/45 px-4">
      <div class="w-full max-w-md rounded-2xl border border-outline-variant bg-surface-container-lowest p-6 shadow-2xl">
        <h3 id="dashboardConfirmTitle" class="text-headline-md text-on-surface">Konfirmasi</h3>
        <p id="dashboardConfirmMessage" class="mt-2 text-sm text-on-surface-variant">Apakah Anda yakin?</p>
        <div class="mt-5 flex justify-end gap-3">
          <button type="button" class="rounded-xl border border-outline-variant px-4 py-2 text-sm font-medium text-on-surface hover:bg-surface-container" onclick="closeConfirmModal()">Batal</button>
          <button id="dashboardConfirmActionButton" type="button" class="rounded-xl bg-error px-4 py-2 text-sm font-medium text-on-error hover:brightness-95">Lanjut</button>
        </div>
      </div>
    </div>
    <form id="logoutForm" method="POST" action="<?php echo e(route('logout')); ?>" class="hidden">
      <?php echo csrf_field(); ?>
    </form>
    <div id="lowStockModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-950/45 px-3 py-3 md:px-4 md:py-6">
      <div class="sf-modal-panel w-full max-w-3xl rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-2xl">
        <div class="flex items-center justify-between border-b border-outline-variant px-6 py-4">
          <div>
            <h2 class="text-headline-md text-on-surface">Daftar Stok Menipis</h2>
            <p class="text-sm text-on-surface-variant">Barang yang perlu restock segera (stok <= 10).</p>
          </div>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeLowStockModal()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="sf-modal-form px-6 py-5">
          <div id="lowStockList" class="space-y-3"></div>
        </div>
      </div>
    </div>
    <div id="imagePreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 px-4 py-6">
      <div class="w-full max-w-3xl rounded-2xl bg-white p-4">
        <div class="mb-3 flex items-center justify-between">
          <h3 id="imagePreviewTitle" class="text-base font-semibold text-on-surface">Preview Foto</h3>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeImagePreview()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="flex items-center justify-center rounded-xl bg-surface-container-low p-2">
          <img id="imagePreviewTarget" src="" alt="Preview foto barang" class="max-h-[70vh] w-auto rounded-lg object-contain">
        </div>
      </div>
    </div>

    <div id="createProductModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-950/45 px-3 py-3 md:px-4 md:py-6">
      <div class="sf-modal-panel w-full max-w-3xl rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-2xl">
        <div class="flex items-center justify-between border-b border-outline-variant px-6 py-4">
          <div>
            <h2 class="text-headline-md text-on-surface">Tambah Barang</h2>
            <p class="text-sm text-on-surface-variant">Simpan barang baru langsung dari dashboard.</p>
          </div>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeCreateModal()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form id="createProductForm" class="sf-modal-form space-y-5 px-6 py-5">
          <input type="hidden" name="supplier_id">
          <div id="createFormAlert" class="hidden rounded-xl border border-error bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
          <?php echo $__env->make('filament.pages.partials.product-info-fields', ['isActiveDefault' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php echo $__env->make('filament.pages.partials.supplier-info-fields', ['supplierTypeOptions' => $supplierTypeOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php echo $__env->make('filament.pages.partials.batch-info-fields', ['batchCodePlaceholder' => 'Opsional, otomatis jika kosong'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <div class="flex justify-end gap-3 border-t border-outline-variant pt-4">
            <button type="button" class="rounded-xl border border-outline-variant px-5 py-3 text-sm font-medium text-on-surface hover:bg-surface-container" onclick="closeCreateModal()">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-medium text-on-primary hover:brightness-90">Simpan Barang</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editProductModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-950/45 px-3 py-3 md:px-4 md:py-6">
      <div class="sf-modal-panel w-full max-w-3xl rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-2xl">
        <div class="flex items-center justify-between border-b border-outline-variant px-6 py-4">
          <div>
            <h2 class="text-headline-md text-on-surface">Edit Barang</h2>
            <p class="text-sm text-on-surface-variant">Perbarui data barang tanpa keluar dari dashboard.</p>
          </div>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeEditModal()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form id="editProductForm" class="sf-modal-form space-y-5 px-6 py-5">
          <input type="hidden" name="product_id">
          <input type="hidden" name="supplier_id">
          <div id="editFormAlert" class="hidden rounded-xl border border-error bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
          <?php echo $__env->make('filament.pages.partials.product-info-fields', ['isActiveDefault' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php echo $__env->make('filament.pages.partials.supplier-info-fields', ['supplierTypeOptions' => $supplierTypeOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php echo $__env->make('filament.pages.partials.batch-info-fields', ['batchCodePlaceholder' => ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <div class="flex justify-end gap-3 border-t border-outline-variant pt-4">
            <button type="button" class="rounded-xl border border-outline-variant px-5 py-3 text-sm font-medium text-on-surface hover:bg-surface-container" onclick="closeEditModal()">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-medium text-on-primary hover:brightness-90">Update Barang</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');
      let deleteTimeout;
      const tableBody = document.getElementById('productTableBody');
      const countText = document.getElementById('productCountText');
      const createModal = document.getElementById('createProductModal');
      const editModal = document.getElementById('editProductModal');
      const createForm = document.getElementById('createProductForm');
      const editForm = document.getElementById('editProductForm');
      const toast = document.getElementById('dashboardToast');
      const loadingModal = document.getElementById('dashboardLoadingModal');
      const loadingText = document.getElementById('dashboardLoadingText');
      const confirmModal = document.getElementById('dashboardConfirmModal');
      const confirmTitle = document.getElementById('dashboardConfirmTitle');
      const confirmMessage = document.getElementById('dashboardConfirmMessage');
      const confirmActionButton = document.getElementById('dashboardConfirmActionButton');
      const lowStockModal = document.getElementById('lowStockModal');
      const lowStockList = document.getElementById('lowStockList');
      const imagePreviewModal = document.getElementById('imagePreviewModal');
      const imagePreviewTarget = document.getElementById('imagePreviewTarget');
      const imagePreviewTitle = document.getElementById('imagePreviewTitle');
      const pageMeta = { currentPage: <?php echo e((int) ($pagination['current_page'] ?? 1)); ?>, perPage: <?php echo e((int) ($pagination['per_page'] ?? (count($products) ?: 10))); ?>, total: <?php echo e((int) ($pagination['total'] ?? count($products))); ?> };
      const storeUrl = <?php echo e(Illuminate\Support\Js::from(route('admin.dashboard.products.store'))); ?>;
      const updateUrlBase = <?php echo e(Illuminate\Support\Js::from(url('/admin/dashboard/products'))); ?>;
      const csrfToken = <?php echo e(Illuminate\Support\Js::from(csrf_token())); ?>;
      const productsForNotification = <?php echo e(Illuminate\Support\Js::from($products)); ?>;
      const lowStockProducts = <?php echo e(Illuminate\Support\Js::from($lowStockProducts)); ?>;
      const globalSearchInput = document.getElementById('globalSearchInput');
      function applyFilters() {
        const searchInput = document.getElementById('globalSearchInput')?.value || '';
        const params = new URLSearchParams();
        if (searchInput) params.append('search', searchInput);
        window.location.search = params.toString();
      }
      function resetAllFilters() { window.location.search = ''; }
      function focusBrandFilter() { const target = document.getElementById('globalSearchInput'); if (!target) return; target.scrollIntoView({ behavior: 'smooth', block: 'center' }); target.focus(); }
      function focusCategoryFilter() { const target = document.getElementById('globalSearchInput'); if (!target) return; target.scrollIntoView({ behavior: 'smooth', block: 'center' }); target.focus(); }
      const headerNotificationsBtn = document.getElementById('headerNotificationsBtn');
      const headerSettingsBtn = document.getElementById('headerSettingsBtn');
      const headerProfileBtn = document.getElementById('headerProfileBtn');
      const notificationsPopup = document.getElementById('notificationsPopup');
      const settingsPopup = document.getElementById('settingsPopup');
      const profilePopup = document.getElementById('profilePopup');
      const notificationsPopupList = document.getElementById('notificationsPopupList');

      function closeHeaderPopups() {
        [notificationsPopup, settingsPopup, profilePopup].forEach((el) => {
          if (!el) return;
          el.classList.add('hidden');
        });
      }

      function togglePopup(popup) {
        const isHidden = popup?.classList.contains('hidden');
        closeHeaderPopups();
        if (popup && isHidden) {
          popup.classList.remove('hidden');
        }
      }

      function showNotifications() {
        if (!notificationsPopupList) return;
        const lowStocks = productsForNotification.filter((item) => Number(item.stock || 0) <= 10);
        if (!lowStocks.length) {
          notificationsPopupList.innerHTML = '<div class="rounded-lg border border-outline-variant bg-surface px-3 py-3 text-xs text-on-surface-variant">Tidak ada notifikasi kritis. Semua stok aman.</div>';
        } else {
          notificationsPopupList.innerHTML = lowStocks.map((item) => `
            <div class="rounded-lg border border-outline-variant bg-surface px-3 py-2">
              <p class="text-sm font-semibold text-on-surface">${escapeHtml(item.sku || '-')}</p>
              <p class="text-xs text-on-surface-variant mt-1">${escapeHtml(item.name || '-')}</p>
              <p class="text-xs text-on-surface-variant mt-1">Stok tersisa: ${Number(item.stock || 0)} unit</p>
            </div>
          `).join('');
        }
        togglePopup(notificationsPopup);
      }

      function openAdminSettings() {
        togglePopup(settingsPopup);
      }

      headerNotificationsBtn?.addEventListener('click', showNotifications);
      headerSettingsBtn?.addEventListener('click', openAdminSettings);
      headerProfileBtn?.addEventListener('click', () => togglePopup(profilePopup));

      document.querySelectorAll('[data-close-popup]').forEach((button) => {
        button.addEventListener('click', () => {
          const targetId = button.getAttribute('data-close-popup');
          const target = targetId ? document.getElementById(targetId) : null;
          target?.classList.add('hidden');
        });
      });

      document.querySelectorAll('[data-settings-action]').forEach((button) => {
        button.addEventListener('click', () => {
          const action = button.getAttribute('data-settings-action');
          if (action === 'reset-filters') {
            resetAllFilters();
            return;
          }
          if (action === 'open-products') {
            window.location.href = '<?php echo e(url('/admin/products')); ?>';
          }
        });
      });

      document.addEventListener('click', (event) => {
        const headerArea = event.target.closest('.sf-header-actions');
        if (!headerArea) {
          closeHeaderPopups();
        }
      });
      function openLowStockModal() {
        if (!lowStockModal || !lowStockList) return;
        renderLowStockList();
        lowStockModal.classList.remove('hidden');
        lowStockModal.classList.add('flex');
      }
      function closeLowStockModal() {
        if (!lowStockModal) return;
        lowStockModal.classList.add('hidden');
        lowStockModal.classList.remove('flex');
      }
      function openImagePreview(url, title) {
        if (!imagePreviewModal || !imagePreviewTarget) return;
        imagePreviewTarget.src = url || '';
        imagePreviewTitle.textContent = title ? `Foto: ${title}` : 'Preview Foto';
        imagePreviewModal.classList.remove('hidden');
        imagePreviewModal.classList.add('flex');
      }
      function closeImagePreview() {
        if (!imagePreviewModal || !imagePreviewTarget) return;
        imagePreviewModal.classList.add('hidden');
        imagePreviewModal.classList.remove('flex');
        imagePreviewTarget.src = '';
      }

      function closeAllDashboardModals() {
        closeLowStockModal();
        closeImagePreview();
        closeCreateModal();
        closeEditModal();
        closeConfirmModal();
      }
      function renderLowStockList() {
        if (!lowStockList) return;
        if (!lowStockProducts.length) {
          lowStockList.innerHTML = '<div class="rounded-xl border border-outline-variant bg-surface px-4 py-5 text-on-surface-variant">Tidak ada barang stok menipis.</div>';
          return;
        }
        lowStockList.innerHTML = lowStockProducts.map((item) => `
          <div class="rounded-xl border border-outline-variant bg-surface px-4 py-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-on-surface">${escapeHtml(item.sku || item.product_sku || item.product_code || item.product_name || '-')}</p>
                <p class="text-xs text-on-surface-variant mt-1">${escapeHtml(item.product_name || item.name || '-')}</p>
                <p class="text-xs text-on-surface-variant mt-1">Batch: ${escapeHtml(item.batch_code || '-')} | Supplier: ${escapeHtml(item.supplier_name || '-')}</p>
                <p class="text-xs text-on-surface-variant mt-1">Harga jual: ${escapeHtml(item.selling_price || 'Rp 0')}</p>
              </div>
              <span class="inline-flex items-center rounded-full bg-on-tertiary-fixed px-3 py-1 text-label-sm text-on-tertiary-fixed-variant">${Number(item.stock || 0)} Unit</span>
            </div>
          </div>
        `).join('');
      }
      function goToPage(page) {
        const searchInput = document.getElementById('globalSearchInput')?.value || '';
        const params = new URLSearchParams();
        if (searchInput) params.append('search', searchInput);
        if (page > 1) params.append('page', String(page));
        window.location.search = params.toString();
      }
      function openLoading(message = 'Memproses data...') {
        if (!loadingModal || !loadingText) return;
        loadingText.textContent = message;
        loadingModal.classList.remove('hidden');
        loadingModal.classList.add('flex');
      }
      function closeLoading() {
        if (!loadingModal) return;
        loadingModal.classList.add('hidden');
        loadingModal.classList.remove('flex');
      }
      function openConfirmModal({ title, message, actionLabel = 'Lanjut', actionClass = 'bg-error text-on-error', onConfirm }) {
        if (!confirmModal || !confirmActionButton) return;
        confirmTitle.textContent = title || 'Konfirmasi';
        confirmMessage.textContent = message || 'Apakah Anda yakin?';
        confirmActionButton.textContent = actionLabel;
        confirmActionButton.className = `rounded-xl px-4 py-2 text-sm font-medium hover:brightness-95 ${actionClass}`;
        confirmActionButton.onclick = () => {
          closeConfirmModal();
          onConfirm?.();
        };
        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
      }
      function closeConfirmModal() {
        if (!confirmModal) return;
        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
      }
      function confirmLogout() {
        openConfirmModal({
          title: 'Keluar Akun',
          message: 'Apakah Anda ingin keluar dari dashboard?',
          actionLabel: 'Ya, Keluar',
          actionClass: 'bg-error text-on-error',
          onConfirm: () => {
            openLoading('Sedang keluar akun...');
            document.getElementById('logoutForm')?.submit();
          },
        });
      }
      function deleteProduct(id, name) {
        openConfirmModal({
          title: 'Hapus Barang',
          message: `Apakah Anda yakin ingin menghapus produk "${name}"?`,
          actionLabel: 'Ya, Hapus',
          actionClass: 'bg-error text-on-error',
          onConfirm: () => {
            openLoading('Menghapus barang...');
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('deleteProduct', id);
            setTimeout(() => { window.location.reload(); }, 450);
          },
        });
      }
      function exportToCSV() {
        const table = document.getElementById('productTable'); if (!table) return;
        const csv = []; const headers = [];
        table.querySelectorAll('thead th').forEach((th) => { const text = th.textContent.trim(); if (text !== 'Aksi') headers.push(text); });
        csv.push(headers.join(','));
        table.querySelectorAll('tbody tr').forEach((tr) => {
          const tds = tr.querySelectorAll('td'); if (tds.length === 0) return;
          const row = []; tds.forEach((td, index) => {
            if (index >= headers.length) return;
            let text = td.textContent.replace(/\s+/g, ' ').trim();
            const mainValue = td.querySelector('.sf-part-number') || td.querySelector('p.font-bold'); if (mainValue) text = mainValue.textContent.trim();
            row.push(`"${text.replace(/"/g, '""')}"`);
          });
          if (row.length) csv.push(row.join(','));
        });
        const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `products-${new Date().toISOString().slice(0, 10)}.csv`; document.body.appendChild(a); a.click(); URL.revokeObjectURL(url); document.body.removeChild(a);
      }
      function openCreateModal() {
        resetFormState(createForm, 'createFormAlert');
        createForm.reset();
        setImagePreview(createForm, null);
        createForm.querySelector('input[type="checkbox"][name="is_active"]').checked = true;
        createForm.querySelector('[name="payment_type"]').value = 'LUNAS';
        syncBatchCreditFields(createForm);
        setCategoryBrandEditable(createForm, true);
        updatePurchaseTotal(createForm);
        createModal.classList.remove('hidden');
        createModal.classList.add('flex');
      }
      function closeCreateModal() { createModal.classList.add('hidden'); createModal.classList.remove('flex'); }
      function openEditModal(product) { resetFormState(editForm, 'editFormAlert'); fillProductForm(editForm, product); editModal.classList.remove('hidden'); editModal.classList.add('flex'); }
      function closeEditModal() { editModal.classList.add('hidden'); editModal.classList.remove('flex'); }

      [createModal, editModal, lowStockModal, imagePreviewModal, confirmModal].forEach((modal) => {
        modal?.addEventListener('click', (event) => {
          if (event.target === modal) {
            closeAllDashboardModals();
          }
        });
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          closeAllDashboardModals();
        }
      });
      function setCategoryBrandEditable(form, editable) {
        const categoryInput = form.querySelector('[name="category"]');
        const brandInput = form.querySelector('[name="brand"]');
        [categoryInput, brandInput].forEach((input) => {
          if (!input) return;
          input.readOnly = !editable;
          input.classList.toggle('bg-surface-container-low', !editable);
          input.classList.toggle('cursor-not-allowed', !editable);
          if (!editable) {
            input.title = 'Kategori dan brand hanya bisa diubah dari menu Kategori & Brand.';
          } else {
            input.removeAttribute('title');
          }
        });
      }

      function syncBatchCreditFields(form) {
        const paymentTypeInput = form.querySelector('[name="payment_type"]');
        const creditDaysWrap = form.querySelector('[data-credit-days-wrap]');
        const creditDueWrap = form.querySelector('[data-credit-due-wrap]');
        const downPaymentWrap = form.querySelector('[data-down-payment-wrap]');
        const creditDaysInput = form.querySelector('[name="credit_days"]');
        const creditDueInput = form.querySelector('[name="credit_due_date"]');
        const downPaymentInput = form.querySelector('[name="down_payment_amount"]');
        const showCredit = (paymentTypeInput?.value || 'LUNAS') === 'KREDIT';
        creditDaysWrap?.classList.toggle('hidden', !showCredit);
        creditDueWrap?.classList.toggle('hidden', !showCredit);
        downPaymentWrap?.classList.toggle('hidden', !showCredit);
        if (creditDueInput) {
          creditDueInput.readOnly = showCredit;
          creditDueInput.classList.toggle('bg-surface-container-low', showCredit);
        }
        if (!showCredit) {
          if (creditDaysInput) creditDaysInput.value = '';
          if (creditDueInput) creditDueInput.value = '';
          if (downPaymentInput) downPaymentInput.value = '';
          updateCreditDueHuman(form, null);
          updateCreditPaymentSummary(form, null);
          return;
        }
        syncCreditDueDateFromDays(form);
        updateCreditPaymentSummary(form);
      }

      function toIsoDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      }

      const indonesianMonths = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
      ];

      function formatIndonesianDate(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        return `${String(date.getDate()).padStart(2, '0')} ${indonesianMonths[date.getMonth()]} ${date.getFullYear()}`;
      }

      function dateFromIso(value) {
        const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) return null;
        return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
      }

      function updateCreditDueHuman(form, date, days = null) {
        const hint = form.querySelector('[data-credit-due-human]');
        if (!hint) return;
        const text = formatIndonesianDate(date);
        hint.textContent = text ? `Tanggal jatuh tempo: ${days ? `${days} hari ` : ''}(${text})` : '';
      }

      function updateCreditPaymentSummary(form, totalOverride = null) {
        const hint = form.querySelector('[data-down-payment-hint]');
        const paymentType = form.querySelector('[name="payment_type"]')?.value || 'LUNAS';
        const downPaymentInput = form.querySelector('[name="down_payment_amount"]');
        if (!hint) return;
        if (paymentType !== 'KREDIT') {
          hint.textContent = '';
          return;
        }

        const totalInput = form.querySelector('[name="total_purchase_display"]');
        const total = Number.isFinite(totalOverride)
          ? Number(totalOverride)
          : parseCurrencyToNumber(totalInput?.value || 0);
        let downPayment = parseCurrencyToNumber(downPaymentInput?.value || 0);
        if (downPayment > total) {
          downPayment = total;
          if (downPaymentInput) {
            downPaymentInput.value = formatRupiahInput(String(downPayment));
          }
        }

        const remaining = Math.max(0, total - downPayment);
        hint.textContent = `Sisa kredit otomatis: Rp ${Number.isFinite(remaining) ? remaining.toLocaleString('id-ID') : '0'}`;
      }

      function syncCreditDueDateFromDays(form) {
        const paymentType = form.querySelector('[name="payment_type"]')?.value || 'LUNAS';
        const creditDaysInput = form.querySelector('[name="credit_days"]');
        const creditDueInput = form.querySelector('[name="credit_due_date"]');
        if (!creditDaysInput || !creditDueInput) return;
        if (paymentType !== 'KREDIT') {
          creditDueInput.value = '';
          updateCreditDueHuman(form, null);
          updateCreditPaymentSummary(form, null);
          return;
        }
        const days = Number(creditDaysInput.value || 0);
        if (!Number.isFinite(days) || days <= 0) {
          creditDueInput.value = '';
          updateCreditDueHuman(form, null);
          updateCreditPaymentSummary(form, null);
          return;
        }
        const dueDate = new Date();
        dueDate.setHours(0, 0, 0, 0);
        dueDate.setDate(dueDate.getDate() + Math.floor(days));
        creditDueInput.value = toIsoDate(dueDate);
        updateCreditDueHuman(form, dueDate, Math.floor(days));
        updateCreditPaymentSummary(form);
      }

      function fillProductForm(form, product) {
        form.querySelector('[name="product_id"]').value = product.id || '';
        form.querySelector('[name="name"]').value = product.name || '';
        form.querySelector('[name="barcode"]').value = product.barcode || '';
        form.querySelector('[name="unit"]').value = product.unit || '';
        form.querySelector('[name="weight"]').value = product.weight || '';
        form.querySelector('[name="slug"]').value = product.slug || '';
        form.querySelector('[name="category"]').value = product.category || '';
        form.querySelector('[name="brand"]').value = product.brand || '';
        form.querySelector('[name="supplier_id"]').value = product.supplier_id || '';
        form.querySelector('[name="supplier_name"]').value = product.supplier_name || '';
        const supplierBranchField = form.querySelector('[name="supplier_branch"]');
        if (supplierBranchField) supplierBranchField.value = product.supplier_branch || '';
        form.querySelector('[name="supplier_phone"]').value = product.supplier_phone || '';
        form.querySelector('[name="supplier_address"]').value = product.supplier_address || '';
        form.querySelector('[name="supplier_note"]').value = product.supplier_note || '';
        form.querySelector('[name="batch_code"]').value = product.batch_code || '';
        form.querySelector('[name="purchase_price"]').value = formatRupiahInput(product.purchase_price_value ?? '');
        form.querySelector('[name="expedition_cost"]').value = formatRupiahInput(product.expedition_cost_value ?? '');
        form.querySelector('[name="selling_price"]').value = formatRupiahInput(product.selling_price_value ?? '');
        form.querySelector('[name="stock"]').value = product.stock ?? 0;
        form.querySelector('[name="payment_type"]').value = product.payment_type || 'LUNAS';
        form.querySelector('[name="credit_days"]').value = product.credit_days || '';
        form.querySelector('[name="credit_due_date"]').value = product.credit_due_date || '';
        const downPaymentField = form.querySelector('[name="down_payment_amount"]');
        if (downPaymentField) {
          downPaymentField.value = formatRupiahInput(product.down_payment_amount_value ?? product.down_payment_amount ?? '');
        }
        updateCreditDueHuman(form, dateFromIso(product.credit_due_date), product.credit_days || null);
        const expiredAtField = form.querySelector('[name="expired_at"]');
        if (expiredAtField) expiredAtField.value = product.expired_at || '';
        syncBatchCreditFields(form);
        updatePurchaseTotal(form);
        const descriptionField = form.querySelector('[name="description"]');
        if (descriptionField) descriptionField.value = product.description || '';
        form.querySelector('input[type="checkbox"][name="is_active"]').checked = Boolean(product.is_active);
        if (form.querySelector('[name="image"]')) form.querySelector('[name="image"]').value = '';
        setImagePreview(form, product.image_url || null);
        setCategoryBrandEditable(form, false);
      }
      function setImagePreview(form, imageUrl) {
        const preview = form.querySelector('[data-image-preview]');
        if (!preview) return;
        if (!imageUrl) {
          preview.classList.add('hidden');
          preview.removeAttribute('src');
          return;
        }
        preview.src = imageUrl;
        preview.classList.remove('hidden');
      }
      function updatePurchaseTotal(form) {
        const unitPrice = parseCurrencyToNumber(form.querySelector('[name="purchase_price"]')?.value || 0);
        const expeditionCost = parseCurrencyToNumber(form.querySelector('[name="expedition_cost"]')?.value || 0);
        const quantity = Number(form.querySelector('[name="stock"]')?.value || 0);
        const totalInput = form.querySelector('[name="total_purchase_display"]');
        if (!totalInput) return;
        const total = (unitPrice * quantity) + expeditionCost;
        totalInput.value = `Rp ${Number.isFinite(total) ? Math.round(total).toLocaleString('id-ID') : '0'}`;
        updateCreditPaymentSummary(form, total);
      }
      function formatRupiahInput(value) {
        const digits = String(value ?? '').replace(/[^\d]/g, '');
        if (!digits) return '';
        return Number(digits).toLocaleString('id-ID');
      }
      function parseCurrencyToNumber(value) {
        const digits = String(value ?? '').replace(/[^\d]/g, '');
        return Number(digits || 0);
      }
      function bindCurrencyFormatter(form) {
        ['purchase_price', 'expedition_cost', 'selling_price', 'down_payment_amount'].forEach((fieldName) => {
          const input = form.querySelector(`[name="${fieldName}"]`);
          if (!input) return;
          input.addEventListener('input', () => {
            const formatted = formatRupiahInput(input.value);
            input.value = formatted;
            if (fieldName === 'purchase_price' || fieldName === 'expedition_cost') {
              updatePurchaseTotal(form);
            } else if (fieldName === 'down_payment_amount') {
              updateCreditPaymentSummary(form);
            }
          });
        });
      }
      function resetFormState(form, alertId) {
        form.querySelectorAll('[data-error-for]').forEach((node) => { node.textContent = ''; });
        form.querySelectorAll('input, select, textarea').forEach((field) => { field.classList.remove('border-error'); field.classList.add('border-outline-variant'); });
        const alertBox = document.getElementById(alertId); if (alertBox) { alertBox.textContent = ''; alertBox.classList.add('hidden'); }
      }
      function buildFormPayload(form) {
        syncCreditDueDateFromDays(form);
        const formData = new FormData();
        formData.append('name', form.querySelector('[name="name"]').value || '');
        formData.append('barcode', form.querySelector('[name="barcode"]').value || '');
        formData.append('unit', form.querySelector('[name="unit"]').value || '');
        formData.append('weight', form.querySelector('[name="weight"]').value || '');
        formData.append('slug', form.querySelector('[name="slug"]').value || '');
        formData.append('category', form.querySelector('[name="category"]').value || '');
        formData.append('brand', form.querySelector('[name="brand"]').value || '');
        formData.append('supplier_id', form.querySelector('[name="supplier_id"]').value || '');
        formData.append('supplier_name', form.querySelector('[name="supplier_name"]').value || '');
        const supplierBranchField = form.querySelector('[name="supplier_branch"]');
        if (supplierBranchField) {
          formData.append('supplier_branch', supplierBranchField.value || '');
        }
        formData.append('supplier_phone', form.querySelector('[name="supplier_phone"]').value || '');
        formData.append('supplier_address', form.querySelector('[name="supplier_address"]').value || '');
        formData.append('supplier_note', form.querySelector('[name="supplier_note"]').value || '');
        formData.append('batch_code', form.querySelector('[name="batch_code"]').value || '');
        formData.append('purchase_price', String(parseCurrencyToNumber(form.querySelector('[name="purchase_price"]').value || '')));
        formData.append('expedition_cost', String(parseCurrencyToNumber(form.querySelector('[name="expedition_cost"]').value || '')));
        formData.append('selling_price', String(parseCurrencyToNumber(form.querySelector('[name="selling_price"]').value || '')));
        formData.append('stock', form.querySelector('[name="stock"]').value || 0);
        formData.append('payment_type', form.querySelector('[name="payment_type"]').value || 'LUNAS');
        formData.append('credit_days', form.querySelector('[name="credit_days"]').value || '');
        formData.append('credit_due_date', form.querySelector('[name="credit_due_date"]').value || '');
        formData.append('down_payment_amount', String(parseCurrencyToNumber(form.querySelector('[name="down_payment_amount"]')?.value || '')));
        const expiredAtField = form.querySelector('[name="expired_at"]');
        if (expiredAtField) {
          formData.append('expired_at', expiredAtField.value || '');
        }
        const descriptionField = form.querySelector('[name="description"]');
        if (descriptionField) {
          formData.append('description', descriptionField.value || '');
        }
        formData.append('is_active', form.querySelector('input[type="checkbox"][name="is_active"]').checked ? 1 : 0);
        const imageInput = form.querySelector('[name="image"]');
        if (imageInput?.files?.length) {
          formData.append('image', imageInput.files[0]);
        }
        return formData;
      }
      async function submitProductForm(form, url, method, alertId) {
        resetFormState(form, alertId);
        openLoading(method === 'POST' ? 'Menyimpan barang baru...' : 'Memperbarui barang...');
        const formData = buildFormPayload(form);
        if (method !== 'POST') {
          formData.append('_method', method);
        }
        const response = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: formData });
        const payload = await response.json().catch(() => ({}));
        closeLoading();
        if (response.status === 422) { showFormErrors(form, alertId, payload.errors || {}); return null; }
        if (!response.ok) { showFormAlert(alertId, payload.message || 'Terjadi kesalahan saat menyimpan data.'); return null; }
        return payload;
      }
      function showFormErrors(form, alertId, errors) {
        const messages = [];
        Object.entries(errors).forEach(([field, fieldMessages]) => {
          const input = form.querySelector(`[name="${field}"]`);
          const errorNode = form.querySelector(`[data-error-for="${field}"]`);
          const message = Array.isArray(fieldMessages) ? fieldMessages[0] : fieldMessages;
          if (input) { input.classList.remove('border-outline-variant'); input.classList.add('border-error'); }
          if (errorNode) errorNode.textContent = message;
          if (message) messages.push(message);
        });
        showFormAlert(alertId, messages.join(' '));
      }
      function showFormAlert(alertId, message) { const alertBox = document.getElementById(alertId); if (!alertBox) return; alertBox.textContent = message; alertBox.classList.remove('hidden'); }
      function showToast(message, type = 'success') {
        toast.textContent = message; toast.classList.remove('hidden', 'border-primary', 'border-error', 'text-primary', 'text-error');
        toast.classList.add(type === 'error' ? 'border-error' : 'border-primary');
        toast.classList.add(type === 'error' ? 'text-error' : 'text-primary');
        window.clearTimeout(showToast.timeoutId);
        showToast.timeoutId = window.setTimeout(() => { toast.classList.add('hidden'); }, 3000);
      }
      function renderProductRow(product) {
        const stock = Number(product.stock || 0);
        const lowStock = stock <= 5;
        const stockClass = lowStock ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700';
        const dotClass = lowStock ? 'bg-red-500' : 'bg-emerald-500';
        const unit = String(product.unit || '').trim() || '-';
        const stockUnit = unit !== '-' ? unit : 'Unit';
        const weightValue = product.weight ?? '';
        const weightNumber = Number(weightValue);
        const weightDisplay = weightValue !== '' && weightValue !== null && Number.isFinite(weightNumber)
          ? `${weightNumber.toLocaleString('id-ID', { maximumFractionDigits: 2 })} Kg`
          : '-';
        const expeditionValue = product.expedition_cost_value ?? product.expedition_cost ?? product.shipping_cost ?? 0;
        const expeditionNumber = parseCurrencyToNumber(expeditionValue);
        const expeditionCost = `Rp ${Number.isFinite(expeditionNumber) ? Math.round(expeditionNumber).toLocaleString('id-ID') : '0'}`;
        const encodedProduct = encodeURIComponent(JSON.stringify(product));
        return `
          <tr class="hover:bg-surface-container-low transition-colors group" data-product-id="${product.id}">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-lg bg-surface flex-shrink-0 border border-outline-variant overflow-hidden flex items-center justify-center">
                  ${product.image_url ? `<button type="button" class="h-full w-full" onclick='openImagePreview(${JSON.stringify(product.image_url)}, ${JSON.stringify(product.name || 'Foto barang')})'><img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name || 'Foto barang')}" class="h-full w-full object-cover"></button>` : `<span class="material-symbols-outlined text-primary">inventory_2</span>`}
                </div>
                <div>
                  <p class="sf-part-number">${escapeHtml(product.sku || '-')}</p>
                  <p class="sf-product-name">${escapeHtml(product.name || '-')}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.category || '-')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.brand || '-')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(weightDisplay)}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(unit)}</td>
            <td class="px-6 py-4"><span class="px-3 py-1 rounded-full ${stockClass} text-label-sm font-semibold inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span>${stock} ${escapeHtml(stockUnit)}</span></td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.purchase_price || 'Rp 0')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.selling_price || 'Rp 0')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(expeditionCost)}</td>
            <td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><button type="button" class="p-1 hover:bg-surface-container rounded-lg text-primary transition-colors" onclick='openEditModal(JSON.parse(decodeURIComponent("${encodedProduct}")))'><span class="material-symbols-outlined">edit</span></button><button type="button" class="p-1 hover:bg-error-container/20 rounded-lg text-error transition-colors" onclick='deleteProduct(${product.id}, ${JSON.stringify(product.name || "-")})'><span class="material-symbols-outlined">delete</span></button></div></td>
          </tr>
        `;
      }
      function upsertProductRow(product, mode = 'update') {
        const existingRow = tableBody.querySelector(`[data-product-id="${product.id}"]`);
        if (existingRow) {
          existingRow.outerHTML = renderProductRow(product);
        } else {
          const emptyState = document.getElementById('emptyProductRow');
          if (emptyState) emptyState.remove();
          tableBody.insertAdjacentHTML('afterbegin', renderProductRow(product));
          if (mode === 'create') {
            pageMeta.total += 1;
            const rows = tableBody.querySelectorAll('tr[data-product-id]');
            if (rows.length > pageMeta.perPage) rows[rows.length - 1].remove();
          }
        }
        syncNotificationProducts(product);
        refreshCountText();
      }
      function syncNotificationProducts(product) {
        const index = productsForNotification.findIndex((item) => Number(item.id) === Number(product.id));
        if (index >= 0) { productsForNotification[index] = product; return; }
        productsForNotification.unshift(product);
      }
      function refreshCountText() { const visibleRows = tableBody.querySelectorAll('tr[data-product-id]').length; countText.textContent = `Menampilkan ${visibleRows > 0 ? 1 : 0}-${visibleRows} dari ${pageMeta.total} barang`; }
      function escapeHtml(value) { return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
      createForm.addEventListener('submit', async function (event) { event.preventDefault(); const payload = await submitProductForm(createForm, storeUrl, 'POST', 'createFormAlert'); if (!payload) return; upsertProductRow(payload.product, 'create'); closeCreateModal(); createForm.reset(); setImagePreview(createForm, null); updatePurchaseTotal(createForm); showToast(payload.message || 'Barang berhasil ditambahkan.'); });
      editForm.addEventListener('submit', async function (event) { event.preventDefault(); const productId = editForm.querySelector('[name="product_id"]').value; const payload = await submitProductForm(editForm, `${updateUrlBase}/${productId}`, 'PUT', 'editFormAlert'); if (!payload) return; upsertProductRow(payload.product, 'update'); closeEditModal(); showToast(payload.message || 'Barang berhasil diperbarui.'); });
      createForm.querySelector('[name="stock"]')?.addEventListener('input', () => updatePurchaseTotal(createForm));
      editForm.querySelector('[name="stock"]')?.addEventListener('input', () => updatePurchaseTotal(editForm));
      createForm.querySelector('[name="payment_type"]')?.addEventListener('change', () => syncBatchCreditFields(createForm));
      editForm.querySelector('[name="payment_type"]')?.addEventListener('change', () => syncBatchCreditFields(editForm));
      createForm.querySelector('[name="credit_days"]')?.addEventListener('input', () => syncCreditDueDateFromDays(createForm));
      editForm.querySelector('[name="credit_days"]')?.addEventListener('input', () => syncCreditDueDateFromDays(editForm));
      createForm.querySelector('[name="credit_due_date"]')?.addEventListener('change', () => updateCreditDueHuman(createForm, dateFromIso(createForm.querySelector('[name="credit_due_date"]')?.value), createForm.querySelector('[name="credit_days"]')?.value || null));
      editForm.querySelector('[name="credit_due_date"]')?.addEventListener('change', () => updateCreditDueHuman(editForm, dateFromIso(editForm.querySelector('[name="credit_due_date"]')?.value), editForm.querySelector('[name="credit_days"]')?.value || null));
      createForm.querySelector('[name="image"]')?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        setImagePreview(createForm, file ? URL.createObjectURL(file) : null);
      });
      editForm.querySelector('[name="image"]')?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        setImagePreview(editForm, file ? URL.createObjectURL(file) : null);
      });
      updatePurchaseTotal(createForm);
      updatePurchaseTotal(editForm);
      bindCurrencyFormatter(createForm);
      bindCurrencyFormatter(editForm);
      syncBatchCreditFields(createForm);
      syncBatchCreditFields(editForm);
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
<?php /**PATH C:\laragon\www\backend\resources\views/filament/pages/admin-dashboard.blade.php ENDPATH**/ ?>
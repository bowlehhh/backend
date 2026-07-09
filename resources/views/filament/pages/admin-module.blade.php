@php
    $type = $type ?? 'credits';
    $title = $title ?? 'Modul';
    $icon = $icon ?? 'credit_card';
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
    $reportCreditMonthlyGroups = $reportCreditMonthlyGroups ?? [];
    $creditSettlementHistory = $creditSettlementHistory ?? [];
    $ptCustomerGroups = $ptCustomerGroups ?? [];
    $ptCustomerDetail = $ptCustomerDetail ?? ['pt_name' => '', 'rows' => [], 'summary' => null];
    $productGroups = $productGroups ?? ['summary' => [], 'groups' => []];
    $currentUser = auth()->user();
    $isAdminBesarContext = $currentUser?->isAdminBesar() ?? false;
    $adminGudangModuleTypes = ['credits', 'supplier-transactions', 'product-groups'];
    $isAdminBesarGudangModuleAccess = $isAdminBesarContext && in_array($type, $adminGudangModuleTypes, true);
    $moduleBaseUrl = url('/admin/admin-module');
    $creditsUrl = $moduleBaseUrl . '?type=credits';
    $supplierTransactionsUrl = $moduleBaseUrl . '?type=supplier-transactions';
    $productGroupsUrl = $moduleBaseUrl . '?type=product-groups';
    $salesListUrl = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? route('admin.admin-besar.index') : route('admin.transaksi.dashboard');
    $draftsUrl = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? route('admin.admin-besar.index') : route('admin.transactions.drafts');
    $historyUrl = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? route('admin.admin-besar.history') : route('admin.transactions.history');
    $salesListActive = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? request()->routeIs('admin.admin-besar.index') : request()->routeIs('admin.transaksi.dashboard');
    $draftsActive = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? request()->routeIs('admin.admin-besar.index') : request()->routeIs('admin.transactions.drafts');
    $historyActive = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? request()->routeIs('admin.admin-besar.history*') : request()->routeIs('admin.transactions.history*');
    $salesListLabel = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? 'Dashboard Admin Besar' : 'Daftar Stok Jual';
    $draftsLabel = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? 'Ringkasan Admin Besar' : 'Draft Tertunda';
    $historyLabel = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? 'History Admin Besar' : 'History & Nota';
    $mobileBackUrl = ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess)
        ? route('admin.admin-besar.index')
        : route('admin.transaksi.dashboard');
@endphp

<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/app-production.css') }}">
    @if (app()->environment('local') && (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))))
        @vite('resources/css/app.css')
    @endif
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <style>
      :root {
        --sf-topbar-h: 52px;
        --sf-sidebar-w: 208px;
        --sf-sidebar-collapsed-w: 76px;
      }
      .sf-wrap {
        font-family: 'Hanken Grotesk', sans-serif;
        width: 100vw;
        max-width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        margin-top: 0;
        font-size: 13px;
      }
      .sf-wrap h1,
      .sf-wrap h2,
      .sf-wrap h3,
      .sf-wrap h4,
      .sf-wrap p {
        margin: 0 !important;
      }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
      /* Hard override: always hide native Filament shell on this custom page */
      .fi-sidebar,
      .fi-topbar,
      .fi-topbar-ctn,
      .fi-header,
      .fi-page-header,
      .fi-layout-sidebar-toggle-btn-ctn,
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
        top: 0 !important;
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
      .sf-dashboard-page .fi-topbar-ctn,
      .sf-dashboard-page .fi-layout-sidebar-toggle-btn-ctn,
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
      .sf-dashboard-page .fi-page-header-main-ctn,
      .sf-dashboard-page .fi-page-main {
        padding: 0 !important;
        gap: 0 !important;
      }
      html.sf-dashboard-page,
      .sf-dashboard-page,
      .sf-dashboard-page body,
      .sf-dashboard-page .fi-body {
        min-height: 100% !important;
        overflow: hidden !important;
      }
      .sf-shell { width: 100%; max-width: 100%; margin: 0; padding: 0; }
      .sf-layout { display: block; }
      .sf-sidebar {
        position: fixed;
        left: 0;
        top: var(--sf-topbar-h);
        width: var(--sf-sidebar-w);
        height: calc(100vh - var(--sf-topbar-h));
        padding: 18px 16px 10px !important;
        border-right: 1px solid #d4dbd7;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        z-index: 30;
        background: #fff;
      }
      .sf-sidebar nav {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding-right: 4px;
      }
      .sf-main-scroll,
      .sf-content {
        height: calc(100vh - var(--sf-topbar-h));
        min-width: 0;
        width: calc(100% - var(--sf-sidebar-w));
        margin-left: var(--sf-sidebar-w);
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box;
        scroll-padding-bottom: 96px;
      }
      .sf-content {
        padding: 14px 16px 96px !important;
        padding-bottom: 96px;
      }
      .sf-content::after {
        content: '';
        display: block;
        height: 120px;
        width: 100%;
        flex-shrink: 0;
      }
      .sf-nav-item { font-size: 14px; }
      .sf-title { font-size: 26px !important; line-height: 32px !important; }
      .sf-wrap .text-headline-lg { font-size: 24px !important; line-height: 30px !important; }
      .sf-wrap .text-on-surface { color: #191c1e !important; }
      .sf-wrap .text-on-surface-variant { color: #52615a !important; }
      .admin-panel-card {
        margin-bottom: 12px !important;
        padding: 12px !important;
      }
      .page-title-block {
        display: block;
        margin-bottom: 20px !important;
      }
      .page-title-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
      }
      .page-title-copy h1,
      .page-title-copy p {
        margin: 0 !important;
      }
      .sf-sidebar .brand-title,
      .sf-sidebar .brand-subtitle {
        line-height: 1.15 !important;
      }
      .sf-sidebar .logout-slot {
        margin-top: auto;
        padding-top: 14px !important;
        padding-bottom: 8px !important;
      }
      .sf-sidebar-collapsed { --sf-sidebar-w: var(--sf-sidebar-collapsed-w); }
      .sf-sidebar-collapsed .sf-sidebar .nav-label,
      .sf-sidebar-collapsed .sf-sidebar .brand-title,
      .sf-sidebar-collapsed .sf-sidebar .brand-subtitle { display: none; }
      .sf-sidebar-collapsed .sf-sidebar .sf-nav-item { justify-content: center; }
      .sf-sidebar-collapsed .sf-sidebar .sf-nav-item span.material-symbols-outlined { margin-right: 0; }
      .sf-sidebar-collapsed .sf-sidebar .admin-panel-card { padding-left: 10px; padding-right: 10px; }
      .custom-shadow { box-shadow: 0 2px 4px rgba(0, 0, 0, .04); }
      .sf-wrap .h-16 { height: var(--sf-topbar-h) !important; }
      .table-sort-link { display: inline-flex; align-items: center; gap: 6px; color: inherit; text-decoration: none; }
      .table-sort-link:hover { color: #006948; }
      .user-delete-modal-backdrop { background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); }
      .sf-mobile-sidebar-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.42);
        z-index: 24;
        opacity: 0;
        pointer-events: none;
        transition: opacity .22s ease;
      }
      .sf-sidebar {
        transition: transform .24s ease, opacity .24s ease, box-shadow .24s ease;
      }
      .sf-mobile-menu-open .sf-mobile-sidebar-backdrop {
        opacity: 1;
        pointer-events: auto;
      }
      .sf-mobile-menu-open .sf-sidebar {
        transform: translateX(0);
        opacity: 1;
        pointer-events: auto;
        box-shadow: 18px 0 40px rgba(0, 0, 0, .14);
      }
      @media (max-width: 1279px) {
        .sf-layout {
          height: auto;
          overflow: visible;
        }
        .sf-sidebar {
          position: fixed;
          left: 0;
          top: var(--sf-topbar-h);
          width: min(84vw, 300px);
          height: calc(100vh - var(--sf-topbar-h));
          transform: translateX(-102%);
          opacity: 0;
          pointer-events: none;
          z-index: 30;
        }
        .sf-main-scroll,
        .sf-content {
          width: 100%;
          margin-left: 0;
          height: auto;
          overflow: visible;
        }
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-content::after { height: 120px; }
        .sf-main-scroll,
        .sf-content { padding: 12px !important; }
        .sf-main-scroll h1,
        .sf-content h1 { font-size: 28px !important; line-height: 34px !important; }
        .sf-main-scroll .px-6,
        .sf-content .px-6 { padding-left: 12px !important; padding-right: 12px !important; }
        .sf-main-scroll .py-4,
        .sf-content .py-4 { padding-top: 10px !important; padding-bottom: 10px !important; }
        .sf-main-scroll table th,
        .sf-main-scroll table td,
        .sf-content table th,
        .sf-content table td { font-size: 13px !important; white-space: nowrap; }
        .sf-wrap .sf-toolbar {
          display: grid;
          grid-template-columns: 1fr;
        }
        .sf-wrap .sf-toolbar .sf-export {
          margin-left: 0 !important;
          width: 100%;
          align-items: flex-start !important;
        }
        .sf-wrap .sf-toolbar .sf-export > div {
          width: 100%;
          justify-content: flex-start;
        }
      }
      @media (max-width: 767px) {
        .sf-wrap header { padding-left: 12px !important; padding-right: 12px !important; }
        .sf-wrap .sf-main-scroll,
        .sf-wrap .sf-content { padding: 12px !important; }
        .sf-wrap .sf-title { font-size: 22px !important; line-height: 28px !important; }
        .sf-wrap .sf-main-scroll h1,
        .sf-wrap .sf-content h1 { font-size: 24px !important; line-height: 30px !important; }
        .sf-wrap .sf-main-scroll .text-[40px],
        .sf-wrap .sf-content .text-[40px] { font-size: 24px !important; line-height: 30px !important; }
        .sf-wrap .sf-main-scroll .grid-cols-1.sm\\:grid-cols-2.xl\\:grid-cols-5,
        .sf-wrap .sf-content .grid-cols-1.sm\\:grid-cols-2.xl\\:grid-cols-5 { gap: 12px !important; }
        .sf-wrap .sf-header-actions { gap: 8px; }
        .sf-wrap .sf-header-actions > div { width: 32px !important; height: 32px !important; }
        .sf-wrap .sf-header-actions .material-symbols-outlined { font-size: 20px; }
      }
      @media (min-width: 768px) {
        .sf-content {
          padding: 16px 16px 96px !important;
        }
      }
    </style>

      <div class="sf-wrap bg-background text-on-surface antialiased min-h-screen overflow-x-hidden">
      <header class="bg-surface-container-lowest text-primary border-b border-outline-variant shadow-sm flex justify-between items-center px-5 h-16 w-full sticky top-0 z-50">
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface hover:bg-surface-container"
            aria-label="Kembali"
            onclick='if (window.history.length > 1) { window.history.back(); } else { window.location.href = @json($mobileBackUrl); }'
          >
            <span class="material-symbols-outlined">arrow_back</span>
          </button>
          <button id="mobileSidebarBtn" type="button" class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface hover:bg-surface-container" aria-label="Buka navigasi">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <div class="flex items-center gap-4"><x-brand.logo class="h-9 w-auto max-w-[240px]" /></div>
        </div>
        <button id="toggleSidebarBtn" type="button" class="hidden lg:inline-flex items-center gap-1 rounded-lg border border-outline-variant px-3 py-2 text-sm text-on-surface hover:bg-surface-container">
          <span class="material-symbols-outlined text-base">left_panel_close</span>
          <span>Sidebar</span>
        </button>
      </header>

      <div class="sf-shell">
      <div class="sf-layout">
        <div id="mobileSidebarBackdrop" class="sf-mobile-sidebar-backdrop lg:hidden"></div>
        <aside class="sf-sidebar flex flex-col w-full p-4 bg-white">
          <div class="admin-panel-card mb-3 rounded-lg border border-[#d4dbd7] bg-[#f2f4f6] p-3">
            <div class="flex items-center gap-2">
              <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#006948] text-white"><x-brand.mark class="h-4 w-4" /></div>
              <div>
                <p class="brand-title text-[13px] font-semibold text-[#006948]">{{ ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? 'Admin Besar Panel' : 'Admin Panel' }}</p>
                <p class="brand-subtitle text-[10px] uppercase tracking-wide text-[#52615a]">{{ ($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess) ? 'Executive Mode' : ($isAdminBesarGudangModuleAccess ? 'Gudang Access Mode' : 'Management Mode') }}</p>
              </div>
            </div>
          </div>
          <nav class="flex-1 min-h-0 flex flex-col space-y-1 overflow-y-auto">
            @if($isAdminBesarContext && ! $isAdminBesarGudangModuleAccess)
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.admin-besar.index') ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ route('admin.admin-besar.index') }}"><span class="material-symbols-outlined">dashboard</span><span class="nav-label">Dashboard Admin Besar</span></a>
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.admin-besar.history*') ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ route('admin.admin-besar.history') }}"><span class="material-symbols-outlined">history</span><span class="nav-label">History Admin Besar</span></a>
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.admin-besar.history.supplier*') || $type === 'supplier-transactions' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ route('admin.admin-besar.history.supplier') }}"><span class="material-symbols-outlined">account_tree</span><span class="nav-label">Transaksi PT/CV</span></a>
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.transaksi.dashboard', 'admin.transactions.*') ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ route('admin.transaksi.dashboard') }}"><span class="material-symbols-outlined">point_of_sale</span><span class="nav-label">Akses Dashboard Admin Gudang</span></a>
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'users' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ $moduleBaseUrl . '?type=users' }}"><span class="material-symbols-outlined">group</span><span class="nav-label">Manajemen Akun</span></a>
            @else
              @if($isAdminBesarGudangModuleAccess)
                <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="{{ route('admin.admin-besar.index') }}"><span class="material-symbols-outlined">arrow_back</span><span class="nav-label">Kembali ke Admin Besar</span></a>
              @endif
              <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="{{ url('/admin/products') }}"><span class="material-symbols-outlined">inventory_2</span><span class="nav-label">Daftar Stok</span></a>
              <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="{{ url('/admin/suppliers') }}"><span class="material-symbols-outlined">local_shipping</span><span class="nav-label">Supplier</span></a>
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'credits' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ $creditsUrl }}"><span class="material-symbols-outlined">credit_card</span><span class="nav-label">Kredit &amp; Utang Saya</span></a>
              <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'supplier-transactions' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ $supplierTransactionsUrl }}"><span class="material-symbols-outlined">account_tree</span><span class="nav-label">Transaksi PT/CV</span></a>
              <div class="space-y-1 pt-1">
                <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'product-groups' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ $productGroupsUrl }}"><span class="material-symbols-outlined">inventory_2</span><span class="nav-label">Kelompok Stok</span></a>
                <div class="ml-3 border-l border-[#d4dbd7] pl-3 py-1 space-y-1">
                  <a class="sf-nav-item flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ $salesListActive ? 'bg-[#e8fff4] text-[#006948]' : 'text-[#52615a] hover:bg-[#f2f4f6]' }}" href="{{ $salesListUrl }}">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                    <span class="nav-label">{{ $salesListLabel }}</span>
                  </a>
                  <a class="sf-nav-item flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ $draftsActive ? 'bg-[#e8fff4] text-[#006948]' : 'text-[#52615a] hover:bg-[#f2f4f6]' }}" href="{{ $draftsUrl }}">
                    <span class="material-symbols-outlined text-[18px]">draft</span>
                    <span class="nav-label">{{ $draftsLabel }}</span>
                  </a>
                  <a class="sf-nav-item flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium {{ $historyActive ? 'bg-[#e8fff4] text-[#006948]' : 'text-[#52615a] hover:bg-[#f2f4f6]' }}" href="{{ $historyUrl }}">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    <span class="nav-label">{{ $historyLabel }}</span>
                  </a>
                </div>
              </div>
            @endif
          </nav>
          <div class="logout-slot pt-3 border-t border-[#d4dbd7]">
            <form method="POST" action="{{ route('logout') }}" class="js-admin-logout-form">
              @csrf
              <button type="submit" class="sf-nav-item w-full flex items-center gap-3 text-[#ba1a1a] px-3 py-2 hover:bg-[#ffdad6] rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span>
                <span class="nav-label">Logout</span>
              </button>
            </form>
          </div>
        </aside>

        <main class="sf-content min-h-screen p-4 md:p-6">
          @if ($type !== 'taxonomy')
            <div class="page-title-block mb-4 md:mb-5">
              <div class="page-title-copy">
                <h1 class="sf-title font-display text-headline-lg text-on-surface leading-none mt-0 mb-1">{{ $title }}</h1>
                <p class="mt-0 text-on-surface-variant text-[13px] leading-5">
                  Kelola data {{ strtolower($title) }} dengan tampilan konsisten seperti halaman daftar stok.
                </p>
              </div>
            </div>
          @endif

          @if ($type === 'reports')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#e6fff3]"><span class="material-symbols-outlined text-[#006948]">today</span></div><span class="text-[#52615a] font-medium">Pengeluaran Hari Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#006948]">Rp {{ number_format((float) ($reportStats['today_total'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ number_format((int) ($reportStats['today_count'] ?? 0), 0, ',', '.') }} transaksi pembelian</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#ececff]"><span class="material-symbols-outlined text-[#4648d4]">calendar_month</span></div><span class="text-[#52615a] font-medium">Pengeluaran Bulan Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#4648d4]">Rp {{ number_format((float) ($reportStats['month_total'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ number_format((int) ($reportStats['month_count'] ?? 0), 0, ',', '.') }} transaksi pembelian</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#fff2df]"><span class="material-symbols-outlined text-[#825100]">calendar_today</span></div><span class="text-[#52615a] font-medium">Pengeluaran Tahun Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#825100]">Rp {{ number_format((float) ($reportStats['year_total'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ number_format((int) ($reportStats['year_count'] ?? 0), 0, ',', '.') }} transaksi pembelian</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#e8efff]"><span class="material-symbols-outlined text-[#1e40af]">point_of_sale</span></div><span class="text-[#52615a] font-medium">Transaksi Admin Hari Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#1e40af]">Rp {{ number_format((float) ($reportStats['cashier_today_total'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ number_format((int) ($reportStats['cashier_today_count'] ?? 0), 0, ',', '.') }} transaksi admin toko</p>
              </div>
            </div>

            <div class="space-y-6">
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-6 custom-shadow">
                <div class="mb-4">
                  <h2 class="text-lg font-semibold text-[#191c1e]">Transaksi Pembelian per Bulan</h2>
                  <p class="text-sm text-[#52615a]">Setiap bulan dipisah ke status <strong>Lunas</strong> dan <strong>Utang</strong> supaya jelas barang apa saja yang masih belum selesai.</p>
                </div>

                <div class="space-y-4">
                  @forelse ($reportMonthlyPurchases as $monthGroup)
                    <div class="rounded-xl border border-[#d4dbd7] bg-[#fdfefe] overflow-hidden">
                      <div class="border-b border-[#d4dbd7] bg-[#f8faf9] px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                          <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Bulan</p>
                            <h3 class="text-2xl font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h3>
                          </div>
                          <div class="flex flex-col items-start gap-3 lg:items-end">
                            <div class="flex flex-wrap gap-2 text-sm">
                              <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 font-semibold text-[#006948]">Total {{ number_format((int) $monthGroup['summary']['total_transaksi'], 0, ',', '.') }} transaksi</span>
                              <span class="inline-flex items-center rounded-full bg-[#ececff] px-3 py-1 font-semibold text-[#4648d4]">Total {{ $monthGroup['summary']['total_nilai'] }}</span>
                              <span class="inline-flex items-center rounded-full bg-[#eef8f4] px-3 py-1 font-semibold text-[#006948]">Lunas {{ number_format((int) $monthGroup['summary']['lunas_count'], 0, ',', '.') }}</span>
                              <span class="inline-flex items-center rounded-full bg-[#eef8f4] px-3 py-1 font-semibold text-[#006948]">Nilai Lunas {{ $monthGroup['summary']['lunas_value'] }}</span>
                              <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Utang {{ number_format((int) $monthGroup['summary']['utang_count'], 0, ',', '.') }}</span>
                              <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Nilai Utang {{ $monthGroup['summary']['utang_value'] }}</span>
                            </div>
                            <button
                              type="button"
                              data-print-month-purchase="{{ e(json_encode($monthGroup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"
                              onclick="printMonthlyPurchaseNota(this)"
                              class="inline-flex items-center gap-2 rounded-lg border border-[#bccac0] bg-white px-3 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2] shadow-sm"
                              title="Print nota bulanan"
                              aria-label="Print nota bulanan"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M6 9V2h12v7"></path>
                                <path d="M6 18H5a3 3 0 0 1-3-3v-3a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3h-1"></path>
                                <path d="M6 14h12v8H6z"></path>
                                <path d="M8 18h8"></path>
                              </svg>
                              <span>Nota Bulan Ini</span>
                            </button>
                          </div>
                        </div>
                      </div>

                      <div class="grid grid-cols-1 gap-4 p-5">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 overflow-hidden">
                          <div class="flex items-center justify-between gap-3 border-b border-emerald-200 px-4 py-3">
                            <div>
                              <h4 class="font-semibold text-[#126c3a]">Transaksi Lunas</h4>
                              <p class="text-xs text-[#4b6b56]">Barang yang sudah terbayar penuh pada bulan ini.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#126c3a]">{{ number_format((int) $monthGroup['summary']['lunas_count'], 0, ',', '.') }} data</span>
                          </div>
                          <div class="overflow-x-auto">
                            <table class="min-w-[980px] w-full text-left border-collapse">
                              <thead>
                                <tr class="bg-white text-[#3d4a42] border-b border-emerald-200">
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Tanggal</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Supplier</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Barang</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Qty</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Harga Satuan</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Total</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                              </thead>
                              <tbody class="divide-y divide-emerald-100 bg-white">
                                @forelse ($monthGroup['lunas'] as $trx)
                                  <tr class="hover:bg-emerald-50/40 transition-colors">
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['tanggal'] }}</td>
                                    <td class="px-4 py-3 text-[15px] font-semibold">{{ $trx['supplier'] }}</td>
                                    <td class="px-4 py-3 text-[15px]">{{ $trx['barang'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ number_format((int) $trx['qty'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['harga_satuan'] }}</td>
                                    <td class="px-4 py-3 text-[15px] font-semibold whitespace-nowrap">{{ $trx['total'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['jatuh_tempo'] }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                      @if (!empty($trx['supplier_id']))
                                        <a href="{{ url('/admin/suppliers/' . $trx['supplier_id'] . '?batch_id=' . $trx['batch_id']) }}#riwayat-pembelian" class="inline-flex items-center gap-1 rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">
                                          <span class="material-symbols-outlined text-base">open_in_new</span>
                                          <span>Detail</span>
                                        </a>
                                      @else
                                        <span class="text-sm text-[#52615a]">-</span>
                                      @endif
                                    </td>
                                  </tr>
                                @empty
                                  <tr><td colspan="8" class="px-4 py-8 text-center text-[#52615a]">Tidak ada transaksi lunas di bulan ini.</td></tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <div class="rounded-xl border border-amber-200 bg-amber-50/60 overflow-hidden">
                          <div class="flex items-center justify-between gap-3 border-b border-amber-200 px-4 py-3">
                            <div>
                              <h4 class="font-semibold text-[#915500]">Transaksi Utang</h4>
                              <p class="text-xs text-[#6f531d]">Barang kredit yang masih ada sisa tagihan atau jatuh tempo.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#915500]">{{ number_format((int) $monthGroup['summary']['utang_count'], 0, ',', '.') }} data</span>
                          </div>
                          <div class="overflow-x-auto">
                            <table class="min-w-[1180px] w-full text-left border-collapse">
                              <thead>
                                <tr class="bg-white text-[#3d4a42] border-b border-amber-200">
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Tanggal</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Supplier</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Barang</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Qty</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Harga Satuan</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Total</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">DP</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Sudah Dibayar</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Sisa</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider">Status</th>
                                  <th class="px-4 py-3 font-medium uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                              </thead>
                              <tbody class="divide-y divide-amber-100 bg-white">
                                @forelse ($monthGroup['utang'] as $trx)
                                  @php
                                    $statusClass = $trx['status'] === 'JATUH TEMPO'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-amber-100 text-amber-700';
                                  @endphp
                                  <tr class="hover:bg-amber-50/40 transition-colors">
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['tanggal'] }}</td>
                                    <td class="px-4 py-3 text-[15px] font-semibold">{{ $trx['supplier'] }}</td>
                                    <td class="px-4 py-3 text-[15px]">{{ $trx['barang'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ number_format((int) $trx['qty'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['harga_satuan'] }}</td>
                                    <td class="px-4 py-3 text-[15px] font-semibold whitespace-nowrap">{{ $trx['total'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['down_payment'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['sudah_dibayar'] }}</td>
                                    <td class="px-4 py-3 text-[15px] font-semibold whitespace-nowrap">{{ $trx['sisa_kredit'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">{{ $trx['jatuh_tempo'] }}</td>
                                    <td class="px-4 py-3 text-[15px] whitespace-nowrap">
                                      <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $trx['status'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                      @if (!empty($trx['supplier_id']))
                                        <a href="{{ url('/admin/suppliers/' . $trx['supplier_id']) }}#riwayat-pembelian" class="inline-flex items-center gap-1 rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">
                                          <span class="material-symbols-outlined text-base">open_in_new</span>
                                          <span>Detail</span>
                                        </a>
                                      @else
                                        <span class="text-sm text-[#52615a]">-</span>
                                      @endif
                                    </td>
                                  </tr>
                                @empty
                                  <tr><td colspan="12" class="px-4 py-8 text-center text-[#52615a]">Tidak ada transaksi utang di bulan ini.</td></tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  @empty
                    <div class="rounded-xl border border-[#d4dbd7] bg-white px-6 py-10 text-center text-[#52615a]">Belum ada data pembelian.</div>
                  @endforelse
                </div>
              </div>

              <div class="rounded-xl border border-[#d4dbd7] bg-white p-6 custom-shadow">
                <div class="mb-4">
                  <h2 class="text-lg font-semibold text-[#191c1e]">Transaksi Admin per Bulan</h2>
                  <p class="text-sm text-[#52615a]">Riwayat penjualan dan invoice kasir juga dikelompokkan per bulan supaya mudah ditelusuri.</p>
                </div>

                <div class="space-y-4">
                  @forelse ($reportMonthlyCashierTransactions as $monthGroup)
                    <div class="rounded-xl border border-[#d4dbd7] bg-[#fdfefe] overflow-hidden">
                      <div class="border-b border-[#d4dbd7] bg-[#f8faf9] px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                          <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Bulan</p>
                            <h3 class="text-2xl font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h3>
                          </div>
                          <div class="grid grid-cols-2 gap-2 text-sm sm:flex sm:flex-wrap">
                            <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 font-semibold text-[#006948]">Total {{ number_format((int) $monthGroup['summary']['total_transaksi'], 0, ',', '.') }} transaksi</span>
                            <span class="inline-flex items-center rounded-full bg-[#ececff] px-3 py-1 font-semibold text-[#4648d4]">Nilai {{ $monthGroup['summary']['total_nilai'] }}</span>
                            <span class="inline-flex items-center rounded-full bg-[#e8efff] px-3 py-1 font-semibold text-[#1e40af]">Cash {{ number_format((int) $monthGroup['summary']['cash_count'], 0, ',', '.') }}</span>
                            <span class="inline-flex items-center rounded-full bg-[#e8efff] px-3 py-1 font-semibold text-[#1e40af]">Lunas {{ number_format((int) $monthGroup['summary']['lunas_count'], 0, ',', '.') }}</span>
                            <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Credit {{ number_format((int) $monthGroup['summary']['credit_count'], 0, ',', '.') }}</span>
                            <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Utang {{ number_format((int) $monthGroup['summary']['utang_count'], 0, ',', '.') }}</span>
                          </div>
                        </div>
                      </div>

                      <div class="overflow-x-auto">
                        <table class="min-w-[1280px] w-full text-left border-collapse">
                          <thead>
                            <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Invoice</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Pembeli</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Barang</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Qty</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Metode</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Total</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Kredit</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Tempo</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider">Status</th>
                              <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-[#e4e8e6]">
                            @forelse ($monthGroup['rows'] as $trx)
                              @php
                                $statusClass = $trx['status'] === 'LUNAS'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : ($trx['status'] === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                              @endphp
                              <tr class="hover:bg-[#f6f8f7] transition-colors">
                                <td class="px-5 py-4 font-semibold whitespace-nowrap">{{ $trx['invoice_number'] }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">{{ $trx['created_at'] }}</td>
                                <td class="px-5 py-4">{{ $trx['customer_name'] }}</td>
                                @php
                                  $barangItems = array_values(array_filter($trx['barang_items'] ?? []));
                                  $barangVisible = array_slice($barangItems, 0, 3);
                                  $barangRemaining = max(0, count($barangItems) - count($barangVisible));
                                @endphp
                                <td class="px-5 py-4 align-top">
                                  <div class="min-w-[260px] max-w-[380px]">
                                    <div class="flex flex-wrap gap-1.5">
                                      @forelse($barangVisible as $barang)
                                        <span class="inline-flex max-w-full items-center rounded-full border border-[#d4dbd7] bg-[#f8faf9] px-2.5 py-1 text-xs font-medium text-[#3d4a42]">
                                          <span class="truncate">{{ $barang }}</span>
                                        </span>
                                      @empty
                                        <span class="text-sm text-[#52615a]">-</span>
                                      @endforelse
                                      @if($barangRemaining > 0)
                                        <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-2.5 py-1 text-xs font-semibold text-[#006948]">
                                          +{{ number_format($barangRemaining, 0, ',', '.') }} lainnya
                                        </span>
                                      @endif
                                    </div>
                                  </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) $trx['qty'], 0, ',', '.') }}</td>
                                <td class="px-5 py-4 uppercase whitespace-nowrap">{{ $trx['payment_method'] }}</td>
                                <td class="px-5 py-4 text-right font-semibold whitespace-nowrap">{{ $trx['total'] }}</td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">{{ $trx['credit_amount'] }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">{{ $trx['credit_due_date'] }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                  <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $trx['status'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                  <a href="{{ route('admin.sales.receipt', ['sale' => $trx['sale_id']]) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                                </td>
                              </tr>
                            @empty
                              <tr><td colspan="11" class="px-5 py-10 text-center text-[#52615a]">Belum ada transaksi kasir di bulan ini.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                  @empty
                    <div class="rounded-xl border border-[#d4dbd7] bg-white px-6 py-10 text-center text-[#52615a]">Belum ada transaksi kasir.</div>
                  @endforelse
                </div>
              </div>

              <div class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden custom-shadow">
                <div class="px-6 py-4 border-b border-[#d4dbd7]">
                  <h2 class="text-lg font-semibold text-[#191c1e]">Kelompok PT: Kredit & Lunas</h2>
                  <p class="text-sm text-[#52615a]">Ringkasan transaksi per PT/CV, dipisahkan status kredit dan lunas.</p>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-[980px] w-full text-left border-collapse">
                    <thead>
                      <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                        <th class="px-6 py-4 font-medium uppercase tracking-wider">PT / CV</th>
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
                      @forelse ($reportSupplierGroups as $group)
                        <tr class="hover:bg-[#f6f8f7] transition-colors">
                          <td class="px-6 py-4 font-semibold">{{ $group['supplier'] }}</td>
                          <td class="px-6 py-4">{{ number_format((int) $group['total_transaksi'], 0, ',', '.') }}</td>
                          <td class="px-6 py-4">{{ number_format((int) $group['total_qty'], 0, ',', '.') }}</td>
                          <td class="px-6 py-4 font-semibold">{{ $group['total_modal'] }}</td>
                          <td class="px-6 py-4">{{ number_format((int) $group['kredit_count'], 0, ',', '.') }}</td>
                          <td class="px-6 py-4">{{ number_format((int) $group['jatuh_tempo_count'], 0, ',', '.') }}</td>
                          <td class="px-6 py-4">{{ number_format((int) $group['lunas_count'], 0, ',', '.') }}</td>
                          <td class="px-6 py-4">{{ $group['last_purchase_at'] }}</td>
                          <td class="px-6 py-4 text-right">
                            @if(!empty($group['supplier_id']))
                              <a href="{{ url('/admin/suppliers/' . $group['supplier_id']) }}#riwayat-pembelian" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                            @else
                              <span class="text-sm text-[#52615a]">-</span>
                            @endif
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kelompok PT/CV.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          @elseif ($type === 'supplier-transactions')
            <div class="space-y-6">
              @forelse ($ptCustomerGroups as $monthGroup)
                <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
                  <div class="px-6 py-4 border-b border-[#d4dbd7]">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                      <div>
                        <p class="text-xs uppercase tracking-[0.32em] text-[#52615a]">Bulan</p>
                        <h2 class="text-lg font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h2>
                        <p class="text-sm text-[#52615a]">Transaksi PT/CV yang tercatat pada bulan ini.</p>
                      </div>
                      <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                          PT {{ number_format((int) ($monthGroup['summary']['total_pt'] ?? 0), 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                          Transaksi {{ number_format((int) ($monthGroup['summary']['total_transaksi'] ?? 0), 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-sm font-semibold text-violet-700">
                          Nilai {{ $monthGroup['summary']['total_nilai'] ?? 'Rp 0' }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
                          Kredit {{ number_format((int) ($monthGroup['summary']['kredit'] ?? 0), 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-sm font-semibold text-rose-700">
                          Jatuh Tempo {{ number_format((int) ($monthGroup['summary']['jatuh_tempo'] ?? 0), 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-sm font-semibold text-cyan-700">
                          Lunas {{ number_format((int) ($monthGroup['summary']['lunas'] ?? 0), 0, ',', '.') }}
                        </span>
                      </div>
                    </div>
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
                        @forelse ($monthGroup['rows'] as $group)
                          @php
                            $ptDetailUrl = $supplierTransactionsUrl . '&pt=' . urlencode($group['pt_name']);
                          @endphp
                          <tr
                            class="cursor-pointer hover:bg-[#f6f8f7] transition-colors"
                            onclick="window.location.href={{ Illuminate\Support\Js::from($ptDetailUrl) }}"
                            onkeydown="if(event.key === 'Enter' || event.key === ' '){ event.preventDefault(); window.location.href={{ Illuminate\Support\Js::from($ptDetailUrl) }}; }"
                            tabindex="0"
                            role="link"
                            aria-label="Buka detail {{ $group['pt_name'] }}"
                          >
                            <td class="px-6 py-4 font-semibold">{{ $group['pt_name'] }}</td>
                            <td class="px-6 py-4">{{ number_format((int) $group['total_transaksi'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ number_format((int) $group['total_qty'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-semibold">{{ $group['total_nilai'] }}</td>
                            <td class="px-6 py-4">{{ number_format((int) $group['kredit'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ number_format((int) $group['jatuh_tempo'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ number_format((int) $group['lunas'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $group['terakhir_beli'] }}</td>
                            <td class="relative z-10 px-6 py-4 text-right">
                              <a
                                href="{{ $ptDetailUrl }}"
                                class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm font-medium text-[#006948] hover:bg-[#f1f4f2]"
                                onclick="event.stopPropagation()"
                              >
                                Detail
                              </a>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kelompok PT/CV pada bulan ini.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              @empty
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-6 py-10 text-center text-[#52615a]">Belum ada data kelompok PT/CV.</div>
              @endforelse
            </div>

            @if(!empty($ptCustomerDetail['pt_name']))
              <div class="mt-6 bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">Detail Riwayat PT/CV: {{ $ptCustomerDetail['pt_name'] }}</h2>
                  <p class="text-sm text-[#52615a]">Riwayat transaksi dari kasir, lengkap dengan nota penjualan.</p>
              </div>
                @if(!empty($ptCustomerDetail['summary']))
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 px-6 py-4 border-b border-[#d4dbd7] bg-[#f8faf9]">
                    <div><p class="text-xs uppercase text-[#52615a]">Total Transaksi</p><p class="text-xl font-semibold text-[#191c1e]">{{ number_format((int) $ptCustomerDetail['summary']['total_transaksi'], 0, ',', '.') }} kali</p></div>
                    <div><p class="text-xs uppercase text-[#52615a]">Total Qty</p><p class="text-xl font-semibold text-[#191c1e]">{{ number_format((int) $ptCustomerDetail['summary']['total_qty'], 0, ',', '.') }}</p></div>
                    <div><p class="text-xs uppercase text-[#52615a]">Total Nilai</p><p class="text-xl font-semibold text-[#191c1e]">{{ $ptCustomerDetail['summary']['total_nilai'] }}</p></div>
                  </div>
                @endif
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
                      @forelse($ptCustomerDetail['rows'] as $row)
                        <tr class="hover:bg-[#f6f8f7] transition-colors">
                          <td class="px-6 py-4 font-semibold">{{ $row['invoice'] }}</td>
                          <td class="px-6 py-4">{{ $row['waktu'] }}</td>
                          <td class="px-6 py-4">{{ $row['metode'] }}</td>
                          <td class="px-6 py-4">{{ number_format((int) $row['qty'], 0, ',', '.') }}</td>
                          <td class="px-6 py-4 font-semibold">{{ $row['total'] }}</td>
                          <td class="px-6 py-4">{{ $row['kredit'] }}</td>
                          <td class="px-6 py-4">{{ $row['jatuh_tempo'] }}</td>
                          <td class="px-6 py-4">
                            @php
                              $s = $row['status'] ?? 'LUNAS';
                              $statusClass = $s === 'LUNAS'
                                  ? 'bg-emerald-100 text-emerald-700'
                                  : ($s === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $s }}</span>
                          </td>
                          <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.sales.receipt', ['sale' => $row['sale_id']]) }}" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Lihat Nota</a>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada riwayat transaksi PT ini.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            @endif
          @elseif ($type === 'product-groups')
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="text-xs uppercase tracking-[0.2em] text-[#52615a]">Total Produk</div>
                <div class="mt-2 text-[34px] leading-[42px] font-bold text-[#191c1e]">{{ number_format((int) ($productGroups['summary']['total_products'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">Part number yang punya riwayat pembelian atau penjualan</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="text-xs uppercase tracking-[0.2em] text-[#52615a]">Transaksi Admin</div>
                <div class="mt-2 text-[34px] leading-[42px] font-bold text-[#191c1e]">{{ number_format((int) ($productGroups['summary']['sales_count'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ $productGroups['summary']['sales_value'] ?? 'Rp 0' }} total nilai jual</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="text-xs uppercase tracking-[0.2em] text-[#52615a]">Pembelian Admin</div>
                <div class="mt-2 text-[34px] leading-[42px] font-bold text-[#191c1e]">{{ number_format((int) ($productGroups['summary']['purchase_count'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ $productGroups['summary']['purchase_value'] ?? 'Rp 0' }} total nilai beli</p>
              </div>
              <div class="bg-white p-6 rounded-xl border border-[#d4dbd7] custom-shadow">
                <div class="text-xs uppercase tracking-[0.2em] text-[#52615a]">Status Kredit</div>
                <div class="mt-2 text-[34px] leading-[42px] font-bold text-[#191c1e]">{{ number_format((int) (($productGroups['summary']['sales_kredit'] ?? 0) + ($productGroups['summary']['purchase_kredit'] ?? 0)), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">Gabungan transaksi kredit pembelian dan penjualan</p>
              </div>
            </div>

            <div class="mb-6 rounded-xl border border-[#d4dbd7] bg-white p-5 custom-shadow">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm font-semibold text-[#191c1e]">Akses Transaksi Admin</p>
                  <p class="text-sm text-[#52615a]">Pakai daftar barang jual, simpan draft transaksi, lalu buka history atau nota dari sini.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                  <a href="{{ $salesListUrl }}" class="inline-flex items-center justify-center rounded-lg bg-[#006948] px-4 py-2 text-sm font-semibold text-white hover:brightness-95">
                    Buka Transaksi
                  </a>
                  <a href="{{ $draftsUrl }}" class="inline-flex items-center justify-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]">
                    {{ $draftsLabel }}
                  </a>
                  <a href="{{ $historyUrl }}" class="inline-flex items-center justify-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]">
                    {{ $historyLabel }}
                  </a>
                </div>
              </div>
            </div>

            <div class="mb-4 flex flex-wrap justify-end gap-2">
              <a
                id="productGroupsExportCsvBtn"
                href="{{ route('admin.product-groups.export.csv') }}"
                download="kelompok-barang-{{ now()->format('Ymd-His') }}.csv"
                data-export-url="{{ route('admin.product-groups.export.csv') }}"
                data-filename="kelompok-barang-{{ now()->format('Ymd-His') }}.csv"
                class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]"
              >
                Export CSV
              </a>
              <a
                id="productGroupsExportBtn"
                href="{{ route('admin.product-groups.export') }}"
                download="kelompok-barang-{{ now()->format('Ymd-His') }}.xlsx"
                data-export-url="{{ route('admin.product-groups.export') }}"
                data-filename="kelompok-barang-{{ now()->format('Ymd-His') }}.xlsx"
                class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]"
              >
                Export Excel
              </a>
            </div>

            <div class="space-y-4">
              @forelse ($productGroups['groups'] as $group)
                <div class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden custom-shadow">
                  <div class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                      <p class="text-xs uppercase tracking-[0.32em] text-[#52615a]">Part Number</p>
                      @if (!empty($group['supplier_id']))
                        <a
                          href="{{ url('/admin/suppliers/' . $group['supplier_id']) }}#riwayat-pembelian"
                          class="inline-flex max-w-full"
                          title="Lihat detail supplier"
                        >
                          <h2 class="truncate text-2xl font-semibold text-[#191c1e] transition-colors hover:text-[#006948]">
                            {{ $group['part_number'] }}
                          </h2>
                        </a>
                      @else
                        <h2 class="truncate text-2xl font-semibold text-[#191c1e]">{{ $group['part_number'] }}</h2>
                      @endif
                      <p class="mt-1 text-sm text-[#52615a]">{{ $group['part_name'] }}</p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                      <a href="{{ route('admin.product-groups.show', ['product' => $group['product_id']]) }}" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                    </div>
                  </div>
                </div>
              @empty
                <div class="bg-white p-8 rounded-xl border border-[#d4dbd7] text-center text-[#52615a]">Belum ada part number yang punya riwayat transaksi.</div>
              @endforelse
            </div>
          @elseif ($type === 'credits')
            @if(session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if($errors->any())
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <div class="mb-4 flex flex-col gap-3 rounded-xl border border-[#d4dbd7] bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="text-sm font-semibold text-[#191c1e]">Export Excel Utang</p>
                <p class="text-sm text-[#52615a]">Berisi daftar utang, detail cicilan, pokok, DP, dan riwayat pelunasan dalam beberapa sheet.</p>
              </div>
              <a
                href="{{ route('admin.credits.export.xlsx') }}"
                download="utang-saya-{{ now()->format('Ymd-His') }}.xlsx"
                class="inline-flex items-center justify-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]"
              >
                Export Excel
              </a>
            </div>
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 pt-5">
                <p class="text-sm text-[#52615a]">Tabel ini menampilkan semua kredit dari seluruh supplier yang sudah dicatat. Detail, cicilan, dan nota bisa kamu buka lewat rekap per bulan di bawah.</p>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full text-left border-collapse text-xs md:text-sm">
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
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    @forelse ($rows as $row)
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-3 py-2.5 text-[13px] font-semibold whitespace-nowrap">{{ $row['supplier'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['part_number'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['part_name'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['merek'] ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['unit'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['qty'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['harga_beli'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] font-semibold whitespace-nowrap">{{ $row['total_kredit'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['down_payment'] ?? 'Rp 0' }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['sudah_dibayar'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] font-semibold whitespace-nowrap">{{ $row['sisa_kredit'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">{{ $row['jatuh_tempo'] }}</td>
                        <td class="px-3 py-2.5 text-[13px] whitespace-nowrap">
                          @php
                            $status = $row['status'] ?? 'BELUM LUNAS';
                            $statusClass = $status === 'LUNAS'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($status === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                          @endphp
                          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="13" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kredit.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <p class="mt-3 text-sm text-[#52615a]">Untuk lihat detail, cicilan, dan nota, scroll ke bawah ke rekap per bulan.</p>
            </div>
            <div class="mt-6 rounded-xl border border-[#d4dbd7] bg-white p-6 custom-shadow">
              @php
                $creditTotals = [
                  'months' => count($reportCreditMonthlyGroups),
                  'transactions' => collect($reportCreditMonthlyGroups)->sum(fn ($g) => (int) ($g['summary']['total_transaksi'] ?? 0)),
                  'total' => collect($reportCreditMonthlyGroups)->sum(fn ($g) => (float) ($g['summary']['total_nilai_value'] ?? 0)),
                  'sisa' => collect($reportCreditMonthlyGroups)->sum(fn ($g) => (float) ($g['summary']['total_sisa_value'] ?? 0)),
                  'jatuh_tempo' => collect($reportCreditMonthlyGroups)->sum(fn ($g) => (int) ($g['summary']['jatuhtempo_count'] ?? 0)),
                ];
              @endphp
              <div class="mb-4">
                <h2 class="text-lg font-semibold text-[#191c1e]">Pengelompokan Kredit per Bulan</h2>
                <p class="text-sm text-[#52615a]">Ringkasan kredit dan utang ditampilkan per bulan supaya mudah dipantau tanpa pindah halaman.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="rounded-xl border border-[#d4dbd7] bg-[#fffaf2] p-5">
                  <p class="text-xs uppercase tracking-[0.2em] text-[#826100]">Total Nilai</p>
                  <p class="mt-2 text-2xl font-semibold text-[#a36700]">Rp {{ number_format((float) $creditTotals['total'], 0, ',', '.') }}</p>
                  <p class="mt-1 text-sm text-[#6f6a5f]">Akumulasi semua pembelian kredit</p>
                </div>
                <div class="rounded-xl border border-[#d4dbd7] bg-[#f4fbf7] p-5">
                  <p class="text-xs uppercase tracking-[0.2em] text-[#006948]">Sisa Tagihan</p>
                  <p class="mt-2 text-2xl font-semibold text-[#006948]">Rp {{ number_format((float) $creditTotals['sisa'], 0, ',', '.') }}</p>
                  <p class="mt-1 text-sm text-[#52615a]">Total yang belum lunas</p>
                </div>
                <div class="rounded-xl border border-[#d4dbd7] bg-[#fff4f2] p-5">
                  <p class="text-xs uppercase tracking-[0.2em] text-[#ba1a1a]">Perhatian</p>
                  <p class="mt-2 text-2xl font-semibold text-[#ba1a1a]">{{ number_format((int) $creditTotals['jatuh_tempo'], 0, ',', '.') }}</p>
                  <p class="mt-1 text-sm text-[#52615a]">Transaksi jatuh tempo</p>
                </div>
                <div class="rounded-xl border border-[#d4dbd7] bg-[#f4f6ff] p-5">
                  <p class="text-xs uppercase tracking-[0.2em] text-[#4648d4]">Bulan Tercatat</p>
                  <p class="mt-2 text-2xl font-semibold text-[#4648d4]">{{ number_format((int) $creditTotals['months'], 0, ',', '.') }}</p>
                  <p class="mt-1 text-sm text-[#52615a]">{{ number_format((int) $creditTotals['transactions'], 0, ',', '.') }} transaksi kredit</p>
                </div>
              </div>

              <div class="space-y-4">
                @forelse ($reportCreditMonthlyGroups as $monthGroup)
                  <div class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden">
                    <div class="border-b border-[#d4dbd7] bg-[#f8faf9] px-5 py-4">
                      <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                          <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Bulan</p>
                          <h3 class="text-2xl font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h3>
                          <p class="mt-1 text-sm text-[#52615a]">Kredit dan utang yang tercatat pada bulan ini.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm sm:flex sm:flex-wrap">
                          <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 font-semibold text-[#006948]">Transaksi {{ number_format((int) $monthGroup['summary']['total_transaksi'], 0, ',', '.') }}</span>
                          <span class="inline-flex items-center rounded-full bg-[#ececff] px-3 py-1 font-semibold text-[#4648d4]">Nilai {{ $monthGroup['summary']['total_nilai'] }}</span>
                          <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Sisa {{ $monthGroup['summary']['total_sisa'] }}</span>
                          <span class="inline-flex items-center rounded-full bg-[#ffdad6] px-3 py-1 font-semibold text-[#ba1a1a]">Jatuh Tempo {{ number_format((int) $monthGroup['summary']['jatuhtempo_count'], 0, ',', '.') }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="overflow-x-auto">
                      <table class="min-w-[1180px] w-full text-left border-collapse">
                        <thead>
                          <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                            <th class="px-5 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider">Supplier</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider">Barang</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider">Qty</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Subtotal</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">DP</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Sudah Dibayar</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Sisa</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e4e8e6]">
                          @forelse ($monthGroup['rows'] as $trx)
                            @php
                              $statusClass = $trx['status'] === 'JATUH TEMPO'
                                  ? 'bg-red-100 text-red-700'
                                  : ($trx['status'] === 'LUNAS' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700');
                              $statusHref = $trx['status'] === 'LUNAS'
                                  ? route('admin.credits.receipt', ['batch' => $trx['batch_id']])
                                  : route('admin.credits.detail', ['batch' => $trx['batch_id']]) . '#input-cicilan';
                              $statusLabel = $trx['status'] === 'LUNAS' ? 'Lihat Nota' : 'Buka Detail';
                            @endphp
                            <tr class="hover:bg-[#f6f8f7] transition-colors">
                              <td class="px-5 py-4 whitespace-nowrap">{{ $trx['tanggal'] }}</td>
                              <td class="px-5 py-4 font-semibold">{{ $trx['supplier'] }}</td>
                              <td class="px-5 py-4 font-medium text-[#191c1e]">{{ $trx['barang'] }}</td>
                              <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) $trx['qty'], 0, ',', '.') }}</td>
                              <td class="px-5 py-4 text-right whitespace-nowrap">{{ $trx['subtotal'] }}</td>
                              <td class="px-5 py-4 text-right whitespace-nowrap">{{ $trx['down_payment'] }}</td>
                              <td class="px-5 py-4 text-right whitespace-nowrap">{{ $trx['sudah_dibayar'] }}</td>
                              <td class="px-5 py-4 text-right font-semibold whitespace-nowrap">{{ $trx['sisa_kredit'] }}</td>
                              <td class="px-5 py-4 whitespace-nowrap">{{ $trx['jatuh_tempo'] }}</td>
                              <td class="px-5 py-4 whitespace-nowrap text-center">
                                <a
                                  href="{{ $statusHref }}"
                                  class="inline-flex w-full max-w-[130px] cursor-pointer select-none items-center justify-center rounded-full px-3 py-2 text-xs font-semibold {{ $statusClass }} hover:opacity-90"
                                  title="{{ $statusLabel }}"
                                  aria-label="{{ $statusLabel }}"
                                  role="button"
                                >
                                  {{ $trx['status'] }}
                                </a>
                              </td>
                              <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if (!empty($trx['batch_id']))
                                  <div class="flex flex-wrap justify-end gap-1.5">
                                    <a href="{{ route('admin.credits.detail', ['batch' => $trx['batch_id']]) }}#input-cicilan" class="inline-flex items-center gap-1 rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">
                                      <span class="material-symbols-outlined text-base">open_in_new</span>
                                      <span>Detail / Cicil</span>
                                    </a>
                                    <a href="{{ route('admin.credits.receipt', ['batch' => $trx['batch_id']]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">
                                      <span class="material-symbols-outlined text-base">receipt_long</span>
                                      <span>Nota</span>
                                    </a>
                                  </div>
                                @else
                                  <span class="text-sm text-[#52615a]">-</span>
                                @endif
                              </td>
                            </tr>
                          @empty
                            <tr><td colspan="11" class="px-5 py-10 text-center text-[#52615a]">Belum ada transaksi kredit pada bulan ini.</td></tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                @empty
                  <div class="rounded-xl border border-[#d4dbd7] bg-white px-6 py-10 text-center text-[#52615a]">Belum ada kredit yang tercatat.</div>
                @endforelse
              </div>

              @php
                $settlementTotals = [
                  'count' => count($creditSettlementHistory),
                  'total' => collect($creditSettlementHistory)->sum(fn ($row) => (float) ($row['nominal'] ?? 0)),
                  'pelunasan_count' => collect($creditSettlementHistory)->where('jenis', 'Pelunasan')->count(),
                ];
              @endphp

              <div class="mt-6 rounded-xl border border-[#d4dbd7] bg-white overflow-hidden">
                <div class="border-b border-[#d4dbd7] bg-[#f8faf9] px-5 py-4">
                  <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                      <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Riwayat Pelunasan</p>
                      <h3 class="text-2xl font-semibold text-[#191c1e]">Riwayat Pelunasan Kredit</h3>
                      <p class="mt-1 text-sm text-[#52615a]">Bagian ini hanya menampilkan kredit yang sudah lunas, lengkap dengan riwayat pembayaran dan nota.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm sm:flex sm:flex-wrap">
                      <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 font-semibold text-[#006948]">Total {{ number_format((int) $settlementTotals['count'], 0, ',', '.') }} transaksi</span>
                      <span class="inline-flex items-center rounded-full bg-[#ececff] px-3 py-1 font-semibold text-[#4648d4]">Nilai Rp {{ number_format((float) $settlementTotals['total'], 0, ',', '.') }}</span>
                      <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Pelunasan {{ number_format((int) $settlementTotals['pelunasan_count'], 0, ',', '.') }}</span>
                    </div>
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-[1080px] w-full text-left border-collapse">
                    <thead>
                      <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Supplier</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Barang</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Nominal</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Jenis</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Admin</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider">Catatan</th>
                        <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e4e8e6]">
                      @forelse ($creditSettlementHistory as $history)
                        <tr class="hover:bg-[#f6f8f7] transition-colors">
                          <td class="px-5 py-4 whitespace-nowrap">{{ $history['tanggal'] }}</td>
                          <td class="px-5 py-4 font-semibold">{{ $history['supplier'] }}</td>
                          <td class="px-5 py-4 font-medium text-[#191c1e]">
                            <div>{{ $history['barang'] }}</div>
                            <div class="mt-1 text-xs text-[#52615a]">{{ $history['part_number'] }}</div>
                          </td>
                          <td class="px-5 py-4 font-semibold whitespace-nowrap">{{ $history['nominal_text'] }}</td>
                          <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $history['jenis_class'] }}">{{ $history['jenis'] }}</span>
                          </td>
                          <td class="px-5 py-4 whitespace-nowrap">{{ $history['kasir'] }}</td>
                          <td class="px-5 py-4">{{ $history['note'] }}</td>
                          <td class="px-5 py-4 text-right whitespace-nowrap">
                            <a href="{{ $history['receipt_url'] }}" class="inline-flex items-center gap-1 rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">
                              <span class="material-symbols-outlined text-base">receipt_long</span>
                              <span>Nota</span>
                            </a>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-[#52615a]">Belum ada riwayat pelunasan.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            @elseif ($type === 'users')
            @if(session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if($errors->any())
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="mb-6 rounded-xl border border-[#d4dbd7] bg-white p-5 custom-shadow">
              <h2 class="mb-4 text-lg font-semibold text-[#191c1e]">Tambah Akun Baru</h2>
              <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf
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
                    <option value="admin">Admin Toko/Gudang</option>
                    <option value="admin_besar">Admin Besar</option>
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
              @forelse ($userRows as $userRow)
                <div class="rounded-xl border border-[#d4dbd7] bg-white p-5 custom-shadow">
                  <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                      <h3 class="text-base font-semibold text-[#191c1e]">{{ $userRow['nama'] }}</h3>
                      <p class="text-sm text-[#52615a]">{{ $userRow['email'] }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.users.destroy', $userRow['id']) }}" onsubmit="return openDeleteUserModal(this)">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">Hapus</button>
                    </form>
                  </div>

                  <form method="POST" action="{{ route('admin.users.update', $userRow['id']) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Nama</label>
                      <input type="text" name="name" value="{{ $userRow['nama'] }}" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Email</label>
                      <input type="email" name="email" value="{{ $userRow['email'] }}" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-sm font-medium text-[#3d4a42]">Role</label>
                      <select name="role" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20" required>
                        <option value="admin" @selected($userRow['role'] === 'admin')>Admin Toko/Gudang</option>
                        <option value="admin_besar" @selected($userRow['role'] === 'admin_besar')>Admin Besar</option>
                      </select>
                    </div>
                    <div class="flex items-end">
                      <label class="inline-flex items-center gap-2 rounded-lg border border-[#d4dbd7] px-3 py-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($userRow['is_active']) class="rounded border-[#bccac0] text-[#006948] focus:ring-[#006948]/20">
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
              @empty
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-6 py-10 text-center text-[#52615a]">Belum ada user.</div>
              @endforelse
            </div>
          @else
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7] flex items-center justify-end">
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Print Tabel</button>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse print-source-table">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      @if (!empty($rows))
                        @foreach (array_keys($rows[0]) as $key)
                          <th class="px-6 py-4 font-medium uppercase tracking-wider">{{ str_replace('_', ' ', $key) }}</th>
                        @endforeach
                      @endif
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    @forelse ($rows as $row)
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        @foreach ($row as $value)
                          <td class="px-6 py-4 text-[15px]">{{ $value }}</td>
                        @endforeach
                      </tr>
                    @empty
                      <tr><td colspan="10" class="px-6 py-10 text-center text-[#52615a]">Belum ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        </main>
      </div>
      </div>
    </div>

    @if ($type === 'credits')
      <div id="creditInstallmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 px-4">
        <div class="w-full max-w-xl rounded-xl border border-[#d4dbd7] bg-white p-6 shadow-2xl">
          <h3 class="text-xl font-semibold text-[#191c1e]">Bayar Cicilan Kredit</h3>
          <p id="creditInstallmentInfo" class="mt-1 text-sm text-[#52615a]">Isi nominal cicilan untuk kredit yang dipilih.</p>
          <form id="creditInstallmentForm" method="POST" class="mt-4 space-y-4">
            @csrf
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
    @endif

    @if ($type === 'taxonomy')
      <div id="taxonomyModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/50 px-4">
        <div class="w-full max-w-2xl rounded-xl border border-[#d4dbd7] bg-white p-6 shadow-2xl">
          <h3 id="taxonomyModalTitle" class="text-xl font-semibold text-[#191c1e]">Tambah Data</h3>
          <form id="taxonomyForm" method="POST" action="{{ route('admin.taxonomy.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="q" value="{{ $searchKeyword }}">
            <input type="hidden" name="sort" value="{{ $taxonomySort }}">
            <input type="hidden" name="dir" value="{{ $taxonomyDir }}">
            <input type="hidden" name="page" value="{{ $taxonomyPagination['current_page'] }}">
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
          <form id="taxonomyDeleteForm" method="POST" action="{{ route('admin.taxonomy.destroy') }}" class="mt-5">
            @csrf
            @method('DELETE')
            <input type="hidden" name="category_id" id="taxonomyDeleteCategoryId" value="">
            <input type="hidden" name="brand_id" id="taxonomyDeleteBrandId" value="">
            <input type="hidden" name="q" value="{{ $searchKeyword }}">
            <input type="hidden" name="sort" value="{{ $taxonomySort }}">
            <input type="hidden" name="dir" value="{{ $taxonomyDir }}">
            <input type="hidden" name="page" value="{{ $taxonomyPagination['current_page'] }}">
            <div class="flex justify-end gap-2">
              <button type="button" id="closeTaxonomyDeleteBtn" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42]">Batal</button>
              <button type="submit" class="rounded-lg bg-[#ba1a1a] px-4 py-2 text-white">Ya, Hapus</button>
            </div>
          </form>
        </div>
      </div>
    @endif

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
      const mobileSidebarBtn = document.getElementById('mobileSidebarBtn');
      const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');
      sidebarToggleBtn?.addEventListener('click', () => {
        wrap?.classList.toggle('sf-sidebar-collapsed');
      });
      mobileSidebarBtn?.addEventListener('click', () => {
        document.body.classList.toggle('sf-mobile-menu-open');
      });
      mobileSidebarBackdrop?.addEventListener('click', () => {
        document.body.classList.remove('sf-mobile-menu-open');
      });
      document.querySelectorAll('.sf-sidebar a').forEach((link) => {
        link.addEventListener('click', () => {
          if (window.innerWidth < 1024) {
            document.body.classList.remove('sf-mobile-menu-open');
          }
        });
      });
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
          document.body.classList.remove('sf-mobile-menu-open');
        }
      });

      @if ($type === 'credits')
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
      @endif

      @if ($type === 'taxonomy')
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
        taxonomyForm.action = '{{ route('admin.taxonomy.store') }}';
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
          taxonomyForm.action = '{{ route('admin.taxonomy.update') }}';
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

      const productGroupsExportBtn = document.getElementById('productGroupsExportBtn');
      productGroupsExportBtn?.addEventListener('click', async (event) => {
        event.preventDefault();
        const exportUrl = productGroupsExportBtn.getAttribute('data-export-url');
        const filename = productGroupsExportBtn.getAttribute('data-filename') || 'kelompok-barang.xlsx';
        if (!exportUrl) return;

        const originalText = productGroupsExportBtn.textContent;
        productGroupsExportBtn.textContent = 'Menyiapkan Excel...';

        try {
          const response = await fetch(exportUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            },
          });

          if (!response.ok) {
            throw new Error(`Export gagal (${response.status})`);
          }

          const blob = await response.blob();
          const blobUrl = window.URL.createObjectURL(blob);
          const anchor = document.createElement('a');
          anchor.href = blobUrl;
          anchor.download = filename;
          document.body.appendChild(anchor);
          anchor.click();
          anchor.remove();
          window.URL.revokeObjectURL(blobUrl);
        } catch (error) {
          console.error(error);
          alert('Export Excel gagal. Silakan coba lagi.');
        } finally {
          productGroupsExportBtn.textContent = originalText;
        }
      });

      const productGroupsExportCsvBtn = document.getElementById('productGroupsExportCsvBtn');
      productGroupsExportCsvBtn?.addEventListener('click', async (event) => {
        event.preventDefault();
        const exportUrl = productGroupsExportCsvBtn.getAttribute('data-export-url');
        const filename = productGroupsExportCsvBtn.getAttribute('data-filename') || 'kelompok-barang.csv';
        if (!exportUrl) return;

        const originalText = productGroupsExportCsvBtn.textContent;
        productGroupsExportCsvBtn.textContent = 'Menyiapkan CSV...';

        try {
          const response = await fetch(exportUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'text/csv',
            },
          });

          if (!response.ok) {
            throw new Error(`Export gagal (${response.status})`);
          }

          const blob = await response.blob();
          const blobUrl = window.URL.createObjectURL(blob);
          const anchor = document.createElement('a');
          anchor.href = blobUrl;
          anchor.download = filename;
          document.body.appendChild(anchor);
          anchor.click();
          anchor.remove();
          window.URL.revokeObjectURL(blobUrl);
        } catch (error) {
          console.error(error);
          alert('Export CSV gagal. Silakan coba lagi.');
        } finally {
          productGroupsExportCsvBtn.textContent = originalText;
        }
      });
      @endif
    </script>

    <script src="{{ asset('js/admin-module-credit-print.js') }}?v={{ filemtime(public_path('js/admin-module-credit-print.js')) }}"></script>

    @include('filament.partials.logout-modal')
</x-filament-panels::page>

@php
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
    $currentUser = auth()->user();
    $isAdminBesarAccess = $currentUser?->isAdminBesar() ?? false;
@endphp

<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/app-production.css') }}">
    @if (app()->environment('local'))
        @vite('resources/css/app.css')
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <style>
      :root {
        --sf-topbar-h: 52px;
        --sf-sidebar-w: 208px;
      }
      .sf-wrap {
        font-family: 'Hanken Grotesk', sans-serif;
        width: 100vw;
        max-width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        font-size: 13px;
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
      .sf-sidebar nav {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
      }
      .sf-content {
        min-width: 0;
        width: calc(100% - var(--sf-sidebar-w));
        margin-left: var(--sf-sidebar-w);
        height: calc(100vh - var(--sf-topbar-h));
        overflow-y: auto;
      }
      .sf-nav-item { font-size: 13px; }
      .sf-nav-item .material-symbols-outlined { font-size: 16px; }
      .sf-title { font-size: 26px !important; line-height: 32px !important; }
      .sf-value { font-size: 26px !important; line-height: 32px !important; letter-spacing: -0.02em; }
      .sf-wrap .text-headline-lg { font-size: 24px !important; line-height: 30px !important; }
      .sf-wrap .text-headline-md { font-size: 18px !important; line-height: 24px !important; }
      .sf-wrap .text-body-md { font-size: 13px !important; line-height: 20px !important; }
      .sf-wrap .text-label-md { font-size: 13px !important; line-height: 18px !important; }
      .sf-wrap .custom-shadow { box-shadow: 0 1px 3px rgba(0,0,0,.03); }
      .sf-wrap .rounded-xl { border-radius: 9px !important; }
      .sf-wrap .rounded-2xl { border-radius: 11px !important; }
      .sf-wrap .p-6 { padding: .9rem !important; }
      .sf-wrap .p-5 { padding: .85rem !important; }
      .sf-wrap .p-4 { padding: .7rem !important; }
      .sf-wrap .px-6 { padding-left: .9rem !important; padding-right: .9rem !important; }
      .sf-wrap .py-4 { padding-top: .62rem !important; padding-bottom: .62rem !important; }
      .sf-wrap .py-3 { padding-top: .58rem !important; padding-bottom: .58rem !important; }
      .sf-wrap .h-16 { height: var(--sf-topbar-h) !important; }
      .sf-wrap input,
      .sf-wrap select,
      .sf-wrap textarea { min-height: 36px; }
      .sf-wrap table th,
      .sf-wrap table td { padding-top: .65rem !important; padding-bottom: .65rem !important; }
      .sf-wrap .text-\[34px\] { font-size: 26px !important; line-height: 32px !important; }
      .sf-wrap .text-\[40px\] { font-size: 30px !important; line-height: 36px !important; }
      .sf-modal-panel { max-height: calc(100dvh - 5rem); overflow: hidden; display: flex; flex-direction: column; }
      .sf-modal-form { overflow-y: auto; -webkit-overflow-scrolling: touch; }
      .sf-modal-form::-webkit-scrollbar { width: 8px; }
      .sf-modal-form::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 10px; }
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
        .sf-wrap .sf-title { font-size: 22px !important; line-height: 28px !important; }
        .sf-wrap .sf-value { font-size: 24px !important; line-height: 30px !important; }
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
        .sf-wrap .sf-modal-panel .px-6 { padding-left: .9rem !important; padding-right: .9rem !important; }
        .sf-wrap .sf-modal-panel .py-5 { padding-top: .8rem !important; padding-bottom: .8rem !important; }
        .sf-wrap .sf-modal-panel .text-headline-md { font-size: 16px !important; line-height: 20px !important; }
        .sf-wrap .sf-export { margin-left: 0 !important; align-items: flex-start !important; }
        .sf-wrap .sf-modal-panel { max-height: calc(100dvh - 4rem); border-radius: .9rem; }
        .sf-wrap .sf-modal-form { padding-left: .9rem; padding-right: .9rem; padding-bottom: .9rem; }
        .sf-wrap .sf-modal-form { gap: .75rem; }
      }
      @media (max-width: 1023px) {
        html.sf-dashboard-page,
        .sf-dashboard-page,
        .sf-dashboard-page body,
        .sf-dashboard-page .fi-body,
        .sf-dashboard-page .fi-layout,
        .sf-dashboard-page .fi-main,
        .sf-dashboard-page .fi-main-ctn {
          height: auto !important;
          min-height: 100% !important;
          overflow-x: hidden !important;
          overflow-y: auto !important;
        }
        .sf-wrap {
          min-height: 100vh;
        }
        .sf-sidebar {
          width: min(84vw, 300px);
          transform: translateX(-102%);
          opacity: 0;
          pointer-events: none;
          z-index: 30;
        }
        .sf-content {
          width: 100%;
          margin-left: 0;
          height: auto;
          overflow: visible;
        }
        .sf-wrap header {
          padding-left: 1rem !important;
          padding-right: 1rem !important;
          gap: .75rem;
        }
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
        .sf-wrap #productTable th,
        .sf-wrap #productTable td {
          padding: 10px 12px !important;
          font-size: 13px !important;
        }
        .sf-wrap #productTable {
          min-width: 1080px;
          width: max-content;
        }
        .sf-wrap .sf-header-actions > div {
          width: 32px !important;
          height: 32px !important;
        }
        .sf-wrap .sf-header-actions .material-symbols-outlined {
          font-size: 20px;
        }
      }
      </style>

    <div class="sf-wrap bg-background text-on-surface antialiased min-h-screen overflow-x-hidden">
      <header class="bg-surface-container-lowest text-primary border-b border-outline-variant shadow-sm flex justify-between items-center px-5 h-16 w-full sticky top-0 z-50">
        <div class="flex items-center gap-3 md:gap-4">
          <button id="mobileSidebarBtn" type="button" class="lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface hover:bg-surface-container" aria-label="Buka navigasi">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <span class="font-display text-[18px] font-bold text-primary leading-none">Surya Duta Multindo</span>
        </div>
        <div></div>
      </header>

      <div class="sf-shell">
        <div class="sf-layout">
          <div id="mobileSidebarBackdrop" class="sf-mobile-sidebar-backdrop lg:hidden"></div>
          <aside class="sf-sidebar flex flex-col w-full p-4 bg-surface">
            <div class="mb-3 rounded-lg border border-outline-variant bg-surface-container-low p-3">
              <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-lg bg-primary text-on-primary flex items-center justify-center">
                  <span class="material-symbols-outlined text-[14px]">inventory</span>
                </div>
                <div>
                  <p class="text-[13px] font-semibold text-primary leading-tight">{{ $isAdminBesarAccess ? 'Admin Besar Panel' : 'Admin Panel' }}</p>
                  <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">{{ $isAdminBesarAccess ? 'Gudang Access Mode' : 'Management Mode' }}</p>
                </div>
              </div>
            </div>
            <nav class="flex-1 flex flex-col space-y-1 overflow-y-auto pr-1">
              @if($isAdminBesarAccess)
                <a class="sf-nav-item flex items-center gap-2.5 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium" href="{{ route('admin.admin-besar.index') }}">
                  <span class="material-symbols-outlined">arrow_back</span>
                  <span>Kembali ke Dashboard Admin Besar</span>
                </a>
              @endif
              <a class="sf-nav-item flex items-center gap-2.5 bg-primary text-on-primary rounded-lg px-3 py-2 font-medium" href="{{ url('/admin/products') }}">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">inventory_2</span>
                <span>Barang</span>
              </a>
              <a class="sf-nav-item w-full flex items-center gap-2.5 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="{{ url('/admin/suppliers') }}"><span class="material-symbols-outlined">local_shipping</span><span>Supplier</span></a>
              <a class="sf-nav-item w-full flex items-center gap-2.5 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="{{ url('/admin/admin-module?type=credits') }}"><span class="material-symbols-outlined">credit_card</span><span>Kredit &amp; Utang Saya</span></a>
              <a class="sf-nav-item w-full flex items-center gap-2.5 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="{{ url('/admin/admin-module?type=supplier-transactions') }}"><span class="material-symbols-outlined">account_tree</span><span>Transaksi PT</span></a>
              <div class="space-y-1 pt-1">
                <a class="sf-nav-item w-full flex items-center gap-2.5 text-on-surface-variant px-3 py-2 hover:bg-surface-container-high transition-all rounded-lg font-medium text-left" href="{{ url('/admin/admin-module?type=product-groups') }}"><span class="material-symbols-outlined">inventory_2</span><span>Kelompok Barang</span></a>
                <div class="ml-3 border-l border-outline-variant pl-3 py-1 space-y-1">
                  <a class="sf-nav-item w-full flex items-center gap-2 text-sm px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.transaksi.dashboard') ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-high' }}" href="{{ route('admin.transaksi.dashboard') }}">
                    <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                    <span>Daftar Barang Jual</span>
                  </a>
                  <a class="sf-nav-item w-full flex items-center gap-2 text-sm px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.transactions.drafts') ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-high' }}" href="{{ route('admin.transactions.drafts') }}">
                    <span class="material-symbols-outlined text-[18px]">draft</span>
                    <span>Draft Tertunda</span>
                  </a>
                  <a class="sf-nav-item w-full flex items-center gap-2 text-sm px-3 py-2 rounded-lg font-medium {{ request()->routeIs('admin.transactions.history*') ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-high' }}" href="{{ route('admin.transactions.history') }}">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    <span>History &amp; Nota</span>
                  </a>
                </div>
              </div>
            </nav>
            <div class="pt-3 border-t border-outline-variant">
              <button type="button" onclick="confirmLogout()" class="sf-nav-item w-full flex items-center gap-2.5 text-error px-3 py-2 hover:bg-error-container/20 transition-all rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
              </button>
            </div>
          </aside>

          <main class="sf-content min-h-screen p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-3 md:gap-4 mb-4 md:mb-5">
              <div>
                <h1 class="sf-title font-display text-headline-lg text-on-surface mb-1">Daftar Barang</h1>
                <p class="text-on-surface-variant text-[13px] leading-5">Kelola seluruh stok inventaris Anda di satu tempat.</p>
                @if($isAdminBesarAccess)
                  <a href="{{ route('admin.admin-besar.index') }}" class="mt-2 inline-flex items-center gap-2 rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[12px] font-semibold text-primary hover:bg-surface-container-high">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke Dashboard Admin Besar
                  </a>
                @endif
              </div>
              <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <button type="button" wire:click="createOfflineBackup" wire:loading.attr="disabled" class="w-full md:w-auto bg-surface text-primary border border-outline-variant px-4 md:px-5 py-2.5 rounded-xl text-[13px] font-medium flex items-center justify-center gap-2 hover:bg-surface-container-high transition-all active:scale-95">
                  <span wire:loading.remove wire:target="createOfflineBackup" class="material-symbols-outlined">folder_zip</span>
                  <span wire:loading wire:target="createOfflineBackup" class="material-symbols-outlined animate-spin">sync</span>
                  <span wire:loading.remove wire:target="createOfflineBackup">Backup Excel</span>
                  <span wire:loading wire:target="createOfflineBackup">Membuat Backup...</span>
                </button>
                <button type="button" onclick="openCreateModal()" class="w-full md:w-auto bg-primary text-on-primary px-4 md:px-6 py-2.5 rounded-xl text-[13px] font-medium flex items-center justify-center gap-2 hover:brightness-90 transition-all active:scale-95">
                  <span class="material-symbols-outlined">add</span>Tambah Barang
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 md:gap-5 mb-5">
              @foreach ($stats as $index => $stat)
                @php
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
                @endphp
                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant custom-shadow {{ $isLowStockCard ? 'cursor-pointer hover:shadow-md transition-shadow' : '' }}" @if($isLowStockCard) onclick="openLowStockModal()" @endif>
                  <div class="flex items-center gap-2.5 mb-2">
                    <div class="p-1 rounded-lg {{ explode(' ', $variantClass)[1] }}">
                      <span class="material-symbols-outlined {{ explode(' ', $variantClass)[0] }}">{{ $stat['icon'] ?? 'inventory_2' }}</span>
                    </div>
                    <span class="text-on-surface-variant text-[13px] font-medium">{{ $stat['label'] ?? '-' }}</span>
                  </div>
                  <div class="sf-value {{ explode(' ', $variantClass)[0] }} font-bold leading-tight">{{ $stat['value'] ?? '0' }}</div>
                  <p class="text-[12px] mt-1 font-medium {{ explode(' ', $variantClass)[0] }}">{{ $stat['description'] ?? '-' }}</p>
                </div>
              @endforeach
            </div>

            <div class="sf-toolbar bg-surface-container-lowest p-3 md:p-3.5 rounded-t-xl border-x border-t border-outline-variant flex flex-wrap items-center gap-3 md:gap-4">
              <div class="flex flex-col gap-1 min-w-0 flex-1">
                <label class="text-[10px] font-bold text-on-surface-variant uppercase ml-1">Search</label>
                <input id="globalSearchInput" type="text" value="{{ $viewData['search'] ?? '' }}" placeholder="Cari part number, nama barang, kategori, merek, barcode..." class="bg-surface border border-outline-variant rounded-lg px-3 py-2 text-[13px] focus:ring-primary focus:border-primary" autocomplete="off" spellcheck="false">
              </div>
              <div class="sf-export flex flex-col gap-1 ml-auto">
                <label class="text-[10px] font-bold text-on-surface-variant uppercase text-right mr-1">Export</label>
                <div class="flex gap-2">
                  <button type="button" class="p-2 bg-surface border border-outline-variant rounded-lg hover:bg-surface-container-high transition-colors" onclick="exportToCSV()"><span class="material-symbols-outlined text-on-surface-variant">download</span></button>
                  <button type="button" class="p-2 bg-surface border border-outline-variant rounded-lg hover:bg-surface-container-high transition-colors" onclick="window.print()"><span class="material-symbols-outlined text-on-surface-variant">print</span></button>
                </div>
              </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-b-xl overflow-hidden custom-shadow">
              <div class="overflow-x-auto table-container">
                <table class="min-w-[1500px] w-full text-left border-collapse" id="productTable">
                  <thead>
                    <tr class="bg-surface-container text-on-surface-variant border-b border-outline-variant">
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Part Number</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Kondisi</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Waktu Input</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Kategori</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Merek</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Berat</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Unit</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Stok</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Harga Beli</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Harga Jual</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Biaya Ekspedisi</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">Supplier</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider">No Inv Supplier</th>
                      <th class="px-5 py-3.5 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-outline-variant" id="productTableBody">
                    @forelse ($products as $product)
                      @php
                        $stock = (int) ($product['stock'] ?? 0);
                        $low = $stock <= 5;
                        $partNumber = trim((string) ($product['part_number'] ?? $product['barcode'] ?? $product['sku'] ?? '')) ?: '-';
                        $partName = trim((string) ($product['part_name'] ?? $product['name'] ?? '')) ?: '-';
                        $unit = trim((string) ($product['unit'] ?? '')) ?: '-';
                        $stockUnit = $unit !== '-' ? $unit : 'Unit';
                        $weight = $product['weight'] ?? null;
                        $weightUnit = trim((string) ($product['weight_unit'] ?? 'kg')) ?: 'kg';
                        $weightDisplay = ($weight !== null && $weight !== '')
                            ? rtrim(rtrim(number_format((float) $weight, 2, ',', '.'), '0'), ',') . ' ' . $weightUnit
                            : '-';
                        $condition = trim((string) ($product['condition'] ?? ''));
                        $expeditionCost = $product['expedition_cost_value'] ?? $product['expedition_cost'] ?? $product['shipping_cost'] ?? null;
                        $expeditionCostDisplay = ($expeditionCost !== null && $expeditionCost !== '')
                            ? 'Rp ' . number_format((float) $expeditionCost, 0, ',', '.')
                            : 'Rp 0';
                      @endphp
                      <tr class="hover:bg-surface-container-low transition-colors group" data-product-id="{{ (int) ($product['id'] ?? 0) }}">
                        <td class="px-5 py-3.5">
                          <div class="flex items-center gap-2.5">
                            <div class="h-11 w-11 rounded-lg bg-surface flex-shrink-0 border border-outline-variant overflow-hidden flex items-center justify-center">
                              @if (!empty($product['image_url']))
                                <button type="button" class="h-full w-full" onclick='openImagePreview(@json($product["image_url"]), @json($product["name"] ?? "Foto barang"))'>
                                  <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] ?? 'Foto barang' }}" class="h-full w-full object-cover">
                                </button>
                              @else
                                <span class="material-symbols-outlined text-primary">inventory_2</span>
                              @endif
                            </div>
                            <div>
                              @if (!empty($product['supplier_id']))
                                <a href="{{ url('/admin/suppliers/' . $product['supplier_id']) }}#riwayat-pembelian" class="inline-flex max-w-full cursor-pointer flex-col text-left" title="Lihat detail supplier">
                                  <p class="sf-part-number transition-colors hover:text-primary">{{ $partNumber }}</p>
                                  <p class="sf-product-name">{{ $partName }}</p>
                                </a>
                              @else
                                <p class="sf-part-number">{{ $partNumber }}</p>
                                <p class="sf-product-name">{{ $partName }}</p>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $condition !== '' ? $condition : '-' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['created_at'] ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['category'] ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['brand'] ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $weightDisplay }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $unit }}</td>
                        <td class="px-5 py-3.5">
                          <span class="px-2.5 py-1 rounded-full {{ $low ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }} text-[11px] font-semibold inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $low ? 'bg-red-500' : 'bg-emerald-500' }}"></span>{{ $stock }} {{ $stockUnit }}
                          </span>
                        </td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['purchase_price'] ?? 'Rp 0' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['selling_price'] ?? 'Rp 0' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $expeditionCostDisplay }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['supplier_name'] ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[13px]">{{ $product['supplier_invoice_number'] ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-right">
                          <div class="flex justify-end gap-2">
                            <button type="button" class="p-1 hover:bg-surface-container rounded-lg text-primary transition-colors" onclick='openEditModal(@json($product))'><span class="material-symbols-outlined">edit</span></button>
                            <button type="button" class="p-1 hover:bg-error-container/20 rounded-lg text-error transition-colors" onclick='deleteProduct({{ (int) ($product['id'] ?? 0) }}, @json($product['name'] ?? "-"))'><span class="material-symbols-outlined">delete</span></button>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr id="emptyProductRow">
                        <td colspan="14" class="px-6 py-10 text-center text-on-surface-variant">Belum ada barang.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <div class="px-5 py-3.5 border-t border-outline-variant flex items-center justify-between bg-surface-container-lowest">
                <p class="text-label-sm text-on-surface-variant" id="productCountText">Menampilkan {{ $pagination['from'] ?? 0 }}-{{ $pagination['to'] ?? 0 }} dari {{ $pagination['total'] ?? count($products) }} barang</p>
                <div class="flex items-center gap-1">
                  <button type="button" class="p-1 rounded hover:bg-surface-container transition-colors disabled:opacity-30" {{ empty($pagination['has_prev']) ? 'disabled' : '' }} onclick="goToPage({{ (int) ($pagination['prev_page'] ?? 1) }})"><span class="material-symbols-outlined">chevron_left</span></button>
                  @php
                    $currentPage = (int) ($pagination['current_page'] ?? 1);
                    $lastPage = (int) ($pagination['last_page'] ?? 1);
                    $startPage = max($currentPage - 1, 1);
                    $endPage = min($startPage + 2, $lastPage);
                    $startPage = max($endPage - 2, 1);
                  @endphp
                  @for ($page = $startPage; $page <= $endPage; $page++)
                    <button type="button" class="w-8 h-8 rounded text-label-sm flex items-center justify-center transition-colors {{ $page === $currentPage ? 'bg-primary text-on-primary' : 'hover:bg-surface-container' }}" onclick="goToPage({{ $page }})">{{ $page }}</button>
                  @endfor
                  @if ($endPage < $lastPage)
                    <span class="px-1 text-on-surface-variant">...</span>
                    <button type="button" class="w-8 h-8 rounded hover:bg-surface-container text-label-sm flex items-center justify-center transition-colors" onclick="goToPage({{ $lastPage }})">{{ $lastPage }}</button>
                  @endif
                  <button type="button" class="p-1 rounded hover:bg-surface-container transition-colors disabled:opacity-30" {{ empty($pagination['has_next']) ? 'disabled' : '' }} onclick="goToPage({{ (int) ($pagination['next_page'] ?? $currentPage) }})"><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>

    <div id="dashboardToast" class="fixed right-6 top-6 z-50 hidden max-w-sm rounded-xl border bg-surface-container-lowest px-4 py-3 text-sm shadow-lg"></div>
    <div id="dashboardLoadingModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/45 px-4">
      <div class="w-full max-w-sm rounded-2xl border border-outline-variant bg-surface-container-lowest px-5 py-4 text-center shadow-2xl">
        <div class="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-4 border-outline-variant border-t-primary"></div>
        <p id="dashboardLoadingText" class="text-sm font-medium text-on-surface">Memproses data...</p>
      </div>
    </div>
    <div id="dashboardConfirmModal" class="fixed inset-0 z-[61] hidden items-center justify-center bg-slate-950/45 px-4">
      <div class="w-full max-w-md rounded-2xl border border-outline-variant bg-surface-container-lowest p-5 shadow-2xl">
        <h3 id="dashboardConfirmTitle" class="text-headline-md text-on-surface">Konfirmasi</h3>
        <p id="dashboardConfirmMessage" class="mt-2 text-sm text-on-surface-variant">Apakah Anda yakin?</p>
        <div class="mt-5 flex justify-end gap-3">
          <button type="button" class="rounded-xl border border-outline-variant px-4 py-2 text-sm font-medium text-on-surface hover:bg-surface-container" onclick="closeConfirmModal()">Batal</button>
          <button id="dashboardConfirmActionButton" type="button" class="rounded-xl bg-error px-4 py-2 text-sm font-medium text-on-error hover:brightness-95">Lanjut</button>
        </div>
      </div>
    </div>
    <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
      @csrf
    </form>
    <div id="lowStockModal" class="fixed inset-0 z-40 hidden items-start justify-center overflow-y-auto bg-slate-950/45 px-3 py-12 md:px-4 md:py-16">
      <div class="sf-modal-panel w-full max-w-xl rounded-[1.1rem] border border-outline-variant bg-surface-container-lowest shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-outline-variant px-4 py-3">
          <div>
            <h2 class="text-[18px] font-semibold leading-tight text-on-surface">Daftar Stok Menipis</h2>
            <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Barang yang perlu restock segera (stok <= 10).</p>
          </div>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeLowStockModal()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="sf-modal-form px-4 py-4">
          <div id="lowStockList" class="space-y-3"></div>
        </div>
      </div>
    </div>
    <div id="imagePreviewModal" class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/75 px-4 py-12 md:py-16">
      <div class="w-full max-w-xl rounded-2xl bg-white p-3.5 shadow-2xl">
        <div class="mb-2.5 flex items-center justify-between gap-4">
          <h3 id="imagePreviewTitle" class="text-[15px] font-semibold text-on-surface">Preview Foto</h3>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeImagePreview()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="flex items-center justify-center rounded-xl bg-surface-container-low p-2">
          <img id="imagePreviewTarget" src="" alt="Preview foto barang" class="max-h-[70vh] w-auto rounded-lg object-contain">
        </div>
      </div>
    </div>

    <div id="createProductModal" class="fixed inset-0 z-40 hidden items-start justify-center overflow-y-auto bg-slate-950/45 px-3 py-12 md:px-4 md:py-16">
      <div class="sf-modal-panel mt-0 w-full max-w-xl rounded-[1.1rem] border border-outline-variant bg-surface-container-lowest shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-outline-variant px-4 py-4">
          <div>
            <h2 class="text-[18px] font-semibold leading-tight text-on-surface">Tambah Barang</h2>
            <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Simpan barang baru langsung dari dashboard.</p>
          </div>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeCreateModal()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form id="createProductForm" class="sf-modal-form space-y-3.5 px-4 py-4">
          <input type="hidden" name="supplier_id">
          <div id="createFormAlert" class="hidden rounded-xl border border-error bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
          @include('filament.pages.partials.product-info-fields', ['isActiveDefault' => true])
          @include('filament.pages.partials.supplier-info-fields', ['supplierTypeOptions' => $supplierTypeOptions])
          @include('filament.pages.partials.batch-info-fields', ['batchCodePlaceholder' => 'Opsional, otomatis jika kosong'])
          <div class="flex justify-end gap-3 border-t border-outline-variant pt-3">
            <button type="button" class="rounded-xl border border-outline-variant px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container" onclick="closeCreateModal()">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-on-primary hover:brightness-90">Simpan Barang</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editProductModal" class="fixed inset-0 z-40 hidden items-start justify-center overflow-y-auto bg-slate-950/45 px-3 py-12 md:px-4 md:py-16">
      <div class="sf-modal-panel mt-0 w-full max-w-xl rounded-[1.1rem] border border-outline-variant bg-surface-container-lowest shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-outline-variant px-4 py-4">
          <div>
            <h2 class="text-[18px] font-semibold leading-tight text-on-surface">Edit Barang</h2>
            <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Perbarui data barang tanpa keluar dari dashboard.</p>
          </div>
          <button type="button" class="rounded-lg p-2 hover:bg-surface-container" onclick="closeEditModal()">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form id="editProductForm" class="sf-modal-form space-y-3.5 px-4 py-4">
          <input type="hidden" name="product_id">
          <input type="hidden" name="supplier_id">
          <div id="editFormAlert" class="hidden rounded-xl border border-error bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
          @include('filament.pages.partials.product-info-fields', ['isActiveDefault' => false])
          @include('filament.pages.partials.supplier-info-fields', ['supplierTypeOptions' => $supplierTypeOptions])
          @include('filament.pages.partials.batch-info-fields', ['batchCodePlaceholder' => ''])
          <div class="flex justify-end gap-3 border-t border-outline-variant pt-3">
            <button type="button" class="rounded-xl border border-outline-variant px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container" onclick="closeEditModal()">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-on-primary hover:brightness-90">Update Barang</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');
      let searchDebounceTimer;
      let searchLoadingTimer;
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
      const pageMeta = { currentPage: {{ (int) ($pagination['current_page'] ?? 1) }}, perPage: {{ (int) ($pagination['per_page'] ?? (count($products) ?: 10)) }}, total: {{ (int) ($pagination['total'] ?? count($products)) }} };
      const productSuggestionUrl = {{ Illuminate\Support\Js::from(route('admin.dashboard.products.suggestions')) }};
      const storeUrl = {{ Illuminate\Support\Js::from(route('admin.dashboard.products.store')) }};
      const updateUrlBase = {{ Illuminate\Support\Js::from(url('/admin/dashboard/products')) }};
      const csrfToken = {{ Illuminate\Support\Js::from(csrf_token()) }};
      const productsForNotification = {{ Illuminate\Support\Js::from($products) }};
      const lowStockProducts = {{ Illuminate\Support\Js::from($lowStockProducts) }};
      const categoryOptionSeeds = {{ Illuminate\Support\Js::from($categoryOptions) }};
      const brandOptionSeeds = {{ Illuminate\Support\Js::from($brandOptions) }};
      const supplierOptionSeeds = {{ Illuminate\Support\Js::from($supplierOptions) }};
      const formHistoryStorageKey = 'admin-dashboard-product-form-history-v1';
      const formHistoryLimit = 300;
      let partNumberSuggestionTimer;
      let partNumberSuggestionAbortController = null;
      const globalSearchInput = document.getElementById('globalSearchInput');
      globalSearchInput?.addEventListener('input', () => {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        if (searchLoadingTimer) clearTimeout(searchLoadingTimer);

        const query = (globalSearchInput.value || '').trim();
        if (!query) {
          closeLoading();
          searchDebounceTimer = setTimeout(() => {
            applyFilters();
          }, 250);
          return;
        }

        searchLoadingTimer = setTimeout(() => {
          openLoading(`Mencari keyword "${query}"...`);
        }, 250);

        searchDebounceTimer = setTimeout(() => {
          openLoading(`Mencari keyword "${query}"...`);
          setTimeout(() => {
            applyFilters();
          }, 80);
        }, 700);
      });

      function applyFilters() {
        const searchInput = document.getElementById('globalSearchInput')?.value || '';
        const params = new URLSearchParams();
        if (searchInput) params.append('search', searchInput);
        window.location.search = params.toString();
      }
      function resetAllFilters() { window.location.search = ''; }
      function focusBrandFilter() { const target = document.getElementById('globalSearchInput'); if (!target) return; target.scrollIntoView({ behavior: 'smooth', block: 'center' }); target.focus(); }
      function focusCategoryFilter() { const target = document.getElementById('globalSearchInput'); if (!target) return; target.scrollIntoView({ behavior: 'smooth', block: 'center' }); target.focus(); }
      const mobileSidebarBtn = document.getElementById('mobileSidebarBtn');
      const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');

      function closeMobileSidebar() {
        document.body.classList.remove('sf-mobile-menu-open');
      }

      function openMobileSidebar() {
        document.body.classList.add('sf-mobile-menu-open');
      }

      function toggleMobileSidebar() {
        if (document.body.classList.contains('sf-mobile-menu-open')) {
          closeMobileSidebar();
        } else {
          openMobileSidebar();
        }
      }

      mobileSidebarBtn?.addEventListener('click', toggleMobileSidebar);
      mobileSidebarBackdrop?.addEventListener('click', closeMobileSidebar);
      document.querySelectorAll('.sf-sidebar a').forEach((link) => {
        link.addEventListener('click', () => {
          if (window.innerWidth < 1024) closeMobileSidebar();
        });
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
                <p class="text-xs text-on-surface-variant mt-1">INV: ${escapeHtml(item.batch_code || '-')} | Supplier: ${escapeHtml(item.supplier_name || '-')}</p>
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
            @this.call('deleteProduct', id);
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
      function normalizeHistoryValue(value) {
        return String(value ?? '').replace(/\s+/g, ' ').trim();
      }
      function readFormHistory() {
        try {
          const raw = window.localStorage.getItem(formHistoryStorageKey);
          const parsed = raw ? JSON.parse(raw) : {};
          return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
          return {};
        }
      }
      function writeFormHistory(historyState) {
        try {
          window.localStorage.setItem(formHistoryStorageKey, JSON.stringify(historyState));
        } catch (error) {
          // Abaikan bila storage penuh atau browser memblokir localStorage.
        }
      }
      function historyValuesFromOptions(options, candidateKeys = []) {
        if (!Array.isArray(options)) return [];
        return options
          .map((option) => {
            if (typeof option === 'string' || typeof option === 'number') {
              return normalizeHistoryValue(option);
            }
            if (!option || typeof option !== 'object') {
              return '';
            }
            for (const key of candidateKeys) {
              const candidate = normalizeHistoryValue(option[key]);
              if (candidate !== '') return candidate;
            }
            return '';
          })
          .filter(Boolean);
      }
      function buildSeedHistory() {
        const productSeeds = Array.isArray(productsForNotification) ? productsForNotification : [];
        return {
          product_name: productSeeds.map((item) => item?.name),
          barcode: productSeeds.map((item) => item?.barcode || item?.sku),
          unit: productSeeds.map((item) => item?.unit),
          weight: productSeeds.map((item) => item?.weight),
          weight_unit_custom: productSeeds.map((item) => item?.weight_unit),
          slug: productSeeds.map((item) => item?.slug),
          category: [
            ...historyValuesFromOptions(categoryOptionSeeds, ['name', 'label', 'value']),
            ...productSeeds.map((item) => item?.category),
          ],
          brand: [
            ...historyValuesFromOptions(brandOptionSeeds, ['name', 'label', 'value']),
            ...productSeeds.map((item) => item?.brand),
          ],
          supplier_name: [
            ...historyValuesFromOptions(supplierOptionSeeds, ['name', 'label', 'value']),
            ...productSeeds.map((item) => item?.supplier_name),
          ],
          supplier_phone: productSeeds.map((item) => item?.supplier_phone),
          supplier_address: productSeeds.map((item) => item?.supplier_address),
          batch_code: productSeeds.map((item) => item?.batch_code),
          supplier_invoice_number: productSeeds.map((item) => item?.supplier_invoice_number),
          purchase_date: productSeeds.map((item) => item?.purchase_date),
          condition: productSeeds.map((item) => item?.condition),
          processed_by: productSeeds.map((item) => item?.processed_by),
          purchase_price: productSeeds.map((item) => item?.purchase_price_value),
          expedition_cost: productSeeds.map((item) => item?.expedition_cost_value),
          selling_price: productSeeds.map((item) => item?.selling_price_value),
          stock: productSeeds.map((item) => item?.stock),
        };
      }
      function getMergedFieldHistory(fieldKey) {
        const savedHistory = readFormHistory();
        const seedHistory = buildSeedHistory();
        const merged = [
          ...(Array.isArray(savedHistory[fieldKey]) ? savedHistory[fieldKey] : []),
          ...(Array.isArray(seedHistory[fieldKey]) ? seedHistory[fieldKey] : []),
        ]
          .map((value) => normalizeHistoryValue(value))
          .filter(Boolean);

        return [...new Set(merged)].slice(0, formHistoryLimit);
      }
      function persistFormHistoryEntries(entries) {
        const historyState = readFormHistory();
        Object.entries(entries).forEach(([fieldKey, values]) => {
          const currentValues = Array.isArray(historyState[fieldKey]) ? historyState[fieldKey] : [];
          const nextValues = [
            ...values.map((value) => normalizeHistoryValue(value)).filter(Boolean),
            ...currentValues.map((value) => normalizeHistoryValue(value)).filter(Boolean),
          ];
          historyState[fieldKey] = [...new Set(nextValues)].slice(0, formHistoryLimit);
        });
        writeFormHistory(historyState);
      }
      function attachHistoryDatalist(input, formId, fieldKey) {
        if (!input || !fieldKey) return;
        const datalistId = `${formId}-${fieldKey}-history`;
        let datalist = document.getElementById(datalistId);
        if (!datalist) {
          datalist = document.createElement('datalist');
          datalist.id = datalistId;
          document.body.appendChild(datalist);
        }
        const values = getMergedFieldHistory(fieldKey);
        datalist.innerHTML = values.map((value) => `<option value="${escapeHtml(value)}"></option>`).join('');
        input.setAttribute('list', datalistId);
      }
      function initializeFormHistoryAutocomplete(form) {
        if (!form?.id) return;
        form.querySelectorAll('input[data-history-key]').forEach((input) => {
          const fieldKey = input.dataset.historyKey;
          attachHistoryDatalist(input, form.id, fieldKey);
        });
      }
      function persistFormHistoryFromForm(form) {
        if (!form) return;
        const entries = {};
        form.querySelectorAll('input[data-history-key]').forEach((input) => {
          const fieldKey = input.dataset.historyKey;
          const value = input.type === 'checkbox' ? '' : input.value;
          if (!fieldKey) return;
          const normalized = normalizeHistoryValue(value);
          if (normalized === '') return;
          entries[fieldKey] = entries[fieldKey] || [];
          entries[fieldKey].push(normalized);
        });
        persistFormHistoryEntries(entries);
      }
      function resetCreateFormForNextEntry() {
        resetFormState(createForm, 'createFormAlert');
        createForm.reset();
        createForm.querySelector('[name="supplier_id"]').value = '';
        const purchaseDateField = createForm.querySelector('[name="purchase_date"]');
        if (purchaseDateField) {
          purchaseDateField.value = new Date().toISOString().slice(0, 10);
        }
        setImagePreview(createForm, null);
        createForm.querySelector('input[type="checkbox"][name="is_active"]').checked = true;
        setWeightUnitFields(createForm, 'kg');
        createForm.querySelector('[name="payment_type"]').value = 'LUNAS';
        syncBatchCreditFields(createForm);
        setCategoryBrandEditable(createForm, true);
        updatePurchaseTotal(createForm);
        initializeFormHistoryAutocomplete(createForm);
        hidePartNumberSuggestions(createForm);
        createForm.scrollTo({ top: 0, behavior: 'smooth' });
        window.requestAnimationFrame(() => {
          createForm.querySelector('[name="barcode"]')?.focus();
        });
      }
      function openCreateModal() {
        createModal.classList.remove('hidden');
        createModal.classList.add('flex');
        resetCreateFormForNextEntry();
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
          closeMobileSidebar();
        }
      });
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
          closeMobileSidebar();
        }
      });
      function setCategoryBrandEditable(form, editable) {
        const categoryInput = form.querySelector('[name="category"]');
        const brandInput = form.querySelector('[name="brand"]');
        [categoryInput, brandInput].forEach((input) => {
          if (!input) return;
          input.disabled = false;
          input.classList.remove('bg-surface-container-low', 'cursor-not-allowed');
          input.removeAttribute('title');
        });
      }

      function syncBatchCreditFields(form) {
        const paymentTypeInput = form.querySelector('[name="payment_type"]');
        const creditDaysWrap = form.querySelector('[data-credit-days-wrap]');
        const creditDueWrap = form.querySelector('[data-credit-due-wrap]');
        const creditDaysInput = form.querySelector('[name="credit_days"]');
        const creditDueInput = form.querySelector('[name="credit_due_date"]');
        const showCredit = (paymentTypeInput?.value || 'LUNAS') === 'KREDIT';
        creditDaysWrap?.classList.toggle('hidden', !showCredit);
        creditDueWrap?.classList.toggle('hidden', !showCredit);
        if (creditDueInput) {
          creditDueInput.readOnly = showCredit;
          creditDueInput.classList.toggle('bg-surface-container-low', showCredit);
        }
        if (!showCredit) {
          if (creditDaysInput) creditDaysInput.value = '';
          if (creditDueInput) creditDueInput.value = '';
          updateCreditDueHuman(form, null);
          updateCreditPaymentSummary(form, null);
          return;
        }
        syncCreditDueDateFromDays(form);
        updateCreditPaymentSummary(form);
      }

      function syncWeightUnitFields(form) {
        const weightUnitSelect = form.querySelector('[name="weight_unit"]');
        const customWrap = form.querySelector('[data-weight-unit-custom-wrap]');
        const customInput = form.querySelector('[name="weight_unit_custom"]');
        if (!weightUnitSelect || !customWrap) return;
        const isCustom = weightUnitSelect.value === 'other';
        customWrap.classList.toggle('hidden', !isCustom);
        if (!isCustom && customInput) {
          customInput.value = '';
        }
      }

      function setWeightUnitFields(form, unit = 'kg', customValue = '') {
        const weightUnitSelect = form.querySelector('[name="weight_unit"]');
        const customInput = form.querySelector('[name="weight_unit_custom"]');
        if (weightUnitSelect) {
          const normalizedUnit = String(unit || 'kg').trim().toLowerCase();
          const knownUnits = ['kg', 'gram', 'ton', 'lb', 'oz'];
          if (knownUnits.includes(normalizedUnit)) {
            weightUnitSelect.value = normalizedUnit;
          } else {
            weightUnitSelect.value = 'other';
            if (customInput) {
              customInput.value = customValue || normalizedUnit || '';
            }
          }
        }
        syncWeightUnitFields(form);
      }

      function getWeightUnitValue(form) {
        const weightUnitSelect = form.querySelector('[name="weight_unit"]');
        const customInput = form.querySelector('[name="weight_unit_custom"]');
        const selected = String(weightUnitSelect?.value || 'kg').trim().toLowerCase();
        if (selected === 'other') {
          return String(customInput?.value || '').trim().toLowerCase();
        }
        return selected || 'kg';
      }

      function formatWeightDisplay(weightValue, weightUnit) {
        const numericWeight = Number(weightValue);
        const unit = String(weightUnit || 'kg').trim().toLowerCase();
        if (!Number.isFinite(numericWeight)) return '-';
        return `${numericWeight.toLocaleString('id-ID', { maximumFractionDigits: 2 })} ${unit}`;
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
        const paymentType = form.querySelector('[name="payment_type"]')?.value || 'LUNAS';
        if (paymentType !== 'KREDIT') {
          return;
        }
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
        setWeightUnitFields(form, product.weight_unit || 'kg');
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
        form.querySelector('[name="supplier_invoice_number"]').value = product.supplier_invoice_number || '';
        const purchaseDateField = form.querySelector('[name="purchase_date"]');
        if (purchaseDateField) purchaseDateField.value = product.purchase_date || '';
        form.querySelector('[name="condition"]').value = product.condition || '';
        const processedByField = form.querySelector('[name="processed_by"]');
        if (processedByField) processedByField.value = product.processed_by || '';
        form.querySelector('[name="purchase_price"]').value = formatRupiahInput(product.purchase_price_value ?? '');
        form.querySelector('[name="expedition_cost"]').value = formatRupiahInput(product.expedition_cost_value ?? '');
        form.querySelector('[name="selling_price"]').value = formatRupiahInput(product.selling_price_value ?? '');
        form.querySelector('[name="stock"]').value = product.stock ?? 0;
        form.querySelector('[name="payment_type"]').value = product.payment_type || 'LUNAS';
        form.querySelector('[name="credit_days"]').value = product.credit_days || '';
        form.querySelector('[name="credit_due_date"]').value = product.credit_due_date || '';
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
        setCategoryBrandEditable(form, true);
        initializeFormHistoryAutocomplete(form);
      }
      function applyAutofillProductToCreateForm(form, product) {
        if (!form || !product) return;
        form.querySelector('[name="barcode"]').value = product.barcode || product.part_number || product.sku || '';
        form.querySelector('[name="name"]').value = product.part_name || product.name || '';
        form.querySelector('[name="unit"]').value = product.unit || '';
        form.querySelector('[name="weight"]').value = product.weight || '';
        setWeightUnitFields(form, product.weight_unit || 'kg');
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
        form.querySelector('[name="condition"]').value = product.condition || '';
        const purchasePriceField = form.querySelector('[name="purchase_price"]');
        if (purchasePriceField) purchasePriceField.value = formatRupiahInput(product.purchase_price_value ?? '');
        const expeditionCostField = form.querySelector('[name="expedition_cost"]');
        if (expeditionCostField) expeditionCostField.value = formatRupiahInput(product.expedition_cost_value ?? '');
        const sellingPriceField = form.querySelector('[name="selling_price"]');
        if (sellingPriceField) sellingPriceField.value = formatRupiahInput(product.selling_price_value ?? '');
        const paymentTypeField = form.querySelector('[name="payment_type"]');
        if (paymentTypeField) paymentTypeField.value = product.payment_type || 'LUNAS';
        const creditDaysField = form.querySelector('[name="credit_days"]');
        if (creditDaysField) creditDaysField.value = product.credit_days || '';
        syncBatchCreditFields(form);
        updatePurchaseTotal(form);
      }
      function getPartNumberAutofillElements(form) {
        if (!form) return {};
        const wrap = form.querySelector('[data-part-number-autofill]');
        return {
          wrap,
          input: form.querySelector('[name="barcode"]'),
          panel: wrap?.querySelector('[data-part-number-suggestions]'),
          helper: form.querySelector('[data-part-number-helper]'),
        };
      }
      function hidePartNumberSuggestions(form) {
        const { panel, helper } = getPartNumberAutofillElements(form);
        if (panel) {
          panel.innerHTML = '';
          panel.classList.add('hidden');
        }
        helper?.classList.add('hidden');
      }
      function renderPartNumberSuggestions(form, items) {
        const { panel, helper } = getPartNumberAutofillElements(form);
        if (!panel) return;
        if (!Array.isArray(items) || items.length === 0) {
          hidePartNumberSuggestions(form);
          return;
        }
        panel.innerHTML = items.map((item) => {
          const serialized = encodeURIComponent(JSON.stringify(item));
          return `
            <button type="button" class="flex w-full items-start justify-between gap-3 border-b border-outline-variant px-3 py-2.5 text-left last:border-b-0 hover:bg-surface-container" data-part-number-select="${serialized}">
              <span class="min-w-0">
                <span class="block truncate text-[13px] font-semibold text-on-surface">${escapeHtml(item.part_number || item.barcode || item.sku || '-')}</span>
                <span class="mt-0.5 block truncate text-[12px] text-on-surface-variant">${escapeHtml(item.part_name || item.name || '-')}</span>
              </span>
              <span class="shrink-0 text-right text-[11px] text-on-surface-variant">
                <span class="block">${escapeHtml(item.brand || '-')}</span>
                <span class="block">${escapeHtml(item.supplier_name || '-')}</span>
              </span>
            </button>
          `;
        }).join('');
        panel.classList.remove('hidden');
        helper?.classList.remove('hidden');
        panel.querySelectorAll('[data-part-number-select]').forEach((button) => {
          button.addEventListener('click', () => {
            try {
              const product = JSON.parse(decodeURIComponent(button.dataset.partNumberSelect || ''));
              applyAutofillProductToCreateForm(form, product);
            } catch (error) {
              // Abaikan bila data rekomendasi rusak.
            }
            hidePartNumberSuggestions(form);
            form.querySelector('[name="name"]')?.focus();
          });
        });
      }
      async function fetchPartNumberSuggestions(form, query) {
        if (partNumberSuggestionAbortController) {
          partNumberSuggestionAbortController.abort();
        }
        partNumberSuggestionAbortController = new AbortController();
        try {
          const url = new URL(productSuggestionUrl, window.location.origin);
          url.searchParams.set('q', query);
          const response = await fetch(url.toString(), {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            signal: partNumberSuggestionAbortController.signal,
          });
          if (!response.ok) {
            hidePartNumberSuggestions(form);
            return;
          }
          const payload = await response.json().catch(() => ({}));
          renderPartNumberSuggestions(form, Array.isArray(payload.items) ? payload.items : []);
        } catch (error) {
          if (error.name !== 'AbortError') {
            hidePartNumberSuggestions(form);
          }
        }
      }
      function initializePartNumberAutofill(form) {
        if (!form || form.dataset.partNumberAutofillBound === '1') return;
        const { input, wrap } = getPartNumberAutofillElements(form);
        if (!input || !wrap) return;
        form.dataset.partNumberAutofillBound = '1';
        input.addEventListener('input', () => {
          input.value = String(input.value || '').toUpperCase();
          const query = input.value.trim();
          if (partNumberSuggestionTimer) clearTimeout(partNumberSuggestionTimer);
          if (query.length < 2) {
            hidePartNumberSuggestions(form);
            return;
          }
          partNumberSuggestionTimer = setTimeout(() => {
            fetchPartNumberSuggestions(form, query);
          }, 220);
        });
        input.addEventListener('focus', () => {
          const query = String(input.value || '').trim();
          if (query.length >= 2) {
            fetchPartNumberSuggestions(form, query);
          }
        });
        document.addEventListener('click', (event) => {
          if (wrap.contains(event.target)) return;
          hidePartNumberSuggestions(form);
        });
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
        ['purchase_price', 'expedition_cost', 'selling_price'].forEach((fieldName) => {
          const input = form.querySelector(`[name="${fieldName}"]`);
          if (!input) return;
          input.addEventListener('input', () => {
            const formatted = formatRupiahInput(input.value);
            input.value = formatted;
            if (fieldName === 'purchase_price' || fieldName === 'expedition_cost') {
              updatePurchaseTotal(form);
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
        formData.append('weight_unit', getWeightUnitValue(form));
        formData.append('weight_unit_custom', form.querySelector('[name="weight_unit_custom"]')?.value || '');
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
        formData.append('supplier_invoice_number', form.querySelector('[name="supplier_invoice_number"]')?.value || '');
        formData.append('purchase_date', form.querySelector('[name="purchase_date"]')?.value || '');
        formData.append('condition', form.querySelector('[name="condition"]').value || '');
        formData.append('processed_by', form.querySelector('[name="processed_by"]')?.value || '');
        formData.append('purchase_price', String(parseCurrencyToNumber(form.querySelector('[name="purchase_price"]').value || '')));
        formData.append('expedition_cost', String(parseCurrencyToNumber(form.querySelector('[name="expedition_cost"]').value || '')));
        formData.append('selling_price', String(parseCurrencyToNumber(form.querySelector('[name="selling_price"]').value || '')));
        formData.append('stock', form.querySelector('[name="stock"]').value || 0);
        formData.append('payment_type', form.querySelector('[name="payment_type"]').value || 'LUNAS');
        formData.append('credit_days', form.querySelector('[name="credit_days"]').value || '');
        formData.append('credit_due_date', form.querySelector('[name="credit_due_date"]').value || '');
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
        const weightDisplay = weightValue !== '' && weightValue !== null
          ? formatWeightDisplay(weightValue, product.weight_unit || 'kg')
          : '-';
        const conditionLabel = String(product.condition || '').trim();
        const createdAtLabel = String(product.created_at || '').trim() || '-';
        const expeditionValue = product.expedition_cost_value ?? product.expedition_cost ?? product.shipping_cost ?? 0;
        const expeditionNumber = parseCurrencyToNumber(expeditionValue);
        const expeditionCost = `Rp ${Number.isFinite(expeditionNumber) ? Math.round(expeditionNumber).toLocaleString('id-ID') : '0'}`;
        const partNumber = String(product.part_number || product.barcode || product.sku || '-').trim() || '-';
        const partName = String(product.part_name || product.name || '-').trim() || '-';
        const encodedProduct = encodeURIComponent(JSON.stringify(product));
        return `
          <tr class="hover:bg-surface-container-low transition-colors group" data-product-id="${product.id}">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-lg bg-surface flex-shrink-0 border border-outline-variant overflow-hidden flex items-center justify-center">
                  ${product.image_url ? `<button type="button" class="h-full w-full" onclick='openImagePreview(${JSON.stringify(product.image_url)}, ${JSON.stringify(product.name || 'Foto barang')})'><img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name || 'Foto barang')}" class="h-full w-full object-cover"></button>` : `<span class="material-symbols-outlined text-primary">inventory_2</span>`}
                </div>
                <div>
                  <p class="sf-part-number">${escapeHtml(partNumber)}</p>
                  <p class="sf-product-name">${escapeHtml(partName)}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-body-md">${conditionLabel ? escapeHtml(conditionLabel) : '-'}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(createdAtLabel)}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.category || '-')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.brand || '-')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(weightDisplay)}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(unit)}</td>
            <td class="px-6 py-4"><span class="px-3 py-1 rounded-full ${stockClass} text-label-sm font-semibold inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span>${stock} ${escapeHtml(stockUnit)}</span></td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.purchase_price || 'Rp 0')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.selling_price || 'Rp 0')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(expeditionCost)}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.supplier_name || '-')}</td>
            <td class="px-6 py-4 text-body-md">${escapeHtml(product.supplier_invoice_number || '-')}</td>
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
      createForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const payload = await submitProductForm(createForm, storeUrl, 'POST', 'createFormAlert');
        if (!payload) return;
        persistFormHistoryFromForm(createForm);
        upsertProductRow(payload.product, 'create');
        resetCreateFormForNextEntry();
        showToast((payload.message || 'Barang berhasil ditambahkan.') + ' Silakan lanjut input barang berikutnya.');
      });
      editForm.addEventListener('submit', async function (event) { event.preventDefault(); const productId = editForm.querySelector('[name="product_id"]').value; const payload = await submitProductForm(editForm, `${updateUrlBase}/${productId}`, 'PUT', 'editFormAlert'); if (!payload) return; persistFormHistoryFromForm(editForm); upsertProductRow(payload.product, 'update'); closeEditModal(); showToast(payload.message || 'Barang berhasil diperbarui.'); });
      createForm.querySelector('[name="stock"]')?.addEventListener('input', () => updatePurchaseTotal(createForm));
      editForm.querySelector('[name="stock"]')?.addEventListener('input', () => updatePurchaseTotal(editForm));
      createForm.querySelector('[name="payment_type"]')?.addEventListener('change', () => syncBatchCreditFields(createForm));
      editForm.querySelector('[name="payment_type"]')?.addEventListener('change', () => syncBatchCreditFields(editForm));
      createForm.querySelector('[name="weight_unit"]')?.addEventListener('change', () => syncWeightUnitFields(createForm));
      editForm.querySelector('[name="weight_unit"]')?.addEventListener('change', () => syncWeightUnitFields(editForm));
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
      syncWeightUnitFields(createForm);
      syncWeightUnitFields(editForm);
      syncBatchCreditFields(createForm);
      syncBatchCreditFields(editForm);
      initializeFormHistoryAutocomplete(createForm);
      initializeFormHistoryAutocomplete(editForm);
      initializePartNumberAutofill(createForm);
    </script>
</x-filament-panels::page>

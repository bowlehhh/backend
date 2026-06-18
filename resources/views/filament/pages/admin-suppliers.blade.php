@php
    $viewData = $this->getViewData();
    $stats = $viewData['stats'] ?? ['total' => 0, 'active' => 0, 'total_stock' => 0];
    $suppliers = $viewData['suppliers'] ?? [];
    $currentUser = auth()->user();
    $isAdminBesarAccess = $currentUser?->isAdminBesar() ?? false;
@endphp

<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

    <style>
      :root { --sf-topbar-h: 52px; --sf-sidebar-w: 208px; }
      .sf-wrap {
        font-family: 'Hanken Grotesk', sans-serif;
        width: 100vw;
        max-width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        font-size: 13px;
      }
      .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; }
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
      .sf-sidebar { position: fixed; top: var(--sf-topbar-h); left: 0; width: var(--sf-sidebar-w); height: calc(100vh - var(--sf-topbar-h)); border-right: 1px solid #d4dbd7; overflow: hidden; z-index: 30; display: flex; flex-direction: column; background: #fff; }
      .sf-main-scroll { min-width: 0; width: calc(100% - var(--sf-sidebar-w)); margin-left: var(--sf-sidebar-w); height: calc(100vh - var(--sf-topbar-h)); overflow-y: auto; }
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
      .sf-wrap .h-16 { height: var(--sf-topbar-h) !important; }
      .sf-wrap .text-4xl { font-size: 30px !important; line-height: 36px !important; }
      .sf-wrap .text-5xl { font-size: 30px !important; line-height: 36px !important; }
      .sf-wrap table th,
      .sf-wrap table td { padding-top: .65rem !important; padding-bottom: .65rem !important; }
      @media (max-width: 1279px) {
        .sf-layout { display: block; }
        .sf-sidebar { display: none; }
        .sf-main-scroll { width: 100%; margin-left: 0; height: auto; overflow: visible; }
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-main-scroll { padding: 12px !important; }
        .sf-main-scroll h1 { font-size: 28px !important; line-height: 34px !important; }
        .sf-main-scroll .text-5xl { font-size: 30px !important; line-height: 36px !important; }
        .sf-main-scroll table th, .sf-main-scroll table td { white-space: nowrap; font-size: 13px !important; }
      }
    </style>

    <div class="sf-wrap bg-[#f7f9fb] text-[#191c1e] min-h-screen overflow-x-hidden">
      <header class="bg-white border-b border-[#d4dbd7] flex justify-between items-center px-5 h-16 w-full sticky top-0 z-50">
        <div class="flex items-center gap-4">
          <span class="block leading-none text-xl font-bold text-[#006948]">Surya Duta Multindo</span>
        </div>
      </header>

      <div class="sf-shell">
      <div class="sf-layout">
        <aside class="sf-sidebar lg:flex flex-col w-full p-4 pb-6 bg-white hidden">
          <div class="mb-4 rounded-lg border border-[#d4dbd7] bg-[#f2f4f6] p-3">
            <div class="flex items-center gap-2">
              <div class="h-8 w-8 rounded-lg bg-[#006948] text-white flex items-center justify-center">
                <span class="material-symbols-outlined text-sm">inventory</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-[#006948]">{{ $isAdminBesarAccess ? 'Admin Besar Panel' : 'Admin Panel' }}</p>
                <p class="text-[10px] uppercase tracking-wide text-[#52615a]">{{ $isAdminBesarAccess ? 'Gudang Access Mode' : 'Management Mode' }}</p>
              </div>
            </div>
          </div>
          <nav class="flex-1 min-h-0 flex flex-col space-y-1 overflow-y-auto">
            @if($isAdminBesarAccess)
              <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ route('admin.admin-besar.index') }}">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Kembali ke Dashboard Admin Besar</span>
              </a>
            @endif
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ url('/admin/products') }}">
              <span class="material-symbols-outlined">inventory_2</span>
              <span>Barang</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 bg-[#006948] text-white rounded-lg px-3 py-2 font-medium" href="{{ url('/admin/suppliers') }}">
              <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">local_shipping</span>
              <span>Supplier</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="{{ route('admin.transaksi.dashboard') }}">
              <span class="material-symbols-outlined">point_of_sale</span><span>Transaksi</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="{{ url('/admin/admin-module?type=credits') }}">
              <span class="material-symbols-outlined">credit_card</span><span>Kredit &amp; Utang Saya</span>
            </a>
            <a class="sf-nav-item w-full flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="{{ url('/admin/admin-module?type=supplier-transactions') }}">
              <span class="material-symbols-outlined">account_tree</span><span>Transaksi PT</span>
            </a>
            <a class="sf-nav-item w-full mt-auto flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium text-left" href="{{ url('/admin/admin-module?type=product-groups') }}">
              <span class="material-symbols-outlined">inventory_2</span><span>Kelompok Barang</span>
            </a>
          </nav>
          <div class="mt-4 pt-3 pb-5 border-t border-[#d4dbd7]">
            <form method="POST" action="{{ route('logout') }}" class="js-admin-logout-form">
              @csrf
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
              @if($isAdminBesarAccess)
                <a href="{{ route('admin.admin-besar.index') }}" class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#bccac0] bg-white px-3 py-2 text-[12px] font-semibold text-[#006948] hover:bg-[#f2f4f6]">
                  <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                  Kembali ke Dashboard Admin Besar
                </a>
              @endif
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5 mb-6">
            <div class="bg-[#ffffff] border border-[#b8c5be] rounded-xl p-5 md:p-6 custom-shadow">
              <p class="text-base text-[#415149] font-medium">Total Supplier</p>
              <p class="text-[34px] md:text-[40px] leading-tight font-extrabold text-[#006948] mt-2">{{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-[#ffffff] border border-[#b8c5be] rounded-xl p-5 md:p-6 custom-shadow">
              <p class="text-base text-[#415149] font-medium">Supplier Aktif</p>
              <p class="text-[34px] md:text-[40px] leading-tight font-extrabold text-[#825100] mt-2">{{ number_format($stats['active'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-[#ffffff] border border-[#b8c5be] rounded-xl p-5 md:p-6 custom-shadow">
              <p class="text-base text-[#415149] font-medium">Total Stok Supplier</p>
              <p class="text-[34px] md:text-[40px] leading-tight font-extrabold text-[#4648d4] mt-2">{{ number_format($stats['total_stock'] ?? 0, 0, ',', '.') }}</p>
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
                @forelse($suppliers as $supplier)
                  <tr class="border-t border-[#e4e8e6]">
                    <td class="px-5 py-4 font-semibold">{{ $supplier['name'] ?: '-' }}</td>
                    <td class="px-5 py-4">{{ $supplier['type'] ?: '-' }}</td>
                    <td class="px-5 py-4">{{ $supplier['address'] ?: '-' }}</td>
                    <td class="px-5 py-4">{{ $supplier['phone'] ?: '-' }}</td>
                    <td class="px-5 py-4">{{ $supplier['product_count'] }}</td>
                    <td class="px-5 py-4">{{ $supplier['stock_total'] }}</td>
                    <td class="px-5 py-4 text-right">
                      <a class="text-[#006948]" href="{{ url('/admin/suppliers/' . $supplier['id']) }}">Detail</a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="px-5 py-8 text-center text-[#52615a]">Belum ada supplier.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </main>
      </div>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');
    </script>

    @include('filament.partials.logout-modal')
</x-filament-panels::page>

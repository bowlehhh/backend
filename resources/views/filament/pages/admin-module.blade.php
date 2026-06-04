@php
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
@endphp

<x-filament-panels::page>
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
      .sf-dashboard-page .fi-body {
        min-height: 100% !important;
        overflow: hidden !important;
      }
      .sf-layout {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        gap: 0;
        height: calc(100vh - 64px);
        overflow: hidden;
      }
      .sf-sidebar {
        position: sticky;
        top: 64px;
        height: calc(100vh - 64px);
        border-right: 1px solid #d4dbd7;
        overflow: hidden;
        display: flex;
        flex-direction: column;
      }
      .sf-sidebar nav {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
      }
      .sf-main-scroll {
        height: calc(100vh - 64px);
        min-width: 0;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
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
          grid-template-columns: 1fr;
          height: auto;
          overflow: visible;
        }
        .sf-sidebar {
          position: fixed;
          left: 0;
          top: 64px;
          width: min(84vw, 300px);
          height: calc(100vh - 64px);
          transform: translateX(-102%);
          opacity: 0;
          pointer-events: none;
          z-index: 30;
        }
        .sf-main-scroll {
          width: 100%;
          height: auto;
          overflow: visible;
        }
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-main-scroll { padding: 12px !important; }
        .sf-main-scroll h1 { font-size: 28px !important; line-height: 34px !important; }
        .sf-main-scroll .px-6 { padding-left: 12px !important; padding-right: 12px !important; }
        .sf-main-scroll .py-4 { padding-top: 10px !important; padding-bottom: 10px !important; }
        .sf-main-scroll table th,
        .sf-main-scroll table td { font-size: 13px !important; white-space: nowrap; }
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
        .sf-wrap .sf-main-scroll { padding: 12px !important; }
        .sf-wrap .sf-main-scroll h1 { font-size: 24px !important; line-height: 30px !important; }
        .sf-wrap .sf-main-scroll .text-[40px] { font-size: 24px !important; line-height: 30px !important; }
        .sf-wrap .sf-main-scroll .grid-cols-1.sm\\:grid-cols-2.xl\\:grid-cols-5 { gap: 12px !important; }
        .sf-wrap .sf-header-actions { gap: 8px; }
        .sf-wrap .sf-header-actions > div { width: 32px !important; height: 32px !important; }
        .sf-wrap .sf-header-actions .material-symbols-outlined { font-size: 20px; }
      }
    </style>

    <div class="sf-wrap bg-[#f7f9fb] text-[#191c1e] antialiased h-screen w-screen max-w-none ml-[calc(50%-50vw)] mr-[calc(50%-50vw)] overflow-hidden">
      <header class="bg-white border-b border-[#d4dbd7] shadow-sm flex justify-between items-center px-6 h-16 w-full sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <button id="mobileSidebarBtn" type="button" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#bccac0] bg-white text-[#3d4a42] hover:bg-[#f1f4f2]" aria-label="Buka navigasi">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <div class="flex items-center gap-4"><span class="text-xl font-bold text-[#006948]">Toko Pak Paul</span></div>
        </div>
        <button id="toggleSidebarBtn" type="button" class="hidden lg:inline-flex items-center gap-1 rounded-lg border border-[#bccac0] px-3 py-2 text-sm text-[#3d4a42] hover:bg-[#f1f4f2]">
          <span class="material-symbols-outlined text-base">left_panel_close</span>
          <span>Sidebar</span>
        </button>
      </header>

      <div class="sf-layout">
        <div id="mobileSidebarBackdrop" class="sf-mobile-sidebar-backdrop lg:hidden"></div>
        <aside class="sf-sidebar flex flex-col w-full p-4 pb-6 bg-white">
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
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="{{ url('/admin/products') }}"><span class="material-symbols-outlined">inventory_2</span><span class="nav-label">Barang</span></a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-3 py-2 hover:bg-[#eceef0] rounded-lg font-medium" href="{{ url('/admin/suppliers') }}"><span class="material-symbols-outlined">local_shipping</span><span class="nav-label">Supplier</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'credits' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ url('/admin/admin-module?type=credits') }}"><span class="material-symbols-outlined">credit_card</span><span class="nav-label">Kredit</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'supplier-transactions' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ url('/admin/admin-module?type=supplier-transactions') }}"><span class="material-symbols-outlined">account_tree</span><span class="nav-label">Transaksi PT/CV</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'batches' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ url('/admin/admin-module?type=batches') }}"><span class="material-symbols-outlined">layers</span><span class="nav-label">Batch Barang</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'taxonomy' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ url('/admin/admin-module?type=taxonomy') }}"><span class="material-symbols-outlined">category</span><span class="nav-label">Kategori & Merek</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'reports' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ url('/admin/admin-module?type=reports') }}"><span class="material-symbols-outlined">analytics</span><span class="nav-label">Laporan</span></a>
            <a class="sf-nav-item flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $type === 'users' ? 'bg-[#006948] text-white' : 'text-[#47534d] hover:bg-[#eceef0]' }}" href="{{ url('/admin/admin-module?type=users') }}"><span class="material-symbols-outlined">group</span><span class="nav-label">User</span></a>
          </nav>
          <div class="mt-4 pt-3 pb-5 border-t border-[#d4dbd7]">
            <form method="POST" action="{{ route('logout') }}" class="js-admin-logout-form">
              @csrf
              <button type="submit" class="sf-nav-item w-full flex items-center gap-3 text-[#ba1a1a] px-3 py-2 hover:bg-[#ffdad6] rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span>
                <span class="nav-label">Logout</span>
              </button>
            </form>
          </div>
        </aside>

        <main class="sf-main-scroll p-4 md:p-6">
          @if ($type !== 'taxonomy')
            <div class="flex items-end justify-between gap-4 mb-6">
              <div>
                <h1 class="text-[40px] leading-[48px] font-semibold text-[#191c1e]">{{ $title }}</h1>
                <p class="text-[#52615a]">Kelola data {{ strtolower($title) }} dengan tampilan konsisten seperti halaman barang.</p>
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
                <div class="flex items-center gap-3 mb-2"><div class="p-1 rounded-lg bg-[#e8efff]"><span class="material-symbols-outlined text-[#1e40af]">point_of_sale</span></div><span class="text-[#52615a] font-medium">Transaksi Kasir Hari Ini</span></div>
                <div class="text-[34px] leading-[42px] font-bold text-[#1e40af]">Rp {{ number_format((float) ($reportStats['cashier_today_total'] ?? 0), 0, ',', '.') }}</div>
                <p class="text-sm text-[#52615a] mt-2">{{ number_format((int) ($reportStats['cashier_today_count'] ?? 0), 0, ',', '.') }} transaksi kasir</p>
              </div>
            </div>

            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">List Transaksi Pembelian</h2>
                <p class="text-sm text-[#52615a]">Klik detail untuk masuk ke riwayat transaksi supplier terkait.</p>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full text-left border-collapse">
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
                    @forelse ($reportTransactions as $trx)
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 text-[15px]">{{ $trx['tanggal'] }}</td>
                        <td class="px-6 py-4 text-[15px] font-semibold">{{ $trx['supplier'] }}</td>
                        <td class="px-6 py-4 text-[15px]">{{ $trx['barang'] }}</td>
                        <td class="px-6 py-4 text-[15px]">{{ number_format((int) $trx['qty'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-[15px]">{{ $trx['harga_satuan'] }}</td>
                        <td class="px-6 py-4 text-[15px] font-semibold">{{ $trx['total'] }}</td>
                        <td class="px-6 py-4 text-right">
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
                      <tr><td colspan="7" class="px-6 py-10 text-center text-[#52615a]">Belum ada transaksi pembelian.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <div class="mt-6 bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
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
                    @forelse ($reportCashierTransactions as $trx)
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 font-semibold">{{ $trx['invoice_number'] }}</td>
                        <td class="px-6 py-4">{{ $trx['customer_name'] }}</td>
                        <td class="px-6 py-4">{{ $trx['cashier_name'] }}</td>
                        <td class="px-6 py-4 uppercase">{{ $trx['payment_method'] }}</td>
                        <td class="px-6 py-4">{{ $trx['created_at'] }}</td>
                        <td class="px-6 py-4 text-right font-semibold">{{ $trx['total'] }}</td>
                        <td class="px-6 py-4 text-right">
                          <div>{{ $trx['credit_amount'] }}</div>
                          <div class="text-xs text-[#52615a]">Tempo: {{ $trx['credit_due_date'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">{{ $trx['total_return_refund'] }}</td>
                        <td class="px-6 py-4 text-right">
                          <a href="{{ route('admin.sales.receipt', ['sale' => $trx['sale_id']]) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada transaksi kasir.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          @elseif ($type === 'supplier-transactions')
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
              <div class="px-6 py-4 border-b border-[#d4dbd7]">
                <h2 class="text-lg font-semibold text-[#191c1e]">Kelompok Transaksi PT/CV (Kredit & Lunas)</h2>
                <p class="text-sm text-[#52615a]">Data ini diambil langsung dari transaksi kasir berdasarkan nama customer yang diisi.</p>
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
                    @forelse ($ptCustomerGroups as $group)
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 font-semibold">{{ $group['pt_name'] }}</td>
                        <td class="px-6 py-4">{{ number_format((int) $group['total_transaksi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format((int) $group['total_qty'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 font-semibold">{{ $group['total_nilai'] }}</td>
                        <td class="px-6 py-4">{{ number_format((int) $group['kredit'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format((int) $group['jatuh_tempo'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format((int) $group['lunas'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $group['terakhir_beli'] }}</td>
                        <td class="px-6 py-4 text-right">
                          <a href="{{ url('/admin/admin-module?type=supplier-transactions&pt=' . urlencode($group['pt_name'])) }}" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="9" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kelompok PT/CV.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
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
                            <a href="{{ route('admin.sales.receipt', ['sale' => $row['sale_id']]) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Lihat Nota</a>
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
          @elseif ($type === 'credits')
            @if(session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if($errors->any())
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow">
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
                      <th class="px-3 py-2.5 font-medium uppercase tracking-wider text-right whitespace-nowrap">Aksi</th>
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
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                          <div class="flex min-w-max flex-row flex-nowrap items-center justify-end gap-1.5">
                            <a href="{{ route('admin.credits.detail', ['batch' => $row['batch_id']]) }}" class="inline-flex h-8 items-center rounded-lg border border-[#bccac0] bg-white px-2.5 py-1 text-xs text-[#006948] hover:bg-[#f1f4f2]">Detail</a>
                            <a href="{{ route('admin.credits.receipt', ['batch' => $row['batch_id']]) }}" target="_blank" rel="noopener" class="inline-flex h-8 items-center rounded-lg border border-[#bccac0] bg-white px-2.5 py-1 text-xs text-[#006948] hover:bg-[#f1f4f2]">Nota</a>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="14" class="px-6 py-10 text-center text-[#52615a]">Belum ada data kredit.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          @elseif ($type === 'taxonomy')
            @if(session('success'))
              <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif
            @if($errors->any())
              <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="mb-6">
              <h1 class="text-[40px] leading-[48px] font-semibold text-[#191c1e]">Kategori & Brand</h1>
              <p class="text-[#52615a]">Kelola kategori dan brand produk toko.</p>
            </div>

            <form id="taxonomySearchForm" method="GET" action="{{ url('/admin/admin-module') }}" class="mb-4">
              <input type="hidden" name="type" value="taxonomy">
              <input type="hidden" name="sort" value="{{ $taxonomySort }}">
              <input type="hidden" name="dir" value="{{ $taxonomyDir }}">
              <div class="bg-white border border-[#d4dbd7] rounded-xl p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto_auto] gap-3 items-center">
                <input id="taxonomySearchInput" type="text" name="q" value="{{ $searchKeyword }}" placeholder="Cari kategori / brand" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
                <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-white">Cari</button>
                <a href="{{ url('/admin/admin-module?type=taxonomy') }}" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42] text-center">Reset</a>
              </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-4 custom-shadow"><p class="text-xs uppercase text-[#52615a]">Total Kategori</p><p class="mt-1 text-2xl font-semibold text-[#006948]">{{ number_format((int) $taxonomyStats['total_categories'], 0, ',', '.') }}</p></div>
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-4 custom-shadow"><p class="text-xs uppercase text-[#52615a]">Total Brand</p><p class="mt-1 text-2xl font-semibold text-[#006948]">{{ number_format((int) $taxonomyStats['total_brands'], 0, ',', '.') }}</p></div>
              <div class="rounded-xl border border-[#d4dbd7] bg-white p-4 custom-shadow"><p class="text-xs uppercase text-[#52615a]">Total Produk</p><p class="mt-1 text-2xl font-semibold text-[#006948]">{{ number_format((int) $taxonomyStats['total_products'], 0, ',', '.') }}</p></div>
            </div>

            <div class="bg-white border border-[#d4dbd7] rounded-xl overflow-hidden custom-shadow min-w-0">
              <div class="overflow-x-auto w-full max-w-full">
                <table class="min-w-[1180px] w-max text-left border-collapse">
                  <thead>
                    <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                      @php
                        $dirCategory = $taxonomySort === 'category' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                        $dirBrand = $taxonomySort === 'brand' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                        $dirTotal = $taxonomySort === 'total_produk' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                        $dirStatus = $taxonomySort === 'status' && $taxonomyDir === 'asc' ? 'desc' : 'asc';
                      @endphp
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=category&dir=' . $dirCategory) }}">Nama Kategori</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=brand&dir=' . $dirBrand) }}">Nama Brand</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=total_produk&dir=' . $dirTotal) }}">Total Produk</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider"><a class="table-sort-link" href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=status&dir=' . $dirStatus) }}">Status</a></th>
                      <th class="px-6 py-4 font-medium uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-[#e4e8e6]">
                    @forelse ($rows as $row)
                      <tr class="hover:bg-[#f6f8f7] transition-colors">
                        <td class="px-6 py-4 text-[15px] font-medium">{{ $row['kategori'] }}</td>
                        <td class="px-6 py-4 text-[15px]">{{ $row['brand'] }}</td>
                        <td class="px-6 py-4 text-[15px]">
                          <a href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=' . $taxonomySort . '&dir=' . $taxonomyDir . '&category_id=' . $row['category_id'] . '&brand_id=' . $row['brand_id']) }}" class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 text-xs font-semibold text-[#006948]">{{ number_format((int) $row['total_produk'], 0, ',', '.') }}</a>
                        </td>
                        <td class="px-6 py-4 text-[15px]">
                          <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $row['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $row['status'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                          <div class="inline-flex items-center gap-2">
                            <button
                              type="button"
                              class="rounded-lg border border-[#bccac0] px-2.5 py-1 text-xs text-[#3d4a42] hover:bg-[#f1f4f2]"
                              data-tax-edit='@json($row)'>Edit</button>
                            <button
                              type="button"
                              class="rounded-lg border border-red-200 px-2.5 py-1 text-xs text-red-700 hover:bg-red-50"
                              data-tax-delete='@json($row)'>Hapus</button>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="px-6 py-12 text-center text-[#52615a]">Belum ada data kategori-brand. Klik <strong>+ Tambah Data</strong> untuk mulai.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-t border-[#d4dbd7] px-5 py-3 text-sm text-[#52615a]">
                <p>Menampilkan {{ $taxonomyPagination['from'] }}-{{ $taxonomyPagination['to'] }} dari {{ $taxonomyPagination['total'] }} data</p>
                <div class="flex items-center gap-2">
                  @if($taxonomyPagination['has_prev'])
                    <a href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=' . $taxonomySort . '&dir=' . $taxonomyDir . '&page=' . $taxonomyPagination['prev_page']) }}" class="rounded-lg border border-[#bccac0] px-3 py-1.5">Prev</a>
                  @endif
                  <span class="rounded-lg bg-[#f1f4f2] px-3 py-1.5">{{ $taxonomyPagination['current_page'] }} / {{ $taxonomyPagination['last_page'] }}</span>
                  @if($taxonomyPagination['has_next'])
                    <a href="{{ url('/admin/admin-module?type=taxonomy&q=' . urlencode($searchKeyword) . '&sort=' . $taxonomySort . '&dir=' . $taxonomyDir . '&page=' . $taxonomyPagination['next_page']) }}" class="rounded-lg border border-[#bccac0] px-3 py-1.5">Next</a>
                  @endif
                </div>
              </div>
            </div>

            @if(!empty($taxonomyProducts))
              <div class="mt-4 rounded-xl border border-[#d4dbd7] bg-white custom-shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-[#d4dbd7]">
                  <h3 class="text-lg font-semibold text-[#191c1e]">Daftar Produk Terkait</h3>
                  <p class="text-sm text-[#52615a]">Kategori ID {{ $taxonomySelectedCategoryId }} & Brand ID {{ $taxonomySelectedBrandId }}</p>
                </div>
              <div class="overflow-x-auto w-full max-w-full">
                <table class="min-w-[1100px] w-max text-left border-collapse">
                    <thead>
                      <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                        <th class="px-6 py-3 font-medium uppercase tracking-wider">Nama Produk</th>
                        <th class="px-6 py-3 font-medium uppercase tracking-wider">Barcode</th>
                        <th class="px-6 py-3 font-medium uppercase tracking-wider">Status</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e4e8e6]">
                      @foreach($taxonomyProducts as $product)
                        <tr class="hover:bg-[#f6f8f7]">
                          <td class="px-6 py-3">{{ $product['name'] }}</td>
                          <td class="px-6 py-3">{{ $product['barcode'] }}</td>
                          <td class="px-6 py-3">{{ $product['status'] }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endif
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
                        <option value="cashier" @selected($userRow['role'] === 'cashier')>Cashier</option>
                        <option value="admin" @selected($userRow['role'] === 'admin')>Admin</option>
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
            @if ($type === 'batches')
              <form method="GET" action="{{ url('/admin/admin-module') }}" class="mb-4">
                <input type="hidden" name="type" value="batches">
                <div class="bg-white border border-[#d4dbd7] rounded-xl p-4 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_auto_auto] gap-3 items-center">
                  <input type="text" name="q" value="{{ $searchKeyword }}" placeholder="Cari kode batch... (contoh: BATCH-008 atau 202605)" class="w-full rounded-lg border-[#bccac0] focus:border-[#006948] focus:ring-[#006948]/20">
                  <button type="submit" class="rounded-lg bg-[#006948] px-4 py-2 text-white">Cari</button>
                  <a href="{{ url('/admin/admin-module?type=batches') }}" class="rounded-lg border border-[#bccac0] px-4 py-2 text-[#3d4a42] text-center">Reset</a>
                </div>
              </form>
            @endif
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
      @endif
    </script>

    @include('filament.partials.logout-modal')
</x-filament-panels::page>

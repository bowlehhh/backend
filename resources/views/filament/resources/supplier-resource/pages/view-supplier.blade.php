@php
    $viewData = $this->getViewData();
    $supplier = $viewData['supplier'];
    $summary = $viewData['summary'] ?? [];
    $rows = $viewData['purchaseRows'] ?? [];
    $invoiceNumbers = $viewData['invoiceNumbers'] ?? [];
    $dueDateGroups = $viewData['dueDateGroups'] ?? [];
    $purchaseDateGroups = $viewData['purchaseDateGroups'] ?? [];
    $purchaseItems = $viewData['purchaseItems'] ?? [];
    $focusBatchId = (int) ($viewData['focusBatchId'] ?? 0);
    $currentUser = auth()->user();
    $isAdminBesarAccess = $currentUser?->isAdminBesar() ?? false;
@endphp

<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

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
      .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; display: inline-block; vertical-align: middle; }
      .table-container::-webkit-scrollbar { height: 8px; }
      .table-container::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 10px; }
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
      .sf-dashboard-page .fi-layout-sidebar-toggle-btn-ctn,
      .sf-dashboard-page .fi-header { display: none !important; }
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
      .sf-layout { display: block; }
      .sf-sidebar { position: fixed; top: var(--sf-topbar-h); left: 0; width: var(--sf-sidebar-w); height: calc(100vh - var(--sf-topbar-h)); border-right: 1px solid #d4dbd7; overflow: hidden; z-index: 30; display: flex; flex-direction: column; background: #fff; }
      .sf-sidebar nav { flex: 1 1 auto; min-height: 0; overflow-y: auto; }
      .sf-content { min-width: 0; width: calc(100% - var(--sf-sidebar-w)); margin-left: var(--sf-sidebar-w); height: calc(100vh - var(--sf-topbar-h)); min-height: 0; overflow-x: hidden; overflow-y: auto; -webkit-overflow-scrolling: touch; padding-top: 0; }
      .sf-nav-item { font-size: 13px; }
      .sf-table th { font-size: 12px; letter-spacing: .02em; }
      .sf-table td { font-size: 13px; }
      .sf-chip { display: inline-flex; align-items: center; border-radius: 999px; border: 1px solid #cbd5d1; background: #f8faf9; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #3d4a42; }
      .sf-modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 80; display: none; align-items: center; justify-content: center; padding: 16px; }
      .sf-modal-backdrop.show { display: flex; }
      .sf-modal-card { width: min(860px, 100%); max-height: 85vh; overflow: hidden; border-radius: 18px; border: 1px solid #d4dbd7; background: white; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22); }
      .sf-modal-body { max-height: calc(85vh - 72px); overflow-y: auto; }
      .sf-mobile-sidebar-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.42);
        z-index: 24;
        opacity: 0;
        pointer-events: none;
        transition: opacity .22s ease;
      }
      .sf-mobile-menu-open .sf-mobile-sidebar-backdrop {
        opacity: 1;
        pointer-events: auto;
      }
      .sf-sidebar {
        transition: transform .24s ease, opacity .24s ease, box-shadow .24s ease;
      }
      @media (max-width: 1279px) {
        .sf-layout { display: block; }
        .sf-sidebar { position: static; width: 100%; height: auto; border-right: 0; border-bottom: 1px solid #d4dbd7; transform: none; opacity: 1; pointer-events: auto; box-shadow: none; }
        .sf-content { width: 100%; margin-left: 0; height: auto; min-height: auto; overflow: visible; }
      }
      @media (max-width: 767px) {
        .sf-wrap header { height: 56px !important; padding-left: 14px !important; padding-right: 14px !important; }
        .sf-content { padding: 12px !important; }
        .sf-table th, .sf-table td { font-size: 13px !important; white-space: nowrap; }
      }
    </style>

    <div class="sf-wrap bg-[#f7f9fb] text-[#191c1e] antialiased min-h-screen overflow-x-hidden">
      <header id="supplierPageHeader" class="bg-white border-b border-[#d4dbd7] shadow-sm flex justify-between items-center px-5 h-16 w-full sticky top-0 z-50">
        <div class="flex items-center gap-3">
          <button id="mobileSidebarBtn" type="button" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#bccac0] bg-white text-[#3d4a42] hover:bg-[#f1f4f2]" aria-label="Buka navigasi">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <span class="font-display text-[18px] font-bold text-[#006948] leading-none">Surya Duta Multindo</span>
        </div>
      </header>

      <div class="sf-layout">
        <div id="mobileSidebarBackdrop" class="sf-mobile-sidebar-backdrop lg:hidden"></div>
        <aside class="sf-sidebar flex flex-col w-full p-3 bg-white">
          <div class="mb-3 rounded-lg border border-[#d4dbd7] bg-[#f2f4f6] p-2.5">
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
          <nav class="flex-1 flex flex-col space-y-1 overflow-y-auto pr-1">
            @if($isAdminBesarAccess)
              <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ route('admin.admin-besar.index') }}">
                <span class="material-symbols-outlined">arrow_back</span><span>Kembali ke Dashboard Admin Besar</span>
              </a>
            @endif
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ url('/admin/products') }}">
              <span class="material-symbols-outlined">inventory_2</span><span>Barang</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 bg-[#006948] text-white rounded-lg px-2.5 py-2 font-medium" href="{{ url('/admin/suppliers') }}">
              <span class="material-symbols-outlined">local_shipping</span><span>Supplier</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ route('admin.transaksi.dashboard') }}">
              <span class="material-symbols-outlined">point_of_sale</span><span>Transaksi</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ url('/admin/admin-module?type=credits') }}">
              <span class="material-symbols-outlined">credit_card</span><span>Kredit</span>
            </a>
            <a class="sf-nav-item flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ url('/admin/admin-module?type=supplier-transactions') }}">
              <span class="material-symbols-outlined">account_tree</span><span>Transaksi PT</span>
            </a>
            <a class="sf-nav-item mt-auto flex items-center gap-3 text-[#47534d] px-2.5 py-2 hover:bg-[#eceef0] transition-all rounded-lg font-medium" href="{{ url('/admin/admin-module?type=product-groups') }}">
              <span class="material-symbols-outlined">inventory_2</span><span>Kelompok Barang</span>
            </a>
          </nav>
          <div class="pt-3 border-t border-[#d4dbd7]">
            <form method="POST" action="{{ route('logout') }}" class="js-admin-logout-form">
              @csrf
              <button type="submit" class="sf-nav-item w-full flex items-center gap-3 text-[#ba1a1a] px-2.5 py-2 hover:bg-[#ffdad6] transition-all rounded-lg font-medium text-left">
                <span class="material-symbols-outlined">logout</span><span>Logout</span>
              </button>
            </form>
          </div>
        </aside>

        <main class="sf-content p-4 md:p-6 space-y-4">
          <div class="rounded-xl border border-[#d4dbd7] bg-white p-4">
            <h1 class="text-[28px] leading-tight font-semibold text-[#191c1e]">{{ $supplier->name }}</h1>
            <p class="mt-1 text-sm text-[#52615a]">Detail supplier dan seluruh riwayat pembelian (kredit + lunas).</p>
            @if($isAdminBesarAccess)
              <a href="{{ route('admin.admin-besar.index') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-[#bccac0] bg-white px-3 py-2 text-[12px] font-semibold text-[#006948] hover:bg-[#f2f4f6]">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Dashboard Admin Besar
              </a>
            @endif
            <div class="mt-3 flex flex-wrap gap-2">
              @forelse($invoiceNumbers as $invoiceNumber)
                <span class="sf-chip">INV: {{ $invoiceNumber }}</span>
              @empty
                <span class="text-xs text-[#7b8b83]">Belum ada nomor invoice supplier yang tersimpan.</span>
              @endforelse
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="rounded-xl border border-[#d4dbd7] bg-white p-4">
              <p class="text-xs uppercase tracking-wide text-[#52615a]">Barang Kredit</p>
              <p class="mt-1 text-2xl font-semibold text-[#1e40af]">{{ number_format((int) ($summary['kredit_count'] ?? 0), 0, ',', '.') }}</p>
              <p class="text-xs text-[#52615a]">Belum lunas</p>
            </div>
            <button type="button" data-modal-open="dueDateModal" class="rounded-xl border border-[#f1d4d4] bg-[#fff6f6] p-4 text-left transition hover:border-[#e5a8a8] hover:bg-[#fff1f1]">
              <p class="text-xs uppercase tracking-wide text-[#8a1c1c]">Warning Jatuh Tempo</p>
              <p class="mt-1 text-2xl font-semibold text-[#ba1a1a]">{{ number_format((int) ($summary['warning_count'] ?? 0), 0, ',', '.') }}</p>
              <p class="text-xs text-[#8a1c1c]">Klik untuk lihat barang dan tanggal jatuh tempo.</p>
            </button>
            <button type="button" data-modal-open="purchaseDateModal" class="rounded-xl border border-[#d4dbd7] bg-[#f4faf7] p-4 text-left transition hover:border-[#9dc9b3] hover:bg-[#eef8f2]">
              <p class="text-xs uppercase tracking-wide text-[#2f5f47]">Tanggal Beli Barang</p>
              <p class="mt-1 text-2xl font-semibold text-[#006948]">{{ number_format((int) ($summary['purchase_date_count'] ?? 0), 0, ',', '.') }}</p>
              <p class="text-xs text-[#2f5f47]">Klik untuk lihat grup tanggal pembelian barang supplier.</p>
            </button>
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
                    <td class="px-4 py-2.5 font-semibold">{{ (int) ($summary['total_transactions'] ?? 0) }} kali</td>
                    <td class="px-4 py-2.5 font-semibold">{{ (int) ($summary['total_products'] ?? 0) }} item</td>
                    <td class="px-4 py-2.5 font-semibold">{{ number_format((int) ($summary['total_qty'] ?? 0), 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 font-semibold">{{ $summary['total_modal'] ?? 'Rp 0' }}</td>
                    <td class="px-4 py-2.5">{{ $summary['last_purchase_at'] ?? '-' }}</td>
                    <td class="px-4 py-2.5">{{ (int) ($summary['kredit_count'] ?? 0) }}</td>
                    <td class="px-4 py-2.5">{{ (int) ($summary['lunas_count'] ?? 0) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div id="riwayat-pembelian" class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-[#d4dbd7]">
              <div>
                <h2 class="text-lg font-semibold text-[#191c1e]">Riwayat Pembelian Barang Berdasarkan Tanggal</h2>
                <p class="text-sm text-[#52615a]">Barang dengan tanggal beli yang sama digabung dalam satu riwayat, lalu tanggal yang berbeda tampil di bawahnya.</p>
              </div>
              <a href="{{ route('admin.suppliers.invoice-recap', ['supplier' => $supplier->id]) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#006948] bg-[#006948] px-4 py-2 text-sm font-semibold text-white hover:bg-[#00563b]">
                Lihat Nota
              </a>
            </div>
            <div class="p-4 space-y-4">
              @forelse($purchaseDateGroups as $group)
                <div class="rounded-xl border border-[#d4dbd7] bg-white overflow-hidden">
                  <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#d4dbd7] bg-[#f8faf9] px-4 py-3">
                    <div>
                      <h3 class="text-base font-semibold text-[#191c1e]">Riwayat Pembelian Barang {{ $group['date'] }}</h3>
                      <p class="text-sm text-[#52615a]">
                        {{ $group['date'] === 'Tanpa Tanggal Beli'
                            ? 'Barang yang belum memiliki tanggal beli dikumpulkan di sini.'
                            : 'Semua barang yang dibeli pada tanggal ini digabung dalam satu riwayat.' }}
                      </p>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="sf-chip">{{ number_format((int) ($group['count'] ?? 0), 0, ',', '.') }} barang</span>
                      <a href="{{ route('admin.suppliers.invoice-recap', ['supplier' => $supplier->id, 'date' => $group['date_value'] ?? '']) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-[#006948] bg-[#006948] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#00563b]">
                        Lihat Nota
                      </a>
                    </div>
                  </div>
                  <div class="overflow-x-auto table-container">
                    <table class="sf-table w-full min-w-[860px] text-left border-collapse">
                      <thead>
                        <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                          <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Invoice Supplier</th>
                          <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Part Number</th>
                          <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Part Name</th>
                          <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Metode</th>
                          <th class="px-4 py-2.5 font-medium uppercase tracking-wider">Qty</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach(($group['items'] ?? []) as $item)
                          <tr class="border-b border-[#e4e8e6]">
                            <td class="px-4 py-2.5 font-semibold">{{ $item['invoice'] }}</td>
                            <td class="px-4 py-2.5 font-semibold">{{ $item['part_number'] }}</td>
                            <td class="px-4 py-2.5">{{ $item['part_name'] }}</td>
                            <td class="px-4 py-2.5">{{ $item['payment_type'] }}</td>
                            <td class="px-4 py-2.5">{{ number_format((int) ($item['qty'] ?? 0), 0, ',', '.') }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              @empty
                <div class="rounded-xl border border-dashed border-[#d4dbd7] bg-[#f8faf9] px-4 py-8 text-center text-[#52615a]">
                  Belum ada riwayat pembelian untuk supplier ini.
                </div>
              @endforelse
            </div>
          </div>
        </main>
      </div>
    </div>

    <script>
      document.documentElement.classList.remove('dark');
      document.documentElement.classList.add('light', 'sf-dashboard-page');
      document.body.classList.add('sf-dashboard-page');
      const supplierPageHeader = document.getElementById('supplierPageHeader');

      function syncSupplierLayoutHeight() {
        if (!supplierPageHeader) {
          return;
        }

        const headerHeight = supplierPageHeader.offsetHeight || 56;
        document.documentElement.style.setProperty('--sf-header-h', `${headerHeight}px`);
      }

      syncSupplierLayoutHeight();
      window.addEventListener('load', syncSupplierLayoutHeight);
      window.addEventListener('resize', syncSupplierLayoutHeight);
      const mobileSidebarBtn = document.getElementById('mobileSidebarBtn');
      const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');
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
      document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) {
          return;
        }

        const openButton = target.closest('[data-modal-open]');
        if (openButton) {
          const targetId = openButton.getAttribute('data-modal-open');
          document.getElementById(targetId)?.classList.add('show');
          return;
        }

        const closeButton = target.closest('[data-modal-close]');
        if (closeButton) {
          closeButton.closest('.sf-modal-backdrop')?.classList.remove('show');
          return;
        }

        const modal = target.classList.contains('sf-modal-backdrop') ? target : null;
        if (modal) {
          modal.classList.remove('show');
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
          return;
        }

        document.querySelectorAll('.sf-modal-backdrop.show').forEach((modal) => {
          modal.classList.remove('show');
        });
      });
    </script>

    <div id="dueDateModal" class="sf-modal-backdrop">
      <div class="sf-modal-card">
        <div class="flex items-center justify-between border-b border-[#d4dbd7] px-5 py-4">
          <div>
            <h3 class="text-lg font-semibold text-[#191c1e]">Barang Jatuh Tempo</h3>
            <p class="text-sm text-[#52615a]">Daftar barang kredit beserta tanggal jatuh temponya.</p>
          </div>
          <button type="button" data-modal-close class="rounded-lg border border-[#d4dbd7] px-3 py-1.5 text-sm text-[#52615a] hover:bg-[#f3f4f6]">Tutup</button>
        </div>
        <div class="sf-modal-body p-5 space-y-4">
          @forelse($dueDateGroups as $group)
            <div class="rounded-xl border border-[#ead1d1] bg-[#fff8f8] p-4">
              <div class="flex items-center justify-between gap-3">
                <h4 class="font-semibold text-[#8a1c1c]">{{ $group['date'] }}</h4>
                <span class="sf-chip">{{ number_format((int) ($group['count'] ?? 0), 0, ',', '.') }} barang</span>
              </div>
              <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[520px] text-sm">
                  <thead>
                    <tr class="text-[#6b7280]">
                      <th class="border-b border-[#ead1d1] px-2 py-2 text-left">Invoice</th>
                      <th class="border-b border-[#ead1d1] px-2 py-2 text-left">Part Number</th>
                      <th class="border-b border-[#ead1d1] px-2 py-2 text-left">Part Name</th>
                      <th class="border-b border-[#ead1d1] px-2 py-2 text-right">Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach(($group['items'] ?? []) as $item)
                      <tr>
                        <td class="border-b border-[#f3dddd] px-2 py-2">{{ $item['invoice'] }}</td>
                        <td class="border-b border-[#f3dddd] px-2 py-2 font-semibold">{{ $item['part_number'] }}</td>
                        <td class="border-b border-[#f3dddd] px-2 py-2">{{ $item['part_name'] }}</td>
                        <td class="border-b border-[#f3dddd] px-2 py-2 text-right">{{ number_format((int) ($item['qty'] ?? 0), 0, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @empty
            <p class="text-sm text-[#52615a]">Belum ada barang kredit dengan tanggal jatuh tempo.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div id="purchaseDateModal" class="sf-modal-backdrop">
      <div class="sf-modal-card" style="width: min(1100px, 100%);">
        <div class="flex items-center justify-between border-b border-[#d4dbd7] px-5 py-4">
          <div>
            <h3 class="text-lg font-semibold text-[#191c1e]">Tanggal Pembelian Barang</h3>
            <p class="text-sm text-[#52615a]">Semua barang supplier ini ditampilkan lengkap bersama tanggal belinya dan nomor invoice supplier.</p>
          </div>
          <button type="button" data-modal-close class="rounded-lg border border-[#d4dbd7] px-3 py-1.5 text-sm text-[#52615a] hover:bg-[#f3f4f6]">Tutup</button>
        </div>
        <div class="sf-modal-body p-5">
          @if(count($purchaseItems) > 0)
            <div class="overflow-x-auto rounded-xl border border-[#d4dbd7] bg-white">
              <table class="w-full min-w-[860px] text-sm">
                <thead>
                  <tr class="bg-[#f4faf7] text-[#52615a]">
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-left font-semibold">Tanggal Beli</th>
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-left font-semibold">Invoice</th>
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-left font-semibold">Part Number</th>
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-left font-semibold">Part Name</th>
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-center font-semibold">Metode</th>
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-right font-semibold">Qty</th>
                    <th class="border-b border-[#d4dbd7] px-3 py-2 text-center font-semibold">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($purchaseItems as $item)
                    <tr class="border-b border-[#edf0ee]">
                      <td class="px-3 py-2">{{ $item['purchase_date'] }}</td>
                      <td class="px-3 py-2">{{ $item['invoice'] }}</td>
                      <td class="px-3 py-2 font-semibold">{{ $item['part_number'] }}</td>
                      <td class="px-3 py-2">{{ $item['part_name'] }}</td>
                      <td class="px-3 py-2 text-center">{{ $item['payment_type'] }}</td>
                      <td class="px-3 py-2 text-right">{{ number_format((int) ($item['qty'] ?? 0), 0, ',', '.') }}</td>
                      <td class="px-3 py-2 text-center">
                        <span class="sf-chip">{{ $item['status'] }}</span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-sm text-[#52615a]">Belum ada data tanggal pembelian barang.</p>
          @endif
        </div>
      </div>
    </div>

    @include('filament.partials.logout-modal')
</x-filament-panels::page>

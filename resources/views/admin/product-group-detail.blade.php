@php
    $purchaseSummary = $purchaseSummary ?? ['count' => 0, 'value' => 'Rp 0', 'lunas' => 0, 'utang' => 0];
    $salesSummary = $salesSummary ?? ['count' => 0, 'value' => 'Rp 0', 'credit' => 0, 'lunas' => 0, 'retur' => 0];
    $monthlyRecapGroups = $monthlyRecapGroups ?? [];
    $purchaseInvoiceMonthGroups = $purchaseInvoiceMonthGroups ?? [];
    $purchaseMonthGroups = $purchaseMonthGroups ?? [];
    $salesMonthGroups = $salesMonthGroups ?? [];
    $purchasePartners = $purchasePartners ?? [];
    $salesPartners = $salesPartners ?? [];
    $latestActivityAt = $latestActivityAt ?? null;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Stok - {{ $product->barcode ?? '-' }}</title>
    <x-brand.meta />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hanken Grotesk', sans-serif; }
        .card-shadow { box-shadow: 0 2px 6px rgba(17, 24, 39, 0.06); }
    </style>
</head>
<body class="bg-[#f4f7f6] text-[#191c1e]">
<main class="mx-auto max-w-7xl p-4 md:p-6">
    <div class="mb-5 rounded-2xl border border-[#d4dbd7] bg-white px-5 py-4 card-shadow">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <x-brand.logo class="mb-3 h-11 w-auto" />
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Detail Stok</h1>
                <p class="mt-1 text-[#52615a]">Pantau riwayat pembelian admin, transaksi kasir, retur, dan nota per part number.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.product-groups.receipt', ['product' => $product->id]) }}" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Print Rekap</a>
                <a href="{{ url('/admin/products') }}" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Kembali ke Daftar Stok</a>
                <a href="{{ url('/admin/admin-module?type=product-groups') }}" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Kelompok Stok</a>
            </div>
        </div>
    </div>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white overflow-hidden card-shadow">
        <div class="border-b border-[#d4dbd7] px-5 py-3 bg-[#f8fbfa]">
            <h2 class="text-lg font-bold text-[#193429]">Informasi Stok</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-5">
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Part Number</p><p class="mt-1 text-2xl font-semibold">{{ $product->barcode ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Nama Stok</p><p class="mt-1 text-2xl font-semibold">{{ $product->name ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Kategori</p><p class="mt-1 text-2xl font-semibold">{{ $product->category?->name ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Merek</p><p class="mt-1 text-2xl font-semibold">{{ $product->brand?->name ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Unit</p><p class="mt-1 text-2xl font-semibold">{{ $product->unit ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Stok Aktif</p><p class="mt-1 text-xl font-semibold">{{ number_format((int) ($product->batches->where('is_active', true)->sum('stock') ?? 0), 0, ',', '.') }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Pembelian Admin</p><p class="mt-1 text-xl font-semibold">{{ number_format((int) ($purchaseSummary['count'] ?? 0), 0, ',', '.') }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Transaksi Admin</p><p class="mt-1 text-xl font-semibold">{{ number_format((int) ($salesSummary['count'] ?? 0), 0, ',', '.') }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Pembelian Lunas</p><p class="mt-1 text-xl font-semibold text-[#0f8a54]">{{ number_format((int) ($purchaseSummary['lunas'] ?? 0), 0, ',', '.') }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Pembelian Utang</p><p class="mt-1 text-xl font-semibold text-[#b42318]">{{ number_format((int) ($purchaseSummary['utang'] ?? 0), 0, ',', '.') }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Retur Transaksi</p><p class="mt-1 text-xl font-semibold text-[#b36a00]">{{ number_format((int) ($salesSummary['retur'] ?? 0), 0, ',', '.') }}</p></div>
            <div class="md:col-span-2"><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Aktivitas Terakhir</p><p class="mt-1 text-xl font-semibold">{{ $latestActivityAt?->format('d M Y H:i') ?? '-' }}</p></div>
            <div class="md:col-span-2"><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Beli Dari</p><p class="mt-1 text-lg font-semibold">{{ count($purchasePartners) > 0 ? implode(', ', $purchasePartners) : '-' }}</p></div>
            <div class="md:col-span-3"><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Jual Ke</p><p class="mt-1 text-lg font-semibold">{{ count($salesPartners) > 0 ? implode(', ', $salesPartners) : '-' }}</p></div>
        </div>
    </section>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white p-5 card-shadow">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Rekap Bulanan Arus Stok</h2>
                <p class="text-sm text-[#52615a]">Rekap per bulan jika ada pembelian atau penjualan, lengkap dengan asal beli dan tujuan jual.</p>
            </div>
            <div class="rounded-xl bg-[#eef7f3] px-4 py-2 text-sm text-[#006948] font-semibold">
                Stok Saat Ini: {{ number_format((int) ($product->batches->where('is_active', true)->sum('stock') ?? 0), 0, ',', '.') }}
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($monthlyRecapGroups as $monthGroup)
                @php
                    $stockNet = (int) ($monthGroup['summary']['stock_net'] ?? 0);
                    $stockNetClass = $stockNet >= 0 ? 'text-[#0f8a54] bg-[#e6fff3]' : 'text-[#b42318] bg-[#feeceb]';
                @endphp
                <div class="rounded-2xl border border-[#d4dbd7] bg-[#fcfdfd] p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Bulan</p>
                            <h3 class="text-2xl font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h3>
                        </div>
                        <div class="flex flex-col items-start gap-2 lg:items-end">
                            <a
                                href="{{ route('admin.product-groups.receipt', ['product' => $product->id, 'month' => $monthGroup['month_key']]) }}"
                                class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#f1f4f2]"
                            >
                                Print Rekap Bulan
                            </a>
                            <div class="flex flex-wrap gap-2 text-sm">
                                <span class="inline-flex items-center rounded-full bg-[#eef7f3] px-3 py-1 font-semibold text-[#006948]">Beli {{ number_format((int) ($monthGroup['summary']['purchase_count'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#eef2ff] px-3 py-1 font-semibold text-[#4648d4]">Jual {{ number_format((int) ($monthGroup['summary']['sales_count'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Masuk {{ number_format((int) ($monthGroup['summary']['stock_in'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#ffefdb] px-3 py-1 font-semibold text-[#b36a00]">Keluar {{ number_format((int) ($monthGroup['summary']['stock_out'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 font-semibold {{ $stockNetClass }}">Netto {{ number_format($stockNet, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl border border-[#dfe7e3] bg-white px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-[#5c6b64]">Nilai Pembelian</p>
                            <p class="mt-1 text-lg font-semibold">{{ $monthGroup['summary']['purchase_value'] ?? 'Rp 0' }}</p>
                        </div>
                        <div class="rounded-xl border border-[#dfe7e3] bg-white px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-[#5c6b64]">Nilai Penjualan</p>
                            <p class="mt-1 text-lg font-semibold">{{ $monthGroup['summary']['sales_value'] ?? 'Rp 0' }}</p>
                        </div>
                        <div class="rounded-xl border border-[#dfe7e3] bg-white px-4 py-3 md:col-span-2">
                            <p class="text-xs uppercase tracking-wide text-[#5c6b64]">Beli Dari</p>
                            <p class="mt-1 text-base font-semibold">{{ $monthGroup['purchase_sources'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl border border-[#dfe7e3] bg-white px-4 py-3 md:col-span-2 xl:col-span-4">
                            <p class="text-xs uppercase tracking-wide text-[#5c6b64]">Jual Ke</p>
                            <p class="mt-1 text-base font-semibold">{{ $monthGroup['sales_targets'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-4 py-6 text-center text-[#52615a]">Belum ada pembelian atau penjualan yang bisa direkap per bulan untuk part number ini.</div>
            @endforelse
        </div>
    </section>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white p-5 card-shadow">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Pembelian Admin</h2>
                <p class="text-sm text-[#52615a]">Riwayat beli barang ini dikelompokkan per bulan dan per invoice supplier, supaya satu invoice yang berisi banyak barang bisa langsung dibuka sebagai satu rekap.</p>
            </div>
            <div class="rounded-xl bg-[#eef7f3] px-4 py-2 text-sm text-[#006948] font-semibold">
                Total: {{ $purchaseSummary['value'] ?? 'Rp 0' }}
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($purchaseInvoiceMonthGroups as $monthGroup)
                <div class="rounded-2xl border border-[#d4dbd7] bg-white overflow-hidden">
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Bulan</p>
                                <h3 class="text-2xl font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h3>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm">
                                <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 font-semibold text-[#006948]">Invoice {{ number_format((int) ($monthGroup['summary']['invoice_count'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#ececff] px-3 py-1 font-semibold text-[#4648d4]">Baris {{ number_format((int) ($monthGroup['summary']['items_count'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Qty {{ number_format((int) ($monthGroup['summary']['qty_total'] ?? 0), 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#ffefdb] px-3 py-1 font-semibold text-[#b36a00]">Nilai {{ $monthGroup['summary']['total_value'] ?? 'Rp 0' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-[#d4dbd7] overflow-x-auto">
                        <table class="min-w-[1080px] w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">No. Invoice Supplier</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Supplier</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Jumlah Barang</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Total Qty</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Total</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Rekap Invoice</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e4e8e6]">
                                @forelse ($monthGroup['invoice_groups'] as $row)
                                    <tr class="hover:bg-[#f6f8f7] transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['purchase_date'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap font-semibold">{{ $row['supplier_invoice_number'] }}</td>
                                        <td class="px-5 py-4 font-semibold whitespace-nowrap">{{ $row['supplier'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) ($row['items_count'] ?? 0), 0, ',', '.') }} barang</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) ($row['qty_total'] ?? 0), 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 font-semibold whitespace-nowrap">{{ $row['total'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @php
                                                $status = $row['status'] ?? 'LUNAS';
                                                $statusClass = $status === 'LUNAS'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : ($status === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                                            @endphp
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            @if(! empty($row['supplier_id']))
                                                <a
                                                    href="{{ route('admin.suppliers.invoice-recap', ['supplier' => $row['supplier_id'], 'group' => $row['invoice_group_key']]) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]"
                                                >
                                                    Lihat Rekap
                                                </a>
                                            @else
                                                <span class="text-[#52615a]">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-5 py-6 text-center text-[#52615a]">Belum ada rekap invoice pembelian admin pada bulan ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-4 py-6 text-center text-[#52615a]">Belum ada invoice pembelian admin untuk barang ini.</div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-[#d4dbd7] bg-white p-5 card-shadow">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Transaksi Admin</h2>
                <p class="text-sm text-[#52615a]">Riwayat invoice penjualan yang memuat barang ini. Nota dibuka per invoice penuh, bukan subtotal per barang.</p>
            </div>
            <div class="rounded-xl bg-[#eef7f3] px-4 py-2 text-sm text-[#006948] font-semibold">
                Total: {{ $salesSummary['value'] ?? 'Rp 0' }}
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($salesMonthGroups as $monthGroup)
                <div class="rounded-2xl border border-[#d4dbd7] bg-white overflow-hidden">
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.2em] text-[#52615a]">Bulan</p>
                                <h3 class="text-2xl font-semibold text-[#191c1e]">{{ $monthGroup['month_label'] }}</h3>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm">
                                <span class="inline-flex items-center rounded-full bg-[#e6fff3] px-3 py-1 font-semibold text-[#006948]">Transaksi {{ number_format((int) $monthGroup['summary']['count'], 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#ececff] px-3 py-1 font-semibold text-[#4648d4]">Nilai {{ $monthGroup['summary']['value'] }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#fff4dd] px-3 py-1 font-semibold text-[#a36700]">Lunas {{ number_format((int) $monthGroup['summary']['lunas'], 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#ffefdb] px-3 py-1 font-semibold text-[#b36a00]">Kredit {{ number_format((int) $monthGroup['summary']['credit'], 0, ',', '.') }}</span>
                                <span class="inline-flex items-center rounded-full bg-[#f4fbf7] px-3 py-1 font-semibold text-[#006948]">Retur {{ number_format((int) $monthGroup['summary']['retur'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-[#d4dbd7] overflow-x-auto">
                        <table class="min-w-[1180px] w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Invoice</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Customer / PT</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Site / PO</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Admin</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Barang di Invoice</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Qty Part Ini</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Qty Invoice</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Total Invoice</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Metode</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Kredit</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Retur</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Invoice</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e4e8e6]">
                                @forelse ($monthGroup['rows'] as $row)
                                    <tr class="hover:bg-[#f6f8f7] transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['tanggal'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['invoice'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap font-semibold">{{ $row['customer'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div>{{ $row['site_name'] ?? '-' }}</div>
                                            <div class="text-xs text-[#52615a]">PO: {{ $row['po_number'] ?? '-' }}</div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['cashier'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) ($row['invoice_items_count'] ?? 0), 0, ',', '.') }} barang</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) ($row['part_qty'] ?? 0), 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) ($row['qty'] ?? 0), 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap font-semibold">{{ $row['total'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['payment_method'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['kredit'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if(($row['return_count'] ?? 0) > 0)
                                                <span class="inline-flex rounded-full bg-[#eef7f3] px-3 py-1 text-xs font-semibold text-[#006948]">Retur {{ number_format((int) $row['return_count'], 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-[#52615a]">-</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['jatuh_tempo'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @php
                                                $status = $row['status'] ?? 'LUNAS';
                                                $statusClass = $status === 'LUNAS'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : ($status === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                                            @endphp
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <a href="{{ route('admin.sales.receipt', ['sale' => $row['sale_id']]) }}" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Lihat Invoice</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="16" class="px-5 py-6 text-center text-[#52615a]">Belum ada riwayat penjualan kasir pada bulan ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-4 py-6 text-center text-[#52615a]">Belum ada riwayat penjualan kasir untuk barang ini.</div>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>

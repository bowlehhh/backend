@php
    $purchaseSummary = $purchaseSummary ?? ['count' => 0, 'value' => 'Rp 0', 'lunas' => 0, 'utang' => 0];
    $salesSummary = $salesSummary ?? ['count' => 0, 'value' => 'Rp 0', 'credit' => 0, 'lunas' => 0, 'retur' => 0];
    $purchaseMonthGroups = $purchaseMonthGroups ?? [];
    $salesMonthGroups = $salesMonthGroups ?? [];
    $latestActivityAt = $latestActivityAt ?? null;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Barang - {{ $product->barcode ?? '-' }}</title>
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
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Detail Barang</h1>
                <p class="mt-1 text-[#52615a]">Pantau riwayat pembelian admin, transaksi kasir, retur, dan nota per part number.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.product-groups.receipt', ['product' => $product->id]) }}" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Print Nota</a>
                <a href="{{ url('/admin/products') }}" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Kembali ke Barang</a>
                <a href="{{ url('/admin/admin-module?type=product-groups') }}" class="rounded-xl border border-[#bccac0] bg-white px-4 py-2 text-sm font-semibold text-[#006948] hover:bg-[#eef7f3]">Kelompok Barang</a>
            </div>
        </div>
    </div>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white overflow-hidden card-shadow">
        <div class="border-b border-[#d4dbd7] px-5 py-3 bg-[#f8fbfa]">
            <h2 class="text-lg font-bold text-[#193429]">Informasi Barang</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-5">
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Part Number</p><p class="mt-1 text-2xl font-semibold">{{ $product->barcode ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-[#5c6b64]">Nama Barang</p><p class="mt-1 text-2xl font-semibold">{{ $product->name ?? '-' }}</p></div>
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
        </div>
    </section>

    <section class="mb-4 rounded-2xl border border-[#d4dbd7] bg-white p-5 card-shadow">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Pembelian Admin</h2>
                <p class="text-sm text-[#52615a]">Riwayat admin beli barang ini dari supplier, dikelompokkan per bulan.</p>
            </div>
            <div class="rounded-xl bg-[#eef7f3] px-4 py-2 text-sm text-[#006948] font-semibold">
                Total: {{ $purchaseSummary['value'] ?? 'Rp 0' }}
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($purchaseMonthGroups as $monthGroup)
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
                                <span class="inline-flex items-center rounded-full bg-[#ffefdb] px-3 py-1 font-semibold text-[#b36a00]">Utang {{ number_format((int) $monthGroup['summary']['utang'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                            <div class="border-t border-[#d4dbd7] overflow-x-auto">
                        <table class="min-w-[1240px] w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#eceef0] text-[#3d4a42] border-b border-[#d4dbd7]">
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Tanggal</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Supplier</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Diproses Oleh</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Kondisi</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Qty</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Harga Beli</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Total</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">DP</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Sisa</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Nota</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e4e8e6]">
                                @forelse ($monthGroup['rows'] as $row)
                                    <tr class="hover:bg-[#f6f8f7] transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['tanggal'] }}</td>
                                        <td class="px-5 py-4 font-semibold whitespace-nowrap">{{ $row['supplier'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['processed_by'] ?? '-' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['condition'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) $row['qty'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['harga_beli'] }}</td>
                                        <td class="px-5 py-4 font-semibold whitespace-nowrap">{{ $row['total'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['down_payment'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['sisa_kredit'] }}</td>
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
                                            <a href="{{ route('admin.credits.receipt', ['batch' => $row['batch_id']]) }}" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Print Nota</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12" class="px-5 py-6 text-center text-[#52615a]">Belum ada riwayat pembelian admin pada bulan ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-[#d4dbd7] bg-white px-4 py-6 text-center text-[#52615a]">Belum ada riwayat pembelian admin untuk barang ini.</div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-[#d4dbd7] bg-white p-5 card-shadow">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Transaksi Admin</h2>
                <p class="text-sm text-[#52615a]">Riwayat barang ini terjual ke siapa di kasir, termasuk retur jika ada.</p>
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
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Admin</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Qty</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Subtotal</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Metode</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Kredit</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Retur</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Jatuh Tempo</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-4 font-medium uppercase tracking-wider text-right">Nota</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e4e8e6]">
                                @forelse ($monthGroup['rows'] as $row)
                                    <tr class="hover:bg-[#f6f8f7] transition-colors">
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['tanggal'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['invoice'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap font-semibold">{{ $row['customer'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ $row['cashier'] }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap">{{ number_format((int) $row['qty'], 0, ',', '.') }}</td>
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
                                            <a href="{{ route('admin.sales.receipt', ['sale' => $row['sale_id']]) }}" class="inline-flex items-center rounded-lg border border-[#bccac0] bg-white px-3 py-1.5 text-sm text-[#006948] hover:bg-[#f1f4f2]">Lihat Nota</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12" class="px-5 py-6 text-center text-[#52615a]">Belum ada riwayat penjualan kasir pada bulan ini.</td></tr>
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

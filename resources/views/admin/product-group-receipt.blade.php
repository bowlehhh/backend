@php
    $formatNotaDate = function ($value, bool $withTime = true): string {
        if (empty($value)) {
            return '-';
        }

        $date = $value instanceof \Carbon\CarbonInterface
            ? $value
            : \Carbon\Carbon::parse((string) $value);

        return $withTime
            ? $date->locale('id')->translatedFormat('d M Y H:i l')
            : $date->locale('id')->translatedFormat('d M Y l');
    };

    $storeName = config('app.name', 'Surya Duta Multindo');
    $partNumber = strtoupper((string) ($product->barcode ?? '-'));
    $partName = strtoupper((string) ($product->name ?? '-'));
    $categoryName = strtoupper((string) ($product->category?->name ?? '-'));
    $brandName = strtoupper((string) ($product->brand?->name ?? '-'));
    $unitName = strtoupper((string) ($product->unit ?? '-'));
    $activeStock = (int) ($product->batches->where('is_active', true)->sum('stock') ?? 0);
    $purchaseSummary = $purchaseSummary ?? ['count' => 0, 'value' => 'Rp 0', 'lunas' => 0, 'utang' => 0];
    $salesSummary = $salesSummary ?? ['count' => 0, 'value' => 'Rp 0', 'credit' => 0, 'lunas' => 0, 'retur' => 0];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Detail Stok - {{ $product->barcode ?? '-' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            color-scheme: light;
            --ink: #102033;
            --muted: #667581;
            --line: #cfd7df;
            --soft: #f5f8fb;
            --paper: #ffffff;
            --brand: #0b7a5a;
            --brand-soft: #e8f7f1;
            --blue: #5b5cf6;
            --amber: #c07a00;
            --orange: #c56d0e;
            --red: #d4332a;
        }

        @page {
            size: A4 landscape;
            margin: 3mm;
        }

        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body {
            margin: 0;
            padding: 0;
            background: #eef3f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            width: 100%;
            overflow-x: hidden;
        }

        .paper {
            max-width: 280mm;
            max-width: calc(100vw - 16px);
            margin: 8px auto;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(16, 32, 51, 0.06);
            overflow: hidden;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            padding: 18px 20px 16px;
            border-bottom: 1px solid var(--line);
            background:
                linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%);
        }

        .brand-block {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .store-name {
            margin: 0;
            font-size: 29px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .doc-title {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--brand);
        }

        .doc-sub {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.45;
        }

        .hero-note {
            margin-top: 2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 6px 10px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .doc-meta {
            text-align: right;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.5;
            color: var(--ink);
            white-space: nowrap;
        }

        .doc-meta .muted {
            color: var(--muted);
            font-weight: 700;
        }

        .meta-table,
        .summary-table {
            width: calc(100% - 40px);
            margin: 12px 20px 0;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid var(--line);
            background: #fff;
        }

        .meta-table td,
        .summary-table td {
            border: 1px solid var(--line);
            padding: 8px 10px;
            vertical-align: top;
        }

        .meta-table .label,
        .summary-table .label {
            display: block;
        }

        .meta-table .value {
            margin-top: 5px;
            font-size: 16px;
        }

        .meta-table .muted {
            margin-top: 4px;
        }

        .summary-table {
            margin-top: 10px;
        }

        .summary-table .value {
            margin-top: 4px;
            font-size: 20px;
        }

        .summary-table .muted {
            margin-top: 4px;
        }

        .label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .value {
            margin-top: 6px;
            font-size: 18px;
            font-weight: 900;
            line-height: 1.18;
            word-break: break-word;
        }

        .value.small {
            font-size: 15px;
        }

        .muted {
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.45;
        }

        .value.brand { color: var(--brand); }
        .value.blue { color: var(--blue); }
        .value.amber { color: var(--amber); }
        .value.red { color: var(--red); }

        .report-section {
            margin: 12px 20px 0;
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }

        .section-head {
            padding: 14px 16px 12px;
            background: linear-gradient(180deg, #fafcff 0%, #f4f7fa 100%);
            border-bottom: 1px solid var(--line);
        }

        .section-title {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .section-desc {
            margin: 6px 0 0;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.45;
        }

        .month-block + .month-block {
            border-top: 1px solid var(--line);
        }

        .month-block {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .month-title {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            padding: 12px 14px;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }

        .month-label {
            margin: 0;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--muted);
        }

        .month-name {
            margin: 2px 0 0;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
            background: #fff;
            color: var(--ink);
        }

        .chip.green { background: var(--brand-soft); color: var(--brand); }
        .chip.blue { background: #eef0ff; color: var(--blue); }
        .chip.amber { background: #fff6e7; color: var(--amber); }
        .chip.orange { background: #fff1df; color: var(--orange); }
        .chip.red { background: #ffecec; color: var(--red); }

        .table-wrap {
            overflow-x: auto;
            background: #fff;
        }

        table {
            width: 100%;
            min-width: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border-top: 1px solid var(--line);
            padding: 8px 8px;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f8fafc;
            color: #42515f;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.04em;
        }

        tbody tr:nth-child(even) td {
            background: #fcfdff;
        }

        td strong {
            color: var(--ink);
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .empty-state {
            padding: 22px 18px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }

        .actions {
            margin: 14px 20px 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--brand);
            color: var(--brand);
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 800;
            background: #fff;
            min-height: 40px;
        }

        .btn.primary {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 8px 18px rgba(11, 122, 90, 0.18);
        }

        .btn.secondary {
            background: var(--brand-soft);
        }

        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .paper {
                margin: 0;
                max-width: none;
                border: 1px solid var(--line);
                border-radius: 0;
                box-shadow: none;
                padding: 0;
                page-break-after: avoid;
            }

            .topbar {
                padding: 8px 10px;
                page-break-inside: avoid;
            }

            .store-name {
                font-size: 18px;
                margin: 0;
            }

            .doc-title {
                font-size: 10px;
            }

            .doc-sub {
                font-size: 9px;
            }

            .hero-note {
                display: none;
            }

            .doc-meta {
                font-size: 9px;
                line-height: 1.3;
            }

            .meta-table,
            .summary-table {
                width: calc(100% - 20px);
                margin: 6px 10px 0;
                page-break-inside: avoid;
            }

            .meta-table td,
            .summary-table td {
                padding: 2px 3px;
                font-size: 8px;
                border: 1px solid var(--line);
            }

            .label {
                font-size: 8px;
                background: #f3f4f6;
            }

            .value {
                font-size: 10px;
                margin-top: 2px;
            }

            .value.small {
                font-size: 9px;
            }

            .report-section {
                margin: 6px 10px 0;
                border-radius: 0;
                page-break-inside: avoid;
            }

            .section-head {
                padding: 6px 10px;
                page-break-inside: avoid;
            }

            .section-title {
                font-size: 11px;
            }

            .section-desc {
                font-size: 8px;
                margin-top: 2px;
            }

            .month-title {
                padding: 6px 10px;
                page-break-inside: avoid;
            }

            .month-label {
                font-size: 8px;
            }

            .month-name {
                font-size: 11px;
            }

            .chip {
                padding: 2px 5px;
                font-size: 8px;
            }

            th, td {
                padding: 2px 3px;
                font-size: 8px;
                border: 1px solid var(--line);
            }

            th {
                font-size: 8px;
                background: #e5e7eb;
                font-weight: 700;
            }

            .actions {
                display: none !important;
            }

            .report-section,
            .meta-table,
            .summary-table,
            .section-head,
            .month-title,
            .topbar {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .table-wrap {
                overflow: visible;
            }

            table {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="paper">
        <div class="topbar">
            <div class="brand-block">
                <h1 class="store-name">{{ strtoupper($storeName) }}</h1>
                <p class="doc-title">Nota Detail Stok / Part Number</p>
                <p class="doc-sub">
                    Part Number: <strong>{{ $partNumber }}</strong> &middot; Nama Barang: <strong>{{ $partName }}</strong>
                </p>
                <div class="hero-note">
                    Ringkasan pembelian admin, transaksi kasir, dan status per bulan.
                </div>
            </div>
            <div class="doc-meta">
                <div><span class="muted">Tanggal Cetak:</span> {{ $formatNotaDate($printedAt) }}</div>
                <div><span class="muted">Kategori:</span> {{ $categoryName }}</div>
                <div><span class="muted">Merek:</span> {{ $brandName }}</div>
            </div>
        </div>

        <table class="meta-table">
            <tr>
                <td>
                    <span class="label">Part Number</span>
                    <div class="value">{{ $partNumber }}</div>
                    <div class="muted">{{ $partName }}</div>
                </td>
                <td>
                    <span class="label">Nama Barang</span>
                    <div class="value">{{ $partName }}</div>
                </td>
                <td>
                    <span class="label">Kategori</span>
                    <div class="value">{{ $categoryName }}</div>
                </td>
                <td>
                    <span class="label">Merek</span>
                    <div class="value">{{ $brandName }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Unit</span>
                    <div class="value">{{ $unitName }}</div>
                </td>
                <td>
                    <span class="label">Stok Aktif</span>
                    <div class="value">{{ number_format($activeStock, 0, ',', '.') }}</div>
                </td>
                <td colspan="2">
                    <span class="label">Aktivitas Terakhir</span>
                    <div class="value small">{{ $formatNotaDate($latestActivityAt) }}</div>
                </td>
            </tr>
        </table>

        <table class="summary-table">
            <tr>
                <td>
                    <span class="label">Pembelian Admin</span>
                    <div class="value brand">{{ number_format((int) $purchaseSummary['count'], 0, ',', '.') }}</div>
                    <div class="muted">{{ $purchaseSummary['value'] }} total nilai beli</div>
                </td>
                <td>
                    <span class="label">Transaksi Admin</span>
                    <div class="value blue">{{ number_format((int) $salesSummary['count'], 0, ',', '.') }}</div>
                    <div class="muted">{{ $salesSummary['value'] }} total nilai jual</div>
                </td>
                <td>
                    <span class="label">Lunas / Kredit</span>
                    <div class="value small amber">
                        {{ number_format((int) ($purchaseSummary['lunas'] + $salesSummary['lunas']), 0, ',', '.') }}
                        /
                        {{ number_format((int) ($purchaseSummary['utang'] + $salesSummary['credit']), 0, ',', '.') }}
                    </div>
                    <div class="muted">Pembelian lunas dan transaksi kredit</div>
                </td>
                <td>
                    <span class="label">Retur Transaksi</span>
                    <div class="value red">{{ number_format((int) $salesSummary['retur'], 0, ',', '.') }}</div>
                    <div class="muted">Retur yang tercatat pada barang ini</div>
                </td>
            </tr>
        </table>

        <div class="report-section">
            <div class="section-head">
                <h2 class="section-title">Pembelian Admin</h2>
                <p class="section-desc">Riwayat pembelian barang ini dari supplier, dikelompokkan per bulan.</p>
            </div>
            <div>
                @forelse ($purchaseMonthGroups as $monthGroup)
                    <div class="month-block">
                        <div class="month-title">
                            <div>
                                <p class="month-label">Bulan</p>
                                <h3 class="month-name">{{ $monthGroup['month_label'] }}</h3>
                            </div>
                            <div class="chip-row">
                                <span class="chip green">Transaksi {{ number_format((int) $monthGroup['summary']['count'], 0, ',', '.') }}</span>
                                <span class="chip blue">Nilai {{ $monthGroup['summary']['value'] }}</span>
                                <span class="chip amber">Lunas {{ number_format((int) $monthGroup['summary']['lunas'], 0, ',', '.') }}</span>
                                <span class="chip orange">Utang {{ number_format((int) $monthGroup['summary']['utang'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Supplier</th>
                                        <th>Diproses Oleh</th>
                                        <th>Kondisi</th>
                                        <th class="center">Qty</th>
                                        <th class="num">Harga Beli</th>
                                        <th class="num">Total</th>
                                        <th class="num">DP</th>
                                        <th class="num">Sisa</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monthGroup['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['tanggal'] }}</td>
                                            <td><strong>{{ $row['supplier'] }}</strong></td>
                                            <td>{{ $row['processed_by'] ?? '-' }}</td>
                                            <td>{{ $row['condition'] }}</td>
                                            <td class="center">{{ number_format((int) $row['qty'], 0, ',', '.') }}</td>
                                            <td class="num">{{ $row['harga_beli'] }}</td>
                                            <td class="num"><strong>{{ $row['total'] }}</strong></td>
                                            <td class="num">{{ $row['down_payment'] }}</td>
                                            <td class="num">{{ $row['sisa_kredit'] }}</td>
                                            <td>{{ $row['jatuh_tempo'] }}</td>
                                            <td><strong>{{ $row['status'] }}</strong></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="11" class="empty-state">Belum ada riwayat pembelian admin pada bulan ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada riwayat pembelian admin untuk barang ini.</div>
                @endforelse
            </div>
        </div>

        <div class="report-section">
            <div class="section-head">
                <h2 class="section-title">Transaksi Admin</h2>
                <p class="section-desc">Riwayat barang ini terjual ke siapa di kasir, termasuk retur jika ada.</p>
            </div>
            <div>
                @forelse ($salesMonthGroups as $monthGroup)
                    <div class="month-block">
                        <div class="month-title">
                            <div>
                                <p class="month-label">Bulan</p>
                                <h3 class="month-name">{{ $monthGroup['month_label'] }}</h3>
                            </div>
                            <div class="chip-row">
                                <span class="chip green">Transaksi {{ number_format((int) $monthGroup['summary']['count'], 0, ',', '.') }}</span>
                                <span class="chip blue">Nilai {{ $monthGroup['summary']['value'] }}</span>
                                <span class="chip amber">Lunas {{ number_format((int) $monthGroup['summary']['lunas'], 0, ',', '.') }}</span>
                                <span class="chip orange">Kredit {{ number_format((int) $monthGroup['summary']['credit'], 0, ',', '.') }}</span>
                                <span class="chip red">Retur {{ number_format((int) $monthGroup['summary']['retur'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Invoice</th>
                                        <th>Customer / PT</th>
                                        <th>Admin</th>
                                        <th class="center">Qty</th>
                                        <th class="num">Subtotal</th>
                                        <th>Metode</th>
                                        <th class="num">Kredit</th>
                                        <th class="center">Retur</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monthGroup['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['tanggal'] }}</td>
                                            <td>{{ $row['invoice'] }}</td>
                                            <td><strong>{{ $row['customer'] }}</strong></td>
                                            <td>{{ $row['cashier'] }}</td>
                                            <td class="center">{{ number_format((int) $row['qty'], 0, ',', '.') }}</td>
                                            <td class="num"><strong>{{ $row['total'] }}</strong></td>
                                            <td>{{ $row['payment_method'] }}</td>
                                            <td class="num">{{ $row['kredit'] }}</td>
                                            <td class="center">
                                                @if(($row['return_count'] ?? 0) > 0)
                                                    Retur {{ number_format((int) $row['return_count'], 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $row['jatuh_tempo'] }}</td>
                                            <td><strong>{{ $row['status'] }}</strong></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="11" class="empty-state">Belum ada riwayat penjualan kasir pada bulan ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada riwayat penjualan kasir untuk barang ini.</div>
                @endforelse
            </div>
        </div>

        @if(! ($pdf ?? false))
            <div class="actions">
                <button type="button" class="btn primary" onclick="window.print()">Print Nota</button>
                <a class="btn secondary" href="{{ route('admin.product-groups.show', ['product' => $product->id]) }}">Kembali ke Detail</a>
                <a class="btn secondary" href="{{ url('/admin/products') }}">Kembali ke Daftar Stok</a>
            </div>
        @endif
    </div>
</body>
</html>

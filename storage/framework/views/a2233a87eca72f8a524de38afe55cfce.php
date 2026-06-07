<?php
    $storeName = config('app.name', 'Toko Pak Paul');
    $partNumber = strtoupper((string) ($product->barcode ?? '-'));
    $partName = strtoupper((string) ($product->name ?? '-'));
    $categoryName = strtoupper((string) ($product->category?->name ?? '-'));
    $brandName = strtoupper((string) ($product->brand?->name ?? '-'));
    $unitName = strtoupper((string) ($product->unit ?? '-'));
    $activeStock = (int) ($product->batches->where('is_active', true)->sum('stock') ?? 0);
    $purchaseSummary = $purchaseSummary ?? ['count' => 0, 'value' => 'Rp 0', 'lunas' => 0, 'utang' => 0];
    $salesSummary = $salesSummary ?? ['count' => 0, 'value' => 'Rp 0', 'credit' => 0, 'lunas' => 0, 'retur' => 0];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Detail Barang - <?php echo e($product->barcode ?? '-'); ?></title>
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
            margin: 5mm;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef3f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .paper {
            max-width: 1260px;
            margin: 14px auto;
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
            }

            .paper {
                margin: 0;
                max-width: none;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .topbar {
                padding: 10px 12px 8px;
            }

            .store-name {
                font-size: 24px;
            }

            .doc-title {
                font-size: 11px;
            }

            .doc-sub {
                font-size: 10px;
            }

            .hero-note {
                display: none;
            }

            .doc-meta {
                font-size: 10px;
                line-height: 1.4;
            }

            .meta-table,
            .summary-table {
                width: calc(100% - 24px);
                margin: 8px 12px 0;
            }

            .meta-table td,
            .summary-table td {
                padding: 6px 8px;
            }

            .label {
                font-size: 9px;
            }

            .value {
                font-size: 14px;
                margin-top: 4px;
            }

            .value.small {
                font-size: 12px;
            }

            .report-section {
                margin: 10px 12px 0;
                border-radius: 10px;
            }

            .section-head {
                padding: 10px 12px 8px;
            }

            .section-title {
                font-size: 15px;
            }

            .section-desc {
                font-size: 10px;
                margin-top: 4px;
            }

            .month-title {
                padding: 10px 12px;
            }

            .month-label {
                font-size: 9px;
            }

            .month-name {
                font-size: 15px;
            }

            .chip {
                padding: 4px 8px;
                font-size: 9px;
            }

            th, td {
                padding: 6px 7px;
                font-size: 9px;
            }

            th {
                font-size: 9px;
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
                <h1 class="store-name"><?php echo e(strtoupper($storeName)); ?></h1>
                <p class="doc-title">Nota Detail Barang / Part Number</p>
                <p class="doc-sub">
                    Part Number: <strong><?php echo e($partNumber); ?></strong> &middot; Nama Barang: <strong><?php echo e($partName); ?></strong>
                </p>
                <div class="hero-note">
                    Ringkasan pembelian admin, transaksi kasir, dan status per bulan.
                </div>
            </div>
            <div class="doc-meta">
                <div><span class="muted">Tanggal Cetak:</span> <?php echo e($printedAt->format('d M Y H:i')); ?></div>
                <div><span class="muted">Kategori:</span> <?php echo e($categoryName); ?></div>
                <div><span class="muted">Merek:</span> <?php echo e($brandName); ?></div>
            </div>
        </div>

        <table class="meta-table">
            <tr>
                <td>
                    <span class="label">Part Number</span>
                    <div class="value"><?php echo e($partNumber); ?></div>
                    <div class="muted"><?php echo e($partName); ?></div>
                </td>
                <td>
                    <span class="label">Nama Barang</span>
                    <div class="value"><?php echo e($partName); ?></div>
                </td>
                <td>
                    <span class="label">Kategori</span>
                    <div class="value"><?php echo e($categoryName); ?></div>
                </td>
                <td>
                    <span class="label">Merek</span>
                    <div class="value"><?php echo e($brandName); ?></div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Unit</span>
                    <div class="value"><?php echo e($unitName); ?></div>
                </td>
                <td>
                    <span class="label">Stok Aktif</span>
                    <div class="value"><?php echo e(number_format($activeStock, 0, ',', '.')); ?></div>
                </td>
                <td colspan="2">
                    <span class="label">Aktivitas Terakhir</span>
                    <div class="value small"><?php echo e($latestActivityAt?->format('d M Y H:i') ?? '-'); ?></div>
                </td>
            </tr>
        </table>

        <table class="summary-table">
            <tr>
                <td>
                    <span class="label">Pembelian Admin</span>
                    <div class="value brand"><?php echo e(number_format((int) $purchaseSummary['count'], 0, ',', '.')); ?></div>
                    <div class="muted"><?php echo e($purchaseSummary['value']); ?> total nilai beli</div>
                </td>
                <td>
                    <span class="label">Transaksi Kasir</span>
                    <div class="value blue"><?php echo e(number_format((int) $salesSummary['count'], 0, ',', '.')); ?></div>
                    <div class="muted"><?php echo e($salesSummary['value']); ?> total nilai jual</div>
                </td>
                <td>
                    <span class="label">Lunas / Kredit</span>
                    <div class="value small amber">
                        <?php echo e(number_format((int) ($purchaseSummary['lunas'] + $salesSummary['lunas']), 0, ',', '.')); ?>

                        /
                        <?php echo e(number_format((int) ($purchaseSummary['utang'] + $salesSummary['credit']), 0, ',', '.')); ?>

                    </div>
                    <div class="muted">Pembelian lunas dan transaksi kredit</div>
                </td>
                <td>
                    <span class="label">Retur Kasir</span>
                    <div class="value red"><?php echo e(number_format((int) $salesSummary['retur'], 0, ',', '.')); ?></div>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $purchaseMonthGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="month-block">
                        <div class="month-title">
                            <div>
                                <p class="month-label">Bulan</p>
                                <h3 class="month-name"><?php echo e($monthGroup['month_label']); ?></h3>
                            </div>
                            <div class="chip-row">
                                <span class="chip green">Transaksi <?php echo e(number_format((int) $monthGroup['summary']['count'], 0, ',', '.')); ?></span>
                                <span class="chip blue">Nilai <?php echo e($monthGroup['summary']['value']); ?></span>
                                <span class="chip amber">Lunas <?php echo e(number_format((int) $monthGroup['summary']['lunas'], 0, ',', '.')); ?></span>
                                <span class="chip orange">Utang <?php echo e(number_format((int) $monthGroup['summary']['utang'], 0, ',', '.')); ?></span>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $monthGroup['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr>
                                            <td><?php echo e($row['tanggal']); ?></td>
                                            <td><strong><?php echo e($row['supplier']); ?></strong></td>
                                            <td><?php echo e($row['processed_by'] ?? '-'); ?></td>
                                            <td><?php echo e($row['condition']); ?></td>
                                            <td class="center"><?php echo e(number_format((int) $row['qty'], 0, ',', '.')); ?></td>
                                            <td class="num"><?php echo e($row['harga_beli']); ?></td>
                                            <td class="num"><strong><?php echo e($row['total']); ?></strong></td>
                                            <td class="num"><?php echo e($row['down_payment']); ?></td>
                                            <td class="num"><?php echo e($row['sisa_kredit']); ?></td>
                                            <td><?php echo e($row['jatuh_tempo']); ?></td>
                                            <td><strong><?php echo e($row['status']); ?></strong></td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr><td colspan="11" class="empty-state">Belum ada riwayat pembelian admin pada bulan ini.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="empty-state">Belum ada riwayat pembelian admin untuk barang ini.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="report-section">
            <div class="section-head">
                <h2 class="section-title">Transaksi Kasir</h2>
                <p class="section-desc">Riwayat barang ini terjual ke siapa di kasir, termasuk retur jika ada.</p>
            </div>
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $salesMonthGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="month-block">
                        <div class="month-title">
                            <div>
                                <p class="month-label">Bulan</p>
                                <h3 class="month-name"><?php echo e($monthGroup['month_label']); ?></h3>
                            </div>
                            <div class="chip-row">
                                <span class="chip green">Transaksi <?php echo e(number_format((int) $monthGroup['summary']['count'], 0, ',', '.')); ?></span>
                                <span class="chip blue">Nilai <?php echo e($monthGroup['summary']['value']); ?></span>
                                <span class="chip amber">Lunas <?php echo e(number_format((int) $monthGroup['summary']['lunas'], 0, ',', '.')); ?></span>
                                <span class="chip orange">Kredit <?php echo e(number_format((int) $monthGroup['summary']['credit'], 0, ',', '.')); ?></span>
                                <span class="chip red">Retur <?php echo e(number_format((int) $monthGroup['summary']['retur'], 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Invoice</th>
                                        <th>Customer / PT</th>
                                        <th>Kasir</th>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $monthGroup['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr>
                                            <td><?php echo e($row['tanggal']); ?></td>
                                            <td><?php echo e($row['invoice']); ?></td>
                                            <td><strong><?php echo e($row['customer']); ?></strong></td>
                                            <td><?php echo e($row['cashier']); ?></td>
                                            <td class="center"><?php echo e(number_format((int) $row['qty'], 0, ',', '.')); ?></td>
                                            <td class="num"><strong><?php echo e($row['total']); ?></strong></td>
                                            <td><?php echo e($row['payment_method']); ?></td>
                                            <td class="num"><?php echo e($row['kredit']); ?></td>
                                            <td class="center">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($row['return_count'] ?? 0) > 0): ?>
                                                    Retur <?php echo e(number_format((int) $row['return_count'], 0, ',', '.')); ?>

                                                <?php else: ?>
                                                    -
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td><?php echo e($row['jatuh_tempo']); ?></td>
                                            <td><strong><?php echo e($row['status']); ?></strong></td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr><td colspan="11" class="empty-state">Belum ada riwayat penjualan kasir pada bulan ini.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="empty-state">Belum ada riwayat penjualan kasir untuk barang ini.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! ($pdf ?? false)): ?>
            <div class="actions">
                <button type="button" class="btn primary" onclick="window.print()">Print Nota</button>
                <a class="btn secondary" href="<?php echo e(route('admin.product-groups.show', ['product' => $product->id])); ?>">Kembali ke Detail</a>
                <a class="btn secondary" href="<?php echo e(url('/admin/products')); ?>">Kembali ke Barang</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\backend\resources\views/admin/product-group-receipt.blade.php ENDPATH**/ ?>
@php
    $formatNotaDate = function ($value, bool $withTime = true): string {
        if (empty($value)) {
            return '-';
        }

        $date = $value instanceof \Carbon\CarbonInterface
            ? $value
            : \Carbon\Carbon::parse((string) $value);

        return $withTime
            ? $date->locale('id')->translatedFormat('d M Y H:i')
            : $date->locale('id')->translatedFormat('d M Y');
    };

    $purchaseStart = $summary['purchase_date_start'] ?? null;
    $purchaseEnd = $summary['purchase_date_end'] ?? null;
    $purchaseDateLabel = '-';

    if ($purchaseStart && $purchaseEnd) {
        $purchaseDateLabel = $purchaseStart->isSameDay($purchaseEnd)
            ? $formatNotaDate($purchaseStart, false)
            : $formatNotaDate($purchaseStart, false) . ' - ' . $formatNotaDate($purchaseEnd, false);
    }

    $showCreditColumns = (bool) ($summary['show_credit_columns'] ?? false);
    $showExpeditionColumn = (bool) ($summary['show_expedition_column'] ?? false);
    $recapLabel = $recapLabel ?? ($isFullSupplierRecap ? 'SEMUA PEMBELIAN SUPPLIER' : $invoiceNumber);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ !empty($isFullSupplierRecap) ? 'Nota Rekap Supplier' : 'Rekap Supplier ' . $recapLabel }}</title>
    <x-brand.meta />
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #eef2f7; font-family: Arial, Helvetica, sans-serif; color: #0f172a; }
        .paper { width: 190mm; max-width: calc(100vw - 16px); margin: 8px auto; background: #fff; border: 2px solid #0f172a; padding: 16px; }
        .head { display: flex; justify-content: space-between; gap: 16px; }
        .brand-logo { height: 36px; width: auto; display: block; margin-bottom: 8px; }
        .title { font-size: 28px; font-weight: 800; margin: 0; }
        .sub { margin: 6px 0 0; font-size: 13px; }
        .meta { text-align: right; font-size: 14px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #0f172a; padding: 7px; font-size: 12px; vertical-align: top; }
        th { background: #f1f5f9; text-transform: uppercase; }
        .num { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .summary td { font-weight: 700; }
        .actions { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #0b7a5a; background: #0b7a5a; color: #fff; padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 12px; cursor: pointer; }
        .btn.secondary { background: #fff; color: #0b7a5a; }
        @media print {
            .actions { display: none !important; }
            body { background: #fff; }
            .paper { margin: 0; width: 100%; max-width: none; border: 1px solid #0f172a; padding: 4mm; }
            th, td { font-size: 10px; padding: 4px 5px; }
            .title { font-size: 22px; }
            .sub, .meta { font-size: 11px; }
        }
    </style>
</head>
<body>
    <div class="paper">
        <div class="head">
            <div>
                <img src="{{ asset('branding/sdm-logo-horizontal.svg') }}" alt="Logo Surya Duta Multindo" class="brand-logo">
                <h1 class="title">{{ !empty($isFullSupplierRecap) ? 'NOTA REKAP SUPPLIER' : 'REKAP SUPPLIER' }}</h1>
                <p class="sub">Supplier: <strong>{{ $supplier->name }}</strong></p>
                <p class="sub">{{ !empty($isFullSupplierRecap) ? 'Dokumen' : 'Rekap' }}: <strong>{{ $recapLabel }}</strong></p>
                <p class="sub">Tanggal Beli: <strong>{{ $purchaseDateLabel }}</strong></p>
            </div>
            <div class="meta">
                <div>Tanggal Cetak: {{ $formatNotaDate($printedAt) }}</div>
                <div>Metode: {{ $summary['payment_type'] ?? 'LUNAS' }}</div>
                <div>Status: {{ $summary['status'] ?? 'LUNAS' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal Beli</th>
                    <th>Part Number</th>
                    <th>Qty</th>
                    <th>Harga Beli</th>
                    @if($showExpeditionColumn)
                        <th>Ekspedisi</th>
                    @endif
                    <th>Subtotal</th>
                    @if($showCreditColumns)
                        <th>Dibayar</th>
                        <th>Sisa</th>
                        <th>Jatuh Tempo</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['purchase_date'] }}</td>
                        <td>{{ $item['part_number'] }}</td>
                        <td class="center num">{{ number_format((int) $item['qty'], 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format((float) $item['purchase_price'], 0, ',', '.') }}</td>
                        @if($showExpeditionColumn)
                            <td class="num">Rp {{ number_format((float) $item['expedition_cost'], 0, ',', '.') }}</td>
                        @endif
                        <td class="num">Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</td>
                        @if($showCreditColumns)
                            <td class="num">Rp {{ number_format((float) $item['paid'], 0, ',', '.') }}</td>
                            <td class="num">Rp {{ number_format((float) $item['remaining'], 0, ',', '.') }}</td>
                            <td class="center">{{ $item['credit_due_date'] }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tbody>
                <tr><td>Total Item</td><td class="num">{{ number_format((int) ($summary['items_count'] ?? 0), 0, ',', '.') }}</td></tr>
                <tr><td>Total Qty</td><td class="num">{{ number_format((int) ($summary['qty_total'] ?? 0), 0, ',', '.') }}</td></tr>
                <tr><td>Total Pembelian</td><td class="num">Rp {{ number_format((float) ($summary['subtotal'] ?? 0), 0, ',', '.') }}</td></tr>
                @if($showCreditColumns)
                    <tr><td>Total Dibayar</td><td class="num">Rp {{ number_format((float) ($summary['paid'] ?? 0), 0, ',', '.') }}</td></tr>
                    <tr><td>Sisa Kredit</td><td class="num">Rp {{ number_format((float) ($summary['remaining'] ?? 0), 0, ',', '.') }}</td></tr>
                @endif
            </tbody>
        </table>

        @if(! (($pdf ?? false) || request()->boolean('pdf')))
            <div class="actions">
                <button type="button" class="btn" onclick="window.print()">Print Rekap</button>
                <a class="btn secondary" href="{{ url('/admin/suppliers/' . $supplier->id) }}">Kembali ke Supplier</a>
                <a class="btn" href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}">Download PDF</a>
            </div>
        @endif
    </div>
</body>
</html>

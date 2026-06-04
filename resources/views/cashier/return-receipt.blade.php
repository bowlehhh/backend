<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Retur {{ $salesReturn->return_number }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .wrap { width: 190mm; margin: 12px auto; background: #fff; border: 2px solid #111827; padding: 14px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        .title { display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px; }
        .title h1 { margin: 0; font-size: 22px; }
        .title .badge { border: 2px solid #111827; padding: 6px 12px; font-weight: 700; }
        .meta td, .items td, .items th, .totals td { border: 1px solid #111827; padding: 7px; font-size: 12px; }
        .meta { margin-top: 8px; }
        .meta .label, .totals .label { font-weight: 700; background: #f9fafb; width: 22%; }
        .items { margin-top: 10px; }
        .items th { background: #f3f4f6; text-align: center; }
        .num { text-align: right; }
        .totals { margin-top: 10px; }
        .actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border: 1px solid #0f766e; background: #0f766e; color: #fff; padding: 10px 14px; border-radius: 8px; font-weight: 700; text-decoration: none; }
        .btn.secondary { background: #fff; color: #0f766e; }
        @media print { .actions { display: none; } body { background: #fff; } .wrap { margin: 0 auto; } }
    </style>
</head>
<body>
@php
    $sale = $salesReturn->sale;
    $creditOutstanding = (float) ($sale->credit_amount ?? 0);
@endphp
<div class="wrap">
    <div class="title">
        <div>
            <h1>{{ strtoupper($storeName) }}</h1>
            <div style="font-size: 12px;">Dokumen retur transaksi penjualan</div>
        </div>
        <div class="badge">NOTA RETUR</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">No. Retur</td>
            <td>{{ $salesReturn->return_number }}</td>
            <td class="label">Tanggal</td>
            <td>{{ $salesReturn->created_at?->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Invoice Asal</td>
            <td>{{ $sale->invoice_number }}</td>
            <td class="label">Kasir</td>
            <td>{{ $salesReturn->user?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pembeli</td>
            <td>{{ $sale->customer_name ?: '-' }}</td>
            <td class="label">Metode</td>
            <td class="uppercase">{{ $sale->payment_method }}</td>
        </tr>
        <tr>
            <td class="label">No. Telp Pembeli</td>
            <td>{{ $sale->customer_phone ?: '-' }}</td>
            <td class="label">No. Telp Kasir</td>
            <td>{{ $sale->cashier_phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Catatan</td>
            <td colspan="3">{{ $salesReturn->notes ?: '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
        <tr>
            <th style="width: 50px;">No</th>
            <th>Produk</th>
            <th style="width: 90px;">Qty</th>
            <th style="width: 160px;">Harga</th>
            <th style="width: 170px;">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach($salesReturn->items as $index => $item)
            <tr>
                <td style="text-align:center;">{{ $index + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td style="text-align:center;">{{ $item->qty }}</td>
                <td class="num">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">TOTAL RETUR</td>
            <td class="num">Rp {{ number_format((float) $salesReturn->total_refund, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">SISA KREDIT</td>
            <td class="num">Rp {{ number_format($creditOutstanding, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">JATUH TEMPO KREDIT</td>
            <td class="num">{{ $sale->credit_due_date?->format('d M Y') ?: '-' }}</td>
        </tr>
    </table>

    <div class="actions">
        <button type="button" class="btn" onclick="window.print()">Print Nota Retur</button>
        <a href="{{ $saleUrl }}" class="btn secondary">Lihat Nota Penjualan</a>
        <a href="{{ $historyUrl }}" class="btn secondary">Kembali ke History</a>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota {{ $sale->invoice_number }}</title>
    <style>
        :root { --sheet-width: 190mm; --sheet-border: 2px; --sheet-pad: 14px; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 0; background: #f3f4f6; }
        .wrap { width: var(--sheet-width); margin: 12px auto; background: #fff; border: var(--sheet-border) solid #111827; padding: var(--sheet-pad); box-sizing: border-box; }
        .header-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; }
        .header-table td { border: 0; padding: 0; vertical-align: top; }
        .header-left { width: 42%; }
        .header-right { width: 58%; text-align: right; }
        .company h1 { margin: 0; font-size: 24px; letter-spacing: 0.3px; }
        .company p { margin: 2px 0; font-size: 13px; }
        .invoice-title { border: 2px solid #111827; padding: 6px 16px; font-size: 30px; font-weight: 800; letter-spacing: 1px; text-align: center; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 12px; border: 1.5px solid #111827; table-layout: fixed; }
        .meta-table td { border: 1px solid #111827; padding: 7px 8px; font-size: 13px; }
        .meta-table .label { font-weight: 700; background: #f9fafb; width: 15%; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1.5px solid #111827; table-layout: fixed; }
        th, td { border: 1px solid #111827; padding: 8px 7px; font-size: 13px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; text-align: center; }
        td.num { text-align: right; }
        .desc { min-height: 90px; }
        .totals { margin-top: 0; width: 100%; border-collapse: collapse; }
        .totals td { border: 1px solid #111827; padding: 7px; font-size: 13px; }
        .totals .label { text-align: right; font-weight: 700; background: #f9fafb; }
        .totals .grand td { font-size: 20px; font-weight: 800; }
        .foot-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .foot-table td { vertical-align: top; font-size: 12px; }
        .foot-table .note-col { width: 72%; padding-right: 18px; line-height: 1.45; }
        .foot-table .sign-col { width: 28%; text-align: center; }
        .sign-box { min-height: 120px; }
        .sign-box .sign-bottom { margin-top: 70px; }
        .actions { margin-top: 16px; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border: 1px solid #0f766e; background: #0f766e; color: #fff; padding: 10px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; }
        .btn.secondary { background: #fff; color: #0f766e; }
        @page { size: auto; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .wrap { width: var(--sheet-width); margin: 0 auto; border: var(--sheet-border) solid #111827; padding: var(--sheet-pad); }
            .actions { display: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="company">
                    <h1>{{ strtoupper($storeName) }}</h1>
                    <p>Jl. Contoh Alamat Toko No. 1</p>
                    <p>Telp: {{ $sale->cashier_phone ?: '08xx-xxxx-xxxx' }}</p>
                </div>
            </td>
            <td class="header-right">
                <div class="invoice-title">INVOICE PENJUALAN</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="label">No. Invoice</td>
            <td>{{ $sale->invoice_number }}</td>
            <td class="label">Tanggal</td>
            <td>{{ $sale->created_at?->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Pembeli</td>
            <td>{{ $sale->customer_name ?: '-' }}</td>
            <td class="label">Pelayan</td>
            <td>{{ $sale->cashier_service_name ?: ($sale->user?->name ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Metode Bayar</td>
            <td>{{ strtoupper($sale->payment_method) }}</td>
            <td class="label">No. Telp Kasir</td>
            <td>{{ $sale->cashier_phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kasir Akun</td>
            <td>Kasir</td>
            <td class="label">Status</td>
            <td>LUNAS</td>
        </tr>
    </table>

    <table>
        <thead>
        <tr>
            <th style="width: 52px;">No</th>
            <th>Deskripsi</th>
            <th style="width: 90px;">Unit</th>
            <th style="width: 160px;">Harga / Unit</th>
            <th style="width: 170px;">Nilai</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sale->items as $idx => $item)
            <tr>
                <td style="text-align:center;">{{ $idx + 1 }}</td>
                <td class="desc">
                    <strong>{{ $item->product_name }}</strong><br>
                    Qty {{ $item->qty }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }} pada {{ $sale->created_at?->format('d M Y H:i') }}
                </td>
                <td style="text-align:center;">{{ $item->qty }}</td>
                <td class="num">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">SUBTOTAL</td><td class="num">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td></tr>
        <tr><td class="label">BAYAR</td><td class="num">Rp {{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</td></tr>
        <tr><td class="label">KEMBALIAN</td><td class="num">Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</td></tr>
        <tr class="grand"><td class="label">GRAND TOTAL</td><td class="num">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td></tr>
    </table>

    <table class="foot-table">
        <tr>
            <td class="note-col">
                <strong>Catatan:</strong><br>
                1. Barang yang sudah dibeli tidak dapat ditukar/dikembalikan kecuali ada kesalahan dari toko.<br>
                2. Simpan faktur ini sebagai bukti transaksi resmi.
            </td>
            <td class="sign-col">
                <div class="sign-box">
                    <div>{{ $sale->created_at?->format('d M Y') }}</div>
                    <div class="sign-bottom">
                        <strong>{{ $sale->cashier_service_name ?: ($sale->user?->name ?? 'Kasir') }}</strong><br>
                        {{ $sale->cashier_phone ?: 'Kasir' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if(! request()->boolean('pdf'))
        <div class="actions">
            <button type="button" class="btn" onclick="window.print()">Print Nota</button>
            <a href="{{ $historyUrl ?? route('cashier.history') }}" class="btn secondary">{{ $historyLabel ?? 'Kembali ke History' }}</a>
            @if(($showNewTransactionButton ?? true) === true)
                <a href="{{ $newTransactionUrl ?? route('cashier.dashboard') }}" class="btn secondary">Transaksi Baru</a>
            @endif
        </div>
    @endif
</div>
@if(request()->boolean('print'))
    <script>
        window.addEventListener('load', function () {
            const originalTitle = document.title;
            document.title = '';
            window.print();
            setTimeout(function () {
                document.title = originalTitle;
            }, 300);
        });
    </script>
@endif
</body>
</html>

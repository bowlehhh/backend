<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Retur {{ $salesReturn->return_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; background: #f3f4f6; color: #111827; width: 100%; overflow-x: hidden; }
        .wrap { width: 190mm; max-width: calc(100vw - 16px); margin: 8px auto; background: #fff; border: 2px solid #111827; padding: 14px; box-sizing: border-box; }
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
        .foot-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .foot-table td { border: 1px solid #111827; padding: 8px 10px; vertical-align: top; font-size: 12px; }
        .foot-table .note-col { width: 56%; line-height: 1.35; }
        .foot-table .sign-col { width: 44%; text-align: right; }
        .sign-box { min-height: 112px; }
        .sign-box .sign-bottom { margin-top: 56px; }
        .return-page + .return-page { margin-top: 10px; }
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            .actions { display: none !important; }
            body { background: #fff; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .wrap { margin: 0; width: auto; border: 1px solid #111827; padding: 5mm; page-break-after: avoid; }
            .return-page { page-break-after: always; }
            .return-page:last-of-type { page-break-after: auto; }
            .title { margin-bottom: 6px; page-break-inside: avoid; }
            .title h1 { font-size: 22px; }
            .meta, .items, .totals, .foot-table { page-break-inside: avoid; }
            .meta td, .items td, .items th, .totals td { padding: 4px 5px; font-size: 10px; line-height: 1.2; border: 1px solid #111827; }
            .items th { background: #e5e7eb; font-weight: 700; }
            .foot-table td { padding: 5px 6px; font-size: 10px; line-height: 1.25; }
            .sign-box { min-height: 92px; }
            .sign-box .sign-bottom { margin-top: 38px; }
        }
    </style>
</head>
<body>
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
@endphp
@php
    $sale = $salesReturn->sale;
    $creditOutstanding = (float) ($sale->credit_amount ?? 0);
    $cashierDisplayName = $sale->cashier_display_name;
    $returnValue = (float) ($salesReturn->return_total ?? 0);
    $exchangeTotal = (float) ($salesReturn->exchange_total ?? 0);
    $priceDifferenceTotal = (float) ($salesReturn->price_difference_total ?? 0);
    $extraPaymentAmount = (float) ($salesReturn->extra_payment_amount ?? 0);
    $extraPaymentChangeAmount = (float) ($salesReturn->extra_payment_change_amount ?? 0);
    $settlementRemaining = $priceDifferenceTotal > 0 ? max(0, $priceDifferenceTotal - $extraPaymentAmount) : 0;
    $refundRemaining = $priceDifferenceTotal < 0 ? max(0, abs($priceDifferenceTotal) - $extraPaymentAmount) : 0;
    $returnItemPages = collect($salesReturn->items ?? [])->chunk(35)->values();
    if ($returnItemPages->isEmpty()) {
        $returnItemPages = collect([collect([])]);
    }
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
            <td>{{ $formatNotaDate($salesReturn->created_at) }}</td>
        </tr>
        <tr>
            <td class="label">Invoice Asal</td>
            <td>{{ $sale->invoice_number }}</td>
            <td class="label">Admin</td>
            <td>{{ $cashierDisplayName }}</td>
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
            <td class="label">No. Telp Admin</td>
            <td>{{ $sale->cashier_phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Catatan</td>
            <td colspan="3">{{ $salesReturn->notes ?: '-' }}</td>
        </tr>
    </table>

    @foreach($returnItemPages as $pageIndex => $itemPage)
    <div class="return-page">
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
        @foreach($itemPage as $index => $item)
            @php
                $replacementDetails = $item->replacementDetailsResolved ?? [];
                $replacementQtyTotal = collect($replacementDetails)->sum(fn (array $replacement): int => (int) ($replacement['qty'] ?? 0));
            @endphp
            <tr>
                <td style="text-align:center;">{{ ($pageIndex * 35) + $index + 1 }}</td>
                <td>
                    <div style="font-weight:700;">{{ $item->product_name }}</div>
                    @if(! empty($replacementDetails))
                        <div style="font-size: 11px; color: #0f766e; margin-top: 2px; line-height: 1.35;">
                            @foreach($replacementDetails as $lineIndex => $replacement)
                                <div style="margin-top: {{ $lineIndex === 0 ? '0' : '2px' }};">
                                    Ganti {{ $lineIndex + 1 }}:
                                    {{ $replacement['part_name'] ?? ($replacement['label'] ?? '-') }}
                                    [{{ $replacement['part_number'] ?? ($replacement['label'] ?? '-') }}]
                                    @if((int) ($replacement['qty'] ?? 0) > 0)
                                        x{{ (int) $replacement['qty'] }}
                                    @endif
                                    @if(($replacement['subtotal'] ?? 0) > 0)
                                        - Rp {{ number_format((float) ($replacement['subtotal'] ?? 0), 0, ',', '.') }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($item->priceDifferenceResolved != 0)
                            <div style="font-size: 11px; color: {{ $item->priceDifferenceResolved > 0 ? '#b45309' : '#15803d' }}; margin-top: 2px;">
                                Selisih: Rp {{ number_format(abs($item->priceDifferenceResolved), 0, ',', '.') }}
                                {{ $item->priceDifferenceResolved > 0 ? '(tambahan)' : '(refund)' }}
                            </div>
                        @endif
                    @endif
                </td>
                <td style="text-align:center;">
                    <div style="font-weight:700;">Retur {{ $item->qty }}</div>
                    @if($replacementQtyTotal > 0)
                        <div style="font-size: 11px; color: #0f766e; margin-top: 2px;">Ganti {{ $replacementQtyTotal }}</div>
                    @endif
                </td>
                <td class="num">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($loop->last)
    <table class="totals">
        <tr>
            <td class="label">TOTAL RETUR BARANG</td>
            <td class="num">Rp {{ number_format($returnValue, 0, ',', '.') }}</td>
        </tr>
        @if($exchangeTotal > 0)
        <tr>
            <td class="label">NILAI BARANG PENGGANTI</td>
            <td class="num">Rp {{ number_format($exchangeTotal, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($priceDifferenceTotal !== 0.0)
        <tr>
            <td class="label">{{ $priceDifferenceTotal > 0 ? 'TAMBAHAN BAYAR' : 'REFUND SELISIH' }}</td>
            <td class="num">Rp {{ number_format(abs($priceDifferenceTotal), 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($extraPaymentAmount > 0)
        <tr>
            <td class="label">UANG SELISIH DIBAYAR</td>
            <td class="num">Rp {{ number_format($extraPaymentAmount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($settlementRemaining > 0)
        <tr>
            <td class="label">SELISIH MASUK KE KREDIT</td>
            <td class="num">Rp {{ number_format($settlementRemaining, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($refundRemaining > 0)
        <tr>
            <td class="label">REFUND BELUM DIBAYAR</td>
            <td class="num">Rp {{ number_format($refundRemaining, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($extraPaymentChangeAmount > 0)
        <tr>
            <td class="label">KEMBALIAN SELISIH</td>
            <td class="num">Rp {{ number_format($extraPaymentChangeAmount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">SISA KREDIT</td>
            <td class="num">Rp {{ number_format($creditOutstanding, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">JATUH TEMPO KREDIT</td>
            <td class="num">{{ $sale->credit_due_date?->format('d M Y') ?: '-' }}</td>
        </tr>
    </table>

    @if($priceDifferenceTotal > 0)
        <div style="margin-top: 8px; font-size: 12px; color: #92400e; font-weight: 700;">
            Selisih positif akan menambah tagihan kredit / pembayaran tambahan.
        </div>
    @elseif($priceDifferenceTotal < 0)
        <div style="margin-top: 8px; font-size: 12px; color: #166534; font-weight: 700;">
            Selisih negatif menjadi refund / pengurangan tagihan sesuai sisa kredit.
        </div>
    @endif

    <table class="foot-table">
        <tr>
            <td class="note-col">
                <strong>Catatan:</strong>
                <div style="margin-top: 4px; padding-left: 10px; font-size: 10px; line-height: 1.35;">
                    <div>1. Simpan nota retur ini sebagai bukti retur resmi.</div>
                    <div>2. Pastikan jumlah retur, barang pengganti, dan selisih sudah sesuai.</div>
                </div>
            </td>
            <td class="sign-col">
                <div class="sign-box" style="text-align: right;">
                    <div style="font-size: 10px; font-weight: 700;">Yang Menyerahkan</div>
                    <div class="sign-bottom">
                        <strong style="font-size: 10px;">{{ $cashierDisplayName }}</strong><br>
                        <span style="font-size: 9px;">{{ $sale->cashier_phone ?: 'Admin' }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if(! ($pdf ?? false))
        <div class="actions">
            <button type="button" class="btn" onclick="window.print()">Print Nota Retur</button>
            <a href="{{ $saleUrl }}?from=return" class="btn secondary">Lihat Nota Penjualan</a>
            <a href="{{ $historyUrl }}" class="btn secondary">Kembali ke History</a>
        </div>
    @endif
    @endif
    </div>
    @endforeach
</div>
</body>
</html>

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
    $cashierDisplayName = $sale->cashier_display_name;
    $returnValue = (float) ($salesReturn->return_total ?? 0);
    $exchangeTotal = (float) ($salesReturn->exchange_total ?? 0);
    $priceDifferenceTotal = (float) ($salesReturn->price_difference_total ?? 0);
    $extraPaymentAmount = (float) ($salesReturn->extra_payment_amount ?? 0);
    $extraPaymentChangeAmount = (float) ($salesReturn->extra_payment_change_amount ?? 0);
    $settlementRemaining = $priceDifferenceTotal > 0 ? max(0, $priceDifferenceTotal - $extraPaymentAmount) : 0;
    $refundRemaining = $priceDifferenceTotal < 0 ? max(0, abs($priceDifferenceTotal) - $extraPaymentAmount) : 0;
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
            @php
                $replacementDetails = $item->replacementDetailsResolved ?? [];
                $replacementQtyTotal = collect($replacementDetails)->sum(fn (array $replacement): int => (int) ($replacement['qty'] ?? 0));
            @endphp
            <tr>
                <td style="text-align:center;">{{ $index + 1 }}</td>
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

    @if(! ($pdf ?? false))
        <div class="actions">
            <button type="button" class="btn" onclick="window.print()">Print Nota Retur</button>
            <a href="{{ $saleUrl }}?from=return" class="btn secondary">Lihat Nota Penjualan</a>
            <a href="{{ $historyUrl }}" class="btn secondary">Kembali ke History</a>
        </div>
    @endif
</div>
</body>
</html>

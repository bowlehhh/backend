<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota {{ $sale->invoice_number }}</title>
    <style>
        :root { --sheet-width: 176mm; --sheet-border: 1.2px; --sheet-pad: 7px; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 0; background: #f3f4f6; }
        .wrap { width: var(--sheet-width); margin: 12px auto; background: #fff; border: var(--sheet-border) solid #111827; padding: var(--sheet-pad); box-sizing: border-box; }
        .header-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; }
        .header-table td { border: 0; padding: 0; vertical-align: top; }
        .header-left { width: 42%; }
        .header-right { width: 58%; text-align: right; }
        .company h1 { margin: 0; font-size: 19px; letter-spacing: 0.1px; }
        .company p { margin: 1px 0; font-size: 11px; }
        .invoice-title { border: 1.2px solid #111827; padding: 4px 12px; font-size: 24px; font-weight: 800; letter-spacing: 0.6px; text-align: center; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #111827; table-layout: fixed; }
        .meta-table td { border: 1px solid #111827; padding: 4px 5px; font-size: 11px; }
        .meta-table .label { font-weight: 700; background: #f9fafb; width: 15%; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid #111827; table-layout: fixed; }
        th, td { border: 1px solid #111827; padding: 2px 3px; font-size: 10px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; text-align: center; }
        td.num { text-align: right; }
        .desc { min-height: 0; line-height: 1.15; }
        .item-table th, .item-table td { overflow-wrap: anywhere; word-break: break-word; }
        .item-table .col-no { width: 4.5%; }
        .item-table .col-name { width: 23%; }
        .item-table .col-part { width: 18%; }
        .item-table .col-qty { width: 5.5%; }
        .item-table .col-unit { width: 5.5%; }
        .item-table .col-price { width: 17%; }
        .item-table .col-total { width: 26.5%; }
        .totals { margin-top: 0; width: 100%; border-collapse: collapse; }
        .totals td { border: 1px solid #111827; padding: 4px 5px; font-size: 10px; }
        .totals .label { text-align: right; font-weight: 700; background: #f9fafb; }
        .totals .grand td { font-size: 14px; font-weight: 800; }
        .foot-table { width: 100%; border-collapse: collapse; margin-top: 6px; table-layout: fixed; }
        .foot-table td { vertical-align: top; font-size: 10px; }
        .foot-table .note-col { width: 72%; padding-right: 10px; line-height: 1.3; }
        .foot-table .sign-col { width: 28%; text-align: center; }
        .sign-box { min-height: 72px; }
        .sign-box .sign-bottom { margin-top: 30px; }
        .actions { margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap; }
        .btn { border: 1px solid #0f766e; background: #0f766e; color: #fff; padding: 7px 10px; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 11px; }
        .btn.secondary { background: #fff; color: #0f766e; }
        @page { size: A4 portrait; margin: 4mm; }
        @media print {
            body { background: #fff; }
            .wrap { width: var(--sheet-width); margin: 0 auto; border: var(--sheet-border) solid #111827; padding: var(--sheet-pad); transform: scale(0.94); transform-origin: top left; }
            .actions { display: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $isCredit = strtolower((string) $sale->payment_method) === 'credit';
    $downPayment = (float) ($sale->paid_amount ?? 0);
    $installmentPaid = (float) ($sale->installments?->sum('amount') ?? 0);
    $installmentReceived = (float) ($sale->installments?->sum('received_amount') ?? 0);
    $installmentChange = (float) ($sale->installments?->sum('change_amount') ?? 0);
    $installments = collect($sale->installments ?? [])->sortBy([
        ['paid_at', 'asc'],
        ['id', 'asc'],
    ])->values();
    $lastInstallment = $installments->last();
    $lastInstallmentApplied = (float) ($lastInstallment->amount ?? 0);
    $lastInstallmentReceived = (float) ($lastInstallment->received_amount ?? $lastInstallmentApplied);
    $lastInstallmentChange = (float) ($lastInstallment->change_amount ?? 0);
    $creditOutstanding = (float) ($sale->credit_amount ?? 0);
    $creditOutstandingBeforeLastInstallment = $lastInstallment ? ($creditOutstanding + $lastInstallmentApplied) : $creditOutstanding;
    $creditDays = (int) ($sale->credit_days ?? 0);
    $totalReturned = (float) ($sale->returns?->sum('return_total') ?? 0);
    $totalRefundActual = (float) ($sale->returns?->sum('refund_amount') ?? 0);
    $exchangeTotal = (float) ($sale->returns?->sum('exchange_total') ?? 0);
    $priceDifferenceTotal = (float) ($sale->returns?->sum('price_difference_total') ?? 0);
    $extraPaymentTotal = (float) ($sale->returns?->sum('extra_payment_amount') ?? 0);
    $extraPaymentChangeTotal = (float) ($sale->returns?->sum('extra_payment_change_amount') ?? 0);
    $settlementRemainingTotal = $priceDifferenceTotal > 0 ? max(0, $priceDifferenceTotal - $extraPaymentTotal) : 0;
    $refundRemainingTotal = $priceDifferenceTotal < 0 ? max(0, abs($priceDifferenceTotal) - $extraPaymentTotal) : 0;
    $returnItemsBySaleItem = collect($sale->returns ?? [])
        ->flatMap(fn ($return) => $return->items ?? [])
        ->groupBy('sale_item_id');
    $returnRecords = collect($sale->returns ?? [])->sortBy([
        ['returned_at', 'asc'],
        ['id', 'asc'],
    ])->values();
    $hasReturnItems = $returnItemsBySaleItem->isNotEmpty();
    $paymentStatus = ($isCredit && $creditOutstanding > 0) ? 'BELUM LUNAS' : 'LUNAS';
    $finalGrandTotal = $isCredit ? $creditOutstanding : (float) $sale->total;
    $remainingAfterEntry = max(0, (float) $sale->total - $downPayment);
    $cashierDisplayName = $sale->cashier_display_name;
@endphp
@if(session('success'))
    <div style="width: var(--sheet-width); margin: 12px auto 0; background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px 14px; box-sizing: border-box; font-size: 13px; font-weight: 700;">
        {{ session('success') }}
    </div>
@endif
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
            <td>{{ $sale->cashier_display_name }}</td>
        </tr>
        <tr>
            <td class="label">Metode Bayar</td>
            <td>{{ strtoupper($sale->payment_method) }}</td>
            <td class="label">No. Telp Pembeli</td>
            <td>{{ $sale->customer_phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">No. Telp Kasir</td>
            <td>{{ $sale->cashier_phone ?: '-' }}</td>
            <td class="label">Status</td>
            <td>{{ $paymentStatus }}</td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-name">Part Name</th>
            <th class="col-part">Part Number</th>
            <th class="col-qty">Qty</th>
            <th class="col-unit">Unit</th>
            <th class="col-price">Harga</th>
            <th class="col-total">Nilai</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sale->items as $idx => $item)
            @php
                $partNumber = (string) ($item->product?->barcode ?? '-');
                $partName = (string) ($item->product_name ?: $item->product?->name ?: '-');
            @endphp
            <tr>
                <td class="col-no" style="text-align:center;">{{ $idx + 1 }}</td>
                <td class="desc col-name"><strong>{{ $partName }}</strong></td>
                <td class="desc col-part"><span style="font-size: 9px; color: #64748b;">{{ $partNumber }}</span></td>
                <td class="col-qty" style="text-align:center;">{{ $item->qty }}</td>
                <td class="col-unit" style="text-align:center;">{{ $item->product?->unit ?: '-' }}</td>
                <td class="num col-price">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                <td class="num col-total">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($hasReturnItems)
        <table style="margin-top: 6px; font-size: 9px;">
            <thead>
            <tr>
                <th colspan="5">Track Record Retur</th>
            </tr>
            <tr>
                <th style="width: 18%;">No Retur / Tanggal</th>
                <th style="width: 24%;">Produk</th>
                <th style="width: 12%;">Qty Retur</th>
                <th style="width: 28%;">Barang Pengganti</th>
                <th style="width: 18%;">Selisih</th>
            </tr>
            </thead>
            <tbody>
            @foreach($returnRecords as $returnRecord)
                @foreach($returnRecord->items as $returnItem)
                    @php
                        $replacementDetails = $returnItem->replacementDetailsResolved ?? [];
                        $partNumber = (string) ($returnItem->productBatch?->product?->barcode ?? $returnItem->product?->barcode ?? '-');
                        $partName = (string) ($returnItem->product_name ?: $returnItem->product?->name ?: '-');
                        $replacementQtyTotal = (int) collect($replacementDetails)->sum(fn (array $replacement): int => (int) ($replacement['qty'] ?? 0));
                        $returnDifference = (float) $returnItem->priceDifferenceResolved;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:700;">{{ $returnRecord->return_number }}</div>
                            <div style="color:#64748b;">{{ $returnRecord->created_at?->format('d M Y H:i') }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700;">{{ $partName }}</div>
                            <div style="color:#64748b;">Part No: {{ $partNumber }}</div>
                        </td>
                        <td class="num">
                            <div style="font-weight:700;">{{ (int) $returnItem->qty }}</div>
                            @if($replacementQtyTotal > 0)
                                <div style="font-size: 9px; color: #0f766e;">Ganti {{ $replacementQtyTotal }}</div>
                            @endif
                        </td>
                        <td>
                            @if(! empty($replacementDetails))
                                @foreach($replacementDetails as $replacementLineIndex => $replacement)
                                    <div style="margin-bottom: 2px;">
                                        {{ $replacementLineIndex + 1 }}. {{ $replacement['part_name'] ?? ($replacement['label'] ?? '-') }}
                                        [{{ $replacement['part_number'] ?? ($replacement['label'] ?? '-') }}]
                                        x{{ (int) ($replacement['qty'] ?? 0) }}
                                    </div>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                        <td class="num">
                            {{ $returnDifference > 0 ? 'Rp ' . number_format($returnDifference, 0, ',', '.') . ' +' : 'Rp ' . number_format(abs($returnDifference), 0, ',', '.') . ' -' }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    @endif

    <table class="totals">
        <tr><td class="label">SUBTOTAL</td><td class="num">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td></tr>
        @if($isCredit)
            <tr><td class="label">DP / UANG MUKA</td><td class="num">Rp {{ number_format($downPayment, 0, ',', '.') }}</td></tr>
            <tr><td class="label">TOTAL CICILAN</td><td class="num">Rp {{ number_format($installmentPaid, 0, ',', '.') }}</td></tr>
            <tr><td class="label">PEMBAYARAN TERAKHIR</td><td class="num">Rp {{ number_format($lastInstallmentReceived, 0, ',', '.') }}</td></tr>
            @if($lastInstallment)
                <tr><td class="label">SISA SEBELUM BAYAR TERAKHIR</td><td class="num">Rp {{ number_format($creditOutstandingBeforeLastInstallment, 0, ',', '.') }}</td></tr>
            @endif
            <tr><td class="label">SISA CICILAN</td><td class="num">Rp {{ number_format($creditOutstanding, 0, ',', '.') }}</td></tr>
            <tr><td class="label">TEMPO KREDIT</td><td class="num">{{ $creditDays > 0 ? ($creditDays . ' hari') : '-' }}</td></tr>
        @else
            <tr><td class="label">BAYAR</td><td class="num">Rp {{ number_format($downPayment, 0, ',', '.') }}</td></tr>
            <tr><td class="label">KEMBALIAN</td><td class="num">Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td class="label">TOTAL RETUR BARANG</td><td class="num">Rp {{ number_format($totalReturned, 0, ',', '.') }}</td></tr>
        @if($totalRefundActual > 0)
            <tr><td class="label">REFUND AKTUAL</td><td class="num">Rp {{ number_format($totalRefundActual, 0, ',', '.') }}</td></tr>
        @endif
        @if($exchangeTotal > 0)
            <tr><td class="label">NILAI BARANG PENGGANTI</td><td class="num">Rp {{ number_format($exchangeTotal, 0, ',', '.') }}</td></tr>
        @endif
        @if($priceDifferenceTotal !== 0.0)
            <tr>
                <td class="label">{{ $priceDifferenceTotal > 0 ? 'SELISIH TAMBAHAN' : 'SELISIH REFUND' }}</td>
                <td class="num">Rp {{ number_format(abs($priceDifferenceTotal), 0, ',', '.') }}</td>
            </tr>
        @endif
        @if($extraPaymentTotal > 0)
            <tr><td class="label">UANG SELISIH DIBAYAR</td><td class="num">Rp {{ number_format($extraPaymentTotal, 0, ',', '.') }}</td></tr>
        @endif
        @if($settlementRemainingTotal > 0)
            <tr><td class="label">SELISIH MASUK KE KREDIT</td><td class="num">Rp {{ number_format($settlementRemainingTotal, 0, ',', '.') }}</td></tr>
        @endif
        @if($refundRemainingTotal > 0)
            <tr><td class="label">REFUND BELUM DIBAYAR</td><td class="num">Rp {{ number_format($refundRemainingTotal, 0, ',', '.') }}</td></tr>
        @endif
        @if($extraPaymentChangeTotal > 0)
            <tr><td class="label">KEMBALIAN SELISIH</td><td class="num">Rp {{ number_format($extraPaymentChangeTotal, 0, ',', '.') }}</td></tr>
        @endif
        @if($isCredit)
            <tr><td class="label">JATUH TEMPO</td><td class="num">{{ $sale->credit_due_date?->format('d M Y') ?: '-' }}</td></tr>
        @endif
        @if($isCredit)
            <tr class="grand"><td class="label">SISA AKHIR CICILAN</td><td class="num">Rp {{ number_format($finalGrandTotal, 0, ',', '.') }}</td></tr>
            @if($lastInstallmentChange > 0)
                <tr><td class="label">UANG SISA / KEMBALIAN</td><td class="num">Rp {{ number_format($lastInstallmentChange, 0, ',', '.') }}</td></tr>
            @endif
        @else
            <tr class="grand"><td class="label">GRAND TOTAL</td><td class="num">Rp {{ number_format($finalGrandTotal, 0, ',', '.') }}</td></tr>
        @endif
    </table>

    @if($isCredit && ($sale->installments?->isNotEmpty() ?? false))
        <table style="margin-top: 6px;">
            <thead>
            <tr>
                <th colspan="4">Riwayat Cicilan Kredit</th>
            </tr>
            <tr>
                <th style="width: 34%;">Tanggal</th>
                <th style="width: 22%;">Nominal</th>
                <th style="width: 22%;">Sisa Sesudah</th>
                <th style="width: 22%;">Kasir</th>
            </tr>
            </thead>
            <tbody>
            @foreach($installments as $installment)
                @php
                    $remainingAfterEntry = max(0, $remainingAfterEntry - (float) $installment->amount);
                @endphp
                <tr>
                    <td>{{ $installment->paid_at?->format('d M Y H:i') ?: '-' }}</td>
                    <td class="num">Rp {{ number_format((float) $installment->amount, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($remainingAfterEntry, 0, ',', '.') }}</td>
                    <td>{{ $cashierDisplayName }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <table class="foot-table">
        <tr>
            <td class="note-col">
                <strong>Catatan:</strong> Simpan faktur ini sebagai bukti transaksi resmi.
            </td>
            <td class="sign-col">
                <div class="sign-box">
                    <div>{{ $sale->created_at?->format('d M Y') }}</div>
                    <div class="sign-bottom">
                        <strong>{{ $cashierDisplayName }}</strong><br>
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
@if($installmentChange > 0)
<div style="margin-top:12px;padding:8px 10px;border:1px solid #fde68a;background:#fffbeb;color:#92400e;font-size:12px;font-weight:600;">
    Uang sisa / kembalian: Rp {{ number_format($installmentChange, 0, ',', '.') }}
</div>
@endif
</body>
</html>

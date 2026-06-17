<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota {{ $sale->invoice_number }}</title>
    <style>
        :root { --sheet-width: 198mm; --sheet-border: 1.2px; --sheet-pad: 10px; }
        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 0; padding: 0; background: #f3f4f6; width: 100%; overflow-x: hidden; }
        .wrap { width: var(--sheet-width); max-width: calc(100vw - 16px); margin: 8px auto; background: #fff; border: var(--sheet-border) solid #111827; padding: var(--sheet-pad); box-sizing: border-box; }
        .header-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; }
        .header-table td { border: 0; padding: 0; vertical-align: top; }
        .header-title { width: 100%; text-align: center; }
        .invoice-title { border: 1.2px solid #111827; padding: 6px 12px; font-size: 28px; font-weight: 800; letter-spacing: 0.6px; text-align: center; }
        .meta-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 8px; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; table-layout: fixed; }
        .meta-table td { border-top: 1px solid #111827; border-left: 1px solid #111827; padding: 5px 6px; font-size: 12px; overflow-wrap: anywhere; word-break: break-word; }
        .meta-table tr > *:last-child { border-right: 1px solid #111827; }
        .meta-table tbody tr:last-child > * { border-bottom: 1px solid #111827; }
        .meta-table .label { font-weight: 700; background: #f9fafb; width: 15%; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 6px; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; table-layout: fixed; }
        th, td { border-top: 1px solid #111827; border-left: 1px solid #111827; padding: 4px 5px; font-size: 11px; vertical-align: top; overflow-wrap: anywhere; word-break: break-word; }
        tr > *:last-child { border-right: 1px solid #111827; }
        tbody tr:last-child > * { border-bottom: 1px solid #111827; }
        thead tr:last-child > * { border-bottom: 1px solid #111827; }
        th { background: #f3f4f6; font-weight: 700; text-align: center; }
        td.num { text-align: right; }
        .desc { min-height: 0; line-height: 1.15; }
        .item-table th, .item-table td { overflow-wrap: anywhere; word-break: break-word; }
        .item-table .col-no { width: 6%; }
        .item-table .col-name { width: 27%; }
        .item-table .col-part { width: 16%; }
        .item-table .col-qty { width: 8%; }
        .item-table .col-unit { width: 8%; }
        .item-table .col-price { width: 16%; }
        .item-table .col-total { width: 19%; }
        .totals { margin-top: 0; width: 100%; border-collapse: collapse; }
        .totals td { border: 1px solid #111827; padding: 5px 6px; font-size: 11px; }
        .totals .label { text-align: right; font-weight: 700; background: #f9fafb; }
        .totals .grand td { font-size: 16px; font-weight: 800; }
        .foot-table { width: 100%; border-collapse: collapse; margin-top: 6px; table-layout: fixed; }
        .foot-table td { vertical-align: top; font-size: 11px; }
        .foot-table .note-col { width: 58%; padding-right: 10px; line-height: 1.3; }
        .foot-table .sign-col { width: 42%; text-align: right; }
        .sign-box { min-height: 116px; }
        .sign-box .sign-bottom { margin-top: 62px; }
        .actions { margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap; }
        .btn { border: 1px solid #0f766e; background: #0f766e; color: #fff; padding: 7px 10px; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 11px; }
        .btn.secondary { background: #fff; color: #0f766e; }
        .receipt-page + .receipt-page { margin-top: 10px; }
        @page { size: A4 portrait; margin: 6mm; }
        @media print {
            @page { size: A4 portrait; margin: 6mm; }
            body { background: #fff; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .wrap {
                width: 100%;
                margin: 0;
                border: 1px solid #111827;
                max-width: none;
                padding: 4.5mm;
                transform: none;
                transform-origin: initial;
            }
            .actions { display: none !important; }
            .return-history, .installment-history { display: none !important; }
            .receipt-page { page-break-after: always; }
            .receipt-page:last-of-type { page-break-after: auto; }
            .header-table { margin-bottom: 5px; page-break-inside: avoid; }
            .invoice-title { font-size: 26px; padding: 5px 10px; margin: 0; line-height: 1.2; font-weight: 900; letter-spacing: 0.4px; border: 1px solid #111827; }
            .meta-table { margin-top: 4px; margin-bottom: 4px; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; page-break-inside: avoid; }
            .meta-table tr { page-break-inside: avoid; }
            .meta-table td { border-top: 1px solid #111827; border-left: 1px solid #111827; padding: 5px 6px; font-size: 11px; line-height: 1.25; }
            .meta-table .label { width: 12%; font-weight: 700; background: #f3f4f6; }
            .item-table { margin-top: 4px; margin-bottom: 0; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; page-break-inside: avoid; }
            .item-table thead { display: table-header-group; }
            .item-table tr { break-inside: avoid; page-break-inside: avoid; }
            .item-table th { padding: 5px 5px; font-size: 10px; line-height: 1.2; background: #e5e7eb; font-weight: 700; text-transform: uppercase; }
            .item-table td { padding: 5px 5px; font-size: 10px; line-height: 1.2; vertical-align: top; }
            .item-table .col-no { width: 6%; text-align: center; }
            .item-table .col-name { width: 27%; }
            .item-table .col-part { width: 16%; }
            .item-table .col-qty { width: 8%; text-align: center; }
            .item-table .col-unit { width: 8%; text-align: center; }
            .item-table .col-price { width: 16%; text-align: right; }
            .item-table .col-total { width: 19%; text-align: right; }
            .totals { margin-top: 0; margin-bottom: 0; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; page-break-inside: avoid; }
            .totals tr { page-break-inside: avoid; }
            .totals td { padding: 5px 6px; font-size: 11px; line-height: 1.25; }
            .totals .label { text-align: right; font-weight: 700; background: #f3f4f6; width: 75%; }
            .totals .grand { background: #e5e7eb; }
            .totals .grand td { font-size: 14px; font-weight: 900; padding: 5px 6px; letter-spacing: 0.2px; }
            .foot-table { margin-top: 4px; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; page-break-inside: avoid; }
            .foot-table td { padding: 5px 6px; font-size: 10px; line-height: 1.25; vertical-align: top; }
            .foot-table .note-col { width: 56%; padding-right: 3px; }
            .foot-table .sign-col { width: 44%; text-align: right; }
            .sign-box { min-height: 96px; padding: 2px; }
            .sign-box div:first-child { font-size: 10px; font-weight: 700; margin-bottom: 2px; }
            .sign-box .sign-bottom { margin-top: 40px; font-size: 10px; }
            .desc { line-height: 1.1; }
            .desc span { font-size: 9px; }
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
    $displayItems = collect($sale->items ?? [])
        ->groupBy(fn ($item) => strtoupper(trim((string) ($item->part_number ?? $item->product?->barcode ?? 'PRODUCT-' . ($item->product_id ?? 0)))))
        ->map(function ($items) {
            $first = $items->first();
            $uniquePrices = $items
                ->pluck('price')
                ->map(fn ($price) => (float) $price)
                ->unique()
                ->values();
            $priceBreakdown = $items
                ->groupBy(fn ($item) => number_format((float) $item->price, 2, '.', ''))
                ->map(function ($priceItems, $priceKey) {
                    $qty = (int) $priceItems->sum('qty');
                    $price = (float) $priceKey;

                    return [
                        'price' => $price,
                        'qty' => $qty,
                        'subtotal' => $price * $qty,
                    ];
                })
                ->sortBy('price')
                ->values();

            return [
                'product_name' => (string) ($first->product_name ?: $first->product?->name ?: '-'),
                'brand_name' => (string) ($first->product?->brand?->name ?: '-'),
                'part_number' => (string) ($first->part_number ?: $first->product?->barcode ?: '-'),
                'unit' => (string) ($first->product?->unit ?: '-'),
                'qty' => (int) $items->sum('qty'),
                'subtotal' => (float) $items->sum('subtotal'),
                'price' => $uniquePrices->count() === 1 ? (float) $uniquePrices->first() : null,
                'has_mixed_price' => $uniquePrices->count() > 1,
                'price_breakdown' => $priceBreakdown,
            ];
        })
        ->values();
    $displayItemPages = $displayItems->chunk(35)->values();
    if ($displayItemPages->isEmpty()) {
        $displayItemPages = collect([collect([])]);
    }
    $paymentStatus = ($isCredit && $creditOutstanding > 0) ? 'BELUM LUNAS' : 'LUNAS';
    $finalGrandTotal = $isCredit ? $creditOutstanding : (float) $sale->total;
    $remainingAfterEntry = max(0, (float) $sale->total - $downPayment);
    $cashierDisplayName = $sale->cashier_display_name;
@endphp
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
@if(session('success'))
    <div style="width: var(--sheet-width); margin: 12px auto 0; background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px 14px; box-sizing: border-box; font-size: 13px; font-weight: 700;">
        {{ session('success') }}
    </div>
@endif
<div class="wrap">
    <table class="header-table">
        <tr>
            <td class="header-title">
                <div class="invoice-title">INVOICE PENJUALAN</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="label">No. Invoice</td>
            <td>{{ $sale->invoice_number }}</td>
            <td class="label">Tanggal</td>
            <td>{{ $formatNotaDate($sale->created_at) }}</td>
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
            <td class="label">Jatuh Tempo</td>
            <td>{{ $sale->credit_due_date?->format('d M Y') ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tempo Kredit</td>
            <td>{{ $creditDays > 0 ? ($creditDays . ' hari') : '-' }}</td>
            <td class="label">Status</td>
            <td>{{ $paymentStatus }}</td>
        </tr>
    </table>

    @foreach($displayItemPages as $pageIndex => $itemPage)
    <div class="receipt-page">
    <table class="item-table">
        <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-name">Part Name / Merek</th>
            <th class="col-part">Part Number</th>
            <th class="col-qty">Qty</th>
            <th class="col-unit">Unit</th>
            <th class="col-price">Harga</th>
            <th class="col-total">Nilai</th>
        </tr>
        </thead>
        <tbody>
        @foreach($itemPage as $idx => $item)
            <tr>
                <td class="col-no" style="text-align:center;">{{ ($pageIndex * 35) + $idx + 1 }}</td>
                <td class="desc col-name">
                    <strong>{{ $item['product_name'] }}</strong><br>
                    <span style="font-size: 9px; color: #64748b;">{{ $item['brand_name'] ?: '-' }}</span>
                </td>
                <td class="desc col-part"><span style="font-size: 9px; color: #64748b;">{{ $item['part_number'] }}</span></td>
                <td class="col-qty" style="text-align:center;">{{ $item['qty'] }}</td>
                <td class="col-unit" style="text-align:center;">{{ $item['unit'] }}</td>
                <td class="num col-price">
                    @if($item['has_mixed_price'])
                        Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}
                    @else
                        Rp {{ number_format((float) ($item['price'] ?? 0), 0, ',', '.') }}
                    @endif
                </td>
                <td class="num col-total">Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($loop->last && $hasReturnItems)
        <table style="margin-top: 6px; font-size: 9px;" class="return-history">
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
                            <div style="color:#64748b;">{{ $formatNotaDate($returnRecord->created_at) }}</div>
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

    @if($loop->last)
    <table class="totals">
        <tr><td class="label">SUBTOTAL</td><td class="num">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td></tr>
        @if($isCredit)
            <tr><td class="label">DP / UANG MUKA</td><td class="num">Rp {{ number_format($downPayment, 0, ',', '.') }}</td></tr>
            @if($lastInstallment)
                <tr><td class="label">SISA SEBELUM BAYAR TERAKHIR</td><td class="num">Rp {{ number_format($creditOutstandingBeforeLastInstallment, 0, ',', '.') }}</td></tr>
            @endif
        @else
            <tr><td class="label">KEMBALIAN</td><td class="num">Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</td></tr>
        @endif
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
            <tr class="grand"><td class="label">GRAND TOTAL</td><td class="num">Rp {{ number_format($finalGrandTotal, 0, ',', '.') }}</td></tr>
            @if($lastInstallmentChange > 0)
                <tr><td class="label">UANG SISA / KEMBALIAN</td><td class="num">Rp {{ number_format($lastInstallmentChange, 0, ',', '.') }}</td></tr>
            @endif
        @else
            <tr class="grand"><td class="label">GRAND TOTAL</td><td class="num">Rp {{ number_format($finalGrandTotal, 0, ',', '.') }}</td></tr>
        @endif
    </table>

    @if($isCredit && ($sale->installments?->isNotEmpty() ?? false))
        <table style="margin-top: 6px;" class="installment-history">
            <thead>
            <tr>
                <th colspan="4">Riwayat Cicilan Kredit</th>
            </tr>
            <tr>
                <th style="width: 34%;">Tanggal</th>
                <th style="width: 22%;">Nominal</th>
                <th style="width: 22%;">Sisa Sesudah</th>
                <th style="width: 22%;">Admin</th>
            </tr>
            </thead>
            <tbody>
            @foreach($installments as $installment)
                @php
                    $remainingAfterEntry = max(0, $remainingAfterEntry - (float) $installment->amount);
                @endphp
                <tr>
                    <td>{{ $formatNotaDate($installment->paid_at) }}</td>
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
            <td class="note-col footer-note-print">
                <strong style="font-size: 9px;">Catatan:</strong>
                <div style="margin-top: 2px; padding-left: 10px; font-size: 9px; line-height: 1.3;">
                    <div>1. Simpan faktur ini sebagai bukti transaksi resmi.</div>
                    <div>2. Barang yang sudah di beli tidak dapat di tukar, kecuali ada perjanjian.</div>
                </div>
            </td>
            <td class="sign-col">
                <div class="sign-box" style="text-align: right;">
                    <div style="font-size: 10px;">Yang Menyerahkan</div>
                    <div class="sign-bottom">
                        <strong style="font-size: 10px;">{{ $cashierDisplayName }}</strong><br>
                        <span style="font-size: 9px;">{{ $sale->cashier_phone ?: 'Admin' }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    @if(! (($pdf ?? false) || request()->boolean('pdf')))
        <div class="actions">
            <button type="button" class="btn" onclick="window.print()">Print Nota</button>
            <a href="{{ $historyUrl ?? route('cashier.history') }}" class="btn secondary">{{ $historyLabel ?? 'Kembali ke History' }}</a>
            @if(($showNewTransactionButton ?? true) === true)
                <a href="{{ $newTransactionUrl ?? route('cashier.dashboard') }}" class="btn secondary">Transaksi Baru</a>
            @endif
        </div>
    @endif
    @endif
    </div>
    @endforeach
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

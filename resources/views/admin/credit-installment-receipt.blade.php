<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Cicilan Kredit</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        body { font-family: Arial, sans-serif; color:#111; background:#fff; margin: 0; padding: 0; width: 100%; overflow-x: hidden; }
        .sheet { width: 190mm; max-width: calc(100vw - 16px); margin: 8px auto; border: 2px solid #111827; padding: 16px; }
        .row { display:flex; justify-content:space-between; gap:16px; }
        .title { font-size: 34px; font-weight: 800; margin:0; }
        .sub { margin: 4px 0; }
        table {
            width:100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            margin-top: 12px;
            border: 1px solid #111827;
            outline: 1px solid #111827;
            outline-offset: -1px;
        }
        th, td {
            border-top: 1px solid #111827;
            border-left: 1px solid #111827;
            padding: 8px;
            text-align:left;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        tr > *:last-child { border-right: 1px solid #111827; }
        tbody tr:last-child > * { border-bottom: 1px solid #111827; }
        thead tr:last-child > * { border-bottom: 1px solid #111827; }
        .num { text-align:right; }
        .section-title { margin-top: 16px; font-size: 18px; font-weight: 700; }
        .payment-table th:nth-child(1), .payment-table td:nth-child(1) { width: 8%; }
        .payment-table th:nth-child(2), .payment-table td:nth-child(2) { width: 13%; }
        .payment-table th:nth-child(3), .payment-table td:nth-child(3) { width: 16%; }
        .payment-table th:nth-child(4), .payment-table td:nth-child(4) { width: 12%; }
        .payment-table th:nth-child(5), .payment-table td:nth-child(5) { width: 15%; }
        .payment-table th:nth-child(6), .payment-table td:nth-child(6) { width: 20%; }
        .payment-table th:nth-child(7), .payment-table td:nth-child(7) { width: 16%; }
        .foot-table { width:100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; margin-top: 10px; border: 1px solid #111827; outline: 1px solid #111827; outline-offset: -1px; }
        .foot-table td { border-top: 1px solid #111827; border-left: 1px solid #111827; padding: 8px 10px; vertical-align: top; overflow-wrap: anywhere; word-break: break-word; }
        .foot-table tr > *:last-child { border-right: 1px solid #111827; }
        .foot-table tbody tr:last-child > * { border-bottom: 1px solid #111827; }
        .foot-table .note-col { width: 56%; line-height: 1.35; }
        .foot-table .sign-col { width: 44%; text-align: right; }
        .sign-box { min-height: 112px; }
        .sign-box .sign-bottom { margin-top: 56px; }
        .actions { margin-top: 14px; display:flex; gap:10px; }
        .btn { border:1px solid #006948; color:#006948; text-decoration:none; padding:8px 14px; border-radius:8px; font-weight:600; }
        .btn.fill { background:#006948; color:#fff; }
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            .actions { display: none !important; }
            body { background: #fff; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sheet { margin:0; width: 100%; max-width: none; border:1px solid #111827; padding: 4mm; page-break-after: avoid; }
            table {
                width: 100%;
                page-break-inside: avoid;
                border-collapse: separate;
                border-spacing: 0;
                table-layout: fixed;
                border: 1px solid #111827;
                outline: 1px solid #111827;
                outline-offset: -1px;
            }
            th, td {
                border-top: 1px solid #111827;
                border-left: 1px solid #111827;
                padding: 4px 5px;
                font-size: 10px;
                line-height: 1.2;
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            tr > *:last-child { border-right: 1px solid #111827; }
            tbody tr:last-child > * { border-bottom: 1px solid #111827; }
            thead tr:last-child > * { border-bottom: 1px solid #111827; }
            th { background: #e5e7eb; font-weight: 700; }
            .title { font-size: 23px; }
            .sub { font-size: 12px; }
            .section-title { margin-top: 10px; font-size: 16px; }
            .foot-table { margin-top: 8px; page-break-inside: avoid; }
            .foot-table td { padding: 5px 6px; font-size: 10px; line-height: 1.25; }
            .sign-box { min-height: 92px; }
            .sign-box .sign-bottom { margin-top: 38px; }
        }
    </style>
</head>
<body>
@php
    $qty = (int) ($batch->stock ?? 0);
    $price = (float) ($batch->purchase_price ?? 0);
    $subtotal = $qty * $price;
    $expeditionCost = (float) ($batch->expedition_cost ?? 0);
    $downPayment = (float) ($downPayment ?? 0);
    $installmentPaid = (float) ($installmentPaid ?? 0);
    $paymentHistory = $paymentHistory ?? [];
    $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $dueDate = $batch->credit_due_date;
    $dueDateText = $dueDate ? $dueDate->format('d') . ' ' . $monthNames[(int) $dueDate->format('n')] . ' ' . $dueDate->format('Y') : '-';
    $dueDateDisplay = $dueDate
        ? (((int) ($batch->credit_days ?? 0) > 0 ? ((int) $batch->credit_days . ' hari ') : '') . '(' . $dueDateText . ')')
        : '-';
    $supplierInvoiceNumber = $batch->supplier_invoice_number ?: '-';
    $purchaseDate = $batch->purchase_date ?: $batch->created_at;
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
<div class="sheet">
        <div class="row">
            <div>
                <h1 class="title">NOTA CICILAN KREDIT</h1>
                <p class="sub"><strong>Supplier:</strong> {{ $batch->supplier?->name ?? '-' }}</p>
                <p class="sub"><strong>No. Inv Supplier:</strong> {{ $supplierInvoiceNumber }}</p>
                <p class="sub"><strong>Tanggal Beli:</strong> {{ $formatNotaDate($purchaseDate, false) }}</p>
                <p class="sub"><strong>Part Number:</strong> {{ $batch->product?->barcode ?? '-' }}</p>
                <p class="sub"><strong>Part Name:</strong> {{ $batch->product?->name ?? '-' }}</p>
            </div>
        <div>
            <p class="sub"><strong>Cicilan Ke:</strong> {{ $installmentNumber ?? 1 }}</p>
            <p class="sub"><strong>Tanggal Bayar:</strong> {{ $formatNotaDate($installment->paid_at, false) }}</p>
            <p class="sub"><strong>Jatuh Tempo:</strong> {{ $dueDateDisplay }}</p>
            <p class="sub"><strong>Jam Input:</strong> {{ optional($installment->created_at)->format('H:i:s') ?? '-' }}</p>
            <p class="sub"><strong>Diproses Oleh:</strong> {{ $installment->processed_by ?? $installment->user?->name ?? '-' }}</p>
        </div>
    </div>

    <table class="payment-table">
        <tr><th>Subtotal Barang</th><td class="num">Rp {{ number_format($subtotal, 0, ',', '.') }}</td></tr>
        <tr><th>Biaya Ekspedisi</th><td class="num">Rp {{ number_format($expeditionCost, 0, ',', '.') }}</td></tr>
        <tr><th>Total Kredit</th><td class="num">Rp {{ number_format((float) $totalCredit, 0, ',', '.') }}</td></tr>
        <tr><th>DP / Uang Muka</th><td class="num">Rp {{ number_format($downPayment, 0, ',', '.') }}</td></tr>
        <tr><th>Total Cicilan</th><td class="num">Rp {{ number_format($installmentPaid, 0, ',', '.') }}</td></tr>
        <tr><th>Nominal Cicilan Ini</th><td class="num">Rp {{ number_format((float) $installment->amount, 0, ',', '.') }}</td></tr>
        <tr><th>Total Sudah Dibayar</th><td class="num">Rp {{ number_format((float) $totalPaid, 0, ',', '.') }}</td></tr>
        <tr><th>Sisa Kredit</th><td class="num">Rp {{ number_format((float) $remainingCredit, 0, ',', '.') }}</td></tr>
        <tr><th>Catatan</th><td>{{ $installment->note ?: '-' }}</td></tr>
    </table>

    <div class="section-title">Riwayat Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Nominal</th>
                <th>Diproses Oleh</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $paymentRowNumber = 0; @endphp
            @forelse(($paymentHistory ?? []) as $payment)
                @php
                    $isDownPayment = ($payment['type'] ?? '') === 'DP / Uang Muka';
                    if (! $isDownPayment) {
                        $paymentRowNumber++;
                    }
                @endphp
                <tr>
                    <td>{{ $isDownPayment ? 'DP' : $paymentRowNumber }}</td>
                    <td>{{ $payment['type'] ?? '-' }}</td>
                    <td>{{ $payment['date'] ?? '-' }}</td>
                    <td>{{ $payment['time'] ?? '-' }}</td>
                    <td class="num">Rp {{ number_format((float) ($payment['amount'] ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $payment['processed_by'] ?? $payment['user'] ?? '-' }}</td>
                    <td>{{ $payment['note'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Belum ada riwayat pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="foot-table">
        <tbody>
            <tr>
                <td class="note-col">
                    <strong>Catatan:</strong>
                    <div style="margin-top: 4px; padding-left: 10px; font-size: 10px; line-height: 1.35;">
                        <div>1. Simpan nota cicilan ini sebagai bukti pembayaran kredit.</div>
                        <div>2. Cocokkan nominal cicilan, sisa kredit, dan tanggal pembayaran.</div>
                    </div>
                </td>
                <td class="sign-col">
                    <div class="sign-box" style="text-align: right;">
                        <div style="font-size: 10px; font-weight: 700;">Yang Menyerahkan</div>
                        <div class="sign-bottom">
                            <strong style="font-size: 10px;">{{ $installment->processed_by ?? $installment->user?->name ?? 'Admin' }}</strong><br>
                            <span style="font-size: 9px;">Admin Toko/Gudang</span>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    @if(! (($pdf ?? false) || request()->boolean('pdf')))
        <div class="actions">
            <button class="btn fill" onclick="window.print()">Print Nota</button>
            <a class="btn" href="{{ route('admin.credits.installment.receipt', ['batch' => $batch->id, 'installment' => $installment->id, 'pdf' => 1]) }}">Download PDF</a>
            <a class="btn" href="{{ url('/admin/products') }}">Kembali ke Barang</a>
        </div>
    @endif
</div>
</body>
</html>

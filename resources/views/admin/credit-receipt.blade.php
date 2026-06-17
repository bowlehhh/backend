@php
    $storeName = config('app.name', 'Surya Duta Multindo');
    $supplierName = $batch->supplier?->name ?? '-';
    $supplierInvoiceNumber = $batch->supplier_invoice_number ?: '-';
    $partNumber = strtoupper((string) ($batch->product?->barcode ?? '-'));
    $partName = strtoupper((string) ($batch->product?->name ?? '-'));
    $unit = strtoupper((string) ($batch->product?->unit ?? '-'));
    $qty = (int) ($batch->stock ?? 0);
    $price = (float) ($batch->purchase_price ?? 0);
    $expeditionCost = (float) ($batch->expedition_cost ?? 0);
    $subtotal = $qty * $price;
    $total = (float) $totalCredit;
    $downPayment = (float) ($downPayment ?? 0);
    $installmentPaid = (float) ($installmentPaid ?? 0);
    $paid = (float) $totalPaid;
    $remaining = (float) $remainingCredit;
    $paymentHistory = $paymentHistory ?? [];
    $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $isCredit = strtoupper((string) ($batch->payment_type ?? 'LUNAS')) === 'KREDIT';
    $dueDate = $batch->credit_due_date;
    $dueDateText = $dueDate ? $dueDate->format('d') . ' ' . $monthNames[(int) $dueDate->format('n')] . ' ' . $dueDate->format('Y') : '-';
    $dueDateDisplay = $dueDate
        ? (((int) ($batch->credit_days ?? 0) > 0 ? ((int) $batch->credit_days . ' hari ') : '') . '(' . $dueDateText . ')')
        : '-';
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isCredit ? 'Nota Kredit' : 'Nota Pembelian Lunas' }} {{ $batch->display_inventory_code }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        :root { color-scheme: light; }
        body { margin: 0; padding: 0; background: #eef2f7; font-family: Arial, Helvetica, sans-serif; color: #0f172a; width: 100%; overflow-x: hidden; }
        .paper { width: 190mm; max-width: calc(100vw - 16px); margin: 8px auto; background: #fff; border: 2px solid #0f172a; padding: 16px; }
        .head { display: flex; justify-content: space-between; gap: 16px; }
        .title { font-size: 28px; font-weight: 800; margin: 0; }
        .sub { margin: 6px 0 0; font-size: 13px; }
        .meta { text-align: right; font-size: 14px; font-weight: 700; }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            margin-top: 12px;
            border: 1px solid #0f172a;
            outline: 1px solid #0f172a;
            outline-offset: -1px;
        }
        th, td {
            border-top: 1px solid #0f172a;
            border-left: 1px solid #0f172a;
            padding: 8px;
            font-size: 14px;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        tr > *:last-child { border-right: 1px solid #0f172a; }
        tbody tr:last-child > * { border-bottom: 1px solid #0f172a; }
        thead tr:last-child > * { border-bottom: 1px solid #0f172a; }
        th { background: #f1f5f9; text-transform: uppercase; font-size: 12px; letter-spacing: .02em; }
        .num { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .section-title { margin-top: 16px; font-size: 18px; font-weight: 700; }
        .purchase-table th:nth-child(1), .purchase-table td:nth-child(1) { width: 15%; }
        .purchase-table th:nth-child(2), .purchase-table td:nth-child(2) { width: 13%; }
        .purchase-table th:nth-child(3), .purchase-table td:nth-child(3) { width: 7.5%; }
        .purchase-table th:nth-child(4), .purchase-table td:nth-child(4) { width: 7%; }
        .purchase-table th:nth-child(5), .purchase-table td:nth-child(5) { width: 12.5%; }
        .purchase-table th:nth-child(6), .purchase-table td:nth-child(6) { width: 12.5%; }
        .purchase-table th:nth-child(7), .purchase-table td:nth-child(7) { width: 15.5%; }
        .purchase-table th:nth-child(8), .purchase-table td:nth-child(8) { width: 17%; }
        .payment-table th:nth-child(1), .payment-table td:nth-child(1) { width: 8%; }
        .payment-table th:nth-child(2), .payment-table td:nth-child(2) { width: 12%; }
        .payment-table th:nth-child(3), .payment-table td:nth-child(3) { width: 18%; }
        .payment-table th:nth-child(4), .payment-table td:nth-child(4) { width: 12%; }
        .payment-table th:nth-child(5), .payment-table td:nth-child(5) { width: 14%; }
        .payment-table th:nth-child(6), .payment-table td:nth-child(6) { width: 20%; }
        .payment-table th:nth-child(7), .payment-table td:nth-child(7) { width: 16%; }
        .foot-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; margin-top: 10px; border: 1px solid #0f172a; outline: 1px solid #0f172a; outline-offset: -1px; }
        .foot-table td { border-top: 1px solid #0f172a; border-left: 1px solid #0f172a; padding: 8px 10px; vertical-align: top; overflow-wrap: anywhere; word-break: break-word; }
        .foot-table tr > *:last-child { border-right: 1px solid #0f172a; }
        .foot-table tbody tr:last-child > * { border-bottom: 1px solid #0f172a; }
        .foot-table .note-col { width: 58%; line-height: 1.35; }
        .foot-table .sign-col { width: 42%; text-align: right; }
        .sign-box { min-height: 112px; }
        .sign-box .sign-bottom { margin-top: 56px; }
        .actions { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #0b7a5a; background: #0b7a5a; color: #fff; padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 12px; cursor: pointer; }
        .btn.secondary { background: #fff; color: #0b7a5a; }
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            .actions { display: none !important; }
            body { background: #fff; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .paper { margin: 0; width: 100%; max-width: none; border: 1px solid #0f172a; padding: 4mm; page-break-after: avoid; }
            .head { page-break-inside: avoid; }
            .title { font-size: 23px; }
            table {
                width: 100%;
                margin-top: 6px;
                page-break-inside: avoid;
                border-collapse: separate;
                border-spacing: 0;
                table-layout: fixed;
                border: 1px solid #0f172a;
                outline: 1px solid #0f172a;
                outline-offset: -1px;
            }
            th, td {
                border-top: 1px solid #0f172a;
                border-left: 1px solid #0f172a;
                padding: 4px 5px;
                font-size: 10px;
                line-height: 1.2;
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            tr > *:last-child { border-right: 1px solid #0f172a; }
            tbody tr:last-child > * { border-bottom: 1px solid #0f172a; }
            thead tr:last-child > * { border-bottom: 1px solid #0f172a; }
            th { background: #e5e7eb; font-weight: 700; }
            .section-title { margin-top: 10px; font-size: 16px; }
            .meta { font-size: 12px; }
            .sub { font-size: 12px; margin: 3px 0 0; }
            .purchase-table th { font-size: 9px; line-height: 1.15; }
            .foot-table { margin-top: 8px; page-break-inside: avoid; }
            .foot-table td { padding: 5px 6px; font-size: 10px; line-height: 1.25; }
            .foot-table .note-col { width: 56%; }
            .foot-table .sign-col { width: 44%; text-align: right; }
            .sign-box { min-height: 92px; }
            .sign-box .sign-bottom { margin-top: 38px; }
        }
    </style>
</head>
<body>
    <div class="paper">
        <div class="head">
            <div>
                <h1 class="title">{{ strtoupper($storeName) }}</h1>
                <p class="sub">{{ $isCredit ? 'NOTA KREDIT SUPPLIER' : 'NOTA PEMBELIAN LUNAS' }}</p>
                <p class="sub">Supplier: <strong>{{ $supplierName }}</strong></p>
                <p class="sub">No. Inv Supplier: <strong>{{ $supplierInvoiceNumber }}</strong></p>
            </div>
            <div class="meta">
                <div>Tanggal Cetak: {{ $formatNotaDate($printedAt) }}</div>
                <div>{{ $isCredit ? 'Jatuh Tempo: ' . $dueDateDisplay : 'Status: LUNAS' }}</div>
            </div>
        </div>

        <table class="purchase-table">
            <thead>
                <tr>
                    <th>Part<br>Number</th>
                    <th>Part<br>Name</th>
                    <th class="center">Unit</th>
                    <th class="center">Qty</th>
                    <th class="num">Harga<br>Beli</th>
                    <th class="num">Subtotal</th>
                    <th class="num">Biaya<br>Ekspedisi</th>
                    <th class="num">{!! $isCredit ? 'Total<br>Kredit' : 'Total<br>Pembelian' !!}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $partNumber }}</td>
                    <td>{{ $partName }}</td>
                    <td class="center">{{ $unit }}</td>
                    <td class="center">{{ number_format($qty, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($expeditionCost, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <tbody>
                @if ($isCredit)
                    <tr><td><strong>DP / Uang Muka</strong></td><td class="num"><strong>Rp {{ number_format($downPayment, 0, ',', '.') }}</strong></td></tr>
                    <tr><td><strong>Total Cicilan</strong></td><td class="num"><strong>Rp {{ number_format($installmentPaid, 0, ',', '.') }}</strong></td></tr>
                    <tr><td><strong>Total Dibayar</strong></td><td class="num"><strong>Rp {{ number_format($paid, 0, ',', '.') }}</strong></td></tr>
                    <tr><td><strong>Sisa Kredit</strong></td><td class="num"><strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong></td></tr>
                    <tr><td><strong>Status</strong></td><td class="num"><strong>{{ $remaining <= 0 ? 'LUNAS' : 'BELUM LUNAS' }}</strong></td></tr>
                @else
                    <tr><td><strong>Total Pembelian</strong></td><td class="num"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td></tr>
                    <tr><td><strong>Total Dibayar</strong></td><td class="num"><strong>Rp {{ number_format($paid, 0, ',', '.') }}</strong></td></tr>
                    <tr><td><strong>Status</strong></td><td class="num"><strong>LUNAS</strong></td></tr>
                @endif
            </tbody>
        </table>

        <div class="section-title">Riwayat Pembayaran</div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Tanggal Bayar</th>
                    <th>Jam</th>
                    <th class="num">Nominal</th>
                    <th>Diproses Oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php $paymentRowNumber = 0; @endphp
                @forelse($paymentHistory as $payment)
                    @php
                        $isDownPayment = ($payment['type'] ?? '') === 'DP / Uang Muka';
                        if (! $isDownPayment) {
                            $paymentRowNumber++;
                        }
                    @endphp
                    <tr>
                        <td class="center">{{ $isDownPayment ? 'DP' : $paymentRowNumber }}</td>
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
                            <div>1. Simpan nota ini sebagai bukti pembelian resmi.</div>
                            <div>2. Pastikan data supplier, nominal, dan status pembayaran sudah sesuai.</div>
                        </div>
                    </td>
                    <td class="sign-col">
                        <div class="sign-box" style="text-align: right;">
                            <div style="font-size: 10px; font-weight: 700;">Yang Menyerahkan</div>
                            <div class="sign-bottom">
                                <strong style="font-size: 10px;">{{ $paymentHistory[0]['processed_by'] ?? $paymentHistory[0]['user'] ?? 'Admin' }}</strong><br>
                                <span style="font-size: 9px;">Admin Toko/Gudang</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        @if(! (($pdf ?? false) || request()->boolean('pdf')))
            <div class="actions">
                <button type="button" class="btn primary" onclick="window.print()">Print Nota</button>
                <a class="btn" href="{{ url('/admin/products') }}">Kembali ke Barang</a>
                <a class="btn" href="{{ route('admin.credits.receipt', ['batch' => $batch->id, 'pdf' => 1]) }}">Download PDF</a>
            </div>
        @endif
    </div>
</body>
</html>

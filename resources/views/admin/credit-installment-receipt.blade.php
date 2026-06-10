<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Cicilan Kredit</title>
    <style>
        body { font-family: Arial, sans-serif; color:#111; background:#fff; }
        .sheet { max-width: 900px; margin: 20px auto; border: 2px solid #111827; padding: 16px; }
        .row { display:flex; justify-content:space-between; gap:16px; }
        .title { font-size: 34px; font-weight: 800; margin:0; }
        .sub { margin: 4px 0; }
        table { width:100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #111827; padding: 8px; text-align:left; }
        .num { text-align:right; }
        .section-title { margin-top: 16px; font-size: 18px; font-weight: 700; }
        .actions { margin-top: 14px; display:flex; gap:10px; }
        .btn { border:1px solid #006948; color:#006948; text-decoration:none; padding:8px 14px; border-radius:8px; font-weight:600; }
        .btn.fill { background:#006948; color:#fff; }
        @media print { .actions { display:none; } .sheet { margin:0; border-width:2px; } }
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
@endphp
<div class="sheet">
        <div class="row">
            <div>
                <h1 class="title">NOTA CICILAN KREDIT</h1>
                <p class="sub"><strong>Supplier:</strong> {{ $batch->supplier?->name ?? '-' }}</p>
                <p class="sub"><strong>No. Inv Supplier:</strong> {{ $supplierInvoiceNumber }}</p>
                <p class="sub"><strong>Part Number:</strong> {{ $batch->product?->barcode ?? '-' }}</p>
                <p class="sub"><strong>Part Name:</strong> {{ $batch->product?->name ?? '-' }}</p>
            </div>
        <div>
            <p class="sub"><strong>Cicilan Ke:</strong> {{ $installmentNumber ?? 1 }}</p>
            <p class="sub"><strong>Tanggal Bayar:</strong> {{ optional($installment->paid_at)->format('d M Y') ?? '-' }}</p>
            <p class="sub"><strong>Jatuh Tempo:</strong> {{ $dueDateDisplay }}</p>
            <p class="sub"><strong>Jam Input:</strong> {{ optional($installment->created_at)->format('H:i:s') ?? '-' }}</p>
            <p class="sub"><strong>Diproses Oleh:</strong> {{ $installment->processed_by ?? $installment->user?->name ?? '-' }}</p>
        </div>
    </div>

    <table>
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

    @if(! ($pdf ?? false))
        <div class="actions">
            <button class="btn fill" onclick="window.print()">Print Nota</button>
            <a class="btn" href="{{ route('admin.credits.installment.receipt', ['batch' => $batch->id, 'installment' => $installment->id, 'pdf' => 1]) }}">Download PDF</a>
            <a class="btn" href="{{ url('/admin/products') }}">Kembali ke Barang</a>
        </div>
    @endif
</div>
</body>
</html>

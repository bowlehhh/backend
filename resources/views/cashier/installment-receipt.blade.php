<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Cicilan - {{ $sale->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<main class="mx-auto max-w-3xl p-4 lg:p-6">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Nota Cicilan</h1>
            <p class="text-sm text-slate-500">Invoice: {{ $sale->invoice_number }}</p>
        </div>
        <a href="{{ $installmentUrl ?? route('cashier.history.installment.form', $sale) }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-slate-500">Tanggal Bayar</p>
                <p class="font-semibold">{{ $installment->paid_at?->format('d M Y H:i') ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-500">Kasir</p>
                <p class="font-semibold">{{ $sale->cashier_display_name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-500">Nominal Cicilan</p>
                <p class="font-semibold text-emerald-700">Rp {{ number_format((float) $installment->amount, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-500">Sisa Kredit</p>
                <p class="font-semibold text-amber-700">Rp {{ number_format((float) $remainingCredit, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="mt-4 rounded-xl bg-slate-50 p-4">
            <p class="text-xs uppercase text-slate-500">Catatan</p>
            <p class="mt-1 text-sm">{{ $installment->note ?: '-' }}</p>
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-sm font-semibold text-slate-800">Track Record Kredit</p>
                <p class="text-xs text-slate-500">Termasuk DP dan semua cicilan yang sudah dibayar.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Jenis</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Nominal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Kasir</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold">DP / Uang Muka</td>
                            <td class="px-4 py-3">{{ $sale->created_at?->format('d M Y H:i') ?: '-' }}</td>
                            <td class="px-4 py-3 font-semibold text-emerald-700">Rp {{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $sale->cashier_display_name }}</td>
                            <td class="px-4 py-3">DP saat transaksi pertama</td>
                        </tr>
                        @foreach($sale->installments as $row)
                            <tr class="border-t border-slate-100 {{ (int) $row->id === (int) $installment->id ? 'bg-emerald-50' : '' }}">
                                <td class="px-4 py-3 font-semibold">Cicilan</td>
                                <td class="px-4 py-3">{{ $row->paid_at?->format('d M Y H:i') ?: '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-emerald-700">Rp {{ number_format((float) $row->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $sale->cashier_display_name }}</td>
                                <td class="px-4 py-3">{{ $row->note ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(! ($pdf ?? false))
            <div class="mt-6 flex flex-wrap gap-2">
                <button type="button" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white" onclick="window.print()">Print Nota</button>
                <a href="{{ $historyUrl ?? route('cashier.history') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Kembali ke History</a>
            </div>
        @endif
    </div>
</main>
@if(! ($pdf ?? false) && session('last_installment_change', 0) > 0)
<div style="margin-top:12px;padding:8px 10px;border:1px solid #fde68a;background:#fffbeb;color:#92400e;font-size:12px;font-weight:600;">
    Uang sisa / kembalian: Rp {{ number_format((float) session('last_installment_change', 0), 0, ',', '.') }}
</div>
@endif
</body>
</html>

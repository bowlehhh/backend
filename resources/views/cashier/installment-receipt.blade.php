<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Cicilan - {{ $sale->invoice_number }}</title>
    <x-brand.meta />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        html { overflow-x: hidden; }
        .foot-wrap { border: 1px solid #e2e8f0; border-radius: 16px; background: #fff; margin-top: 16px; display: grid; grid-template-columns: minmax(0, 1fr) 300px; }
        .foot-note, .foot-sign { padding: 16px 18px; }
        .foot-note { border-right: 1px solid #e2e8f0; }
        .foot-sign { text-align: right; }
        .foot-sign .sign-bottom { margin-top: 64px; }
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            body { background: #fff; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; width: 100%; overflow-x: hidden; }
            main { margin: 0; width: auto; max-width: none; padding: 5mm; page-break-after: avoid; }
            .no-print, .actions { display: none !important; }
            .foot-wrap { margin-top: 10px; border-color: #0f172a; page-break-inside: avoid; }
            .foot-note, .foot-sign { padding: 10px 12px; }
            .foot-note { border-right-color: #0f172a; }
            .foot-sign .sign-bottom { margin-top: 42px; }
        }
    </style>
</head>
<body class="text-slate-900">
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

    $saleSubtotalBeforeDiscount = (float) $sale->items->sum(function ($item) {
        return (float) (($item->price ?? 0) * ($item->qty ?? 0));
    });
    $saleDiscountAmount = (float) ($sale->discount_amount ?? 0);
    $saleDiscountPercent = $saleSubtotalBeforeDiscount > 0
        ? ($saleDiscountAmount / $saleSubtotalBeforeDiscount) * 100
        : 0;
    $saleGrandTotal = (float) ($sale->total ?? max(0, $saleSubtotalBeforeDiscount - $saleDiscountAmount));
    $saleDownPayment = (float) ($sale->paid_amount ?? 0);
@endphp
<main class="mx-auto max-w-3xl p-4 lg:p-6">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <x-brand.logo class="mb-3 h-11 w-auto" />
            <h1 class="text-2xl font-extrabold">Nota Cicilan</h1>
            <p class="text-sm text-slate-500">Invoice: {{ $sale->invoice_number }}</p>
        </div>
        <a href="{{ $installmentUrl ?? route('cashier.history.installment.form', $sale) }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali</a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-slate-500">Tanggal Bayar</p>
                <p class="font-semibold">{{ $formatNotaDate($installment->paid_at) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-500">Admin</p>
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
                <p class="text-sm font-semibold text-slate-800">Ringkasan Invoice Awal</p>
                <p class="text-xs text-slate-500">Diskon dari transaksi awal ikut ditampilkan di nota cicilan.</p>
            </div>
            <div class="grid gap-3 p-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase text-slate-500">Subtotal</p>
                    <p class="font-semibold">Rp {{ number_format($saleSubtotalBeforeDiscount, 0, ',', '.') }}</p>
                </div>
                @if($saleDiscountAmount > 0)
                    <div>
                        <p class="text-xs uppercase text-slate-500">
                            Diskon{{ $saleDiscountPercent > 0 ? ' (' . rtrim(rtrim(number_format($saleDiscountPercent, 2, '.', ''), '0'), '.') . '%)' : '' }}
                        </p>
                        <p class="font-semibold text-rose-700">- Rp {{ number_format($saleDiscountAmount, 0, ',', '.') }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs uppercase text-slate-500">Grand Total Invoice</p>
                    <p class="font-semibold text-slate-900">Rp {{ number_format($saleGrandTotal, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">DP / Uang Muka</p>
                    <p class="font-semibold text-emerald-700">Rp {{ number_format($saleDownPayment, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Total Cicilan Terbayar</p>
                    <p class="font-semibold text-emerald-700">Rp {{ number_format((float) $installmentPaid, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Sisa Kredit Setelah Cicilan Ini</p>
                    <p class="font-semibold text-amber-700">Rp {{ number_format((float) $remainingCredit, 0, ',', '.') }}</p>
                </div>
            </div>
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
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Admin</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold">DP / Uang Muka</td>
                            <td class="px-4 py-3">{{ $formatNotaDate($sale->created_at) }}</td>
                            <td class="px-4 py-3 font-semibold text-emerald-700">Rp {{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $sale->cashier_display_name }}</td>
                            <td class="px-4 py-3">DP saat transaksi pertama</td>
                        </tr>
                        @foreach($sale->installments as $row)
                            <tr class="border-t border-slate-100 {{ (int) $row->id === (int) $installment->id ? 'bg-emerald-50' : '' }}">
                                <td class="px-4 py-3 font-semibold">Cicilan</td>
                                <td class="px-4 py-3">{{ $formatNotaDate($row->paid_at) }}</td>
                                <td class="px-4 py-3 font-semibold text-emerald-700">Rp {{ number_format((float) $row->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $sale->cashier_display_name }}</td>
                                <td class="px-4 py-3">{{ $row->note ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="foot-wrap">
            <div class="foot-note">
                <p class="text-sm font-bold text-slate-900">Catatan:</p>
                <div class="mt-2 text-sm leading-6 text-slate-700">
                    <div>1. Simpan nota cicilan ini sebagai bukti pembayaran resmi.</div>
                    <div>2. Pastikan nominal cicilan dan sisa kredit sudah sesuai.</div>
                </div>
            </div>
            <div class="foot-sign">
                <p class="text-sm font-bold text-slate-900">Yang Menyerahkan</p>
                <div class="sign-bottom">
                    <strong class="text-sm">{{ $sale->cashier_display_name }}</strong><br>
                    <span class="text-xs text-slate-600">{{ $sale->cashier_phone ?: 'Admin' }}</span>
                </div>
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

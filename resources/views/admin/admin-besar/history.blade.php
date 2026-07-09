<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>History Admin Besar - Surya Duta Multindo</title>
    <x-brand.meta />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }
        .history-mobile-card { display: none; }
        @media (max-width: 767px) {
            .history-mobile-card { display: block; }
            .history-desktop-table { display: none; }
            .history-shell { padding: 12px; }
            .history-title { font-size: 22px; line-height: 28px; }
            .history-header { gap: .5rem; padding: 12px 12px 10px; height: 56px; }
            .history-logo { max-width: 150px; height: 32px; }
        }
    </style>
</head>
<body class="text-slate-900">
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[260px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <x-brand.logo class="h-10 w-auto" />
            <p class="text-xs text-slate-500">Admin Besar</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('admin.admin-besar.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">dashboard</span><span class="font-semibold">Admin Besar</span>
            </a>
            <a href="{{ route('admin.admin-besar.history') }}" class="flex items-center gap-3 rounded-xl bg-emerald-700 px-3 py-2 text-white">
                <span class="material-symbols-outlined">history</span><span class="font-semibold">History</span>
            </a>
            <a href="{{ route('admin.admin-besar.history.supplier') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">account_tree</span><span class="font-semibold">PT/CV</span>
            </a>
            <a href="{{ route('admin.transaksi.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span><span class="font-semibold">Akses Dashboard Admin Gudang</span>
            </a>
            <a href="{{ url('/admin/admin-module?type=users') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">group</span><span class="font-semibold">Manajemen Akun</span>
            </a>
        </nav>
        <div class="p-4 border-t border-slate-200">
            <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <span class="material-symbols-outlined">lock_clock</span><span class="font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6 history-shell">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="history-title text-2xl font-extrabold">History Transaksi</h2>
                <p class="text-sm text-slate-500">{{ $user?->name }} - Admin Besar</p>
            </div>
            <a href="{{ route('admin.admin-besar.index') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Admin Besar</a>
        </div>

        <div class="space-y-3 history-mobile-card">
            @forelse($sales as $sale)
                @php
                    $isCredit = (float) ($sale['remaining_credit'] ?? 0) > 0;
                    $statusClass = $sale['payment_status'] === 'LUNAS'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700';
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Invoice</p>
                            <p class="mt-1 break-words text-lg font-extrabold text-slate-900">{{ $sale['invoice_number'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $sale['customer_name'] }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $sale['payment_status'] }}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Waktu</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $sale['created_at'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Metode</p>
                            <p class="mt-1 text-sm font-semibold uppercase text-slate-900">{{ $sale['payment_method'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Total</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">Rp {{ number_format((float) $sale['total'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Sisa Kredit</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $isCredit ? 'Rp ' . number_format((float) $sale['remaining_credit'], 0, ',', '.') : '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ $sale['receipt_url'] }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                        <a href="{{ $sale['receipt_url'] }}?pdf=1" target="_blank" rel="noopener" class="inline-flex flex-1 items-center justify-center rounded-xl border border-emerald-700 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Print</a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-8 text-center text-slate-500">Belum ada transaksi.</div>
            @endforelse
        </div>

        <div class="history-desktop-table overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto w-full max-w-full">
                <table class="min-w-[1280px] w-max text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Pembeli</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Metode</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Total</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Sisa Kredit</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 font-semibold">{{ $sale['invoice_number'] }}</td>
                                <td class="px-4 py-3">{{ $sale['customer_name'] }}</td>
                                <td class="px-4 py-3">{{ $sale['created_at'] }}</td>
                                <td class="px-4 py-3 uppercase">{{ $sale['payment_method'] }}</td>
                                <td class="px-4 py-3 text-right font-bold">Rp {{ number_format((float) $sale['total'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ (float) $sale['remaining_credit'] > 0 ? 'Rp ' . number_format((float) $sale['remaining_credit'], 0, ',', '.') : '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $sale['payment_status'] === 'LUNAS' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                        {{ $sale['payment_status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ $sale['receipt_url'] }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat Detail</a>
                                        <a href="{{ $sale['receipt_url'] }}?pdf=1" target="_blank" rel="noopener" class="rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Print</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
@include('cashier.partials.logout-modal')
</body>
</html>

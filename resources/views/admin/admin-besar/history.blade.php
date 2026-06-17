<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>History Admin Besar - Surya Duta Multindo</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[260px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <h1 class="text-3xl font-extrabold text-emerald-700">Surya Duta Multindo</h1>
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

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold">History Transaksi</h2>
                <p class="text-sm text-slate-500">{{ $user?->name }} - Admin Besar</p>
            </div>
            <a href="{{ route('admin.admin-besar.index') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Admin Besar</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
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

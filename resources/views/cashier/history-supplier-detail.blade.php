<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail PT/CV - Surya Duta Multindo</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
@php
    $isAdminBesarContext = request()->routeIs('admin.admin-besar.*') || str_starts_with(request()->path(), 'admin/admin-besar');
    $homeUrl = $isAdminBesarContext ? route('admin.admin-besar.index') : route('cashier.dashboard');
    $historyUrl = $isAdminBesarContext ? route('admin.admin-besar.history') : route('cashier.history');
    $supplierUrl = $isAdminBesarContext ? route('admin.admin-besar.history.supplier') : route('cashier.history.supplier');
    $backLabel = $isAdminBesarContext ? 'Kembali ke Admin Besar' : 'Kembali ke PT/CV';
@endphp
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[260px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <h1 class="text-3xl font-extrabold text-emerald-700">Surya Duta Multindo</h1>
            <p class="text-xs text-slate-500">{{ $isAdminBesarContext ? 'Admin Besar' : 'Admin Penjualan - Station 01' }}</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ $homeUrl }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">{{ $isAdminBesarContext ? 'dashboard' : 'point_of_sale' }}</span><span class="font-semibold">{{ $isAdminBesarContext ? 'Admin Besar' : 'Penjualan' }}</span>
            </a>
            <a href="{{ $historyUrl }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">history</span><span class="font-semibold">History</span>
            </a>
            <a href="{{ $supplierUrl }}" class="flex items-center gap-3 rounded-xl bg-indigo-500 px-3 py-2 text-white">
                <span class="material-symbols-outlined">account_tree</span><span class="font-semibold">PT/CV</span>
            </a>
            <a href="{{ route('cashier.drafts') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">draft</span><span class="font-semibold">Draft</span>
            </a>
        </nav>
        <div class="px-4 pb-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                <p class="px-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Master Data</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ url('/admin/products') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">inventory_2</span><span class="font-semibold">Daftar Stok</span>
                    </a>
                    <a href="{{ url('/admin/suppliers') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">local_shipping</span><span class="font-semibold">Supplier</span>
                    </a>
                    <a href="{{ url('/admin/admin-module?type=product-groups') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">inventory_2</span><span class="font-semibold">Kelompok Stok</span>
                    </a>
                </div>
            </div>
        </div>
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
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold">Detail Transaksi Pembeli</h2>
                <p class="text-sm text-slate-500">{{ $user?->name }} - {{ $isAdminBesarContext ? 'Admin Besar' : 'Admin' }}</p>
            </div>
            <a href="{{ $supplierUrl }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">{{ $backLabel }}</a>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-4 md:px-5">
                <h3 class="text-2xl font-extrabold text-slate-900">{{ $group['pt_name'] }}</h3>
                <p class="mt-1 text-sm text-slate-500">Semua transaksi dengan nama pembeli yang sama akan masuk ke halaman ini.</p>
            </div>

            <div class="grid gap-3 border-b border-slate-200 bg-slate-50 p-4 md:grid-cols-5">
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ number_format((int) $group['summary']['total_transaksi']) }} kali</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ number_format((int) $group['summary']['total_qty']) }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Nilai</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ $group['summary']['total_nilai'] }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kredit</p>
                    <p class="mt-1 text-lg font-bold text-amber-600">{{ number_format((int) $group['summary']['kredit']) }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lunas</p>
                    <p class="mt-1 text-lg font-bold text-emerald-600">{{ number_format((int) $group['summary']['lunas']) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Metode</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Qty</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Subtotal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Kredit</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($group['transactions'] as $trx)
                        @php
                            $statusClass = $trx['status'] === 'LUNAS'
                                ? 'bg-emerald-100 text-emerald-700'
                                : ($trx['status'] === 'JATUH TEMPO' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                        @endphp
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold">{{ $trx['invoice_number'] }}</td>
                            <td class="px-4 py-3">{{ $trx['waktu'] }}</td>
                            <td class="px-4 py-3">{{ $trx['metode'] }}</td>
                            <td class="px-4 py-3">{{ number_format((int) $trx['qty']) }}</td>
                            <td class="px-4 py-3">{{ $trx['subtotal'] }}</td>
                            <td class="px-4 py-3">{{ $trx['credit_amount'] }}</td>
                            <td class="px-4 py-3">{{ $trx['credit_due_date'] }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $trx['status'] }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('cashier.receipt', ['sale' => $trx['sale_id']]) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="{{ route('cashier.receipt', ['sale' => $trx['sale_id'], 'pdf' => 1]) }}" target="_blank" rel="noopener" class="rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Nota</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi untuk PT/CV ini.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@include('cashier.partials.logout-modal')
</body>
</html>

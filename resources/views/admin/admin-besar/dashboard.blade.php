<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin Besar - {{ config('app.name', 'Surya Duta Multindo') }}</title>
    <x-brand.meta />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        :root { --sb: #0b6b52; }
        body { font-family: 'Hanken Grotesk', sans-serif; background: #f6f8fb; color: #111827; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .scrollbar::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 999px; }
        summary::-webkit-details-marker { display: none; }
        details[open] .company-toggle-closed { display: none; }
        details:not([open]) .company-toggle-open { display: none; }
    </style>
</head>
<body>
@php
    $userName = $user?->name ?? 'Admin Besar';
    $stats = $stats ?? [];
    $companyRecap = $companyRecap ?? ['summary' => ['company_count' => 0, 'invoice_count' => 0, 'credit_count' => 0, 'lunas_count' => 0, 'grand_total' => 'Rp 0'], 'groups' => []];
    $recentTransactions = $recentTransactions ?? [];
    $activityFeed = $activityFeed ?? [];
@endphp
<div class="min-h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[280px] flex-col border-r border-slate-300 bg-white">
        <div class="border-b border-slate-200 px-6 py-6">
            <p class="text-xs font-bold uppercase tracking-[0.28em] text-slate-500">Admin Besar</p>
            <div class="mt-3">
                <x-brand.logo variant="stacked" class="h-auto w-full max-w-[220px]" />
            </div>
            <p class="mt-3 text-sm text-slate-500">Dashboard utama untuk pantau transaksi, nota, PT/CV, dan riwayat admin.</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('admin.admin-besar.index') }}" class="flex items-center gap-3 rounded-xl bg-emerald-700 px-4 py-3 text-white shadow-sm">
                <span class="material-symbols-outlined">dashboard</span><span class="font-semibold">Admin Besar</span>
            </a>
            <a href="{{ route('admin.admin-besar.history') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">history</span><span class="font-semibold">History</span>
            </a>
            <a href="{{ route('admin.admin-besar.history.supplier') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">account_tree</span><span class="font-semibold">PT/CV</span>
            </a>
            <a href="{{ route('admin.transaksi.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span><span class="font-semibold">Akses Dashboard Admin Gudang</span>
            </a>
            <a href="{{ url('/admin/admin-module?type=users') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">group</span><span class="font-semibold">Manajemen Akun</span>
            </a>
        </nav>
        <div class="border-t border-slate-200 p-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <span class="material-symbols-outlined">logout</span><span class="font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="h-screen overflow-y-auto lg:ml-[280px]">
        <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-4 lg:px-6 lg:py-6">
            <section class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm lg:hidden">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Admin Besar</p>
                        <h1 class="mt-1 text-2xl font-extrabold text-emerald-700">Dashboard Pemantau Toko/Gudang</h1>
                        <p class="mt-2 text-sm text-slate-500">Pantau transaksi, nota, subtotal, dan aktivitas admin dari satu tempat.</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            Logout
                        </button>
                    </form>
                </div>
            </section>

            <section class="hidden rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-sm lg:block">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-slate-500">Admin Besar</p>
                        <h1 class="mt-2 text-3xl font-extrabold text-emerald-700">Dashboard Pemantau Toko/Gudang</h1>
                        <p class="mt-2 text-sm text-slate-500">Pantau semua transaksi, nota, subtotal, dan aktivitas admin toko/gudang dari satu tempat.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ $userName }}</p>
                            <p class="text-xs text-slate-500">Role: Admin Besar</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Transaksi</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format((int) ($stats['total_transactions'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Semua nota penjualan aktif</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format((int) ($stats['today_transactions'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Masuk hari ini</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi Bulan Ini</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format((int) ($stats['month_transactions'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Periode berjalan</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nilai Total</p>
                    <p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['total_value'] ?? 'Rp 0' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Subtotal seluruh transaksi</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aktivitas Admin Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold text-violet-700">{{ number_format((int) ($stats['admin_actions_today'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Log dari role Admin Toko/Gudang</p>
                </div>
            </section>

            <section class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-sky-50 shadow-sm">
            <div class="flex flex-col gap-4 border-b border-emerald-100 px-5 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700">Rekap Perusahaan</p>
                    <h2 class="text-lg font-bold text-slate-900">PT / CV, invoice, kredit, dan lunas dalam satu panel</h2>
                    <p class="text-sm text-slate-500">Klik nama perusahaan untuk membuka invoice, lalu klik nota untuk masuk ke detail invoice.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.admin-besar.history') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-700 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                        <span class="material-symbols-outlined text-[18px]">history</span>
                        History Nota
                    </a>
                </div>
            </div>

            <div class="grid gap-3 p-5 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Perusahaan</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format((int) ($companyRecap['summary']['company_count'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">PT / CV yang punya transaksi</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format((int) ($companyRecap['summary']['invoice_count'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Seluruh nota dalam grup PT / CV</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kredit</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format((int) ($companyRecap['summary']['credit_count'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Invoice yang masih berjalan</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lunas</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format((int) ($companyRecap['summary']['lunas_count'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">Invoice yang sudah selesai</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Grand Total</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $companyRecap['summary']['grand_total'] ?? 'Rp 0' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Total nilai semua perusahaan</p>
                </div>
            </div>

            <div class="space-y-4 px-5 pb-5">
                @forelse($companyRecap['groups'] as $group)
                    <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" {{ $loop->first ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="truncate text-xl font-extrabold text-slate-900">{{ $group['company_name'] }}</h3>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ number_format((int) ($group['invoice_count'] ?? 0), 0, ',', '.') }} Invoice
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">Terakhir transaksi {{ $group['last_transaction_at'] ?? '-' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <span class="company-toggle-closed inline-flex rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">Buka</span>
                                <span class="company-toggle-open inline-flex rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">Tutup</span>
                            </div>
                        </summary>
                        <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-4">
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice Per PT</p>
                                        <p class="mt-1 text-sm text-slate-600">Formatnya dibuat seperti contoh yang kamu kirim: invoice ada di bawah nama PT, lalu subtotal di bagian bawah.</p>
                                    </div>
                                    <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">{{ number_format((int) ($group['invoice_count'] ?? 0), 0, ',', '.') }} Invoice</span>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @foreach($group['invoices'] as $invoice)
                                        <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900">{{ $invoice['invoice_number'] }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $invoice['created_at'] }}</p>
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <p class="font-semibold text-slate-900">{{ $invoice['subtotal'] }}</p>
                                                <a href="{{ $invoice['receipt_url'] }}" class="mt-2 inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                                    Detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subtotal</p>
                                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $group['grand_total'] ?? 'Rp 0' }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Invoice {{ number_format((int) ($group['invoice_count'] ?? 0), 0, ',', '.') }}</span>
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Kredit {{ number_format((int) ($group['credit_count'] ?? 0), 0, ',', '.') }}</span>
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Lunas {{ number_format((int) ($group['lunas_count'] ?? 0), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="rounded-2xl border border-dashed border-emerald-200 bg-white px-6 py-10 text-center text-slate-500">
                        Belum ada data PT/CV untuk direkap.
                    </div>
                @endforelse
            </div>
            </section>

            <section id="rekap-invoice" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Rekap Invoice</p>
                    <h2 class="text-lg font-bold text-slate-900">Daftar invoice dipisah dari rekap PT</h2>
                    <p class="text-sm text-slate-500">Semua nota tampil dalam tabel tersendiri supaya alurnya lebih jelas saat dibaca.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-700">
                        {{ number_format((int) ($companyRecap['summary']['invoice_count'] ?? 0), 0, ',', '.') }} Invoice
                    </span>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                        {{ $companyRecap['summary']['grand_total'] ?? 'Rp 0' }}
                    </span>
                </div>
            </div>

            @php
                $invoiceRecap = collect($companyRecap['groups'] ?? [])
                    ->flatMap(fn ($group) => collect($group['invoices'] ?? [])->map(fn ($invoice) => array_merge($invoice, [
                        'company_name' => $group['company_name'] ?? '-',
                    ])))
                    ->sortByDesc('created_ts')
                    ->values();
            @endphp

            <div class="overflow-x-auto scrollbar">
                <div class="grid gap-3 p-5 md:grid-cols-2 xl:grid-cols-4">
                    @forelse($invoiceRecap as $invoice)
                        <a href="{{ $invoice['receipt_url'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Invoice</p>
                                    <h3 class="mt-2 text-lg font-extrabold text-slate-900 group-hover:text-emerald-700">{{ $invoice['invoice_number'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $invoice['company_name'] }}</p>
                                </div>
                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                    {{ $invoice['status'] }}
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Subtotal</p>
                                    <p class="mt-1 text-base font-bold text-slate-900">{{ $invoice['subtotal'] }}</p>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                    Buka Detail
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500 md:col-span-2 xl:col-span-4">
                            Belum ada invoice untuk ditampilkan.
                        </div>
                    @endforelse
                </div>
            </div>
            </section>

            <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Semua Nota & Subtotal</h2>
                        <p class="text-sm text-slate-500">Daftar transaksi terbaru lengkap dengan subtotal, total, dan receipt.</p>
                    </div>
                    <a href="{{ route('admin.admin-besar.history') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                        <span class="material-symbols-outlined text-[18px]">history</span>
                        Lihat History
                    </a>
                </div>

                <div class="overflow-x-auto scrollbar">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Invoice</th>
                                <th class="px-5 py-3">Kasir / Admin</th>
                                <th class="px-5 py-3">Subtotal</th>
                                <th class="px-5 py-3">Total</th>
                                <th class="px-5 py-3">Metode</th>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($recentTransactions as $trx)
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">{{ $trx['invoice'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $trx['customer'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ number_format((int) $trx['items_count'], 0, ',', '.') }} item</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">{{ $trx['cashier'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Nota diterbitkan dari transaksi aktif</p>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $trx['subtotal'] }}</td>
                                    <td class="px-5 py-4 font-semibold text-emerald-700">{{ $trx['total'] }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $trx['payment_method'] }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $trx['created_at'] }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ $trx['receipt_url'] }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                            Nota
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-slate-500">Belum ada transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900">Log Aktivitas Admin Toko/Gudang</h2>
                    <p class="text-sm text-slate-500">Apa saja yang dilakukan role admin: stok masuk, stok keluar, edit transaksi, dan hapus transaksi.</p>
                </div>

                <div class="max-h-[540px] overflow-y-auto scrollbar p-4">
                    <div class="space-y-3">
                        @forelse($activityFeed as $item)
                            @php
                                $detailRows = collect($item['details'] ?? [])->filter(fn ($detail) => filled($detail['value'] ?? null))->values();
                                $lineItems = collect($item['line_items'] ?? [])->filter(fn ($detail) => filled($detail['title'] ?? null))->values();
                                $hasExpandableDetail = $detailRows->isNotEmpty() || $lineItems->isNotEmpty() || ! empty($item['url']);
                            @endphp
                            <details class="group rounded-2xl border border-slate-200 bg-slate-50 p-4" @if(! $hasExpandableDetail) open @endif>
                                <summary class="list-none cursor-pointer">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 rounded-xl bg-white p-2 text-emerald-700 shadow-sm">
                                            <span class="material-symbols-outlined text-[20px]">
                                                {{ $item['kind'] === 'activity' ? 'fact_check' : ($item['kind'] === 'stock' ? 'inventory_2' : ($item['kind'] === 'sale_edit' ? 'edit_note' : 'delete_forever')) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-bold text-slate-900">{{ $item['title'] }}</h3>
                                                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-600">{{ $item['actor'] }}</span>
                                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">{{ $item['created_at'] }}</span>
                                                @if($hasExpandableDetail)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-2.5 py-1 text-[11px] font-bold text-white">
                                                        <span class="material-symbols-outlined text-[14px] group-open:hidden">expand_more</span>
                                                        <span class="material-symbols-outlined hidden text-[14px] group-open:inline-flex">expand_less</span>
                                                        Detail
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-sm text-slate-700">{{ $item['detail'] }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ $item['note'] }}</p>
                                            <p class="mt-2 text-xs font-semibold text-slate-600">{{ $item['value'] }}</p>
                                        </div>
                                    </div>
                                </summary>

                                @if($hasExpandableDetail)
                                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                                        @if($detailRows->isNotEmpty())
                                            <div class="grid gap-3 md:grid-cols-2">
                                                @foreach($detailRows as $detail)
                                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $detail['label'] }}</p>
                                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $detail['value'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($lineItems->isNotEmpty())
                                            <div class="mt-4">
                                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Barang Terkait</p>
                                                <div class="mt-3 space-y-2">
                                                    @foreach($lineItems as $line)
                                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                                                            <p class="font-semibold text-slate-900">{{ $line['title'] }}</p>
                                                            @if(! empty($line['meta']))
                                                                <p class="mt-1 text-xs text-slate-500">{{ $line['meta'] }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if(! empty($item['url']))
                                            <div class="mt-4">
                                                <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    Buka detail
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </details>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-slate-500">
                                Belum ada aktivitas admin yang tercatat.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>

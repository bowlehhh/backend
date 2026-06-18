<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>History PT/CV - Admin Besar</title>
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
            <a href="{{ route('admin.admin-besar.history') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">history</span><span class="font-semibold">History</span>
            </a>
            <a href="{{ route('admin.admin-besar.history.supplier') }}" class="flex items-center gap-3 rounded-xl bg-emerald-700 px-3 py-2 text-white">
                <span class="material-symbols-outlined">account_tree</span><span class="font-semibold">PT/CV</span>
            </a>
            <a href="{{ route('admin.transaksi.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span><span class="font-semibold">Akses Dashboard Admin Gudang</span>
            </a>
            <a href="{{ url('/admin/admin-module?type=users') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">group</span><span class="font-semibold">Manajemen Akun</span>
            </a>
        </nav>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold">Kelompok Transaksi Pembeli</h2>
                <p class="text-sm text-slate-500">{{ $user?->name }} - Admin Besar</p>
            </div>
            <a href="{{ route('admin.admin-besar.index') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Admin Besar</a>
        </div>

        @forelse($groups as $group)
            <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <h3 class="text-2xl font-extrabold text-slate-900">{{ $group['pt_name'] }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Invoice per perusahaan tampil terpisah supaya mudah dibaca.</p>
                    </div>
                    <a href="{{ route('admin.admin-besar.history.supplier.detail', ['pt' => $group['pt_name']]) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Detail</a>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi</p><p class="mt-1 text-lg font-bold text-slate-900">{{ number_format((int) $group['summary']['total_transaksi']) }} kali</p></div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</p><p class="mt-1 text-lg font-bold text-slate-900">{{ number_format((int) $group['summary']['total_qty']) }}</p></div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Nilai</p><p class="mt-1 text-lg font-bold text-slate-900">{{ $group['summary']['total_nilai'] }}</p></div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kredit / Lunas</p><p class="mt-1 text-lg font-bold text-slate-900">{{ number_format((int) $group['summary']['kredit']) }} / {{ number_format((int) $group['summary']['lunas']) }}</p></div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-8 text-center text-slate-500">Belum ada transaksi pembeli yang bisa dikelompokkan.</div>
        @endforelse
    </main>
</div>
</body>
</html>

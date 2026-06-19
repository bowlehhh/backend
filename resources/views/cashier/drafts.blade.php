<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Draft Penjualan - Surya Duta Multindo</title>
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
            <p class="text-xs text-slate-500">Admin Penjualan - Station 01</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="{{ route('cashier.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">point_of_sale</span>
                <span class="font-semibold">Penjualan</span>
            </a>
            <a href="{{ route('cashier.history') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">history</span>
                <span class="font-semibold">History</span>
            </a>
            <a href="{{ route('cashier.history.supplier') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">account_tree</span>
                <span class="font-semibold">PT/CV</span>
            </a>
            <a href="{{ route('cashier.drafts') }}" class="flex items-center gap-3 rounded-xl bg-indigo-500 px-3 py-2 text-white">
                <span class="material-symbols-outlined">draft</span>
                <span class="font-semibold">Draft</span>
            </a>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                <p class="px-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Master Data</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ url('/admin/products') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <span class="font-semibold">Daftar Stok</span>
                    </a>
                    <a href="{{ url('/admin/suppliers') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">local_shipping</span>
                        <span class="font-semibold">Supplier</span>
                    </a>
                    <a href="{{ url('/admin/admin-module?type=product-groups') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-white">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <span class="font-semibold">Kelompok Stok</span>
                    </a>
                </div>
            </div>
        </nav>
        <div class="p-4 border-t border-slate-200">
            <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <span class="material-symbols-outlined">lock_clock</span>
                    <span class="font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="lg:ml-[260px] h-full overflow-y-auto p-4 lg:p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-extrabold">Draft Penjualan</h2>
                <p class="text-sm text-slate-500">{{ $user?->name }} - Admin</p>
            </div>
            <a href="{{ route('cashier.dashboard') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Penjualan</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Judul</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Waktu</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Item</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Total</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($drafts as $draft)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-semibold">{{ $draft->title ?: 'Draft Penjualan' }}</td>
                        <td class="px-4 py-3">{{ $draft->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ number_format((int) $draft->items_count) }}</td>
                        <td class="px-4 py-3 text-right font-bold">Rp {{ number_format((float) $draft->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('cashier.drafts.resume', $draft) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Pakai</button>
                                </form>
                                <form method="POST" action="{{ route('cashier.drafts.delete', $draft) }}" onsubmit="return confirm('Hapus draft ini?')">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada draft transaksi.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $drafts->links() }}</div>
    </main>
</div>
@include('cashier.partials.logout-modal')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surya Duta Multindo</title>
    <x-brand.meta />
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-[#f6f8f6] font-['Hanken_Grotesk',sans-serif] text-slate-900">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(10,90,88,0.10),transparent_34%),radial-gradient(circle_at_top_right,rgba(180,151,92,0.10),transparent_28%),linear-gradient(180deg,#f8fbf9_0%,#eef4f1_100%)]">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-10">
            <header class="flex items-center justify-between gap-4">
                <x-brand.logo class="h-12 w-auto sm:h-14" />
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/admin') }}" class="inline-flex items-center rounded-xl border border-[#b9cbc1] bg-white px-4 py-2 text-sm font-semibold text-[#0a5a58] shadow-sm transition hover:bg-[#f3f8f6]">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-[#0a5a58] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#084847]">
                            Login
                        </a>
                    @endauth
                </div>
            </header>

            <main class="flex flex-1 items-center py-10 lg:py-16">
                <div class="grid w-full gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:items-center">
                    <section class="space-y-6">
                        <div class="inline-flex items-center rounded-full border border-[#cfded6] bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-[#0a5a58] shadow-sm backdrop-blur">
                            Distributor & Sistem Manajemen Alat Berat
                        </div>
                        <div class="space-y-4">
                            <h1 class="max-w-4xl text-4xl font-black tracking-tight text-[#0f1720] sm:text-5xl lg:text-6xl">
                                Satu pusat kerja untuk stok, supplier, penjualan, dan laporan.
                            </h1>
                            <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                                Aplikasi internal Surya Duta Multindo untuk mengelola daftar stok, transaksi PT/CV,
                                riwayat pembelian, nota, dan operasional gudang dengan tampilan yang konsisten.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a href="{{ url('/admin') }}" class="inline-flex items-center rounded-2xl bg-[#0a5a58] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#084847]">
                                    Masuk ke Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-2xl bg-[#0a5a58] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#084847]">
                                    Login Sekarang
                                </a>
                            @endauth
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-[#d1ddd7] bg-white/88 p-6 shadow-[0_24px_60px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
                        <div class="flex justify-center">
                            <x-brand.logo variant="stacked" class="h-auto w-full max-w-[340px]" />
                        </div>
                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[#d7e4de] bg-[#f8fbfa] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Daftar Stok</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Pantau part number, stok aktif, merek, supplier, dan histori input dari satu halaman.</p>
                            </div>
                            <div class="rounded-2xl border border-[#d7e4de] bg-[#f8fbfa] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Transaksi & Nota</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Kelola penjualan, kredit, PT/CV, nota cicilan, dan riwayat admin dengan alur yang terhubung.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

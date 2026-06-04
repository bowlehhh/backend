<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Toko Pak Paul</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: '#f6f8f6',
                        surface: '#fbfcfb',
                        primary: '#006a47',
                        'primary-container': '#00855a',
                        'on-primary-container': '#f5fff6',
                        'on-surface': '#10243a',
                        'on-surface-variant': '#43525f',
                        'outline-variant': '#c5d4cd',
                        'inverse-surface': '#17263f',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                },
            },
        };
    </script>

    <style>
        html, body {
            min-height: 100%;
        }

        body {
            overflow-x: hidden;
            background:
                radial-gradient(circle at top left, rgba(0, 106, 71, 0.08), transparent 34%),
                radial-gradient(circle at 90% 0%, rgba(0, 133, 90, 0.08), transparent 28%),
                linear-gradient(135deg, #f6f8f6 0%, #eef4f0 100%);
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .soft-shadow {
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.04),
                0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
        }
    </style>
</head>
<body class="bg-background text-on-surface font-sans overflow-x-hidden">
<main class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1.02fr)_minmax(0,0.98fr)]">
    <section class="relative flex min-h-screen flex-col justify-between px-5 py-8 sm:px-8 lg:px-14 lg:py-10 xl:px-20">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-[radial-gradient(circle_at_top_left,rgba(0,106,71,0.12),transparent_42%),radial-gradient(circle_at_top_right,rgba(0,133,90,0.10),transparent_35%)]"></div>

        <div class="relative z-10 mx-auto flex w-full max-w-[620px] flex-1 flex-col justify-start">
            <header class="pt-4 sm:pt-8 lg:pt-12">
                <div class="inline-flex items-center gap-3 rounded-full border border-[#d1e0d8] bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-primary shadow-[0_10px_30px_rgba(0,0,0,0.04)] backdrop-blur">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">inventory_2</span>
                    Akses Toko Pak Paul
                </div>

                <div class="mt-6 flex items-start gap-3 text-primary sm:mt-8">
                    <span class="material-symbols-outlined text-[46px] sm:text-[52px]" style="font-variation-settings:'FILL' 1;">inventory_2</span>
                    <div>
                        <h1 class="text-[40px] font-extrabold tracking-tight sm:text-[48px] lg:text-[56px]">Toko Pak Paul</h1>
                        <p class="mt-3 max-w-xl text-base text-on-surface-variant sm:text-lg">Masuk untuk lanjut mengelola stok, transaksi, dan laporan dalam satu panel yang rapi.</p>
                    </div>
                </div>
            </header>

            <div class="mt-8 rounded-[28px] border border-white/70 bg-white/90 p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-[#d9e5de] bg-[#f7faf8] px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-on-surface-variant">Status Sistem</p>
                        <p class="mt-2 text-lg font-bold text-on-surface">Siap dipakai</p>
                        <p class="mt-1 text-sm text-on-surface-variant">Akses login dibatasi sesuai role akun.</p>
                    </div>
                    <div class="rounded-2xl border border-[#d9e5de] bg-[#f7faf8] px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-on-surface-variant">Arah Login</p>
                        <p class="mt-2 text-lg font-bold text-on-surface">Admin / Kasir</p>
                        <p class="mt-1 text-sm text-on-surface-variant">Setelah masuk, sistem akan mengarahkan otomatis ke halaman yang sesuai.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-on-surface-variant" for="email">Email</label>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-on-surface-variant/60 transition-colors group-focus-within:text-primary">mail</span>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="winkytiopratama@gmail.com" class="w-full rounded-2xl border border-[#c8d6ce] bg-white py-3 pl-10 pr-4 text-on-surface outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-4">
                            <label class="block text-xs font-bold uppercase tracking-wide text-on-surface-variant" for="password">Password</label>
                            <a class="text-xs font-semibold text-primary hover:underline" href="#">Lupa Password?</a>
                        </div>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-on-surface-variant/60 transition-colors group-focus-within:text-primary">lock</span>
                            </div>
                            <input id="password" name="password" type="password" required placeholder="••••••••••••" class="w-full rounded-2xl border border-[#c8d6ce] bg-white py-3 pl-10 pr-12 text-on-surface outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" />
                            <button id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant transition-colors hover:text-primary" type="button" aria-label="Toggle password visibility">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-[#c8d6ce] text-primary focus:ring-primary" {{ old('remember') ? 'checked' : '' }} />
                        <label class="cursor-pointer text-sm text-on-surface" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-primary-container px-4 py-4 text-lg font-extrabold text-on-primary-container shadow-[0_12px_28px_rgba(0,106,71,0.22)] transition hover:bg-[#006e4b] active:scale-[0.99]">
                        <span>Sign in</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-8 border-t border-[#e5ece7] pt-6 text-center">
                    <p class="text-sm text-on-surface-variant">
                        Belum punya akun? <a class="font-bold text-primary hover:underline" href="#">Hubungi Admin</a>
                    </p>
                </div>
            </div>

            <footer class="mt-8 flex items-center justify-between gap-3 text-xs font-semibold text-on-surface-variant/70">
                <span>© {{ date('Y') }} Toko Pak Paul</span>
                <span>v1.0.4</span>
            </footer>
        </div>
    </section>

    <section class="relative hidden min-h-screen overflow-hidden bg-[#0f1f35] lg:block">
        <img class="absolute inset-0 h-full w-full object-cover opacity-35 mix-blend-overlay" alt="Gudang modern" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfJQIrNEOBa2JUll1EpdwW737Q--ej1u-Dbf6YEYgEq58zZASRkd4egxq6bXLWP2l3yBB4SahAibsloQjoNop2VDVHKTv0cZZ6HCdEOoS0hveOePb4cyLervaCeHdFlUA6c69pzLs1OaZ97pnzWhiQfmmcoqFRk45R9H1wfeYXxx3h9CtbSE7d5geRSfrVqMwZ6RAfYt83Tsdd2_O6p7COAvcqII--ZoMBnqkPOd9fzaoNzxeLMXknA9bmdnpi8rGk7qX3bbv3Zx_8" />
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(7,15,29,0.18)_0%,rgba(7,15,29,0.64)_55%,rgba(7,15,29,0.88)_100%)]"></div>

        <div class="relative z-10 flex h-full flex-col justify-end p-10 xl:p-14">
            <div class="max-w-xl space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-white/90 backdrop-blur">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">warehouse</span>
                    Real-time Inventory
                </div>
                <h2 class="text-4xl font-extrabold tracking-tight text-white xl:text-5xl">Kontrol stok, transaksi, dan laporan dari satu sistem yang rapi.</h2>
                <p class="text-base leading-7 text-slate-200">Login ke akun Anda untuk melanjutkan pekerjaan sesuai role, lalu sistem akan mengarahkan otomatis ke halaman yang tepat.</p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-[28px] border border-white/15 bg-white/85 p-6 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="mb-3 flex items-center gap-3 text-primary">
                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">trending_up</span>
                        <span class="text-3xl font-extrabold text-[#12324a]">98%</span>
                    </div>
                    <p class="text-sm text-slate-600">Inventory Accuracy Rate</p>
                </div>
                <div class="rounded-[28px] border border-white/15 bg-white/85 p-6 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="mb-3 flex items-center gap-3 text-primary">
                        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">speed</span>
                        <span class="text-3xl font-extrabold text-[#12324a]">Real-time</span>
                    </div>
                    <p class="text-sm text-slate-600">Stock Level Syncing</p>
                </div>
                <div class="sm:col-span-2 rounded-[28px] border border-white/15 bg-white/85 p-6 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#12324a]">Pengelolaan Barang Toko</h3>
                            <p class="mt-1 text-sm text-slate-600">Sistem terintegrasi untuk stok, transaksi, dan laporan.</p>
                        </div>
                        <span class="material-symbols-outlined text-primary text-[34px]">architecture</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const iconSpan = toggleBtn?.querySelector('.material-symbols-outlined');

    toggleBtn?.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        if (iconSpan) {
            iconSpan.textContent = isPassword ? 'visibility_off' : 'visibility';
        }
    });
</script>
</body>
</html>

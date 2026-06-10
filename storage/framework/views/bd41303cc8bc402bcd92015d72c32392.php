<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Surya Duta Multindo</title>
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
            width: 100%;
            height: 100%;
            min-height: 100%;
        }

        body {
            overflow: hidden;
            overscroll-behavior: none;
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
<main class="h-screen overflow-hidden lg:grid lg:grid-cols-[minmax(0,0.96fr)_minmax(0,1.04fr)]">
    <section class="relative flex h-screen flex-col justify-center overflow-hidden px-5 py-5 sm:px-8 lg:px-10 xl:px-14">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-[radial-gradient(circle_at_top_left,rgba(0,106,71,0.12),transparent_42%),radial-gradient(circle_at_top_right,rgba(0,133,90,0.10),transparent_35%)]"></div>

        <div class="relative z-10 mx-auto flex w-full max-w-[560px] flex-col justify-center">
            <header>
                <div class="inline-flex items-center gap-2.5 rounded-full border border-[#d1e0d8] bg-white/85 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-primary shadow-[0_10px_30px_rgba(0,0,0,0.04)] backdrop-blur">
                    <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">inventory_2</span>
                    Akses Surya Duta Multindo
                </div>

                <div class="mt-5 flex items-start gap-3 text-primary sm:mt-6">
                    <span class="material-symbols-outlined text-[40px] sm:text-[46px]" style="font-variation-settings:'FILL' 1;">inventory_2</span>
                    <div>
                        <h1 class="max-w-[10ch] text-[32px] font-extrabold tracking-tight leading-[0.98] sm:text-[40px] lg:text-[44px]">Surya Duta Multindo</h1>
                        <p class="mt-3 max-w-xl text-[14px] leading-6 text-on-surface-variant sm:text-[16px]">Masuk untuk lanjut mengelola stok, transaksi, dan laporan dalam satu panel yang rapi.</p>
                    </div>
                </div>
            </header>

            <div class="mt-5 rounded-[24px] border border-white/70 bg-white/90 p-4 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur sm:p-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-[#d9e5de] bg-[#f7faf8] px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">Status Sistem</p>
                        <p class="mt-1.5 text-base font-bold text-on-surface">Siap dipakai</p>
                        <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Akses login dibatasi sesuai role akun.</p>
                    </div>
                    <div class="rounded-2xl border border-[#d9e5de] bg-[#f7faf8] px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">Arah Login</p>
                        <p class="mt-1.5 text-base font-bold text-on-surface">Admin Toko/Gudang / Admin Besar</p>
                        <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Sistem langsung mengarahkan ke halaman yang sesuai.</p>
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('login.store')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant" for="email">Email</label>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-on-surface-variant/60 transition-colors group-focus-within:text-primary">mail</span>
                            </div>
                            <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required placeholder="winkytiopratama@gmail.com" class="h-12 w-full rounded-2xl border border-[#c8d6ce] bg-white py-2.5 pl-10 pr-4 text-[14px] text-on-surface outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" />
                        </div>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-center justify-between gap-4">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-on-surface-variant" for="password">Password</label>
                            <a class="text-[11px] font-semibold text-primary hover:underline" href="#">Lupa Password?</a>
                        </div>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-on-surface-variant/60 transition-colors group-focus-within:text-primary">lock</span>
                            </div>
                            <input id="password" name="password" type="password" required placeholder="••••••••••••" class="h-12 w-full rounded-2xl border border-[#c8d6ce] bg-white py-2.5 pl-10 pr-12 text-[14px] text-on-surface outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" />
                            <button id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant transition-colors hover:text-primary" type="button" aria-label="Toggle password visibility">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-[#c8d6ce] text-primary focus:ring-primary" <?php echo e(old('remember') ? 'checked' : ''); ?> />
                        <label class="cursor-pointer text-[13px] text-on-surface" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-primary-container px-4 text-[15px] font-extrabold text-on-primary-container shadow-[0_12px_28px_rgba(0,106,71,0.22)] transition hover:bg-[#006e4b] active:scale-[0.99]">
                        <span>Sign in</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-4 border-t border-[#e5ece7] pt-4 text-center">
                    <p class="text-[13px] text-on-surface-variant">
                        Belum punya akun? <a class="font-bold text-primary hover:underline" href="#">Hubungi Admin</a>
                    </p>
                </div>
            </div>

            <footer class="mt-3 flex items-center justify-between gap-3 text-[11px] font-semibold text-on-surface-variant/70">
                <span>© <?php echo e(date('Y')); ?> Surya Duta Multindo</span>
                <span>v1.0.4</span>
            </footer>
        </div>
    </section>

    <section class="relative hidden h-screen overflow-hidden bg-[#0f1f35] lg:block">
        <img class="absolute inset-0 h-full w-full object-cover opacity-35 mix-blend-overlay" alt="Gudang modern" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfJQIrNEOBa2JUll1EpdwW737Q--ej1u-Dbf6YEYgEq58zZASRkd4egxq6bXLWP2l3yBB4SahAibsloQjoNop2VDVHKTv0cZZ6HCdEOoS0hveOePb4cyLervaCeHdFlUA6c69pzLs1OaZ97pnzWhiQfmmcoqFRk45R9H1wfeYXxx3h9CtbSE7d5geRSfrVqMwZ6RAfYt83Tsdd2_O6p7COAvcqII--ZoMBnqkPOd9fzaoNzxeLMXknA9bmdnpi8rGk7qX3bbv3Zx_8" />
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(7,15,29,0.18)_0%,rgba(7,15,29,0.64)_55%,rgba(7,15,29,0.88)_100%)]"></div>

        <div class="relative z-10 flex h-full flex-col justify-end p-8 xl:p-12">
            <div class="max-w-xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/90 backdrop-blur">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">warehouse</span>
                    Real-time Inventory
                </div>
                <h2 class="text-[34px] font-extrabold tracking-tight text-white xl:text-[42px] leading-[1.02]">Kontrol stok, transaksi, dan laporan dari satu sistem yang rapi.</h2>
                <p class="max-w-lg text-[14px] leading-6 text-slate-200">Login ke akun Anda untuk melanjutkan pekerjaan sesuai role, lalu sistem akan mengarahkan otomatis ke halaman yang tepat.</p>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3">
                <div class="rounded-[22px] border border-white/15 bg-white/88 p-4 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="mb-2 flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">trending_up</span>
                        <span class="text-[22px] font-extrabold text-[#12324a]">98%</span>
                    </div>
                    <p class="text-[12px] text-slate-600">Inventory Accuracy</p>
                </div>
                <div class="rounded-[22px] border border-white/15 bg-white/88 p-4 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="mb-2 flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">speed</span>
                        <span class="text-[22px] font-extrabold text-[#12324a]">Live</span>
                    </div>
                    <p class="text-[12px] text-slate-600">Stock Level Sync</p>
                </div>
                <div class="rounded-[22px] border border-white/15 bg-white/88 p-4 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="flex h-full items-center justify-between gap-4">
                        <div>
                            <h3 class="text-[14px] font-bold text-[#12324a]">Pengelolaan Barang</h3>
                            <p class="mt-1 text-[12px] leading-5 text-slate-600">Sistem terintegrasi untuk stok, transaksi, dan laporan.</p>
                        </div>
                        <span class="material-symbols-outlined text-primary text-[26px]">architecture</span>
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
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/auth/login.blade.php ENDPATH**/ ?>
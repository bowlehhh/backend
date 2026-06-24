<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Surya Duta Multindo</title>
    <x-brand.meta />
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

        .login-shell {
            padding-top: 16px;
            padding-bottom: 16px;
        }

        .login-content {
            max-width: 500px;
            transform: scale(0.89);
            transform-origin: center;
        }

        .login-right-panel {
            padding: 28px 32px;
        }

        .login-right-copy {
            max-width: 670px;
            transform: scale(0.9);
            transform-origin: left bottom;
        }

        .login-highlight p:first-child {
            font-size: 10px;
        }

        .login-highlight p:nth-child(2) {
            margin-top: 4px;
            font-size: 14px;
            line-height: 1.3;
        }

        .login-highlight p:last-child {
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.55;
        }

        #password {
            height: 40px;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            font-size: 13px;
        }

        /* Responsive sizing for login form on large screens
           Scales down proportionally without cutting off content */
        @media (min-width: 1400px) {
            main {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
            }

            main > section:first-child {
                max-width: 520px;
                margin: 0 auto;
                padding-left: 20px;
                padding-right: 20px;
            }

            .login-content,
            .login-right-copy {
                transform: scale(0.86);
            }
        }

        @media (min-width: 1600px) {
            main > section:first-child {
                max-width: 550px;
                padding-left: 24px;
                padding-right: 24px;
            }

            .login-content,
            .login-right-copy {
                transform: scale(0.9);
            }
        }

        @media (min-width: 1920px) {
            main > section:first-child {
                max-width: 580px;
                padding-left: 28px;
                padding-right: 28px;
            }

            .login-content,
            .login-right-copy {
                transform: scale(0.94);
            }
        }

        @media (max-height: 940px) {
            .login-shell {
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .login-content {
                max-width: 470px;
                transform: scale(0.82);
            }

            .login-badge {
                padding: 6px 13px;
                font-size: 9px;
            }

            .login-logo {
                max-width: 165px;
            }

            .login-intro {
                font-size: 13px;
                line-height: 1.45;
            }

            .login-card {
                margin-top: 10px;
                padding: 12px;
            }

            .login-highlights {
                gap: 8px;
                margin-bottom: 10px;
            }

            .login-highlight {
                padding: 10px 12px;
            }

            .login-input,
            .login-submit {
                height: 40px;
            }

            .login-right-panel {
                padding: 20px 24px;
            }

            .login-right-copy {
                transform: scale(0.8);
            }

            #password {
                height: 40px;
                font-size: 13px;
            }
        }

        @media (max-height: 840px) {
            .login-shell {
                padding-top: 6px;
                padding-bottom: 6px;
            }

            .login-content {
                max-width: 450px;
                transform: scale(0.74);
            }

            .login-badge {
                padding: 5px 11px;
                font-size: 8px;
                letter-spacing: 0.18em;
            }

            .login-logo {
                max-width: 150px;
            }

            .login-intro {
                font-size: 12px;
                line-height: 1.35;
            }

            .login-card {
                margin-top: 8px;
                padding: 10px;
                border-radius: 18px;
            }

            .login-highlights {
                gap: 6px;
                margin-bottom: 8px;
            }

            .login-highlight {
                padding: 8px 10px;
                border-radius: 16px;
            }

            .login-label {
                margin-bottom: 4px;
                font-size: 9px;
            }

            .login-input,
            .login-submit {
                height: 36px;
                font-size: 12px;
            }

            .login-footer {
                margin-top: 4px;
            }

            .login-footer-badge,
            .login-version {
                font-size: 9px;
            }

            .login-right-panel {
                padding: 14px 18px;
            }

            .login-right-copy {
                transform: scale(0.72);
            }

            #password {
                height: 36px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body class="bg-background text-on-surface font-sans overflow-x-hidden">
<main class="h-screen overflow-hidden lg:grid lg:grid-cols-[minmax(0,0.94fr)_minmax(0,1.06fr)]">
    <section class="login-shell relative flex h-screen flex-col justify-center overflow-hidden px-4 py-3 sm:px-6 lg:px-8 xl:px-10">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-[radial-gradient(circle_at_top_left,rgba(0,106,71,0.12),transparent_42%),radial-gradient(circle_at_top_right,rgba(0,133,90,0.10),transparent_35%)]"></div>

        <div class="login-content relative z-10 mx-auto flex w-full max-w-[500px] flex-col justify-center">
            <header>
                <div class="login-badge inline-flex items-center rounded-full border border-[#d1e0d8] bg-white/85 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-primary shadow-[0_10px_30px_rgba(0,0,0,0.04)] backdrop-blur">
                    Akses Resmi Sistem
                </div>

                <div class="mt-3 space-y-2.5 text-primary sm:mt-4">
                    <x-brand.logo variant="stacked" class="login-logo h-auto w-full max-w-[170px] sm:max-w-[180px] lg:max-w-[190px]" />
                    <p class="login-intro max-w-xl text-[13px] leading-5 text-on-surface-variant sm:text-[14px]">Masuk untuk lanjut mengelola stok, transaksi, dan laporan dalam satu panel yang rapi.</p>
                </div>
            </header>

            <div class="login-card mt-3 rounded-[20px] border border-white/70 bg-white/90 p-3 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur sm:p-3">
                @if($errors->any())
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="login-highlights mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="login-highlight rounded-2xl border border-[#d9e5de] bg-[#f7faf8] px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">Status Sistem</p>
                        <p class="mt-1.5 text-base font-bold text-on-surface">Siap dipakai</p>
                        <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Akses login dibatasi sesuai role akun.</p>
                    </div>
                    <div class="login-highlight rounded-2xl border border-[#d9e5de] bg-[#f7faf8] px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">Arah Login</p>
                        <p class="mt-1.5 text-base font-bold text-on-surface">Admin Toko/Gudang / Admin Besar</p>
                        <p class="mt-1 text-[13px] leading-5 text-on-surface-variant">Sistem langsung mengarahkan ke halaman yang sesuai.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-3" autocomplete="off">
                    @csrf

                    <div>
                        <label class="login-label mb-1 block text-[10px] font-bold uppercase tracking-wide text-on-surface-variant" for="email">Email</label>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-on-surface-variant/60 transition-colors group-focus-within:text-primary">mail</span>
                            </div>
                            <input id="email" name="email" type="email" value="" required autocomplete="off" autocapitalize="off" spellcheck="false" data-form-type="other" class="login-input h-10 w-full rounded-2xl border border-[#c8d6ce] bg-white py-2 pl-10 pr-4 text-[13px] text-on-surface outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" />
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between gap-4">
                            <label class="login-label block text-[10px] font-bold uppercase tracking-wide text-on-surface-variant" for="password">Password</label>
                        </div>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="material-symbols-outlined text-on-surface-variant/60 transition-colors group-focus-within:text-primary">lock</span>
                            </div>
                            <input id="password" name="password" type="password" required placeholder="••••••••••••" autocomplete="new-password" data-form-type="other" class="login-input h-11 w-full rounded-2xl border border-[#c8d6ce] bg-white py-2.5 pl-10 pr-12 text-[14px] text-on-surface outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15" />
                            <button id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant transition-colors hover:text-primary" type="button" aria-label="Toggle password visibility">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-[#c8d6ce] text-primary focus:ring-primary" {{ old('remember') ? 'checked' : '' }} />
                        <label class="cursor-pointer text-[12px] text-on-surface" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="login-submit inline-flex h-10 w-full items-center justify-center gap-2 rounded-2xl bg-primary-container px-4 text-[13px] font-extrabold text-on-primary-container shadow-[0_12px_28px_rgba(0,106,71,0.22)] transition hover:bg-[#006e4b] active:scale-[0.99]">
                        <span>Sign in</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

            </div>

            <footer class="login-footer mt-3 flex items-center justify-between gap-3">
                <div class="login-footer-badge inline-flex items-center gap-2 rounded-full border border-[#d7e4dc] bg-white/80 px-4 py-2 text-[11px] font-semibold tracking-[0.16em] text-on-surface-variant shadow-[0_10px_24px_rgba(15,23,42,0.05)] backdrop-blur">
                    <span class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings:'FILL' 1;">copyright</span>
                    <span class="uppercase">Winkytiopratama</span>
                </div>
                <span class="login-version text-[11px] font-semibold tracking-[0.18em] text-on-surface-variant/55 uppercase">v1.0.4</span>
            </footer>
        </div>
    </section>

    <section class="relative hidden h-screen overflow-hidden bg-[#0f1f35] lg:block">
        <img class="absolute inset-0 h-full w-full object-cover opacity-35 mix-blend-overlay" alt="Gudang modern" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfJQIrNEOBa2JUll1EpdwW737Q--ej1u-Dbf6YEYgEq58zZASRkd4egxq6bXLWP2l3yBB4SahAibsloQjoNop2VDVHKTv0cZZ6HCdEOoS0hveOePb4cyLervaCeHdFlUA6c69pzLs1OaZ97pnzWhiQfmmcoqFRk45R9H1wfeYXxx3h9CtbSE7d5geRSfrVqMwZ6RAfYt83Tsdd2_O6p7COAvcqII--ZoMBnqkPOd9fzaoNzxeLMXknA9bmdnpi8rGk7qX3bbv3Zx_8" />
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(7,15,29,0.18)_0%,rgba(7,15,29,0.64)_55%,rgba(7,15,29,0.88)_100%)]"></div>

        <div class="login-right-panel relative z-10 flex h-full flex-col justify-end">
            <div class="login-right-copy max-w-xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/90 backdrop-blur">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">warehouse</span>
                    Real-time Inventory
                </div>
                <h2 class="text-[30px] font-extrabold tracking-tight text-white xl:text-[36px] leading-[1.02]">Kontrol stok, transaksi, dan laporan dari satu sistem yang rapi.</h2>
                <p class="max-w-lg text-[13px] leading-5 text-slate-200">Login ke akun Anda untuk melanjutkan pekerjaan sesuai role, lalu sistem akan mengarahkan otomatis ke halaman yang tepat.</p>
            </div>

            <div class="login-right-copy mt-5 grid grid-cols-3 gap-3">
                <div class="rounded-[20px] border border-white/15 bg-white/88 p-3 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="mb-2 flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">trending_up</span>
                        <span class="text-[22px] font-extrabold text-[#12324a]">98%</span>
                    </div>
                    <p class="text-[12px] text-slate-600">Inventory Accuracy</p>
                </div>
                <div class="rounded-[20px] border border-white/15 bg-white/88 p-3 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="mb-2 flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">speed</span>
                        <span class="text-[22px] font-extrabold text-[#12324a]">Live</span>
                    </div>
                    <p class="text-[12px] text-slate-600">Stock Level Sync</p>
                </div>
                <div class="rounded-[20px] border border-white/15 bg-white/88 p-3 shadow-[0_22px_55px_rgba(0,0,0,0.18)]">
                    <div class="flex h-full items-center justify-between gap-4">
                        <div>
                            <h3 class="text-[14px] font-bold text-[#12324a]">Pengelolaan Stok</h3>
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

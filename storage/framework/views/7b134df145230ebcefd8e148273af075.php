<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Toko Pak Paul</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: '#f8f9ff',
                        surface: '#f8f9ff',
                        primary: '#006a47',
                        'primary-container': '#00855a',
                        'on-primary-container': '#f5fff6',
                        'on-surface': '#0b1c30',
                        'on-surface-variant': '#3e4942',
                        'outline-variant': '#bdcac0',
                        'inverse-surface': '#213145',
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .soft-shadow {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 10px 15px -5px rgba(0, 0, 0, 0.03);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.83);
            backdrop-filter: blur(8px);
        }
    </style>
</head>
<body class="bg-background text-on-surface font-sans overflow-x-hidden">
<main class="flex min-h-screen">
    <section class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 lg:p-24 bg-surface z-10">
        <div class="max-w-md w-full space-y-8">
            <header class="flex flex-col items-start space-y-2">
                <div class="flex items-center space-x-2 text-primary">
                    <span class="material-symbols-outlined text-[40px]" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                    <h1 class="text-[38px] leading-[44px] font-bold tracking-tight">Toko Pak Paul</h1>
                </div>
                <p class="text-on-surface-variant text-base">Masuk untuk lanjut mengelola stok Anda.</p>
            </header>

            <div class="bg-white p-8 rounded-xl soft-shadow border border-outline-variant/30">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form method="POST" action="<?php echo e(route('login.store')); ?>" class="space-y-6">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-2" for="email">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-on-surface-variant/60 group-focus-within:text-primary transition-colors">mail</span>
                            </div>
                            <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required placeholder="winkytiopratama@gmail.com" class="w-full pl-10 pr-4 py-3 bg-white border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-on-surface" />
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-on-surface-variant" for="password">Password</label>
                            <a class="text-primary text-xs font-semibold hover:underline decoration-2 underline-offset-4" href="#">Lupa Password?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-on-surface-variant/60 group-focus-within:text-primary transition-colors">lock</span>
                            </div>
                            <input id="password" name="password" type="password" required placeholder="••••••••••••" class="w-full pl-10 pr-12 py-3 bg-white border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none text-on-surface" />
                            <button id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-on-surface-variant hover:text-primary transition-colors" type="button" aria-label="Toggle password visibility">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary cursor-pointer" <?php echo e(old('remember') ? 'checked' : ''); ?> />
                        <label class="ml-2 block text-sm text-on-surface cursor-pointer" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="w-full bg-primary-container text-on-primary-container text-xl font-semibold py-4 rounded-lg soft-shadow active:scale-[0.98] transition-all hover:bg-primary-container/90 flex items-center justify-center space-x-2">
                        <span>Sign in</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-outline-variant/30 text-center">
                    <p class="text-on-surface-variant text-sm">
                        Belum punya akun? <a class="text-primary font-bold hover:underline" href="#">Hubungi Admin</a>
                    </p>
                </div>
            </div>

            <footer class="flex justify-between text-on-surface-variant/60 text-xs font-semibold">
                <span>© <?php echo e(date('Y')); ?> Toko Pak Paul</span>
                <span>v1.0.4</span>
            </footer>
        </div>
    </section>

    <section class="hidden lg:flex lg:w-1/2 relative bg-inverse-surface overflow-hidden">
        <img class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-overlay" alt="Gudang modern" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCfJQIrNEOBa2JUll1EpdwW737Q--ej1u-Dbf6YEYgEq58zZASRkd4egxq6bXLWP2l3yBB4SahAibsloQjoNop2VDVHKTv0cZZ6HCdEOoS0hveOePb4cyLervaCeHdFlUA6c69pzLs1OaZ97pnzWhiQfmmcoqFRk45R9H1wfeYXxx3h9CtbSE7d5geRSfrVqMwZ6RAfYt83Tsdd2_O6p7COAvcqII--ZoMBnqkPOd9fzaoNzxeLMXknA9bmdnpi8rGk7qX3bbv3Zx_8" />

        <div class="absolute inset-0 p-12 flex flex-col justify-end">
            <div class="grid grid-cols-2 gap-4">
                <div class="glass-effect p-6 rounded-2xl soft-shadow border border-white/20">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">trending_up</span>
                        <h3 class="text-2xl font-bold text-on-surface">98%</h3>
                    </div>
                    <p class="text-on-surface-variant text-sm">Inventory Accuracy Rate</p>
                </div>

                <div class="glass-effect p-6 rounded-2xl soft-shadow border border-white/20">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">speed</span>
                        <h3 class="text-2xl font-bold text-on-surface">Real-time</h3>
                    </div>
                    <p class="text-on-surface-variant text-sm">Stock Level Syncing</p>
                </div>

                <div class="glass-effect p-6 rounded-2xl soft-shadow border border-white/20 col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-on-surface">Pengelolaan Barang Toko</h3>
                            <p class="text-on-surface-variant text-sm">Sistem terintegrasi untuk stok, transaksi, dan laporan.</p>
                        </div>
                        <span class="material-symbols-outlined text-primary text-[32px]">architecture</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface via-transparent to-transparent opacity-80 pointer-events-none"></div>
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
<?php /**PATH C:\laragon\www\backend\resources\views/auth/login.blade.php ENDPATH**/ ?>
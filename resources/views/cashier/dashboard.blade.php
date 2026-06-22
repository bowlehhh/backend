<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Penjualan - Surya Duta Multindo</title>
    <x-brand.meta />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        :root {
            --cashier-sidebar-w: 340px;
        }
        body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #bccac0; border-radius: 999px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        @media (min-width: 1024px) {
            .cashier-compact {
                font-size: 14px;
            }
            .cashier-compact aside.lg\:flex {
                width: var(--cashier-sidebar-w);
            }
            .cashier-compact main.lg\:ml-\[340px\] {
                margin-left: var(--cashier-sidebar-w);
            }
            .cashier-compact .text-3xl { font-size: 1.65rem !important; line-height: 2rem !important; }
            .cashier-compact .text-4xl { font-size: 1.95rem !important; line-height: 2.15rem !important; }
            .cashier-compact .text-2xl { font-size: 1.3rem !important; line-height: 1.7rem !important; }
            .cashier-compact .rounded-2xl { border-radius: 12px !important; }
            .cashier-compact .rounded-xl { border-radius: 10px !important; }
            .cashier-compact .p-6 { padding: 1rem !important; }
            .cashier-compact .p-4 { padding: .8rem !important; }
            .cashier-compact .px-5 { padding-left: .9rem !important; padding-right: .9rem !important; }
            .cashier-compact .py-5 { padding-top: .85rem !important; padding-bottom: .85rem !important; }
            .cashier-compact .h-11 { height: 2.45rem !important; }
            .cashier-compact .h-36 { height: 8rem !important; }
            .cashier-compact .md\:h-44 { height: 9rem !important; }
            .cashier-compact .xl\:flex.w-\[390px\] { width: 350px; }
            .cashier-compact table th,
            .cashier-compact table td { padding-top: .6rem; padding-bottom: .6rem; }
            .cashier-compact input,
            .cashier-compact select,
            .cashier-compact textarea { min-height: 38px; }
        }
    </style>
</head>
<body class="cashier-compact text-slate-900">
@php
    $creditDaysValue = old('credit_days', '');
    $currentUser = auth()->user();
    $initialDiscountPercent = max(0, min(100, (float) old('discount_amount', 0)));
    $initialDiscountAmount = $subtotal > 0 ? (($subtotal * $initialDiscountPercent) / 100) : 0;
    $initialCartTotal = max(0, $subtotal - $initialDiscountAmount);
    $isAdminBesarGudangAccess = $currentUser?->isAdminBesar() && request()->routeIs('admin.transaksi.dashboard', 'admin.transactions.*');
    $isTransactionDashboard = request()->routeIs('cashier.dashboard', 'admin.transaksi.dashboard');
    $transactionDashboardRoute = $isAdminBesarGudangAccess ? 'admin.transaksi.dashboard' : 'cashier.dashboard';
    $historyRoute = $isAdminBesarGudangAccess ? 'admin.transactions.history' : 'cashier.history';
    $supplierHistoryRoute = $isAdminBesarGudangAccess ? 'admin.transactions.history.supplier' : 'cashier.history.supplier';
    $draftsRoute = $isAdminBesarGudangAccess ? 'admin.transactions.drafts' : 'cashier.drafts';
    $cartClearRoute = $isAdminBesarGudangAccess ? 'admin.transactions.cart.clear' : 'cashier.cart.clear';
    $cartUpdateRoute = $isAdminBesarGudangAccess ? 'admin.transactions.cart.update' : 'cashier.cart.update';
    $cartMergeRoute = $isAdminBesarGudangAccess ? 'admin.transactions.cart.merge' : 'cashier.cart.merge';
    $cartRemoveRoute = $isAdminBesarGudangAccess ? 'admin.transactions.cart.remove' : 'cashier.cart.remove';
    $cartAddRoute = $isAdminBesarGudangAccess ? 'admin.transactions.cart.add' : 'cashier.cart.add';
    $cartHoldRoute = $isAdminBesarGudangAccess ? 'admin.transactions.cart.hold' : 'cashier.cart.hold';
    $checkoutRoute = $isAdminBesarGudangAccess ? 'admin.transactions.checkout' : 'cashier.checkout';
    $backToAdminBesarRoute = route('admin.admin-besar.index');
    $productGridClasses = $showAllProducts
        ? 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-3'
        : 'grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4';
    $productCardMediaClasses = $showAllProducts
        ? 'relative h-24 sm:h-28 lg:h-32 bg-slate-100'
        : 'relative h-36 md:h-44 bg-slate-100';
    $productCardWrapperClasses = $showAllProducts
        ? 'rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm'
        : 'rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm';
    $productCardBodyClasses = $showAllProducts ? 'p-2.5' : 'p-3';
    $productCategoryClasses = $showAllProducts
        ? 'text-[9px] font-bold uppercase tracking-tight text-emerald-700'
        : 'text-[10px] font-bold uppercase tracking-tight text-emerald-700';
    $productPartNumberClasses = $showAllProducts
        ? 'font-bold text-slate-900 text-base sm:text-lg leading-tight line-clamp-2 break-words'
        : 'font-bold text-slate-900 text-xl md:text-lg line-clamp-1';
    $productBrandClasses = $showAllProducts
        ? 'mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600 line-clamp-1'
        : 'mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600 line-clamp-1';
    $productNameClasses = $showAllProducts
        ? 'mt-1 text-[11px] text-slate-500 line-clamp-2'
        : 'mt-1 text-xs text-slate-500 line-clamp-1';
    $productPriceClasses = $showAllProducts
        ? 'text-lg sm:text-xl font-extrabold text-emerald-700 leading-tight'
        : 'text-2xl md:text-2xl font-extrabold text-emerald-700';
    $productAddButtonClasses = $showAllProducts
        ? 'inline-flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg'
        : 'inline-flex h-9 w-9 md:h-10 md:w-10 items-center justify-center rounded-xl';
@endphp
<div class="h-screen overflow-hidden bg-[#f7f9fb]">
    <aside class="hidden lg:flex fixed inset-y-0 left-0 z-30 w-[340px] flex-col border-r border-slate-300 bg-white">
        <div class="px-5 py-5 border-b border-slate-200">
            <x-brand.logo class="h-10 w-auto" />
            <p class="text-xs text-slate-500">{{ $isAdminBesarGudangAccess ? 'Akses Dashboard Admin Gudang' : 'Admin Penjualan - Station 01' }}</p>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto custom-scrollbar px-4 py-4 space-y-4">
            <nav class="space-y-2">
                @if($isAdminBesarGudangAccess)
                    <a href="{{ $backToAdminBesarRoute }}" class="flex items-center gap-3 rounded-xl text-slate-600 hover:bg-slate-100 px-3 py-2">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span class="font-semibold">Kembali ke Admin Besar</span>
                    </a>
                @endif
                <a href="{{ route($transactionDashboardRoute) }}" class="flex items-center gap-3 rounded-xl {{ $isTransactionDashboard ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-100' }} px-3 py-2">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    <span class="font-semibold">Penjualan</span>
                </a>
                <a href="{{ route($historyRoute) }}" class="flex w-full items-center gap-3 rounded-xl {{ request()->routeIs('cashier.history', 'admin.transactions.history') ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-100' }} px-3 py-2">
                    <span class="material-symbols-outlined">history</span>
                    <span class="font-semibold">History</span>
                </a>
                <a href="{{ route($supplierHistoryRoute) }}" class="flex w-full items-center gap-3 rounded-xl {{ request()->routeIs('cashier.history.supplier', 'admin.transactions.history.supplier') ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-100' }} px-3 py-2">
                    <span class="material-symbols-outlined">account_tree</span>
                    <span class="font-semibold">PT/CV</span>
                </a>
                <a href="{{ route($draftsRoute) }}" class="flex w-full items-center gap-3 rounded-xl {{ request()->routeIs('cashier.drafts', 'admin.transactions.drafts') ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-100' }} px-3 py-2">
                    <span class="material-symbols-outlined">draft</span>
                    <span class="font-semibold">Draft</span>
                    @if($draftCount > 0)
                        <span class="ml-auto rounded-full bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $draftCount }}</span>
                    @endif
                </a>
            </nav>
            @if($isAdminBesarGudangAccess)
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                    Anda sedang masuk ke dashboard admin gudang sebagai <span class="font-bold">Admin Besar</span>. Semua proses di halaman ini tetap memakai akses admin gudang, dan Anda bisa kembali kapan saja.
                </div>
            @endif
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
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
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h2 class="text-base font-bold text-slate-900">Keranjang Belanja</h2>
                    <form method="POST" action="{{ route($cartClearRoute) }}">
                        @csrf
                        <button type="submit" class="text-xs text-red-500">Kosongkan</button>
                    </form>
                </div>
                <div class="max-h-[calc(100vh-360px)] space-y-3 overflow-y-auto custom-scrollbar px-4 py-3">
                    @forelse($cartItems as $item)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="text-sm font-semibold">{{ $item['product_name'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">Part No: {{ $item['part_number'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                Stok INV:
                                <span
                                    data-cart-batch-stock
                                    data-batch-base-stock="{{ (int) ($item['batch_stock'] ?? 0) }}"
                                    data-product-batch-id="{{ (int) $item['product_batch_id'] }}"
                                >{{ number_format((int) ($item['batch_stock'] ?? 0), 0, ',', '.') }}</span>
                                @if((int) ($item['available_stock'] ?? 0) > (int) ($item['batch_stock'] ?? 0))
                                    | total gabungan:
                                    <span
                                        data-cart-available-stock
                                        data-available-base-stock="{{ (int) ($item['available_stock'] ?? 0) }}"
                                        data-part-number="{{ $item['part_number'] }}"
                                    >{{ number_format((int) ($item['available_stock'] ?? 0), 0, ',', '.') }}</span>
                                @endif
                            </p>
                            <div class="mt-2 flex justify-between text-sm">
                                <form method="POST" action="{{ route($cartUpdateRoute, $item['product_batch_id']) }}" class="flex flex-col gap-2" data-cart-item-form data-merge-stock="{{ !empty($item['merge_stock']) ? '1' : '0' }}" data-product-id="{{ (int) $item['product_id'] }}" data-product-batch-id="{{ (int) $item['product_batch_id'] }}" data-product-name="{{ $item['product_name'] }}" data-part-number="{{ $item['part_number'] }}">
                                    @csrf
                                    <input type="hidden" name="cart_snapshot" value="" data-cart-snapshot />
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-500">Qty</label>
                                        <input type="number" min="0" max="{{ (int) ($item['max_qty'] ?? 0) }}" name="qty" value="{{ $item['qty'] }}" class="w-20 rounded-lg border border-slate-300 px-2 py-1" data-cart-qty data-max-stock="{{ (int) ($item['max_qty'] ?? 0) }}" />
                                    </div>
                                    <p class="hidden text-[11px] font-medium text-red-500" data-cart-qty-warning></p>
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-500">{{ !empty($item['merge_stock']) ? 'Total Harga' : 'Harga' }}</label>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            name="price"
                                            value="{{ number_format((float) ($item['merge_stock'] ? ($item['line_total'] ?? ((float) $item['price'] * (int) $item['qty'])) : $item['price']), 0, ',', '.') }}"
                                            class="w-28 rounded-lg border border-slate-300 px-2 py-1"
                                            data-rupiah-input
                                            data-cart-price
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="w-fit rounded-lg border border-slate-300 px-2 py-1 text-xs">Update</button>
                                        @if((int) ($item['can_merge_stock'] ?? 0) === 1)
                                            @if(empty($item['merge_stock']))
                                                <button
                                                    type="submit"
                                                    formaction="{{ route($cartMergeRoute, $item['product_batch_id']) }}"
                                                    class="w-fit rounded-lg border border-amber-300 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                                >
                                                    Gabung Stok
                                                </button>
                                            @else
                                                <span class="inline-flex w-fit items-center rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                                    Stok digabung
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </form>
                                <div class="text-right">
                                    <span class="block font-bold" data-cart-line-total>Rp {{ number_format((float) ($item['line_total'] ?? ((float) $item['price'] * (int) $item['qty'])), 0, ',', '.') }}</span>
                                    <form method="POST" action="{{ route($cartRemoveRoute, $item['product_batch_id']) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-500">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada item di keranjang.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </aside>

    <main class="lg:ml-[340px] h-full flex flex-col">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="px-4 lg:px-6 py-3">
                <div class="flex items-center justify-between lg:hidden">
                    <div>
                        <x-brand.logo class="h-9 w-auto" />
                        <p class="mt-1 text-[10px] text-slate-500">{{ $isAdminBesarGudangAccess ? 'Akses Dashboard Admin Gudang' : 'Admin Penjualan - Station 01' }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        @if($isAdminBesarGudangAccess)
                            <a href="{{ $backToAdminBesarRoute }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-700">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                            </a>
                        @endif
                        <a href="{{ route($historyRoute) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                            <span class="material-symbols-outlined text-[18px]">history</span>
                        </a>
                        <a href="{{ route($draftsRoute) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                            <span class="material-symbols-outlined text-[18px]">draft</span>
                        </a>
                        <a href="{{ route($supplierHistoryRoute) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                            @csrf
                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-50 text-red-600">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-4">
                    <form id="search-form" method="GET" action="{{ route($transactionDashboardRoute) }}" class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                        <input id="product-search-input" type="text" name="q" value="{{ $search }}" placeholder="Cari produk berdasarkan part number, nama, atau barcode..." autocomplete="off" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        <div id="product-search-popup" class="absolute left-0 right-0 top-[48px] z-40 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg"></div>
                    </form>
                    <div class="hidden lg:flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-bold">{{ $user?->name ?? 'Admin' }}</p>
                            <p class="text-xs text-slate-500">{{ $isAdminBesarGudangAccess ? 'Admin Besar • Mode Gudang' : 'Admin' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                            @csrf
                            <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-xl border border-red-200 bg-white px-4 text-sm font-semibold text-red-600 hover:bg-red-50">
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 min-h-0 flex">
            <section class="flex-1 min-h-0 overflow-y-auto custom-scrollbar p-4 lg:p-6 pb-40 xl:pb-6">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif
                <div class="mb-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <h3 class="text-2xl md:text-xl font-bold text-slate-700">Daftar Stok</h3>
                        <div class="space-y-2 md:text-right">
                            <p class="mt-1 text-xs text-slate-500">
                                @if($showAllProducts)
                                    Menampilkan semua {{ number_format((int) ($totalFilteredProducts ?? count($products)), 0, ',', '.') }} stok aktif dengan mode rapat.
                                @else
                                    Menampilkan semua {{ number_format((int) ($totalFilteredProducts ?? count($products)), 0, ',', '.') }} stok aktif di tampilan normal.
                                @endif
                            </p>
                            <div>
                                @php
                                    $toggleQuery = request()->query();
                                    if ($showAllProducts) {
                                        unset($toggleQuery['show_all']);
                                    } else {
                                        $toggleQuery['show_all'] = 1;
                                    }
                                @endphp
                                <a
                                    href="{{ route($transactionDashboardRoute, $toggleQuery) }}"
                                    class="inline-flex items-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50"
                                >
                                    {{ $showAllProducts ? 'Kembali ke Tampilan Normal' : 'Lihat Semua Stok' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="{{ $productGridClasses }}">
                    @forelse($products as $product)
                        @php
                            $batch = $product->batches->first();
                            $stock = (int) ($product->display_stock ?? ($batch?->stock ?? 0));
                            $price = (float) ($batch?->selling_price ?? 0);
                            $image = $product->image_path ? asset('storage/' . ltrim($product->image_path, '/')) : null;
                            $partNumber = trim((string) ($product->barcode ?? '')) ?: '-';
                            $batchId = (int) ($batch?->id ?? 0);
                            $canAddToCart = $batchId > 0 && $stock > 0;
                        @endphp
                        <article class="{{ $productCardWrapperClasses }}">
                            <div class="{{ $productCardMediaClasses }}">
                                @if($image)
                                    <img src="{{ $image }}" alt="{{ $product->name }}" class="h-full w-full object-cover" />
                                @else
                                    <div class="h-full w-full flex items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined {{ $showAllProducts ? 'text-4xl' : 'text-5xl' }}">inventory_2</span>
                                    </div>
                                @endif
                                <span class="absolute right-2 top-2 rounded-lg px-2 py-1 text-xs font-semibold {{ $stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    <span
                                        data-product-stock-badge
                                        data-product-batch-id="{{ $batchId }}"
                                        data-base-stock="{{ $stock }}"
                                    >Stok: {{ $stock }}</span>
                                </span>
                            </div>
                            <div class="{{ $productCardBodyClasses }}">
                                <p class="{{ $productCategoryClasses }}">{{ $product->category?->name ?? '-' }}</p>
                                <h3 class="{{ $productPartNumberClasses }}">{{ $partNumber }}</h3>
                                <p class="{{ $productBrandClasses }}">{{ $product->brand?->name ?? '-' }}</p>
                                <p class="{{ $productNameClasses }}">{{ $product->name }}</p>
                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <p class="{{ $productPriceClasses }}">Rp {{ number_format($price, 0, ',', '.') }}</p>
                                    <form
                                        method="POST"
                                        action="{{ $batch ? route($cartAddRoute, $batch) : '#' }}"
                                        data-add-to-cart-form
                                        data-product-batch-id="{{ $batchId }}"
                                        data-part-number="{{ strtoupper(trim($partNumber)) }}"
                                    >
                                        @csrf
                                        <input type="hidden" name="cart_snapshot" value="" data-cart-snapshot />
                                        <input type="hidden" name="current_visible_qty" value="" data-current-visible-qty />
                                        <input type="hidden" name="current_visible_price" value="" data-current-visible-price />
                                        <input type="hidden" name="current_visible_merge_stock" value="0" data-current-visible-merge-stock />
                                        <button
                                            type="submit"
                                            class="{{ $productAddButtonClasses }} {{ $canAddToCart ? 'bg-emerald-700 text-white hover:bg-emerald-600' : 'cursor-not-allowed bg-slate-200 text-slate-400' }}"
                                            @disabled(! $canAddToCart)
                                        >
                                            <span class="material-symbols-outlined">add</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500">
                            Belum ada produk aktif.
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="hidden xl:flex w-[390px] min-h-0 flex-col border-l border-slate-200 bg-white">
                <div class="min-h-0 flex-1 overflow-y-auto custom-scrollbar px-5 py-4 space-y-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><span data-cart-subtotal>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span data-discount-label>Diskon ({{ rtrim(rtrim(number_format($initialDiscountPercent, 2, '.', ''), '0'), '.') }}%)</span><span data-cart-discount>Rp {{ number_format($initialDiscountAmount, 0, ',', '.') }}</span></div>
                    </div>
                    <div class="mt-3 flex items-end justify-between">
                        <span class="text-xs tracking-wide text-slate-500">TOTAL HARGA</span>
                        <span class="text-4xl font-extrabold text-emerald-700" data-cart-total>Rp {{ number_format($initialCartTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button id="print-invoice-btn" type="button" class="rounded-xl border border-emerald-700 px-3 py-3 text-sm font-semibold text-emerald-700">Print Invoice</button>
                        <form method="POST" action="{{ route($cartHoldRoute) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm font-semibold text-slate-700">Tunda Penjualan</button>
                        </form>
                    </div>
                    <form id="checkout-form-desktop" data-checkout-form method="POST" action="{{ route($checkoutRoute) }}" class="mt-3 space-y-2">
                        @csrf
                        <input data-print-receipt-input type="hidden" name="print_receipt" value="0" />
                        <div class="grid grid-cols-[88px_minmax(0,1fr)] gap-2">
                            <select name="payment_method" data-payment-method class="w-full min-w-0 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option>
                                <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                                <option value="debit" @selected(old('payment_method') === 'debit')>Debit</option>
                                <option value="credit" @selected(old('payment_method') === 'credit')>Credit</option>
                            </select>
                            <div class="min-w-0 space-y-1">
                                <label data-payment-amount-label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Jumlah Bayar</label>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    name="paid_amount"
                                    value="{{ old('payment_method') === 'credit' ? old('paid_amount', '0') : old('paid_amount', number_format((float) ceil($initialCartTotal), 0, ',', '.')) }}"
                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                    placeholder="Jumlah bayar"
                                    data-rupiah-input
                                    data-select-all-on-focus
                                    data-paid-amount-input
                                />
                                <p data-payment-summary class="text-[11px] font-medium text-slate-500">DP: Rp 0 | Sisa kredit: Rp 0</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Diskon (%)</label>
                            <input
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                name="discount_amount"
                                value="{{ old('discount_amount', '0') }}"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                placeholder="Masukkan persen diskon"
                                data-discount-input
                            />
                        </div>
                        <div data-credit-days-wrap class="{{ old('payment_method') === 'credit' ? '' : 'hidden' }} space-y-2">
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tempo Kredit</label>
                                    <input type="text" inputmode="numeric" pattern="[0-9]*" name="credit_days" value="" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Isi hari" data-credit-days-input />
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tanggal Jatuh Tempo</label>
                                    <input type="text" readonly value="" placeholder="Otomatis muncul di sini" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700" data-credit-due-display />
                                    <input type="hidden" name="credit_due_date" value="{{ old('credit_due_date', '') }}" data-credit-due-input />
                                </div>
                            </div>
                            <p class="text-[11px] font-medium text-slate-500" data-credit-due-preview>Jatuh tempo otomatis akan dihitung dari hari ini.</p>
                        </div>
                        <input type="text" name="cashier_service_name" maxlength="100" value="{{ old('cashier_service_name', '') }}" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nama petugas / admin" />
                        <input type="text" name="customer_name" maxlength="100" value="{{ old('customer_name', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nama pembeli (opsional)" />
                        <input type="text" name="po_number" maxlength="100" value="{{ old('po_number', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="P.O. No (opsional)" />
                        <input type="text" name="site_name" maxlength="100" value="{{ old('site_name', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Site (opsional)" />
                        <button type="submit" data-normal-submit class="w-full rounded-2xl bg-emerald-700 py-4 text-xl font-extrabold text-white">KONFIRMASI PENJUALAN</button>
                    </form>
                </div>
            </aside>
        </div>
    </main>
</div>

<div class="xl:hidden fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white shadow-[0_-6px_16px_rgba(0,0,0,0.08)] safe-bottom">
    <div class="px-4 py-3 flex items-center justify-between bg-slate-50">
        <div class="leading-tight">
            <p class="text-[10px] text-slate-500 font-medium">Subtotal</p>
            <p class="font-bold text-slate-800" data-cart-subtotal>Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
            <p class="mt-1 text-[10px] text-slate-500 font-medium"><span data-discount-label>Diskon ({{ rtrim(rtrim(number_format($initialDiscountPercent, 2, '.', ''), '0'), '.') }}%)</span>: <span data-cart-discount>Rp {{ number_format($initialDiscountAmount, 0, ',', '.') }}</span></p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route($cartHoldRoute) }}">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-600">TUNDA</button>
            </form>
            <a href="{{ route($draftsRoute) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-600">DRAFT</a>
        </div>
    </div>
    <form id="checkout-form-mobile" data-checkout-form method="POST" action="{{ route($checkoutRoute) }}" class="px-4 pt-2 pb-4 space-y-2">
        @csrf
        <input data-print-receipt-input type="hidden" name="print_receipt" value="0" />
        <input type="hidden" name="payment_method" value="{{ old('payment_method', 'cash') }}" />
        <input type="hidden" name="paid_amount" value="{{ old('payment_method') === 'credit' ? old('paid_amount', '0') : old('paid_amount', (int) ceil($initialCartTotal)) }}" />
        <input type="hidden" name="discount_amount" value="{{ old('discount_amount', '0') }}" />
        <input type="hidden" name="credit_days" value="" />
        <input type="hidden" name="credit_due_date" value="{{ old('credit_due_date', '') }}" />
        <input type="hidden" name="cashier_service_name" value="" />
        <input type="hidden" name="customer_name" value="{{ old('customer_name', '') }}" />
        <input type="hidden" name="po_number" value="{{ old('po_number', '') }}" />
        <input type="hidden" name="site_name" value="{{ old('site_name', '') }}" />
        <div class="flex gap-2">
            <button type="button" data-print-btn class="w-12 rounded-xl border border-slate-300 bg-white text-slate-700">
                <span class="material-symbols-outlined text-[18px]">print</span>
            </button>
            <button type="submit" data-normal-submit class="flex-1 rounded-2xl bg-emerald-700 py-3 text-base font-extrabold text-white tracking-wide">KONFIRMASI PENJUALAN</button>
        </div>
    </form>
</div>

<div id="sale-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4">
    <div class="w-full max-w-xl rounded-2xl bg-white p-5 shadow-2xl">
        <h3 class="text-xl font-extrabold text-slate-900">Konfirmasi Penjualan</h3>
        <p class="mt-1 text-sm text-slate-500">Pastikan item dan total sudah benar sebelum melanjutkan.</p>

        <div id="sale-confirm-items" class="mt-4 max-h-64 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 text-sm"></div>
        <div class="mt-3 rounded-xl border border-slate-200 p-3">
            <p class="text-xs font-semibold text-slate-500">NAMA PEMBELI</p>
            <p id="confirm-customer" class="mt-1 font-semibold text-slate-900">-</p>
        </div>
        <div class="mt-3 grid gap-3 rounded-xl border border-slate-200 p-3 md:grid-cols-2">
            <div>
                <p class="text-xs font-semibold text-slate-500">P.O. NO</p>
                <p id="confirm-po-number" class="mt-1 font-semibold text-slate-900">-</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500">SITE</p>
                <p id="confirm-site-name" class="mt-1 font-semibold text-slate-900">-</p>
            </div>
        </div>
        <div id="mobile-confirm-fields" class="mt-3 hidden space-y-2 rounded-xl border border-slate-200 p-3">
            <div class="grid grid-cols-[88px_minmax(0,1fr)] gap-2">
                <select id="mobile-confirm-payment-method" class="w-full min-w-0 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                    <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option>
                    <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                    <option value="debit" @selected(old('payment_method') === 'debit')>Debit</option>
                    <option value="credit" @selected(old('payment_method') === 'credit')>Credit</option>
                </select>
                <div class="min-w-0 space-y-1">
                    <label id="mobile-confirm-paid-label" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Jumlah Bayar</label>
                    <input
                        id="mobile-confirm-paid-amount"
                        type="text"
                        inputmode="numeric"
                        value="{{ old('payment_method') === 'credit' ? old('paid_amount', '0') : old('paid_amount', number_format((float) ceil($cartTotal), 0, ',', '.')) }}"
                        class="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                        placeholder="Jumlah bayar"
                        data-rupiah-input
                        data-select-all-on-focus
                    />
                    <p id="mobile-confirm-payment-summary" class="text-[11px] font-medium text-slate-500">DP: Rp 0 | Sisa kredit: Rp 0</p>
                </div>
            </div>
            <div id="mobile-credit-days-wrap" class="{{ old('payment_method') === 'credit' ? '' : 'hidden' }} space-y-2">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tempo Kredit</label>
                        <input id="mobile-confirm-credit-days" type="text" inputmode="numeric" pattern="[0-9]*" value="" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Isi hari" />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tanggal Jatuh Tempo</label>
                        <input id="mobile-confirm-credit-due-display" type="text" readonly value="" placeholder="Otomatis muncul di sini" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700" />
                    </div>
                </div>
                <p id="mobile-confirm-credit-preview" class="text-[11px] font-medium text-slate-500">Jatuh tempo otomatis akan dihitung dari hari ini.</p>
            </div>
            <input id="mobile-confirm-cashier-name" type="text" maxlength="100" value="{{ old('cashier_service_name', '') }}" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nama petugas / admin" />
            <input id="mobile-confirm-customer-name" type="text" maxlength="100" value="{{ old('customer_name', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nama pembeli (opsional)" />
            <input id="mobile-confirm-discount-amount" type="number" min="0" max="100" step="0.01" value="{{ old('discount_amount', '0') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Diskon (%)" data-select-all-on-focus />
            <input id="mobile-confirm-po-number" type="text" maxlength="100" value="{{ old('po_number', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="P.O. No (opsional)" />
            <input id="mobile-confirm-site-name" type="text" maxlength="100" value="{{ old('site_name', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Site (opsional)" />
        </div>

        <div class="mt-4 space-y-1 rounded-xl bg-slate-50 p-3 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span id="confirm-subtotal">Rp 0</span></div>
            <div class="flex justify-between"><span id="confirm-discount-label">Diskon (0%)</span><span id="confirm-discount">Rp 0</span></div>
            <div class="mt-1 flex justify-between text-base font-bold"><span>Total</span><span id="confirm-total">Rp 0</span></div>
        </div>

        <div class="mt-5 flex gap-3">
            <button id="cancel-confirm-btn" type="button" class="flex-1 rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700">Batal</button>
            <button id="submit-confirm-btn" type="button" class="flex-1 rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white">Ya, Konfirmasi</button>
        </div>
    </div>
</div>

<script>
    const checkoutForms = Array.from(document.querySelectorAll('[data-checkout-form]'));
    const desktopCheckoutForm = document.getElementById('checkout-form-desktop');
    const printInvoiceBtn = document.getElementById('print-invoice-btn');
    const mobilePrintButtons = Array.from(document.querySelectorAll('[data-print-btn]'));
    let activeCheckoutForm = null;
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('product-search-input');
    const searchPopup = document.getElementById('product-search-popup');
    const modal = document.getElementById('sale-confirm-modal');
    const itemsBox = document.getElementById('sale-confirm-items');
    const confirmSubtotal = document.getElementById('confirm-subtotal');
    const confirmDiscount = document.getElementById('confirm-discount');
    const confirmDiscountLabel = document.getElementById('confirm-discount-label');
    const confirmTotal = document.getElementById('confirm-total');
    const confirmCustomer = document.getElementById('confirm-customer');
    const confirmPoNumber = document.getElementById('confirm-po-number');
    const confirmSiteName = document.getElementById('confirm-site-name');
    const mobileConfirmFields = document.getElementById('mobile-confirm-fields');
    const mobileConfirmPaymentMethod = document.getElementById('mobile-confirm-payment-method');
    const mobileConfirmPaidAmount = document.getElementById('mobile-confirm-paid-amount');
    const mobileConfirmPaidLabel = document.getElementById('mobile-confirm-paid-label');
    const mobileConfirmPaymentSummary = document.getElementById('mobile-confirm-payment-summary');
    const mobileConfirmCreditDays = document.getElementById('mobile-confirm-credit-days');
    const mobileConfirmCreditDueDisplay = document.getElementById('mobile-confirm-credit-due-display');
    const mobileConfirmCreditPreview = document.getElementById('mobile-confirm-credit-preview');
    const mobileCreditDaysWrap = document.getElementById('mobile-credit-days-wrap');
    const mobileConfirmCashierName = document.getElementById('mobile-confirm-cashier-name');
    const mobileConfirmCustomerName = document.getElementById('mobile-confirm-customer-name');
    const mobileConfirmDiscountAmount = document.getElementById('mobile-confirm-discount-amount');
    const mobileConfirmPoNumber = document.getElementById('mobile-confirm-po-number');
    const mobileConfirmSiteName = document.getElementById('mobile-confirm-site-name');
    const cancelBtn = document.getElementById('cancel-confirm-btn');
    const submitBtn = document.getElementById('submit-confirm-btn');
    const desktopPaymentMethod = desktopCheckoutForm?.querySelector('[data-payment-method]');
    const desktopPaidAmountInput = desktopCheckoutForm?.querySelector('[data-paid-amount-input]');
    const desktopDiscountInput = desktopCheckoutForm?.querySelector('[data-discount-input]');
    const discountLabels = Array.from(document.querySelectorAll('[data-discount-label]'));
    const desktopPaymentAmountLabel = desktopCheckoutForm?.querySelector('[data-payment-amount-label]');
    const desktopPaymentSummary = desktopCheckoutForm?.querySelector('[data-payment-summary]');
    const desktopCreditDaysWrap = desktopCheckoutForm?.querySelector('[data-credit-days-wrap]');
    const desktopCreditDaysInput = desktopCheckoutForm?.querySelector('[data-credit-days-input]');
    const desktopCreditDueInput = desktopCheckoutForm?.querySelector('input[name="credit_due_date"]');
    const desktopCreditDueDisplay = desktopCheckoutForm?.querySelector('[data-credit-due-display]');
    const desktopCreditDuePreview = desktopCheckoutForm?.querySelector('[data-credit-due-preview]');

    const cartItems = @json($cartItems);
    const subtotal = Number(@json($subtotal));
    const total = Number(@json($initialCartTotal));
    const searchSuggestions = @json($searchSuggestions);
    const nextResetAtIso = @json($nextResetAtIso);

    const toRupiah = (value) => new Intl.NumberFormat('id-ID').format(Math.round(value));
    const sanitizeRupiahValue = (value) => String(value ?? '').replace(/[^\d]/g, '');
    const formatRupiahInputValue = (value) => {
        const digits = sanitizeRupiahValue(value);
        return digits === '' ? '' : new Intl.NumberFormat('id-ID').format(Number(digits));
    };
    const getNumericInputValue = (input) => Number(sanitizeRupiahValue(input?.value || 0));
    const clampQtyInput = (input) => {
        if (!input) {
            return;
        }

        let value = Number(input.value || 0);

        if (Number.isNaN(value) || value < 0) {
            value = 0;
        }

        input.value = String(value);
    };
    const formatCurrencyLabel = (value) => `Rp ${toRupiah(value)}`;
    const collectLiveCartItems = () => Array.from(document.querySelectorAll('[data-cart-item-form]')).map((form) => {
        const qty = Number(form.querySelector('[data-cart-qty]')?.value || 0);
        const rawPrice = Number(sanitizeRupiahValue(form.querySelector('[data-cart-price]')?.value || 0));
        const mergeStock = form.dataset.mergeStock === '1';
        const lineTotal = mergeStock ? rawPrice : qty * rawPrice;
        const unitPrice = mergeStock && qty > 0 ? (rawPrice / qty) : rawPrice;

        return {
            product_id: Number(form.dataset.productId || 0),
            product_batch_id: Number(form.dataset.productBatchId || 0),
            product_name: form.dataset.productName || '',
            part_number: form.dataset.partNumber || '',
            merge_stock: mergeStock,
            qty,
            price: unitPrice,
            line_total: lineTotal,
        };
    }).filter((item) => item.qty > 0 && item.product_batch_id > 0);

    const syncCheckoutFormItems = (form, items) => {
        if (!form) {
            return;
        }

        form.querySelectorAll('[data-checkout-item-input]').forEach((input) => input.remove());

        items.forEach((item, index) => {
            const fields = {
                product_id: item.product_id,
                product_batch_id: item.product_batch_id,
                product_name: item.product_name,
                part_number: item.part_number,
                merge_stock: item.merge_stock ? '1' : '0',
                qty: String(item.qty),
                price: String(item.merge_stock ? item.line_total : item.price),
            };

            Object.entries(fields).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = String(value ?? '');
                input.setAttribute('data-checkout-item-input', '1');
                form.appendChild(input);
            });
        });
    };
    const pad2 = (value) => String(value).padStart(2, '0');
    const formatDisplayDate = (value) => {
        if (!value) return '-';
        const parsed = new Date(`${value}T00:00:00`);
        if (Number.isNaN(parsed.getTime())) return '-';
        return parsed.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };
    const getCreditDaysValue = (input) => {
        const raw = Number(String(input?.value || '').replace(/[^\d]/g, ''));
        return Number.isFinite(raw) && raw > 0 ? raw : null;
    };
    const computeDueDateFromDays = (days) => {
        const safeDays = Number(days);
        if (!Number.isFinite(safeDays) || safeDays <= 0) {
            return '';
        }
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        today.setDate(today.getDate() + safeDays);
        const year = today.getFullYear();
        const month = pad2(today.getMonth() + 1);
        const day = pad2(today.getDate());
        return `${year}-${month}-${day}`;
    };

    const rupiahInputs = Array.from(document.querySelectorAll('[data-rupiah-input]'));
    rupiahInputs.forEach((input) => {
        input.value = formatRupiahInputValue(input.value);
        input.addEventListener('input', () => {
            input.value = formatRupiahInputValue(input.value);
        });
    });

    document.querySelectorAll('[data-select-all-on-focus]').forEach((input) => {
        input.addEventListener('focus', () => {
            setTimeout(() => input.select(), 0);
        });

        input.addEventListener('pointerup', (event) => {
            event.preventDefault();
        });
    });

    const subtotalDisplays = Array.from(document.querySelectorAll('[data-cart-subtotal]'));
    const discountDisplays = Array.from(document.querySelectorAll('[data-cart-discount]'));
    const totalDisplays = Array.from(document.querySelectorAll('[data-cart-total]'));
    let liveSubtotal = subtotal;
    let liveDiscount = Number(@json((float) $initialDiscountAmount));
    let liveTotal = total;

    const getActiveDiscountPercent = () => {
        if (activeCheckoutForm?.id === 'checkout-form-mobile') {
            return Math.max(0, Math.min(100, Number(mobileConfirmDiscountAmount?.value || 0)));
        }

        return Math.max(0, Math.min(100, Number(desktopDiscountInput?.value || 0)));
    };

    const formatPercentLabel = (value) => {
        const numeric = Number(value || 0);
        const safe = Number.isFinite(numeric) ? numeric : 0;
        return `${safe.toFixed(2).replace(/\.00$/, '').replace(/(\.\d)0$/, '$1')}%`;
    };

    const syncSummaryValues = () => {
        subtotalDisplays.forEach((el) => {
            el.textContent = `Rp ${toRupiah(liveSubtotal)}`;
        });
        discountDisplays.forEach((el) => {
            el.textContent = `Rp ${toRupiah(liveDiscount)}`;
        });
        totalDisplays.forEach((el) => {
            el.textContent = `Rp ${toRupiah(liveTotal)}`;
        });
        const percentLabel = `Diskon (${formatPercentLabel(getActiveDiscountPercent())})`;
        discountLabels.forEach((el) => {
            el.textContent = percentLabel;
        });
    };

    const recalculateCartSummary = () => {
        let nextTotal = 0;

        document.querySelectorAll('[data-cart-item-form]').forEach((form) => {
            const qtyInput = form.querySelector('[data-cart-qty]');
            const priceInput = form.querySelector('[data-cart-price]');
            const qty = Number(qtyInput?.value || 0);
            const price = Number(sanitizeRupiahValue(priceInput?.value || 0));
            const isMerged = form.dataset.mergeStock === '1';
            const lineTotal = isMerged ? price : qty * price;
            nextTotal += lineTotal;

            const lineTotalEl = form.closest('.rounded-xl')?.querySelector('[data-cart-line-total]');
            if (lineTotalEl) {
                lineTotalEl.textContent = `Rp ${toRupiah(lineTotal)}`;
            }
        });

        liveSubtotal = nextTotal;
        const activeDiscountPercent = getActiveDiscountPercent();
        liveDiscount = Math.min(liveSubtotal, (liveSubtotal * activeDiscountPercent) / 100);
        liveTotal = Math.max(0, liveSubtotal - liveDiscount);
        syncSummaryValues();
        syncVisibleStocks();
    };

    const collectReservedQtyByBatch = () => {
        const reservedMap = new Map();

        document.querySelectorAll('[data-cart-item-form]').forEach((form) => {
            const batchId = Number(form.dataset.productBatchId || 0);
            const qty = Number(form.querySelector('[data-cart-qty]')?.value || 0);

            if (batchId <= 0 || qty <= 0) {
                return;
            }

            reservedMap.set(batchId, (reservedMap.get(batchId) || 0) + qty);
        });

        return reservedMap;
    };

    const collectReservedQtyByPartNumber = () => {
        const reservedMap = new Map();

        document.querySelectorAll('[data-cart-item-form]').forEach((form) => {
            const partNumber = String(form.dataset.partNumber || '').trim().toUpperCase();
            const qty = Number(form.querySelector('[data-cart-qty]')?.value || 0);

            if (!partNumber || qty <= 0) {
                return;
            }

            reservedMap.set(partNumber, (reservedMap.get(partNumber) || 0) + qty);
        });

        return reservedMap;
    };

    const syncVisibleStocks = () => {
        const reservedByBatch = collectReservedQtyByBatch();
        const reservedByPartNumber = collectReservedQtyByPartNumber();

        document.querySelectorAll('[data-product-stock-badge]').forEach((badge) => {
            const batchId = Number(badge.dataset.productBatchId || 0);
            const baseStock = Number(badge.dataset.baseStock || 0);
            const reservedQty = reservedByBatch.get(batchId) || 0;
            const displayStock = Math.max(0, baseStock - reservedQty);

            badge.textContent = `Stok: ${displayStock}`;
            const wrapper = badge.closest('span');
            if (wrapper) {
                wrapper.classList.toggle('bg-red-100', displayStock <= 5);
                wrapper.classList.toggle('text-red-700', displayStock <= 5);
                wrapper.classList.toggle('bg-emerald-100', displayStock > 5);
                wrapper.classList.toggle('text-emerald-700', displayStock > 5);
            }
        });

        document.querySelectorAll('[data-cart-batch-stock]').forEach((stockEl) => {
            const batchId = Number(stockEl.dataset.productBatchId || 0);
            const baseStock = Number(stockEl.dataset.batchBaseStock || 0);
            const reservedQty = reservedByBatch.get(batchId) || 0;
            stockEl.textContent = toRupiah(Math.max(0, baseStock - reservedQty));
        });

        document.querySelectorAll('[data-cart-available-stock]').forEach((stockEl) => {
            const partNumber = String(stockEl.dataset.partNumber || '').trim().toUpperCase();
            const baseStock = Number(stockEl.dataset.availableBaseStock || 0);
            const reservedQty = reservedByPartNumber.get(partNumber) || 0;
            stockEl.textContent = toRupiah(Math.max(0, baseStock - reservedQty));
        });
    };

    const syncQtyWarning = (form) => {
        if (!form) {
            return true;
        }

        const qtyInput = form.querySelector('[data-cart-qty]');
        const warningEl = form.querySelector('[data-cart-qty-warning]');
        const maxStock = Number(qtyInput?.dataset.maxStock || 0);
        const qty = Number(qtyInput?.value || 0);

        if (!qtyInput || !warningEl) {
            return true;
        }

        const isInvalid = maxStock >= 0 && qty > maxStock;

        qtyInput.classList.toggle('border-red-400', isInvalid);
        qtyInput.classList.toggle('text-red-600', isInvalid);
        qtyInput.classList.toggle('focus:border-red-500', isInvalid);
        qtyInput.classList.toggle('focus:ring-red-500', isInvalid);
        warningEl.classList.toggle('hidden', !isInvalid);

        if (isInvalid) {
            warningEl.textContent = `Stok tidak cukup. Maksimal ${toRupiah(maxStock)}.`;
            return false;
        }

        warningEl.textContent = '';
        return true;
    };

    document.querySelectorAll('[data-cart-price]').forEach((input) => {
        input.addEventListener('input', () => {
            recalculateCartSummary();

            const form = input.closest('[data-cart-item-form]');
            if (!form) {
                return;
            }

            syncQtyWarning(form);
        });
    });

    document.querySelectorAll('[data-cart-qty]').forEach((input) => {
        clampQtyInput(input);
        syncQtyWarning(input.closest('[data-cart-item-form]'));
        input.addEventListener('input', () => {
            clampQtyInput(input);
            recalculateCartSummary();

            const form = input.closest('[data-cart-item-form]');
            if (!form) {
                return;
            }

            syncQtyWarning(form);
        });
    });

    recalculateCartSummary();

    const findMatchingCartForm = (batchId, partNumber) => {
        const normalizedPartNumber = String(partNumber || '').trim().toUpperCase();
        const cartForms = Array.from(document.querySelectorAll('[data-cart-item-form]'));

        return cartForms.find((form) => Number(form.dataset.productBatchId || 0) === Number(batchId))
            || cartForms.find((form) => String(form.dataset.partNumber || '').trim().toUpperCase() === normalizedPartNumber)
            || null;
    };

    document.querySelectorAll('[data-add-to-cart-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const batchId = Number(form.dataset.productBatchId || 0);
            const partNumber = form.dataset.partNumber || '';
            const matchingCartForm = findMatchingCartForm(batchId, partNumber);

            const qtyField = form.querySelector('[data-current-visible-qty]');
            const priceField = form.querySelector('[data-current-visible-price]');
            const mergeField = form.querySelector('[data-current-visible-merge-stock]');
            const snapshotField = form.querySelector('[data-cart-snapshot]');

            if (snapshotField) {
                snapshotField.value = JSON.stringify(collectLiveCartItems());
            }

            if (!matchingCartForm) {
                if (qtyField) qtyField.value = '';
                if (priceField) priceField.value = '';
                if (mergeField) mergeField.value = '0';
                return;
            }

            const qtyInput = matchingCartForm.querySelector('[data-cart-qty]');
            const priceInput = matchingCartForm.querySelector('[data-cart-price]');

            clampQtyInput(qtyInput);
            if (!syncQtyWarning(matchingCartForm)) {
                event.preventDefault();
                qtyInput?.focus();
                return;
            }

            if (qtyField) {
                qtyField.value = String(Number(qtyInput?.value || 0));
            }

            if (priceField) {
                priceField.value = sanitizeRupiahValue(priceInput?.value || 0);
            }

            if (mergeField) {
                mergeField.value = matchingCartForm.dataset.mergeStock === '1' ? '1' : '0';
            }
        });
    });

    document.querySelectorAll('[data-cart-item-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const qtyInput = form.querySelector('[data-cart-qty]');

            clampQtyInput(qtyInput);
            if (!syncQtyWarning(form)) {
                event.preventDefault();
                qtyInput?.focus();
                return;
            }

            const snapshotField = form.querySelector('[data-cart-snapshot]');
            if (snapshotField) {
                snapshotField.value = JSON.stringify(collectLiveCartItems());
            }
        });
    });

    const syncPaymentSummary = (methodInput, amountInput, labelEl, summaryEl) => {
        const isCredit = (methodInput?.value || 'cash') === 'credit';
        const totalValue = Number(liveTotal || 0);
        const currentAmount = Math.min(totalValue, Math.max(0, getNumericInputValue(amountInput)));

        if (labelEl) {
            labelEl.textContent = isCredit ? 'DP / Uang Muka' : 'Jumlah Bayar';
        }

        if (amountInput) {
            amountInput.placeholder = isCredit ? 'Masukkan DP' : 'Jumlah bayar';
            if (isCredit && (amountInput.value.trim() === '' || getNumericInputValue(amountInput) === totalValue)) {
                amountInput.value = '0';
            }
            if (!isCredit && amountInput.value.trim() === '') {
                amountInput.value = formatRupiahInputValue(totalValue);
            }
        }

        const downPayment = isCredit ? Math.min(totalValue, Math.max(0, getNumericInputValue(amountInput))) : totalValue;
        const remainingCredit = isCredit ? Math.max(0, totalValue - downPayment) : 0;

        if (summaryEl) {
            summaryEl.textContent = isCredit
                ? `DP: ${formatCurrencyLabel(downPayment)} | Sisa kredit: ${formatCurrencyLabel(remainingCredit)}`
                : `Bayar: ${formatCurrencyLabel(currentAmount || totalValue)} | Kembali: ${formatCurrencyLabel(Math.max(0, (currentAmount || totalValue) - totalValue))}`;
        }

        return { downPayment, remainingCredit, currentAmount };
    };

    const syncCreditDueField = (methodInput, daysInput, dueInput, displayInput, previewEl, wrapEl) => {
        const isCredit = (methodInput?.value || 'cash') === 'credit';
        wrapEl?.classList.toggle('hidden', !isCredit);

        if (!isCredit) {
            if (dueInput) {
                dueInput.value = '';
            }
            if (displayInput) {
                displayInput.value = '';
            }
            if (previewEl) {
                previewEl.textContent = 'Isi tempo kredit untuk melihat tanggal jatuh tempo.';
            }
            return;
        }

        const days = getCreditDaysValue(daysInput);
        const dueDate = computeDueDateFromDays(days);
        if (daysInput && daysInput.value === '') {
            daysInput.value = '';
        }
        if (dueInput) {
            dueInput.value = dueDate;
        }
        if (displayInput) {
            displayInput.value = dueDate ? formatDisplayDate(dueDate) : '';
        }
        if (previewEl) {
            previewEl.textContent = dueDate
                ? `Jatuh tempo dipilih: ${formatDisplayDate(dueDate)}`
                : 'Isi tempo kredit untuk melihat tanggal jatuh tempo.';
        }
    };

    const syncCreditDueVisibility = () => {
        syncCreditDueField(desktopPaymentMethod, desktopCreditDaysInput, desktopCreditDueInput, desktopCreditDueDisplay, desktopCreditDuePreview, desktopCreditDaysWrap);

        syncCreditDueField(mobileConfirmPaymentMethod, mobileConfirmCreditDays, null, mobileConfirmCreditDueDisplay, mobileConfirmCreditPreview, mobileCreditDaysWrap);

        syncPaymentSummary(desktopPaymentMethod, desktopPaidAmountInput, desktopPaymentAmountLabel, desktopPaymentSummary);
        syncPaymentSummary(mobileConfirmPaymentMethod, mobileConfirmPaidAmount, mobileConfirmPaidLabel, mobileConfirmPaymentSummary);
    };

    desktopPaymentMethod?.addEventListener('change', syncCreditDueVisibility);
    mobileConfirmPaymentMethod?.addEventListener('change', syncCreditDueVisibility);
    desktopPaidAmountInput?.addEventListener('input', () => syncPaymentSummary(desktopPaymentMethod, desktopPaidAmountInput, desktopPaymentAmountLabel, desktopPaymentSummary));
    mobileConfirmPaidAmount?.addEventListener('input', () => syncPaymentSummary(mobileConfirmPaymentMethod, mobileConfirmPaidAmount, mobileConfirmPaidLabel, mobileConfirmPaymentSummary));
    desktopDiscountInput?.addEventListener('input', () => {
        recalculateCartSummary();
        syncPaymentSummary(desktopPaymentMethod, desktopPaidAmountInput, desktopPaymentAmountLabel, desktopPaymentSummary);
    });
    mobileConfirmDiscountAmount?.addEventListener('input', () => {
        recalculateCartSummary();
        confirmDiscount.textContent = `Rp ${toRupiah(liveDiscount)}`;
        confirmTotal.textContent = `Rp ${toRupiah(liveTotal)}`;
        syncPaymentSummary(mobileConfirmPaymentMethod, mobileConfirmPaidAmount, mobileConfirmPaidLabel, mobileConfirmPaymentSummary);
    });
    desktopCreditDaysInput?.addEventListener('change', syncCreditDueVisibility);
    mobileConfirmCreditDays?.addEventListener('change', syncCreditDueVisibility);
    syncCreditDueVisibility();

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    checkoutForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const liveCartItems = collectLiveCartItems();

            if (!liveCartItems.length) {
                alert('Keranjang masih kosong.');
                return;
            }

            activeCheckoutForm = form;
            const isMobileCheckout = form.id === 'checkout-form-mobile';
            if (mobileConfirmFields) {
                mobileConfirmFields.classList.toggle('hidden', !isMobileCheckout);
            }
            if (isMobileCheckout) {
                mobileConfirmPaymentMethod.value = form.querySelector('input[name="payment_method"]')?.value || 'cash';
                mobileConfirmPaidAmount.value = formatRupiahInputValue(form.querySelector('input[name="paid_amount"]')?.value || (mobileConfirmPaymentMethod.value === 'credit' ? 0 : liveTotal));
                mobileConfirmDiscountAmount.value = form.querySelector('input[name="discount_amount"]')?.value || desktopDiscountInput?.value || 0;
                mobileConfirmCreditDays.value = form.querySelector('[name="credit_days"]')?.value || '';
                mobileConfirmCashierName.value = form.querySelector('input[name="cashier_service_name"]')?.value || '';
                mobileConfirmCustomerName.value = form.querySelector('input[name="customer_name"]')?.value || '';
                mobileConfirmPoNumber.value = form.querySelector('input[name="po_number"]')?.value || '';
                mobileConfirmSiteName.value = form.querySelector('input[name="site_name"]')?.value || '';
            }
            itemsBox.innerHTML = liveCartItems.map((item) => {
                const qty = Number(item.qty || 0);
                const price = Number(item.price || 0);
                const lineTotal = Number(item.line_total || (qty * price));
                return `
                    <div class="flex items-start justify-between gap-3 rounded-lg border border-slate-200 p-2">
                        <div>
                            <p class="font-semibold text-slate-900">${item.product_name}</p>
                            <p class="text-xs text-slate-500">Qty: ${qty} x Rp ${toRupiah(price)}</p>
                        </div>
                        <p class="font-bold text-slate-900">Rp ${toRupiah(lineTotal)}</p>
                    </div>
                `;
            }).join('');

            const customerNameInput = isMobileCheckout ? mobileConfirmCustomerName : form.querySelector('input[name=\"customer_name\"]');
            const customerName = (customerNameInput?.value || '').trim();
            const poNumberInput = isMobileCheckout ? mobileConfirmPoNumber : form.querySelector('input[name=\"po_number\"]');
            const poNumber = (poNumberInput?.value || '').trim();
            const siteNameInput = isMobileCheckout ? mobileConfirmSiteName : form.querySelector('input[name=\"site_name\"]');
            const siteName = (siteNameInput?.value || '').trim();
            confirmCustomer.textContent = customerName !== '' ? customerName : '-';
            confirmPoNumber.textContent = poNumber !== '' ? poNumber : '-';
            confirmSiteName.textContent = siteName !== '' ? siteName : '-';
            const activeDiscountPercent = isMobileCheckout
                ? Math.max(0, Math.min(100, Number(mobileConfirmDiscountAmount?.value || 0)))
                : Math.max(0, Math.min(100, Number(desktopDiscountInput?.value || 0)));
            liveDiscount = Math.min(liveSubtotal, (liveSubtotal * activeDiscountPercent) / 100);
            liveTotal = Math.max(0, liveSubtotal - liveDiscount);
            confirmSubtotal.textContent = `Rp ${toRupiah(liveSubtotal)}`;
            if (confirmDiscountLabel) {
                confirmDiscountLabel.textContent = `Diskon (${formatPercentLabel(activeDiscountPercent)})`;
            }
            confirmDiscount.textContent = `Rp ${toRupiah(liveDiscount)}`;
            confirmTotal.textContent = `Rp ${toRupiah(liveTotal)}`;
            syncCheckoutFormItems(form, liveCartItems);
            syncCreditDueVisibility();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    cancelBtn?.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    submitBtn?.addEventListener('click', () => {
        if (activeCheckoutForm?.id === 'checkout-form-mobile') {
            activeCheckoutForm.querySelector('input[name="payment_method"]').value = mobileConfirmPaymentMethod?.value || 'cash';
            const mobileDownPayment = mobileConfirmPaymentMethod?.value === 'credit'
                ? Math.min(liveTotal, Math.max(0, Number(sanitizeRupiahValue(mobileConfirmPaidAmount?.value || 0))))
                : liveTotal;
            activeCheckoutForm.querySelector('input[name="paid_amount"]').value = String(mobileDownPayment);
            activeCheckoutForm.querySelector('input[name="discount_amount"]').value = String(Math.max(0, Math.min(100, Number(mobileConfirmDiscountAmount?.value || 0))));
            activeCheckoutForm.querySelector('input[name="credit_days"]').value = (mobileConfirmPaymentMethod?.value === 'credit' ? (mobileConfirmCreditDays?.value || '') : '');
            activeCheckoutForm.querySelector('input[name="credit_due_date"]').value = (mobileConfirmPaymentMethod?.value === 'credit' ? computeDueDateFromDays(getCreditDaysValue(mobileConfirmCreditDays)) : '');
            activeCheckoutForm.querySelector('input[name="cashier_service_name"]').value = mobileConfirmCashierName?.value || '';
            activeCheckoutForm.querySelector('input[name="customer_name"]').value = mobileConfirmCustomerName?.value || '';
            activeCheckoutForm.querySelector('input[name="po_number"]').value = mobileConfirmPoNumber?.value || '';
            activeCheckoutForm.querySelector('input[name="site_name"]').value = mobileConfirmSiteName?.value || '';
        } else if (activeCheckoutForm) {
            const paidInput = activeCheckoutForm.querySelector('input[name="paid_amount"]');
            if (paidInput) {
                const methodInput = activeCheckoutForm.querySelector('select[name="payment_method"]');
                const method = methodInput?.value || 'cash';
                const amount = Math.min(liveTotal, Math.max(0, Number(sanitizeRupiahValue(paidInput.value || 0))));
                paidInput.value = String(method === 'credit' ? amount : (amount > 0 ? amount : Math.round(liveTotal)));
            }
            const discountInput = activeCheckoutForm.querySelector('input[name="discount_amount"]');
            if (discountInput) {
                discountInput.value = String(Math.max(0, Math.min(100, Number(discountInput.value || 0))));
            }
            const method = activeCheckoutForm.querySelector('select[name="payment_method"]')?.value || 'cash';
            const dueInput = activeCheckoutForm.querySelector('input[name="credit_due_date"]');
            const daysInput = activeCheckoutForm.querySelector('input[name="credit_days"]');
            if (method !== 'credit') {
                if (dueInput) dueInput.value = '';
                if (daysInput) daysInput.value = '';
            } else {
                const dueDate = computeDueDateFromDays(getCreditDaysValue(daysInput));
                if (dueInput) dueInput.value = dueDate;
            }
        }
            activeCheckoutForm?.submit();
        });

    document.querySelectorAll('form[action*="/cart/"]').forEach((form) => {
        form.addEventListener('submit', () => {
            const priceInput = form.querySelector('input[name="price"]');
            if (priceInput) {
                priceInput.value = sanitizeRupiahValue(priceInput.value);
            }
        });
    });

    printInvoiceBtn?.addEventListener('click', () => {
        const desktopForm = document.getElementById('checkout-form-desktop');
        const printInput = desktopForm?.querySelector('[data-print-receipt-input]');
        if (printInput) {
            printInput.value = '1';
        }
        desktopForm?.requestSubmit();
    });

    mobilePrintButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            const printInput = form?.querySelector('[data-print-receipt-input]');
            if (printInput) {
                printInput.value = '1';
            }
            form?.requestSubmit();
        });
    });

    document.querySelectorAll('[data-normal-submit]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = button.closest('form');
            const printInput = form?.querySelector('[data-print-receipt-input]');
            if (printInput) {
                printInput.value = '0';
            }
        });
    });

    const showSearchPopup = (keyword) => {
        const query = (keyword || '').trim().toLowerCase();
        if (query.length < 2) {
            searchPopup.classList.add('hidden');
            searchPopup.innerHTML = '';
            return;
        }

        const matches = searchSuggestions
            .filter((item) => {
                const supplier = (item.supplier || '').toLowerCase();
                const barcode = (item.barcode || '').toLowerCase();
                return item.name.toLowerCase().includes(query) || supplier.includes(query) || barcode.includes(query);
            })
            .slice(0, 8);

        if (!matches.length) {
            searchPopup.classList.add('hidden');
            searchPopup.innerHTML = '';
            return;
        }

        searchPopup.innerHTML = matches.map((item) => `
            <button type="button" class="search-suggestion-item flex w-full items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 text-left last:border-b-0 hover:bg-slate-50" data-value="${escapeHtml(item.name)}">
                <span class="font-semibold text-slate-800">${escapeHtml(item.name)}</span>
                <span class="text-xs text-slate-500">${escapeHtml(item.supplier || '-')}</span>
            </button>
        `).join('');

        searchPopup.classList.remove('hidden');
    };

    searchInput?.addEventListener('input', (event) => {
        showSearchPopup(event.target.value);
    });

    searchPopup?.addEventListener('click', (event) => {
        const target = event.target.closest('.search-suggestion-item');
        if (!target) {
            return;
        }

        const value = target.getAttribute('data-value') || '';
        searchInput.value = value;
        searchPopup.classList.add('hidden');
        searchForm?.submit();
    });

    document.addEventListener('click', (event) => {
        if (!searchForm?.contains(event.target)) {
            searchPopup?.classList.add('hidden');
        }
    });

    const salesResetTimer = document.getElementById('sales-reset-timer');
    const nextResetDate = nextResetAtIso ? new Date(nextResetAtIso) : null;

    const updateResetTimer = () => {
        if (!salesResetTimer || !nextResetDate || Number.isNaN(nextResetDate.getTime())) {
            return;
        }

        const nowMs = Date.now();
        let diffMs = nextResetDate.getTime() - nowMs;
        if (diffMs < 0) {
            diffMs = 0;
        }

        const totalSeconds = Math.floor(diffMs / 1000);
        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');

        salesResetTimer.textContent = `Reset otomatis dalam ${hours}:${minutes}:${seconds}`;
    };

    updateResetTimer();
    setInterval(updateResetTimer, 1000);
</script>
@include('cashier.partials.logout-modal')
</body>
</html>

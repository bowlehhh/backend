<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Form Retur - {{ $sale->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<main class="mx-auto max-w-5xl p-4 lg:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Form Retur Transaksi</h1>
            <p class="text-sm text-slate-500">Invoice: {{ $sale->invoice_number }} | Admin: {{ $user?->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.receipt', $sale) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Lihat Nota</a>
            <a href="{{ route('cashier.history') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Riwayat</a>
        </div>
    </div>

    <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4">
        <div>
            <p class="text-xs uppercase text-slate-500">Tanggal</p>
            <p class="font-semibold">{{ $sale->created_at?->format('d M Y H:i') }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Pembeli</p>
            <p class="font-semibold">{{ $sale->customer_name ?: '-' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Metode</p>
            <p class="font-semibold uppercase">{{ $sale->payment_method }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-slate-500">Total</p>
            <p class="font-semibold">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('cashier.return.store', $sale) }}" class="rounded-2xl border border-slate-200 bg-white">
        @csrf
        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full text-xs md:text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2.5 text-left font-semibold text-slate-600 whitespace-nowrap">Pilih</th>
                    <th class="px-3 py-2.5 text-left font-semibold text-slate-600 whitespace-nowrap">Produk</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-slate-600 whitespace-nowrap">Qty Jual</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-slate-600 whitespace-nowrap">Sudah Diretur</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-slate-600 whitespace-nowrap">Maks Retur</th>
                    <th class="px-3 py-2.5 text-right font-semibold text-slate-600 whitespace-nowrap">Harga</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sale->items as $index => $item)
                    @php
                        $returnedQty = (int) ($returnedQtyMap[$item->id] ?? 0);
                        $maxReturn = max(0, (int) $item->qty - $returnedQty);
                    @endphp
                    <tr
                        class="border-t border-slate-100 js-return-row"
                        data-index="{{ $index }}"
                        data-product-name="{{ e($item->product_name) }}"
                        data-price="{{ (float) $item->price }}"
                        data-sold-qty="{{ (int) $item->qty }}"
                        data-returned-qty="{{ $returnedQty }}"
                        data-max-return="{{ $maxReturn }}"
                    >
                        <td class="px-3 py-3 align-top">
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-2">
                                <input
                                    type="checkbox"
                                    class="js-return-select h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                    data-index="{{ $index }}"
                                    @checked(old("items.{$index}.qty", 0) > 0)
                                    @disabled($maxReturn === 0)
                                />
                                <span class="text-xs font-semibold text-slate-700">Pilih</span>
                            </label>
                            <div class="js-return-qty-wrap mt-2 hidden w-fit rounded-xl border border-slate-200 bg-white px-2 py-1">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="js-return-qty-minus rounded-lg border border-slate-300 px-2 py-1 text-sm font-bold text-slate-700" data-index="{{ $index }}">-</button>
                                    <input
                                        type="text"
                                        class="js-return-qty-display w-12 border-0 bg-transparent text-center text-sm font-semibold text-slate-900 focus:ring-0"
                                        data-index="{{ $index }}"
                                        value="{{ old("items.{$index}.qty", 0) }}"
                                        readonly
                                    />
                                    <button type="button" class="js-return-qty-plus rounded-lg border border-slate-300 px-2 py-1 text-sm font-bold text-slate-700" data-index="{{ $index }}">+</button>
                                </div>
                            </div>
                            <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}" />
                            <input type="hidden" name="items[{{ $index }}][qty]" value="{{ old("items.{$index}.qty", 0) }}" class="js-return-qty" data-index="{{ $index }}" />
                            <input type="hidden" name="items[{{ $index }}][replacement_product_id]" value="{{ old("items.{$index}.replacement_product_id", '') }}" class="js-replacement-product-id" data-index="{{ $index }}" />
                            <input type="hidden" name="items[{{ $index }}][replacement_batch_id]" value="{{ old("items.{$index}.replacement_batch_id", '') }}" class="js-replacement-batch-id" data-index="{{ $index }}" />
                            <input type="hidden" name="items[{{ $index }}][replacement_qty]" value="{{ old("items.{$index}.replacement_qty", '') }}" class="js-replacement-qty" data-index="{{ $index }}" />
                            <input type="hidden" name="items[{{ $index }}][replacement_label]" value="{{ old("items.{$index}.replacement_label", '') }}" class="js-replacement-label" data-index="{{ $index }}" />
                            <input type="hidden" name="items[{{ $index }}][replacement_price]" value="{{ old("items.{$index}.replacement_price", '') }}" class="js-replacement-price" data-index="{{ $index }}" />
                        </td>
                        <td class="px-3 py-3">
                            <p class="font-semibold">{{ $item->product_name }}</p>
                        </td>
                        <td class="px-3 py-3 text-right">{{ $item->qty }}</td>
                        <td class="px-3 py-3 text-right">{{ $returnedQty }}</td>
                        <td class="px-3 py-3 text-right font-semibold">{{ $maxReturn }}</td>
                        <td class="px-3 py-3 text-right">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="js-replacement-panel hidden border-b border-slate-100 bg-[#fffaf0]" data-index="{{ $index }}" data-old-lines='@json(old("items.{$index}.replacement_lines", []))'>
                        <td colspan="6" class="px-3 py-3">
                            <div class="rounded-xl border border-amber-200 bg-white p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">Barang Pengganti</p>
                                        <p class="text-xs text-slate-500">Cari part number atau nama barang, lalu tambah baris jika butuh lebih dari satu pengganti.</p>
                                    </div>
                                    <span class="text-xs font-semibold text-amber-700">Mode Tukar Barang</span>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-xs text-slate-500">Barang pengganti yang dipilih akan otomatis dihitung per baris.</p>
                                    <button
                                        type="button"
                                        class="js-replacement-add-line rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100"
                                        data-index="{{ $index }}"
                                    >
                                        + Tambah Barang
                                    </button>
                                </div>
                                <div class="js-replacement-lines mt-3 space-y-3" data-index="{{ $index }}"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">
            @php
                $selectedReason = old('return_reason', 'barang_rusak');
            @endphp
            <div id="return-summary" class="mb-4 hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Ringkasan Selisih Barang</h3>
                        <p class="text-xs text-slate-500">Perbandingan nilai barang retur dan barang pengganti per item.</p>
                    </div>
                    <div id="return-summary-total" class="text-xs font-semibold text-slate-600"></div>
                </div>
                <div id="return-summary-items" class="mt-3 space-y-3"></div>
            </div>
            <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Uang Selisih</h3>
                        <p class="text-xs text-slate-500">Isi nominal tambahan jika selisih harga harus dibayar sekarang.</p>
                    </div>
                    <div id="return-payment-hint" class="text-xs font-semibold text-slate-600"></div>
                </div>
                <div class="mt-3 grid gap-4 md:grid-cols-[1fr_0.9fr]">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="extra_payment_amount">Uang Selisih / DP Tambahan</label>
                        <input
                            id="extra_payment_amount"
                            name="extra_payment_amount"
                            type="text"
                            inputmode="numeric"
                            value="{{ old('extra_payment_amount', '') }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                            data-rupiah-input
                            placeholder="0"
                        />
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                        <p class="font-semibold text-slate-800">Keterangan</p>
                        <p class="mt-1">Kredit: masuk sebagai DP tambahan.</p>
                        <p>Cash: langsung lunas / pembayaran tambahan saat itu juga.</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-[1.2fr_0.8fr]">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700" for="return_reason">Alasan Retur</label>
                    <select id="return_reason" name="return_reason" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="barang_rusak" @selected($selectedReason === 'barang_rusak')>Barang rusak</option>
                        <option value="ganti_barang" @selected($selectedReason === 'ganti_barang')>Ganti barang</option>
                        <option value="pengembalian_dana" @selected($selectedReason === 'pengembalian_dana')>Pengembalian dana</option>
                        <option value="salah_kirim" @selected($selectedReason === 'salah_kirim')>Salah kirim</option>
                        <option value="lainnya" @selected($selectedReason === 'lainnya')>Lainnya</option>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Pilih alasan yang paling sesuai agar retur lebih jelas dan mudah dicatat.</p>
                    <p class="mt-1 text-xs font-semibold text-amber-700">Jika pilih <span class="underline">Ganti barang</span>, selisih harga akan dihitung otomatis dan muncul di nota.</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700" for="notes">Catatan Tambahan</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="1000" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Isi detail tambahan bila diperlukan">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <a href="{{ route('cashier.history') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
                <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-500">Proses Retur</button>
            </div>
        </div>
    </form>
</main>
<script>
    const replacementOptions = @json($replacementOptions);
    const returnReasonSelect = document.getElementById('return_reason');
    const returnSummaryBox = document.getElementById('return-summary');
    const returnSummaryItems = document.getElementById('return-summary-items');
    const returnSummaryTotal = document.getElementById('return-summary-total');
    const returnPaymentHint = document.getElementById('return-payment-hint');
    const extraPaymentInput = document.getElementById('extra_payment_amount');
    const paymentMethod = '{{ strtolower((string) $sale->payment_method) }}';

    const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.max(0, Number(value || 0)))}`;
    const sanitizeDigits = (value) => String(value ?? '').replace(/[^\d]/g, '');
    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const getReplacementPanel = (index) => document.querySelector(`.js-replacement-panel[data-index="${index}"]`);
    const getReplacementLinesContainer = (index) => getReplacementPanel(index)?.querySelector('.js-replacement-lines');
    const getReplacementLine = (index, lineId) => getReplacementPanel(index)?.querySelector(`.js-replacement-line[data-line-id="${lineId}"]`);
    const getOldReplacementLines = (panel) => {
        try {
            return JSON.parse(panel?.dataset.oldLines || '[]');
        } catch (error) {
            return [];
        }
    };

    const setHiddenQty = (index, value) => {
        const qtyInput = document.querySelector(`.js-return-qty[data-index="${index}"]`);
        const qtyDisplay = document.querySelector(`.js-return-qty-display[data-index="${index}"]`);
        const row = document.querySelector(`.js-return-row[data-index="${index}"]`);
        const maxReturn = Number(row?.dataset.maxReturn || 0);
        const normalized = Math.max(0, Math.min(Number(value || 0), maxReturn));

        if (qtyInput) qtyInput.value = String(normalized);
        if (qtyDisplay) qtyDisplay.value = String(normalized);

        const checkbox = document.querySelector(`.js-return-select[data-index="${index}"]`);
        if (checkbox) {
            checkbox.checked = normalized > 0;
        }

        const qtyWrap = document.querySelector(`.js-return-qty-wrap[data-index="${index}"]`);
        if (qtyWrap) {
            qtyWrap.classList.toggle('hidden', normalized <= 0);
        }

        return normalized;
    };

    const getLineStockLimit = (row) => {
        const stock = Number(row?.dataset.stock || 0);
        return stock > 0 ? stock : 999999;
    };

    const createReplacementLineMarkup = (index, lineId, data = {}) => {
        const selectedLabel = data.label ? `Dipilih: ${escapeHtml(data.label)}` : '';
        return `
            <div class="js-replacement-line rounded-xl border border-slate-200 bg-white p-3" data-index="${index}" data-line-id="${lineId}" data-stock="${Number(data.stock || 0)}">
                <input type="hidden" name="items[${index}][replacement_lines][${lineId}][product_id]" class="js-line-product-id" value="${data.product_id || ''}" />
                <input type="hidden" name="items[${index}][replacement_lines][${lineId}][batch_id]" class="js-line-batch-id" value="${data.batch_id || ''}" />
                <input type="hidden" name="items[${index}][replacement_lines][${lineId}][qty]" class="js-line-qty" value="${data.qty || 1}" />
                <input type="hidden" name="items[${index}][replacement_lines][${lineId}][price]" class="js-line-price" value="${data.price || ''}" />
                <input type="hidden" name="items[${index}][replacement_lines][${lineId}][label]" class="js-line-label" value="${escapeHtml(data.label || '')}" />
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Barang pengganti ${lineId + 1}</p>
                        <p class="js-line-selected mt-1 text-xs font-semibold text-emerald-700 ${data.label ? '' : 'hidden'}">${selectedLabel}</p>
                    </div>
                    <button type="button" class="js-replacement-line-remove rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                </div>
                <input
                    type="text"
                    class="js-replacement-line-search mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    placeholder="Cari part number / part name"
                    autocomplete="off"
                    value="${escapeHtml(data.query || '')}"
                />
                <div class="js-replacement-line-results mt-2 grid gap-2"></div>
                <div class="js-replacement-line-qty-wrap mt-3 ${data.label ? '' : 'hidden'} rounded-xl border border-slate-200 bg-white px-3 py-2">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-700">Qty barang pengganti</p>
                            <p class="text-[11px] text-slate-500">Gunakan plus/minus kalau jumlah pengganti lebih dari satu.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="js-replacement-line-qty-minus rounded-lg border border-slate-300 px-2 py-1 text-sm font-bold text-slate-700">-</button>
                            <input
                                type="text"
                                class="js-replacement-line-qty-display w-12 border-0 bg-transparent text-center text-sm font-semibold text-slate-900 focus:ring-0"
                                value="${data.qty || 1}"
                                readonly
                            />
                            <button type="button" class="js-replacement-line-qty-plus rounded-lg border border-slate-300 px-2 py-1 text-sm font-bold text-slate-700">+</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    const addReplacementLine = (index, data = {}) => {
        const panel = getReplacementPanel(index);
        const container = getReplacementLinesContainer(index);
        if (!panel || !container) {
            return null;
        }

        const nextLineId = Number(panel.dataset.nextLineId || 0);
        panel.dataset.nextLineId = String(nextLineId + 1);

        container.insertAdjacentHTML('beforeend', createReplacementLineMarkup(index, nextLineId, data));
        const row = getReplacementLine(index, nextLineId);
        if (row && !data.label) {
            renderReplacementResults(index, nextLineId, '');
        }

        return row;
    };

    const collectReplacementLines = (index) => {
        const panel = getReplacementPanel(index);
        if (!panel) {
            return [];
        }

        return Array.from(panel.querySelectorAll('.js-replacement-line')).map((row) => ({
            product_id: Number(row.querySelector('.js-line-product-id')?.value || 0),
            batch_id: Number(row.querySelector('.js-line-batch-id')?.value || 0),
            qty: Math.max(0, Number(row.querySelector('.js-line-qty')?.value || 0)),
            price: Number(row.querySelector('.js-line-price')?.value || 0),
            label: row.querySelector('.js-line-label')?.value || '',
        })).filter((line) => line.product_id > 0 && line.batch_id > 0 && line.qty > 0);
    };

    const setReplacementLineSelection = (index, lineId, option) => {
        const row = getReplacementLine(index, lineId);
        if (!row) {
            return;
        }

        row.dataset.stock = String(Number(option.stock || 0));
        row.querySelector('.js-line-product-id').value = String(option.product_id || '');
        row.querySelector('.js-line-batch-id').value = String(option.batch_id || '');
        row.querySelector('.js-line-price').value = String(option.price || '');
        row.querySelector('.js-line-label').value = option.label || '';

        const selectedBox = row.querySelector('.js-line-selected');
        const qtyWrap = row.querySelector('.js-replacement-line-qty-wrap');
        const qtyDisplay = row.querySelector('.js-replacement-line-qty-display');
        const qtyInput = row.querySelector('.js-line-qty');
        const currentQty = Number(qtyInput?.value || 0) || 1;
        const normalizedQty = Math.max(1, Math.min(currentQty, getLineStockLimit(row)));

        if (selectedBox) {
            selectedBox.classList.remove('hidden');
            selectedBox.textContent = `Dipilih: ${option.label}`;
        }
        if (qtyWrap) qtyWrap.classList.remove('hidden');
        if (qtyDisplay) qtyDisplay.value = String(normalizedQty);
        if (qtyInput) qtyInput.value = String(normalizedQty);

        renderReplacementResults(index, lineId, row.querySelector('.js-replacement-line-search')?.value || '');
        updateReturnSummary();
    };

    const setReplacementLineQty = (index, lineId, value) => {
        const row = getReplacementLine(index, lineId);
        if (!row) {
            return 0;
        }

        const qtyInput = row.querySelector('.js-line-qty');
        const qtyDisplay = row.querySelector('.js-replacement-line-qty-display');
        const normalized = Math.max(1, Math.min(Number(value || 0), getLineStockLimit(row)));

        if (qtyInput) qtyInput.value = String(normalized);
        if (qtyDisplay) qtyDisplay.value = String(normalized);

        return normalized;
    };

    const renderReplacementResults = (index, lineId, query = '') => {
        const row = getReplacementLine(index, lineId);
        const resultsBox = row?.querySelector('.js-replacement-line-results');
        const searchInput = row?.querySelector('.js-replacement-line-search');
        if (!row || !resultsBox || !searchInput) {
            return;
        }

        const normalized = query.trim().toUpperCase();
        const filtered = replacementOptions
            .filter((option) => !normalized || option.search_text.includes(normalized))
            .slice(0, 12);

        resultsBox.innerHTML = filtered.length
            ? filtered.map((option) => `
                <button type="button"
                    class="js-replacement-pick flex w-full flex-col rounded-xl border border-slate-200 bg-white px-3 py-2 text-left hover:border-amber-400 hover:bg-amber-50"
                    data-index="${index}"
                    data-line-id="${lineId}"
                    data-product-id="${option.product_id}"
                    data-batch-id="${option.batch_id}"
                    data-label="${escapeHtml(option.label)}"
                    data-price="${option.price}"
                    data-stock="${option.stock}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900">${option.part_number}</p>
                            <p class="text-xs text-slate-500">${option.part_name}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Stok ${option.stock}</span>
                            <span class="text-xs font-semibold text-emerald-700">Rp ${new Intl.NumberFormat('id-ID').format(option.price)}</span>
                        </div>
                    </div>
                </button>
            `).join('')
            : '<div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-sm text-slate-500">Barang tidak ditemukan.</div>';
    };

    const syncReplacementPanel = (index) => {
        const checkbox = document.querySelector(`.js-return-select[data-index="${index}"]`);
        const qtyInput = document.querySelector(`.js-return-qty[data-index="${index}"]`);
        const qtyDisplay = document.querySelector(`.js-return-qty-display[data-index="${index}"]`);
        const qtyWrap = document.querySelector(`.js-return-qty-wrap[data-index="${index}"]`);
        const row = document.querySelector(`.js-return-row[data-index="${index}"]`);
        const panel = getReplacementPanel(index);
        const isSelected = !!checkbox?.checked;
        const isExchange = returnReasonSelect?.value === 'ganti_barang';
        const shouldShow = isSelected && isExchange;

        const currentQty = Number(qtyInput?.value || 0);
        setHiddenQty(index, isSelected ? (currentQty || 1) : 0);

        if (row) {
            row.classList.toggle('bg-amber-50', isSelected);
        }

        if (qtyWrap) {
            qtyWrap.classList.toggle('hidden', !isSelected);
        }

        if (!panel) {
            return;
        }

        panel.classList.toggle('hidden', !shouldShow);

        if (!shouldShow) {
            panel.querySelector('.js-replacement-lines').innerHTML = '';
            updateReturnSummary();
            return;
        }

        const container = getReplacementLinesContainer(index);
        if (container && !container.children.length) {
            const oldLines = getOldReplacementLines(panel);
            if (oldLines.length) {
                oldLines.forEach((line) => {
                    addReplacementLine(index, {
                        product_id: line.product_id || '',
                        batch_id: line.batch_id || '',
                        qty: line.qty || 1,
                        price: line.price || '',
                        label: line.label || '',
                        stock: line.stock || 0,
                        query: line.query || '',
                    });
                });
            } else {
                addReplacementLine(index);
            }
        }

        updateReturnSummary();
    };

    const updateReturnSummary = () => {
        if (!returnSummaryBox || !returnSummaryItems || !returnSummaryTotal) {
            return;
        }

        const isExchange = returnReasonSelect?.value === 'ganti_barang';
        const selectedIndexes = Array.from(document.querySelectorAll('.js-return-select'))
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.dataset.index);

        if (!selectedIndexes.length) {
            returnSummaryBox.classList.add('hidden');
            returnSummaryItems.innerHTML = '';
            returnSummaryTotal.textContent = '';
            return;
        }

        let totalReturn = 0;
        let totalReplacement = 0;
        let totalDifference = 0;

        const rowsHtml = selectedIndexes.map((index) => {
            const row = document.querySelector(`.js-return-row[data-index="${index}"]`);
            const qtyInput = document.querySelector(`.js-return-qty[data-index="${index}"]`);
            const qty = Number(qtyInput?.value || 0);
            const price = Number(row?.dataset.price || 0);
            const productName = row?.dataset.productName || '-';
            const returnSubtotal = price * qty;
            const replacementLines = isExchange ? collectReplacementLines(index) : [];
            const replacementSubtotal = replacementLines.reduce((sum, line) => sum + (Number(line.price || 0) * Number(line.qty || 0)), 0);
            const difference = isExchange ? (replacementSubtotal - returnSubtotal) : 0;

            totalReturn += returnSubtotal;
            totalReplacement += replacementSubtotal;
            totalDifference += difference;

            const differenceLabel = !isExchange
                ? `Retur: ${formatCurrency(returnSubtotal)}`
                : (difference > 0
                    ? `Selisih tambahan: ${formatCurrency(difference)}`
                    : difference < 0
                        ? `Selisih refund: ${formatCurrency(Math.abs(difference))}`
                        : 'Selisih: Rp 0');

            const replacementHtml = isExchange
                ? (replacementLines.length
                    ? replacementLines.map((line, lineIndex) => `
                        <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-slate-700">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-semibold">Barang pengganti ${lineIndex + 1}</span>
                                <span class="font-semibold text-emerald-700">${escapeHtml(line.label || '-')}</span>
                            </div>
                            <div class="mt-1">
                                ${line.qty} x ${formatCurrency(line.price)} = ${formatCurrency(line.qty * line.price)}
                            </div>
                        </div>
                    `).join('')
                    : '<div class="mt-2 rounded-lg border border-dashed border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">Tambah barang pengganti untuk item ini.</div>')
                : '';

            return `
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">${escapeHtml(productName)}</p>
                            <p class="text-xs text-slate-500">Harga barang awal: ${formatCurrency(price)}</p>
                            <p class="text-xs text-slate-500">Qty retur ${qty} x ${formatCurrency(price)} = ${formatCurrency(returnSubtotal)}</p>
                            ${isExchange && replacementLines.length ? `<p class="text-xs text-slate-500">Total barang pengganti: ${formatCurrency(replacementSubtotal)}</p>` : ''}
                        </div>
                        <div class="text-right text-xs font-semibold ${isExchange && difference > 0 ? 'text-amber-700' : isExchange && difference < 0 ? 'text-emerald-700' : 'text-slate-600'}">
                            ${differenceLabel}
                        </div>
                    </div>
                    ${replacementHtml}
                </div>
            `;
        }).join('');

        returnSummaryBox.classList.remove('hidden');
        returnSummaryItems.innerHTML = rowsHtml;
        returnSummaryTotal.textContent = isExchange
            ? `Total retur: ${formatCurrency(totalReturn)} | Pengganti: ${formatCurrency(totalReplacement)} | Selisih: ${formatCurrency(totalDifference)}`
            : `Total retur: ${formatCurrency(totalReturn)}`;

        if (returnPaymentHint) {
            const paymentLabel = paymentMethod === 'credit' ? 'DP tambahan' : 'pembayaran tambahan';
            returnPaymentHint.textContent = isExchange
                ? `Selisih positif bisa diisi sebagai ${paymentLabel}.`
                : `Tidak ada selisih pembayaran untuk retur biasa.`;
        }
    };

    document.querySelectorAll('.js-return-select').forEach((checkbox) => {
        const index = checkbox.dataset.index;
        checkbox.addEventListener('change', () => syncReplacementPanel(index));
        syncReplacementPanel(index);
    });

    returnReasonSelect?.addEventListener('change', () => {
        document.querySelectorAll('.js-return-select').forEach((checkbox) => {
            syncReplacementPanel(checkbox.dataset.index);
        });
        updateReturnSummary();
    });

    document.addEventListener('input', (event) => {
        const searchInput = event.target.closest('.js-replacement-line-search');
        if (searchInput) {
            const row = searchInput.closest('.js-replacement-line');
            const index = row?.dataset.index;
            const lineId = row?.dataset.lineId;
            renderReplacementResults(index, lineId, searchInput.value);
            return;
        }
    });

    document.addEventListener('click', (event) => {
        const returnQtyButton = event.target.closest('.js-return-qty-plus, .js-return-qty-minus');
        if (returnQtyButton) {
            const index = returnQtyButton.dataset.index;
            const currentQty = Number(document.querySelector(`.js-return-qty[data-index="${index}"]`)?.value || 0);
            const delta = returnQtyButton.classList.contains('js-return-qty-plus') ? 1 : -1;
            setHiddenQty(index, currentQty + delta);
            updateReturnSummary();
            return;
        }

        const replacementAddButton = event.target.closest('.js-replacement-add-line');
        if (replacementAddButton) {
            const index = replacementAddButton.dataset.index;
            addReplacementLine(index);
            updateReturnSummary();
            return;
        }

        const replacementRemoveButton = event.target.closest('.js-replacement-line-remove');
        if (replacementRemoveButton) {
            const line = replacementRemoveButton.closest('.js-replacement-line');
            const index = line?.dataset.index;
            line?.remove();
            if (index) {
                updateReturnSummary();
            }
            return;
        }

        const replacementLineQtyButton = event.target.closest('.js-replacement-line-qty-plus, .js-replacement-line-qty-minus');
        if (replacementLineQtyButton) {
            const line = replacementLineQtyButton.closest('.js-replacement-line');
            const index = line?.dataset.index;
            const lineId = line?.dataset.lineId;
            const currentQty = Number(line?.querySelector('.js-line-qty')?.value || 1);
            const delta = replacementLineQtyButton.classList.contains('js-replacement-line-qty-plus') ? 1 : -1;
            setReplacementLineQty(index, lineId, currentQty + delta);
            updateReturnSummary();
            return;
        }

        const button = event.target.closest('.js-replacement-pick');
        if (!button) {
            return;
        }

        const index = button.dataset.index;
        const lineId = button.dataset.lineId;
        setReplacementLineSelection(index, lineId, {
            product_id: button.dataset.productId,
            batch_id: button.dataset.batchId,
            label: button.dataset.label,
            price: button.dataset.price,
            stock: button.dataset.stock,
        });
    });

    document.querySelectorAll('[data-rupiah-input]').forEach((input) => {
        input.addEventListener('input', () => {
            const digits = sanitizeDigits(input.value);
            input.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        });
    });

    updateReturnSummary();
</script>
</body>
</html>

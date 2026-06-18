<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Transaksi - {{ $sale->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<main class="mx-auto max-w-7xl p-4 lg:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Edit Transaksi</h1>
            <p class="text-sm text-slate-500">Invoice: {{ $sale->invoice_number }} | Tanggal: {{ $sale->created_at?->format('d M Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cashier.receipt', $sale) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Lihat Nota</a>
            <a href="{{ route('cashier.history') }}" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke Riwayat</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @if($blockedByReturn ?? false)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="text-lg font-bold text-amber-800">Edit Dikunci</h2>
            <p class="mt-1 text-sm text-amber-700">Transaksi ini sudah memiliki retur, jadi demi konsistensi stok dan nilai transaksi tidak bisa diedit atau dihapus.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('cashier.history') }}" class="rounded-xl border border-amber-500 px-4 py-2 text-sm font-semibold text-amber-700">Kembali ke Riwayat</a>
                <a href="{{ route('cashier.receipt', $sale) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Buka Nota</a>
            </div>
        </div>
    @else
        @php
            $defaultPaymentMethod = old('payment_method', strtolower((string) $sale->payment_method));
            $defaultPaidAmount = old('paid_amount', number_format((float) $sale->paid_amount, 0, ',', '.'));
            $defaultCreditDueDate = old('credit_due_date', $sale->credit_due_date?->toDateString());
            $defaultTotal = (float) $sale->items->sum(fn ($item) => (float) $item->price * (int) $item->qty);
        @endphp

        <form method="POST" action="{{ route('cashier.history.update', $sale) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <h2 class="text-lg font-bold">Informasi Transaksi</h2>
                <div class="mt-3 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Pembeli</label>
                        <input type="text" name="customer_name" maxlength="100" value="{{ old('customer_name', $sale->customer_name ?? '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nama pembeli (opsional)" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">No. Telepon Pembeli</label>
                        <input type="text" name="customer_phone" maxlength="30" value="{{ old('customer_phone', $sale->customer_phone ?? '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="No. telepon pembeli (opsional)" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">P.O. No</label>
                        <input type="text" name="po_number" maxlength="100" value="{{ old('po_number', $sale->po_number ?? '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nomor PO (opsional)" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Site</label>
                        <input type="text" name="site_name" maxlength="100" value="{{ old('site_name', $sale->site_name ?? '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Site (opsional)" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Petugas / Admin</label>
                        <input type="text" name="cashier_service_name" maxlength="100" value="{{ old('cashier_service_name', $sale->cashier_service_name ?? $user?->name ?? '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">No. Telepon Admin</label>
                        <input type="text" name="cashier_phone" maxlength="30" value="{{ old('cashier_phone', $sale->cashier_phone ?? '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Metode Pembayaran</label>
                        <select name="payment_method" id="payment-method" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="cash" @selected($defaultPaymentMethod === 'cash')>Cash</option>
                            <option value="transfer" @selected($defaultPaymentMethod === 'transfer')>Transfer</option>
                            <option value="qris" @selected($defaultPaymentMethod === 'qris')>QRIS</option>
                            <option value="debit" @selected($defaultPaymentMethod === 'debit')>Debit</option>
                            <option value="credit" @selected($defaultPaymentMethod === 'credit')>Credit</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah Bayar</label>
                        <input type="text" name="paid_amount" id="paid-amount" inputmode="numeric" value="{{ $defaultPaidAmount }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" data-rupiah-input />
                    </div>
                    <div id="credit-due-wrap" class="{{ $defaultPaymentMethod === 'credit' ? '' : 'hidden' }}">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo Kredit</label>
                        <input type="date" name="credit_due_date" id="credit-due-date" value="{{ $defaultCreditDueDate }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="text-lg font-bold">Item Transaksi</h2>
                    <p class="text-sm text-slate-500">Ubah qty atau harga per item lalu isi alasan edit transaksi.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Produk</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Stok Batch</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Harga</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sale->items as $index => $item)
                            @php
                                $qtyValue = old("items.{$index}.qty", (int) $item->qty);
                                $priceValue = old("items.{$index}.price", number_format((float) $item->price, 0, ',', '.'));
                            @endphp
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $item->product_name }}</div>
                                    <div class="text-xs text-slate-500">INV: {{ $item->productBatch?->display_inventory_code ?: '-' }}</div>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}" />
                                </td>
                                <td class="px-4 py-3 text-right">{{ (int) ($item->productBatch?->stock ?? 0) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <input type="number" min="1" step="1" name="items[{{ $index }}][qty]" value="{{ $qtyValue }}" class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-right" data-item-qty />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <input type="text" inputmode="numeric" name="items[{{ $index }}][price]" value="{{ $priceValue }}" class="w-40 rounded-lg border border-slate-300 px-2 py-1 text-right" data-item-price data-rupiah-input />
                                </td>
                                <td class="px-4 py-3 text-right font-semibold" data-item-subtotal>Rp 0</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-4 py-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Total transaksi baru</span>
                        <span id="new-total" class="text-xl font-extrabold text-emerald-700">Rp {{ number_format($defaultTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <label class="mb-2 block text-sm font-semibold text-slate-700" for="edit-note">Alasan Edit (Wajib)</label>
                <textarea id="edit-note" name="edit_note" rows="3" maxlength="1000" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: salah input qty, koreksi harga, dsb.">{{ old('edit_note') }}</textarea>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <a href="{{ route('cashier.history') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    @endif
</main>

<script>
    const paymentMethodInput = document.getElementById('payment-method');
    const creditDueWrap = document.getElementById('credit-due-wrap');
    const creditDueInput = document.getElementById('credit-due-date');
    const newTotalEl = document.getElementById('new-total');
    const itemQtyInputs = Array.from(document.querySelectorAll('[data-item-qty]'));
    const itemPriceInputs = Array.from(document.querySelectorAll('[data-item-price]'));
    const itemSubtotalEls = Array.from(document.querySelectorAll('[data-item-subtotal]'));
    const rupiahInputs = Array.from(document.querySelectorAll('[data-rupiah-input]'));

    const sanitizeRupiahValue = (value) => String(value ?? '').replace(/[^\d]/g, '');
    const toRupiah = (value) => new Intl.NumberFormat('id-ID').format(Math.round(Number(value || 0)));
    const formatRupiahInputValue = (value) => {
        const digits = sanitizeRupiahValue(value);
        return digits === '' ? '' : toRupiah(Number(digits));
    };

    const syncCreditDueVisibility = () => {
        const isCredit = (paymentMethodInput?.value || 'cash') === 'credit';
        creditDueWrap?.classList.toggle('hidden', !isCredit);
        if (!isCredit && creditDueInput) {
            creditDueInput.value = '';
        }
    };

    const recalculateTotals = () => {
        let runningTotal = 0;

        itemQtyInputs.forEach((qtyInput, index) => {
            const qty = Math.max(0, Number(qtyInput.value || 0));
            const rawPrice = sanitizeRupiahValue(itemPriceInputs[index]?.value || 0);
            const price = Number(rawPrice || 0);
            const subtotal = qty * price;
            runningTotal += subtotal;

            if (itemSubtotalEls[index]) {
                itemSubtotalEls[index].textContent = `Rp ${toRupiah(subtotal)}`;
            }
        });

        if (newTotalEl) {
            newTotalEl.textContent = `Rp ${toRupiah(runningTotal)}`;
        }
    };

    rupiahInputs.forEach((input) => {
        input.addEventListener('focus', () => input.select());
        input.addEventListener('input', () => {
            const cursorAtEnd = input.selectionStart === input.value.length;
            input.value = formatRupiahInputValue(input.value);
            if (cursorAtEnd) {
                input.setSelectionRange(input.value.length, input.value.length);
            }
            recalculateTotals();
        });

        if (input.value) {
            input.value = formatRupiahInputValue(input.value);
        }
    });

    itemQtyInputs.forEach((input) => {
        input.addEventListener('input', recalculateTotals);
    });

    paymentMethodInput?.addEventListener('change', syncCreditDueVisibility);
    syncCreditDueVisibility();
    recalculateTotals();

    document.querySelector('form[action*="/history/"]')?.addEventListener('submit', () => {
        const paidAmountInput = document.getElementById('paid-amount');
        if (paidAmountInput) {
            paidAmountInput.value = sanitizeRupiahValue(paidAmountInput.value || 0);
        }

        itemPriceInputs.forEach((input) => {
            input.value = sanitizeRupiahValue(input.value || 0);
        });
    });
</script>
</body>
</html>

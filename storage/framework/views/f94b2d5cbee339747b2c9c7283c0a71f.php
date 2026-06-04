<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Transaksi - <?php echo e($sale->invoice_number); ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>body { background-color: #f7f9fb; font-family: "Hanken Grotesk", sans-serif; }</style>
</head>
<body class="text-slate-900">
<main class="mx-auto max-w-7xl p-4 lg:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Edit Transaksi Kasir</h1>
            <p class="text-sm text-slate-500">Invoice: <?php echo e($sale->invoice_number); ?> | Tanggal: <?php echo e($sale->created_at?->format('d M Y H:i')); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('cashier.receipt', $sale)); ?>" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Lihat Nota</a>
            <a href="<?php echo e(route('cashier.history')); ?>" class="rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700">Kembali ke History</a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($blockedByReturn ?? false): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="text-lg font-bold text-amber-800">Edit Dikunci</h2>
            <p class="mt-1 text-sm text-amber-700">Transaksi ini sudah memiliki retur, jadi demi konsistensi stok dan nilai transaksi tidak bisa diedit atau dihapus.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="<?php echo e(route('cashier.history')); ?>" class="rounded-xl border border-amber-500 px-4 py-2 text-sm font-semibold text-amber-700">Kembali ke History</a>
                <a href="<?php echo e(route('cashier.receipt', $sale)); ?>" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Buka Nota</a>
            </div>
        </div>
    <?php else: ?>
        <?php
            $defaultPaymentMethod = old('payment_method', strtolower((string) $sale->payment_method));
            $defaultPaidAmount = old('paid_amount', number_format((float) $sale->paid_amount, 0, ',', '.'));
            $defaultCreditDueDate = old('credit_due_date', $sale->credit_due_date?->toDateString());
            $defaultTotal = (float) $sale->items->sum(fn ($item) => (float) $item->price * (int) $item->qty);
        ?>

        <form method="POST" action="<?php echo e(route('cashier.history.update', $sale)); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <h2 class="text-lg font-bold">Informasi Transaksi</h2>
                <div class="mt-3 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Pembeli</label>
                        <input type="text" name="customer_name" maxlength="100" value="<?php echo e(old('customer_name', $sale->customer_name ?? '')); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Nama pembeli (opsional)" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">No. Telepon Pembeli</label>
                        <input type="text" name="customer_phone" maxlength="30" value="<?php echo e(old('customer_phone', $sale->customer_phone ?? '')); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="No. telepon pembeli (opsional)" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Pelayan / Kasir</label>
                        <input type="text" name="cashier_service_name" maxlength="100" value="<?php echo e(old('cashier_service_name', $sale->cashier_service_name ?? $user?->name ?? '')); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">No. Telepon Kasir</label>
                        <input type="text" name="cashier_phone" maxlength="30" value="<?php echo e(old('cashier_phone', $sale->cashier_phone ?? '')); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Metode Pembayaran</label>
                        <select name="payment_method" id="payment-method" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="cash" <?php if($defaultPaymentMethod === 'cash'): echo 'selected'; endif; ?>>Cash</option>
                            <option value="transfer" <?php if($defaultPaymentMethod === 'transfer'): echo 'selected'; endif; ?>>Transfer</option>
                            <option value="qris" <?php if($defaultPaymentMethod === 'qris'): echo 'selected'; endif; ?>>QRIS</option>
                            <option value="debit" <?php if($defaultPaymentMethod === 'debit'): echo 'selected'; endif; ?>>Debit</option>
                            <option value="credit" <?php if($defaultPaymentMethod === 'credit'): echo 'selected'; endif; ?>>Credit</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah Bayar</label>
                        <input type="text" name="paid_amount" id="paid-amount" inputmode="numeric" value="<?php echo e($defaultPaidAmount); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" data-rupiah-input />
                    </div>
                    <div id="credit-due-wrap" class="<?php echo e($defaultPaymentMethod === 'credit' ? '' : 'hidden'); ?>">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo Kredit</label>
                        <input type="date" name="credit_due_date" id="credit-due-date" value="<?php echo e($defaultCreditDueDate); ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $qtyValue = old("items.{$index}.qty", (int) $item->qty);
                                $priceValue = old("items.{$index}.price", number_format((float) $item->price, 0, ',', '.'));
                            ?>
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">
                                    <div class="font-semibold"><?php echo e($item->product_name); ?></div>
                                    <div class="text-xs text-slate-500">Batch: <?php echo e($item->productBatch?->batch_code ?: '-'); ?></div>
                                    <input type="hidden" name="items[<?php echo e($index); ?>][id]" value="<?php echo e($item->id); ?>" />
                                </td>
                                <td class="px-4 py-3 text-right"><?php echo e((int) ($item->productBatch?->stock ?? 0)); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <input type="number" min="1" step="1" name="items[<?php echo e($index); ?>][qty]" value="<?php echo e($qtyValue); ?>" class="w-24 rounded-lg border border-slate-300 px-2 py-1 text-right" data-item-qty />
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <input type="text" inputmode="numeric" name="items[<?php echo e($index); ?>][price]" value="<?php echo e($priceValue); ?>" class="w-40 rounded-lg border border-slate-300 px-2 py-1 text-right" data-item-price data-rupiah-input />
                                </td>
                                <td class="px-4 py-3 text-right font-semibold" data-item-subtotal>Rp 0</td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-4 py-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Total transaksi baru</span>
                        <span id="new-total" class="text-xl font-extrabold text-emerald-700">Rp <?php echo e(number_format($defaultTotal, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <label class="mb-2 block text-sm font-semibold text-slate-700" for="edit-note">Alasan Edit (Wajib)</label>
                <textarea id="edit-note" name="edit_note" rows="3" maxlength="1000" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: salah input qty, koreksi harga, dsb."><?php echo e(old('edit_note')); ?></textarea>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <a href="<?php echo e(route('cashier.history')); ?>" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\laragon\www\backend\resources\views\cashier\history-edit.blade.php ENDPATH**/ ?>
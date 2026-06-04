<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Kode Batch</label>
    <input type="text" name="batch_code" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="{{ $batchCodePlaceholder }}">
    <p class="mt-1 text-xs text-error" data-error-for="batch_code"></p>
  </div>
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Harga Beli Satuan</label>
    <input type="text" inputmode="numeric" name="purchase_price" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="0">
    <p class="mt-1 text-xs text-error" data-error-for="purchase_price"></p>
  </div>
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Biaya Ekspedisi</label>
    <input type="text" inputmode="numeric" name="expedition_cost" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="0">
    <p class="mt-1 text-xs text-error" data-error-for="expedition_cost"></p>
  </div>
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Harga Jual</label>
    <input type="text" inputmode="numeric" name="selling_price" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="0">
    <p class="mt-1 text-xs text-error" data-error-for="selling_price"></p>
  </div>
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Jumlah Barang Dibeli</label>
    <input type="number" min="0" step="1" name="stock" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
    <p class="mt-1 text-xs text-error" data-error-for="stock"></p>
  </div>
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Tipe Pembayaran Supplier</label>
    <select name="payment_type" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <option value="LUNAS">Lunas</option>
      <option value="KREDIT">Kredit</option>
    </select>
    <p class="mt-1 text-xs text-error" data-error-for="payment_type"></p>
  </div>
  <div data-credit-days-wrap class="hidden">
    <label class="mb-1 block text-sm font-medium text-on-surface">Tempo (Hari)</label>
    <input type="number" min="1" max="3650" step="1" name="credit_days" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="30">
    <p class="mt-1 text-xs text-error" data-error-for="credit_days"></p>
  </div>
  <div data-credit-due-wrap class="hidden">
    <label class="mb-1 block text-sm font-medium text-on-surface">Jatuh Tempo</label>
    <input type="date" name="credit_due_date" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
    <p class="mt-1 text-xs font-semibold text-primary" data-credit-due-human></p>
    <p class="mt-1 text-xs text-error" data-error-for="credit_due_date"></p>
  </div>
  <div data-down-payment-wrap class="hidden">
    <label class="mb-1 block text-sm font-medium text-on-surface">DP / Uang Muka</label>
    <input type="text" inputmode="numeric" name="down_payment_amount" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="0">
    <p class="mt-1 text-xs font-semibold text-primary" data-down-payment-hint></p>
    <p class="mt-1 text-xs text-error" data-error-for="down_payment_amount"></p>
  </div>
  <div>
    <label class="mb-1 block text-sm font-medium text-on-surface">Total Harga Beli</label>
    <input type="text" name="total_purchase_display" readonly class="w-full rounded-xl border border-outline-variant bg-surface-container px-4 py-3 text-on-surface-variant focus:outline-none" value="Rp 0">
  </div>
</div>

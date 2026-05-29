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
    <label class="mb-1 block text-sm font-medium text-on-surface">Total Harga Beli</label>
    <input type="text" name="total_purchase_display" readonly class="w-full rounded-xl border border-outline-variant bg-surface-container px-4 py-3 text-on-surface-variant focus:outline-none" value="Rp 0">
  </div>
</div>

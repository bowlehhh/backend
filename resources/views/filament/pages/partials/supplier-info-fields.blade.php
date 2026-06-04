<div class="rounded-2xl border border-outline-variant bg-surface p-5">
  <h3 class="mb-4 text-headline-md text-on-surface">Card 2: Informasi Supplier</h3>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Nama Supplier</label>
      <input type="text" name="supplier_name" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_name"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Nomor HP supplier</label>
      <input type="text" name="supplier_phone" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_phone"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Alamat supplier</label>
      <input type="text" name="supplier_address" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_address"></p>
    </div>
    <div class="md:col-span-2">
      <label class="mb-1 block text-sm font-medium text-on-surface">Catatan supplier</label>
      <textarea name="supplier_note" rows="3" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none"></textarea>
      <p class="mt-1 text-xs text-error" data-error-for="supplier_note"></p>
    </div>
  </div>
</div>

<div class="rounded-[1.1rem] border border-outline-variant bg-surface p-3.5">
  <h3 class="mb-2.5 text-[15px] font-semibold leading-tight text-on-surface">Card 2: Informasi Supplier</h3>
  <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Nama Supplier <span class="text-on-surface-variant">(opsional)</span></label>
      <input type="text" name="supplier_name" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_name"></p>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Nomor HP supplier</label>
      <input type="text" name="supplier_phone" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_phone"></p>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Alamat supplier</label>
      <input type="text" name="supplier_address" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_address"></p>
    </div>
    <div class="md:col-span-2">
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Catatan supplier</label>
      <textarea name="supplier_note" rows="2" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none"></textarea>
      <p class="mt-1 text-xs text-error" data-error-for="supplier_note"></p>
    </div>
  </div>
</div>
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/filament/pages/partials/supplier-info-fields.blade.php ENDPATH**/ ?>
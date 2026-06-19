<div class="rounded-[1.1rem] border border-outline-variant bg-surface p-3.5">
  <h3 class="mb-2.5 text-[15px] font-semibold leading-tight text-on-surface">Card 1: Informasi Barang</h3>
  <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Part Number</label>
      <div class="relative" data-part-number-autofill>
        <input type="text" name="barcode" data-history-key="barcode" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] uppercase focus:border-primary focus:outline-none">
        <div data-part-number-suggestions class="absolute left-0 right-0 top-[calc(100%+0.35rem)] z-20 hidden overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-xl"></div>
      </div>
      <p class="mt-1 text-[11px] leading-4 text-on-surface-variant">Boleh sama kalau kondisi atau waktu input berbeda.</p>
      <p class="mt-1 hidden text-[11px] leading-4 text-primary" data-part-number-helper>Pilih rekomendasi untuk isi otomatis data barang yang pernah dibuat.</p>
      <p class="mt-1 text-xs text-error" data-error-for="barcode"></p>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Part Name <span class="text-on-surface-variant">(opsional)</span></label>
      <input type="text" name="name" data-history-key="product_name" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="name"></p>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Unit</label>
      <input type="text" name="unit" data-history-key="unit" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] uppercase focus:border-primary focus:outline-none" placeholder="PCS / UNIT / BOX">
      <p class="mt-1 text-xs text-error" data-error-for="unit"></p>
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_140px]">
      <div>
        <label class="mb-1 block text-[12px] font-medium text-on-surface">Berat</label>
      <input type="number" step="0.01" min="0" name="weight" data-history-key="weight" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none" placeholder="0">
        <p class="mt-1 text-xs text-error" data-error-for="weight"></p>
      </div>
      <div>
        <label class="mb-1 block text-[12px] font-medium text-on-surface">Satuan</label>
        <select name="weight_unit" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none">
          <option value="kg" selected>kg</option>
          <option value="gram">gram</option>
          <option value="ton">ton</option>
          <option value="lb">lb</option>
          <option value="oz">oz</option>
          <option value="other">Lainnya</option>
        </select>
        <p class="mt-1 text-xs text-error" data-error-for="weight_unit"></p>
      </div>
      <div class="sm:col-span-2 hidden" data-weight-unit-custom-wrap>
        <label class="mb-1 block text-[12px] font-medium text-on-surface">Satuan Kustom</label>
        <input type="text" name="weight_unit_custom" data-history-key="weight_unit_custom" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none" placeholder="Contoh: sack, box, pack">
        <p class="mt-1 text-xs text-error" data-error-for="weight_unit_custom"></p>
      </div>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Slug</label>
      <input type="text" name="slug" data-history-key="slug" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none" placeholder="Otomatis dari nama barang">
      <p class="mt-1 text-xs text-error" data-error-for="slug"></p>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Kategori <span class="text-on-surface-variant">(opsional)</span></label>
      <input type="text" name="category" data-history-key="category" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none" placeholder="Opsional, bisa dikosongkan">
      <p class="mt-1 text-[11px] leading-4 text-on-surface-variant">Kategori opsional, bisa dihapus kalau tidak dipakai.</p>
      <p class="mt-1 text-xs text-error" data-error-for="category"></p>
    </div>
    <div>
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Merek <span class="text-on-surface-variant">(opsional)</span></label>
      <input type="text" name="brand" data-history-key="brand" autocomplete="off" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none" placeholder="Masukkan merek">
      <p class="mt-1 text-xs text-error" data-error-for="brand"></p>
    </div>
    <div class="md:col-span-2">
      <label class="mb-1 block text-[12px] font-medium text-on-surface">Foto Barang</label>
      <input type="file" name="image" accept="image/*" class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-[13px] focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="image"></p>
      <img data-image-preview class="mt-2.5 hidden h-16 w-16 rounded-lg border border-outline-variant object-cover" alt="Preview foto barang">
    </div>
    <div class="md:col-span-2">
      <label class="flex items-center gap-3 rounded-lg border border-outline-variant bg-surface px-3 py-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary" @checked($isActiveDefault)>
        <span class="text-[12px] text-on-surface">Status aktif dijual</span>
      </label>
    </div>
  </div>
</div>

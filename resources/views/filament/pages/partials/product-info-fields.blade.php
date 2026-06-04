<div class="rounded-2xl border border-outline-variant bg-surface p-5">
  <h3 class="mb-4 text-headline-md text-on-surface">Card 1: Informasi Barang</h3>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Part Name</label>
      <input type="text" name="name" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="name"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Part Number</label>
      <input type="text" name="barcode" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 uppercase focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="barcode"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Unit</label>
      <input type="text" name="unit" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 uppercase focus:border-primary focus:outline-none" placeholder="PCS / UNIT / BOX">
      <p class="mt-1 text-xs text-error" data-error-for="unit"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Berat (kg)</label>
      <input type="number" step="0.01" min="0" name="weight" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="0">
      <p class="mt-1 text-xs text-error" data-error-for="weight"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Slug</label>
      <input type="text" name="slug" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none" placeholder="Otomatis dari nama barang">
      <p class="mt-1 text-xs text-error" data-error-for="slug"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Kategori</label>
      <select name="category" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
        <option value="Alat Berat">Alat Berat</option>
      </select>
      <p class="mt-1 text-xs text-error" data-error-for="category"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Merek</label>
      <input type="text" name="brand" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="brand"></p>
    </div>
    <div class="md:col-span-2">
      <label class="mb-1 block text-sm font-medium text-on-surface">Foto Barang</label>
      <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="image"></p>
      <img data-image-preview class="mt-3 hidden h-24 w-24 rounded-lg border border-outline-variant object-cover" alt="Preview foto barang">
    </div>
    <div class="md:col-span-2">
      <label class="flex items-center gap-3 rounded-xl border border-outline-variant bg-surface px-4 py-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-outline-variant text-primary focus:ring-primary" @checked($isActiveDefault)>
        <span class="text-sm text-on-surface">Status aktif dijual</span>
      </label>
    </div>
  </div>
</div>

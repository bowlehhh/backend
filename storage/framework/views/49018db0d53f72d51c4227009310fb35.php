<div class="rounded-2xl border border-outline-variant bg-surface p-5">
  <h3 class="mb-4 text-headline-md text-on-surface">Card 2: Informasi Supplier</h3>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Nama Supplier</label>
      <input type="text" name="supplier_name" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
      <p class="mt-1 text-xs text-error" data-error-for="supplier_name"></p>
    </div>
    <div>
      <label class="mb-1 block text-sm font-medium text-on-surface">Tipe supplier</label>
      <select name="supplier_branch" class="w-full rounded-xl border border-outline-variant bg-surface px-4 py-3 focus:border-primary focus:outline-none">
        <option value="">Pilih tipe supplier</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $supplierTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplierType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
          <option value="<?php echo e($supplierType); ?>"><?php echo e($supplierType); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
      </select>
      <p class="mt-1 text-xs text-error" data-error-for="supplier_branch"></p>
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
<?php /**PATH /home/mrgana/pos-inventory/backend/resources/views/filament/pages/partials/supplier-info-fields.blade.php ENDPATH**/ ?>
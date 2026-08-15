<?php $__env->startSection('title', 'Transfer Stok Baru'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Input Transfer Stok Antar Gudang</h1>
    <form method="POST" action="<?php echo e(route('inventory.transfers.store')); ?>" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. Transfer *</label>
                <input type="text" name="number" value="TRF-202608-<?php echo e(rand(1000, 9999)); ?>" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal *</label>
                <input type="date" name="date" value="<?php echo e(date('Y-m-d')); ?>" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Gudang Asal *</label>
                <select name="source_warehouse_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Gudang Tujuan *</label>
                <select name="destination_warehouse_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($wh->id); ?>" <?php echo e($index == 1 ? 'selected' : ''); ?>><?php echo e($wh->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-erp-border">
            <h3 class="text-xs font-bold text-erp-green uppercase tracking-wider">Detail Item Ditransfer</h3>
            <div class="grid grid-cols-12 gap-3 items-center">
                <div class="col-span-8">
                    <label class="block text-[11px] text-gray-600 dark:text-gray-400">Pilih Item</label>
                    <select name="items[0][item_id]" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($itm->id); ?>"><?php echo e($itm->name); ?> (<?php echo e($itm->sku); ?>)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="block text-[11px] text-gray-600 dark:text-gray-400">Qty Ditransfer</label>
                    <input type="number" name="items[0][quantity]" value="25" min="1" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white"></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-erp-border">
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs">Simpan Draft Transfer</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\transfers\create.blade.php ENDPATH**/ ?>
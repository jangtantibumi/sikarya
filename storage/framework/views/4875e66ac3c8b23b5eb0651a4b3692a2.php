<?php $__env->startSection('title', 'Edit Master Item'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Master Item: <?php echo e($item->name); ?></h1>
        <a href="<?php echo e(route('inventory.items.index')); ?>" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
            <i class="ph ph-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <form method="POST" action="<?php echo e(route('inventory.items.update', $item->id)); ?>" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Item *</label>
                <input type="text" name="name" value="<?php echo e(old('name', $item->name)); ?>" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">SKU Code *</label>
                <input type="text" name="sku" value="<?php echo e(old('sku', $item->sku)); ?>" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                <input type="text" name="barcode" value="<?php echo e(old('barcode', $item->barcode)); ?>" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori *</label>
                <select name="category_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($cat->id); ?>" <?php echo e($item->category_id == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Brand *</label>
                <select name="brand_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($brd->id); ?>" <?php echo e($item->brand_id == $brd->id ? 'selected' : ''); ?>><?php echo e($brd->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of Measure (UoM) *</label>
                <select name="uom_id" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $uoms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($uom->id); ?>" <?php echo e($item->uom_id == $uom->id ? 'selected' : ''); ?>><?php echo e($uom->name); ?> (<?php echo e($uom->symbol); ?>)</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Beli *</label>
                <input type="number" name="cost_price" value="<?php echo e(old('cost_price', $item->cost_price)); ?>" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Jual *</label>
                <input type="number" name="selling_price" value="<?php echo e(old('selling_price', $item->selling_price)); ?>" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock *</label>
                <input type="number" name="min_stock" value="<?php echo e(old('min_stock', $item->min_stock)); ?>" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Max Stock *</label>
                <input type="number" name="max_stock" value="<?php echo e(old('max_stock', $item->max_stock)); ?>" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Point *</label>
                <input type="number" name="reorder_point" value="<?php echo e(old('reorder_point', $item->reorder_point)); ?>" min="0" required class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Item</label>
            <textarea name="description" rows="3" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white focus:border-erp-green"><?php echo e(old('description', $item->description)); ?></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-erp-border">
            <a href="<?php echo e(route('inventory.items.index')); ?>" class="bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg text-xs">Batal</a>
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs shadow-lg">Update Item</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\items\edit.blade.php ENDPATH**/ ?>
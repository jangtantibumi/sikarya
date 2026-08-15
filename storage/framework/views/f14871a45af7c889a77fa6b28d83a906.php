<?php $__env->startSection('title', 'Pengaturan Gudang & Stok'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Pengaturan Modul Gudang & Stok</h1>
    <form method="POST" action="<?php echo e(route('inventory.settings.update')); ?>" class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border space-y-6">
        <?php echo csrf_field(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div>
                <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1 uppercase font-mono"><?php echo e($st->setting_key); ?></label>
                <p class="text-[11px] text-gray-600 dark:text-gray-400 mb-2"><?php echo e($st->description); ?></p>
                <input type="text" name="<?php echo e($st->setting_key); ?>" value="<?php echo e($st->setting_value); ?>" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-2 text-xs text-gray-900 dark:text-white">
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-erp-border">
            <button type="submit" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-bold py-2 px-6 rounded-lg text-xs">Simpan Pengaturan</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\settings\index.blade.php ENDPATH**/ ?>
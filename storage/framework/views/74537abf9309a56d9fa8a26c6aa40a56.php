<?php $__env->startSection('title', 'Detail Packing - ' . $packing->number); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($packing->number); ?></h1>
            <p class="text-xs text-erp-green">Packer: <?php echo e($packing->packer_name); ?> | Tanggal: <?php echo e($packing->date); ?></p>
        </div>
        <a href="<?php echo e(route('inventory.packings.index')); ?>" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center">
            <i class="ph ph-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="glass-panel p-6 rounded-xl border border-gray-200 dark:border-erp-border mb-6">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Item Dikemas</h3>
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Item SKU & Nama</th>
                    <th class="py-3 px-4">Packed Qty</th>
                    <th class="py-3 px-4">No. Box / Kardus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $packing->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white"><?php echo e(optional($line->item)->name); ?> (<?php echo e(optional($line->item)->sku); ?>)</td>
                        <td class="py-3 px-4 font-bold text-emerald-400"><?php echo e(number_format($line->packed_qty)); ?></td>
                        <td class="py-3 px-4 font-mono text-amber-400"><?php echo e($line->box_number); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\packings\show.blade.php ENDPATH**/ ?>
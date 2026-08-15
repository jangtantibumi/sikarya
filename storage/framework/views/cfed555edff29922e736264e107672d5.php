<?php $__env->startSection('title', 'Picking List'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Picking (Pengambilan Barang)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Instruksi pengambilan barang dari rak/bin gudang.</p>
    </div>
    <a href="<?php echo e(route('inventory.pickings.create')); ?>" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-plus mr-1.5 text-sm"></i> Picking Order Baru
    </a>
</div>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">No. Picking</th>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Picker Staff</th>
                <th class="py-3 px-4">Gudang</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pickings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green"><?php echo e($pic->number); ?></td>
                    <td class="py-3 px-4"><?php echo e($pic->date); ?></td>
                    <td class="py-3 px-4 font-medium"><?php echo e($pic->picker_name); ?></td>
                    <td class="py-3 px-4"><?php echo e(optional($pic->warehouse)->name); ?></td>
                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 uppercase"><?php echo e($pic->status); ?></span></td>
                    <td class="py-3 px-4 text-center">
                        <a href="<?php echo e(route('inventory.pickings.show', $pic->id)); ?>" class="text-blue-400 hover:underline"><i class="ph ph-eye text-base"></i></a>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
</div>
<div><?php echo e($pickings->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\pickings\index.blade.php ENDPATH**/ ?>
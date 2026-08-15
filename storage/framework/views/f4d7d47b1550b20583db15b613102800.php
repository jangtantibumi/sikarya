<?php $__env->startSection('title', 'Stock Summary'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Ringkasan Stok (Stock Summary)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Posisi real-time kuantitas stok per item, per gudang, dan bin lokasi.</p>
    </div>
    <a href="<?php echo e(route('inventory.stock-summary.export')); ?>" class="bg-white dark:bg-erp-card border border-gray-200 dark:border-erp-border hover:bg-erp-border text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg text-xs flex items-center">
        <i class="ph ph-download-simple mr-1.5 text-sm"></i> Export CSV
    </a>
</div>

<form method="GET" action="<?php echo e(route('inventory.stock-summary.index')); ?>" class="glass-panel p-4 rounded-xl border border-gray-200 dark:border-erp-border mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari Nama Item atau SKU..." class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
    </div>
    <div class="w-48">
        <select name="warehouse_id" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
            <option value="">Semua Gudang</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($wh->id); ?>" <?php echo e(request('warehouse_id') == $wh->id ? 'selected' : ''); ?>><?php echo e($wh->name); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
    </div>
    <button type="submit" class="bg-erp-border text-gray-900 dark:text-white font-medium py-1.5 px-4 rounded-lg text-xs">Filter</button>
</form>

<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">Item SKU & Nama</th>
                <th class="py-3 px-4">Gudang</th>
                <th class="py-3 px-4">Bin Lokasi</th>
                <th class="py-3 px-4">Stok Fisik (Qty)</th>
                <th class="py-3 px-4">Reserved</th>
                <th class="py-3 px-4">Allocated</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stockSummaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4">
                        <div class="font-bold text-gray-900 dark:text-white"><?php echo e(optional($s->item)->name); ?></div>
                        <div class="text-[10px] text-erp-green font-mono"><?php echo e(optional($s->item)->sku); ?></div>
                    </td>
                    <td class="py-3 px-4 font-medium"><?php echo e(optional($s->warehouse)->name); ?></td>
                    <td class="py-3 px-4 font-mono text-gray-600 dark:text-gray-400"><?php echo e(optional($s->bin)->name ?? 'General Bin'); ?></td>
                    <td class="py-3 px-4 font-bold text-emerald-400"><?php echo e(number_format($s->quantity)); ?></td>
                    <td class="py-3 px-4 text-amber-400 font-medium"><?php echo e(number_format($s->reserved_qty)); ?></td>
                    <td class="py-3 px-4 text-blue-400 font-medium"><?php echo e(number_format($s->allocated_qty)); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-500">Tidak ada data stok ditemukan.</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>

<div>
    <?php echo e($stockSummaries->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\stock-summary\index.blade.php ENDPATH**/ ?>
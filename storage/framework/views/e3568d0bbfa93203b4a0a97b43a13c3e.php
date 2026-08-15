<?php $__env->startSection('title', 'Master Items'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Master Data Items</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Kelola katalog barang, SKU, harga, dan reorder point.</p>
    </div>
    <div class="flex space-x-3">
        <a href="<?php echo e(route('inventory.items.export')); ?>" class="bg-white dark:bg-erp-card hover:bg-erp-border text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-erp-border font-medium py-2 px-3 rounded-lg text-xs flex items-center">
            <i class="ph ph-download-simple mr-1.5 text-sm"></i> Export CSV
        </a>
        <a href="<?php echo e(route('inventory.items.create')); ?>" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg shadow-lg text-xs flex items-center">
            <i class="ph ph-plus mr-1.5 text-sm"></i> Tambah Item Baru
        </a>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="<?php echo e(route('inventory.items.index')); ?>" class="glass-panel p-4 rounded-xl border border-gray-200 dark:border-erp-border mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari Nama Item, SKU, atau Barcode..." class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-erp-green">
    </div>
    <div class="w-48">
        <select name="category_id" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            <option value="">Semua Kategori</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
    </div>
    <div class="w-48">
        <select name="brand_id" class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-erp-green">
            <option value="">Semua Brand</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($brd->id); ?>" <?php echo e(request('brand_id') == $brd->id ? 'selected' : ''); ?>><?php echo e($brd->name); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
    </div>
    <button type="submit" class="bg-erp-border hover:bg-gray-700 text-gray-900 dark:text-white font-medium py-1.5 px-4 rounded-lg text-xs">Filter</button>
    <a href="<?php echo e(route('inventory.items.index')); ?>" class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white">Reset</a>
</form>

<!-- Table -->
<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border overflow-hidden mb-6">
    <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
            <tr>
                <th class="py-3 px-4">SKU & Barcode</th>
                <th class="py-3 px-4">Nama Item</th>
                <th class="py-3 px-4">Kategori & Brand</th>
                <th class="py-3 px-4">UoM</th>
                <th class="py-3 px-4">Harga Beli / Jual</th>
                <th class="py-3 px-4">Stok Fisik</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="py-3 px-4 font-mono">
                        <span class="text-erp-green font-semibold"><?php echo e($item->sku); ?></span>
                        <div class="text-[10px] text-gray-500 dark:text-gray-500"><?php echo e($item->barcode ?? '-'); ?></div>
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white"><?php echo e($item->name); ?></td>
                    <td class="py-3 px-4">
                        <div><?php echo e(optional($item->category)->name ?? '-'); ?></div>
                        <div class="text-[10px] text-gray-600 dark:text-gray-400"><?php echo e(optional($item->brand)->name ?? '-'); ?></div>
                    </td>
                    <td class="py-3 px-4 font-medium text-gray-700 dark:text-gray-300"><?php echo e(optional($item->uom)->symbol ?? optional($item->uom)->name); ?></td>
                    <td class="py-3 px-4 font-mono">
                        <div class="text-gray-700 dark:text-gray-300">Rp <?php echo e(number_format($item->cost_price)); ?></div>
                        <div class="text-[10px] text-emerald-400">Rp <?php echo e(number_format($item->selling_price)); ?></div>
                    </td>
                    <td class="py-3 px-4 font-bold text-emerald-400">
                        <?php echo e(number_format($item->total_stock)); ?>

                    </td>
                    <td class="py-3 px-4 text-center space-x-2">
                        <a href="<?php echo e(route('inventory.items.show', $item->id)); ?>" class="text-blue-400 hover:text-blue-300" title="View"><i class="ph ph-eye text-base"></i></a>
                        <a href="<?php echo e(route('inventory.items.edit', $item->id)); ?>" class="text-amber-400 hover:text-amber-300" title="Edit"><i class="ph ph-pencil-simple text-base"></i></a>
                        <form method="POST" action="<?php echo e(route('inventory.items.destroy', $item->id)); ?>" class="inline" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-rose-400 hover:text-rose-300" title="Delete"><i class="ph ph-trash text-base"></i></button>
                        </form>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-500">Tidak ada item ditemukan.</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php echo e($items->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\items\index.blade.php ENDPATH**/ ?>
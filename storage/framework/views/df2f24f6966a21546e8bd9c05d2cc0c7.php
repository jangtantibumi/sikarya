<?php $__env->startSection('title', 'Stock Buku Catatan (Kartu Stok)'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-end mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Stock Buku Catatan (Kartu Stok Digital)</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Jurnal riwayat mutasi stok lengkap dari seluruh transaksi.</p>
    </div>
</div>

<form method="GET" action="<?php echo e(route('inventory.stock-Buku Catatan.index')); ?>" class="glass-panel p-4 rounded-xl border border-gray-200 dark:border-erp-border mb-6 flex flex-wrap gap-4 items-center">
    <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari Ref Number, SKU, Nama Item..." class="w-full bg-gray-50 dark:bg-erp-dark border border-gray-200 dark:border-erp-border rounded-lg px-3 py-1.5 text-xs text-gray-900 dark:text-white">
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
                <th class="py-3 px-4">Ref Number</th>
                <th class="py-3 px-4">Tipe Transaksi</th>
                <th class="py-3 px-4">Item SKU & Nama</th>
                <th class="py-3 px-4">Gudang</th>
                <th class="py-3 px-4">Kuantitas</th>
                <th class="py-3 px-4">Harga Satuan</th>
                <th class="py-3 px-4">Operator</th>
                <th class="py-3 px-4">Waktu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr class="hover:bg-white/5">
                    <td class="py-3 px-4 font-mono font-bold text-erp-green"><?php echo e($m->reference_number); ?></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase
                            <?php if($m->quantity > 0): ?> bg-emerald-500/20 text-emerald-300 <?php else: ?> bg-rose-500/20 text-rose-300 <?php endif; ?>">
                            <?php echo e(str_replace('_', ' ', $m->transaction_type)); ?>

                        </span>
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white"><?php echo e(optional($m->item)->name); ?> <span class="text-gray-500 dark:text-gray-500">(<?php echo e(optional($m->item)->sku); ?>)</span></td>
                    <td class="py-3 px-4"><?php echo e(optional($m->warehouse)->name); ?></td>
                    <td class="py-3 px-4 font-bold <?php echo e($m->quantity > 0 ? 'text-emerald-400' : 'text-rose-400'); ?>">
                        <?php echo e($m->quantity > 0 ? '+'.number_format($m->quantity) : number_format($m->quantity)); ?>

                    </td>
                    <td class="py-3 px-4 font-mono">Rp <?php echo e(number_format($m->unit_cost)); ?></td>
                    <td class="py-3 px-4 text-gray-600 dark:text-gray-400"><?php echo e($m->created_by); ?></td>
                    <td class="py-3 px-4 text-gray-600 dark:text-gray-400 text-[11px]"><?php echo e($m->created_at->format('d M Y H:i')); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
</div>
<div><?php echo e($movements->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\stock-ledger\index.blade.php ENDPATH**/ ?>
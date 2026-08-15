<?php $__env->startSection('title', 'Gudang & Stok Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-end mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Dasbor Gudang</h1>
        <p class="text-xs text-gray-600 dark:text-gray-400">Ringkasan langsung aktivitas gudang dan stok bahan baku Anda.</p>
    </div>
    <div class="flex space-x-3">
        <a href="<?php echo e(route('inventory.items.create')); ?>" class="bg-erp-green hover:bg-emerald-400 text-gray-900 font-semibold py-2 px-4 rounded-lg shadow-lg text-xs flex items-center transition-all">
            <i class="ph ph-plus mr-1.5 text-sm"></i> Tambah Produk/Barang Baru
        </a>
        <a href="<?php echo e(route('inventory.stock-in.create')); ?>" class="bg-blue-600 hover:bg-blue-500 text-gray-900 dark:text-white font-semibold py-2 px-4 rounded-lg shadow-lg text-xs flex items-center transition-all">
            <i class="ph ph-arrow-down-right mr-1.5 text-sm"></i> Barang Masuk (Stock In)
        </a>
    </div>
</div>

<!-- Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
        <div class="flex justify-between items-start mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Jenis Barang</span>
            <div class="p-2 bg-emerald-500/10 rounded-lg text-erp-green">
                <i class="ph ph-box-cube text-xl"></i>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1"><?php echo e(number_format($totalItems)); ?></div>
        <span class="text-[11px] text-emerald-400">100% item F&B terdaftar</span>
    </div>

    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
        <div class="flex justify-between items-start mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Lokasi Gudang</span>
            <div class="p-2 bg-blue-500/10 rounded-lg text-blue-400">
                <i class="ph ph-warehouse text-xl"></i>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1"><?php echo e(number_format($totalWarehouses)); ?></div>
        <span class="text-[11px] text-blue-400">5 Gudang & Cold Storage</span>
    </div>

    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
        <div class="flex justify-between items-start mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Total Jumlah Stok</span>
            <div class="p-2 bg-purple-500/10 rounded-lg text-purple-400">
                <i class="ph ph-stack text-xl"></i>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1"><?php echo e(number_format($totalStockQty)); ?></div>
        <span class="text-[11px] text-purple-400">Tersedia di semua lokasi</span>
    </div>

    <div class="glass-panel p-5 rounded-xl border border-gray-200 dark:border-erp-border">
        <div class="flex justify-between items-start mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Menunggu Persetujuan</span>
            <div class="p-2 bg-amber-500/10 rounded-lg text-amber-400">
                <i class="ph ph-clock-afternoon text-xl"></i>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1"><?php echo e($pendingStockIns + $pendingStockOuts + $pendingTransfers + $pendingAdjustments); ?></div>
        <span class="text-[11px] text-amber-400">Draft butuh persetujuan</span>
    </div>
</div>

<!-- Recent Stock Movements Table -->
<div class="glass-panel rounded-xl border border-gray-200 dark:border-erp-border p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Riwayat Keluar Masuk Barang (Buku Stok)</h2>
            <p class="text-xs text-gray-600 dark:text-gray-400">Catatan transaksi barang masuk, keluar, dan pindah gudang.</p>
        </div>
        <a href="<?php echo e(route('inventory.stock-Buku Catatan.index')); ?>" class="text-xs text-erp-green hover:underline flex items-center">
            Lihat Semua Buku Catatan <i class="ph ph-arrow-right ml-1"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-erp-dark text-gray-600 dark:text-gray-400 uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Ref Number</th>
                    <th class="py-3 px-4">Tipe Transaksi</th>
                    <th class="py-3 px-4">Item SKU & Nama</th>
                    <th class="py-3 px-4">Gudang</th>
                    <th class="py-3 px-4">Qty</th>
                    <th class="py-3 px-4">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border text-gray-800 dark:text-gray-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="py-3 px-4 font-mono text-erp-green"><?php echo e($m->reference_number); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase
                                <?php if($m->quantity > 0): ?> bg-emerald-500/20 text-emerald-300 <?php else: ?> bg-rose-500/20 text-rose-300 <?php endif; ?>">
                                <?php echo e(str_replace('_', ' ', $m->transaction_type)); ?>

                            </span>
                        </td>
                        <td class="py-3 px-4 font-medium"><?php echo e(optional($m->item)->name); ?> <span class="text-gray-500 dark:text-gray-500">(<?php echo e(optional($m->item)->sku); ?>)</span></td>
                        <td class="py-3 px-4"><?php echo e(optional($m->warehouse)->name); ?></td>
                        <td class="py-3 px-4 font-bold <?php echo e($m->quantity > 0 ? 'text-emerald-400' : 'text-rose-400'); ?>">
                            <?php echo e($m->quantity > 0 ? '+'.number_format($m->quantity) : number_format($m->quantity)); ?>

                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400"><?php echo e($m->created_at->diffForHumans()); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-500 dark:text-gray-500">Belum ada mutasi stok terdeteksi.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('inventory.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\dashboard.blade.php ENDPATH**/ ?>
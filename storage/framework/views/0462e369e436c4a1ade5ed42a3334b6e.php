<?php $__env->startSection('title', 'Currencies - ERP Finance'); ?>
<?php $__env->startSection('page_heading', 'Currency Master (Mata Uang)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Currency</h2>
            <form action="<?php echo e(route('finance.currencies.store')); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode ISO</label>
                    <input type="text" name="code" required placeholder="IDR, USD, EUR" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Mata Uang</label>
                    <input type="text" name="name" required placeholder="Rupiah Indonesia" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Simbol</label>
                        <input type="text" name="symbol" required placeholder="Rp, $" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Desimal</label>
                        <input type="number" name="decimal_places" value="2" min="0" max="6" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="pt-2">
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_base" value="1" class="rounded bg-slate-900 border-slate-700 text-emerald-600 focus:ring-0">
                        Set Sebagai Base Currency Company
                    </label>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Currency
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Currency Master</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">ISO Code</th>
                        <th class="px-4 py-3">Nama Uang</th>
                        <th class="px-4 py-3">Simbol</th>
                        <th class="px-4 py-3">Desimal</th>
                        <th class="px-4 py-3">Base</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $curr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400"><?php echo e($curr->code); ?></td>
                            <td class="px-4 py-3"><?php echo e($curr->name); ?></td>
                            <td class="px-4 py-3 font-bold"><?php echo e($curr->symbol); ?></td>
                            <td class="px-4 py-3"><?php echo e($curr->decimal_places); ?></td>
                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($curr->is_base): ?>
                                    <span class="px-2 py-0.5 text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded">BASE</span>
                                <?php else: ?>
                                    <span class="text-slate-500">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <form action="<?php echo e(route('finance.currencies.destroy', $curr->id)); ?>" method="POST" onsubmit="return confirm('Hapus currency ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada Currency terdaftar.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('finance::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\Modules\Finance\resources\views\currencies\index.blade.php ENDPATH**/ ?>
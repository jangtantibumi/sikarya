<?php $__env->startSection('title', 'Tax Master - ERP Finance'); ?>
<?php $__env->startSection('page_heading', 'Tax Master (Pajak ERP)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Tambah Kode Pajak</h2>
            <form action="<?php echo e(route('finance.tax-masters.store')); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode Pajak</label>
                    <input type="text" name="code" required placeholder="PPN11, PPH23" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Deskripsi Pajak</label>
                    <input type="text" name="name" required placeholder="PPN 11% Keluaran / Masukan" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tarif (%)</label>
                        <input type="number" step="0.01" name="rate" required placeholder="11.00" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kalkulasi</label>
                        <select name="calculation_type" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="exclusive">Exclusive (Belum Termasuk)</option>
                            <option value="inclusive">Inclusive (Sudah Termasuk)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Jenis Pajak</label>
                    <select name="tax_type" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="vat">VAT / PPN</option>
                        <option value="withholding">Withholding Tax (PPh 23/21)</option>
                        <option value="sales_tax">Sales Tax</option>
                        <option value="service_tax">Service Tax</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Linked Account (CoA)</label>
                    <select name="chart_of_account_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Akun Pajak --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->code); ?> - <?php echo e($acc->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Simpan Tax Master
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Master Pajak</h2>
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-800/60 text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama Pajak</th>
                        <th class="px-4 py-3">Tarif</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Hitung</th>
                        <th class="px-4 py-3">Linked CoA</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400"><?php echo e($tax->code); ?></td>
                            <td class="px-4 py-3"><?php echo e($tax->name); ?></td>
                            <td class="px-4 py-3 font-bold text-white"><?php echo e($tax->rate); ?>%</td>
                            <td class="px-4 py-3 uppercase text-xs"><?php echo e($tax->tax_type); ?></td>
                            <td class="px-4 py-3 capitalize text-xs"><?php echo e($tax->calculation_type); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400"><?php echo e($tax->chartOfAccount->name ?? '-'); ?></td>
                            <td class="px-4 py-3">
                                <form action="<?php echo e(route('finance.tax-masters.destroy', $tax->id)); ?>" method="POST" onsubmit="return confirm('Hapus pajak ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada Tax Master.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('finance::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\Modules\Finance\resources\views\tax-masters\index.blade.php ENDPATH**/ ?>
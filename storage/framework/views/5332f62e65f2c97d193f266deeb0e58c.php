<?php $__env->startSection('title', 'Fiscal Years & Periods - ERP Finance'); ?>
<?php $__env->startSection('page_heading', 'Fiscal Year & Period Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h2 class="text-lg font-bold text-white mb-2">Buka Tahun Buku Baru</h2>
            <form action="<?php echo e(route('finance.fiscal-years.store')); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kode FY</label>
                    <input type="text" name="code" required placeholder="Contoh: FY2026" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Nama Tahun Buku</label>
                    <input type="text" name="name" required placeholder="Contoh: Tahun Buku 2026" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tgl Mulai</label>
                        <input type="date" name="start_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Tgl Selesai</label>
                        <input type="date" name="end_date" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <p class="text-xs text-slate-400">Sistem akan secara otomatis membuat 12 Periode Bulanan saat Tahun Buku dibuat.</p>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition shadow-md">
                    Generate Fiscal Year &amp; Periods
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl overflow-x-auto space-y-6">
            <h2 class="text-lg font-bold text-white">Daftar Tahun Buku &amp; Status Periode</h2>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $fiscalYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="bg-slate-900/80 border border-slate-700/60 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                        <div>
                            <span class="font-mono font-bold text-emerald-400 text-base"><?php echo e($fy->code); ?></span>
                            <span class="text-slate-300 ml-2 font-semibold"><?php echo e($fy->name); ?></span>
                            <span class="text-xs text-slate-500 ml-3">(<?php echo e($fy->start_date->format('d M Y')); ?> - <?php echo e($fy->end_date->format('d M Y')); ?>)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 text-xs rounded border <?php echo e($fy->is_closed ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'); ?>">
                                <?php echo e($fy->is_closed ? 'CLOSED' : 'OPEN'); ?>

                            </span>
                        </div>
                    </div>

                    <!-- Periode Bulanan -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 pt-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fy->periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="bg-slate-800/60 p-2 rounded-lg border border-slate-700/50 flex flex-col justify-between">
                                <div class="text-xs font-bold text-white">P<?php echo e($fp->period_number); ?>: <?php echo e($fp->name); ?></div>
                                <div class="text-[10px] text-slate-400 mt-1"><?php echo e(\Carbon\Carbon::parse($fp->start_date)->format('d M')); ?> - <?php echo e(\Carbon\Carbon::parse($fp->end_date)->format('d M Y')); ?></div>
                                <div class="mt-2">
                                    <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded <?php echo e($fp->status === 'open' ? 'bg-emerald-500/20 text-emerald-300' : ($fp->status === 'closed' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300')); ?>">
                                        <?php echo e($fp->status); ?>

                                    </span>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p class="text-center text-slate-500 py-6">Belum ada Fiscal Year terdaftar.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('finance::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\Modules\Finance\resources\views\fiscal-years\index.blade.php ENDPATH**/ ?>
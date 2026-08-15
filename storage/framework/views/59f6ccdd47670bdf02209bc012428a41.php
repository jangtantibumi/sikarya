<?php $__env->startSection('title', 'Loyalty Rules - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Loyalty Rules</h1>
        <p>Atur cara pelanggan mendapatkan poin dari setiap transaksi (Misal: Rp 10.000 = 1 Poin).</p>
    </div>
    <div>
        <a href="<?php echo e(route('crm.loyalties.create')); ?>" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Rule</a>
    </div>
</div>

<div class="table-wrapper">
    <table class="crm-table">
        <thead>
            <tr>
                <th>Nama Aturan</th>
                <th>Pembelanjaan</th>
                <th>Poin Didapat</th>
                <th>Status</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $loyalties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td style="font-weight: 600; color: #1e293b;">
                    <?php echo e($rule->rule_name); ?>

                </td>
                <td style="color: #64748b; font-weight: 500;">Rp <?php echo e(number_format($rule->spending_amount, 0, ',', '.')); ?></td>
                <td style="color: var(--crm-primary); font-weight: 700;">+<?php echo e(number_format($rule->points_awarded)); ?> Poin</td>
                <td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rule->is_active): ?>
                        <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Nonaktif</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td style="text-align: right;">
                    <a href="<?php echo e(route('crm.loyalties.edit', $rule->id)); ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                </td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada loyalty rule.</td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\loyalties\index.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Membership Tiers - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Membership Tiers</h1>
        <p>Kelola tingkatan membership customer beserta benefitnya.</p>
    </div>
    <div>
        <a href="<?php echo e(route('crm.memberships.create')); ?>" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Tier</a>
    </div>
</div>

<div class="table-wrapper">
    <table class="crm-table">
        <thead>
            <tr>
                <th>Nama Tier</th>
                <th>Min Poin</th>
                <th>Diskon</th>
                <th>Benefit</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $memberships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td>
                    <span class="badge badge-success"><?php echo e($tier->name); ?></span>
                </td>
                <td style="color: var(--crm-primary); font-weight: 700;"><?php echo e(number_format($tier->min_points)); ?> pts</td>
                <td style="color: var(--text-accent); font-weight: 700;"><?php echo e(number_format($tier->discount_percentage, 2)); ?>%</td>
                <td style="color: #64748b; font-size: 13px;"><?php echo e($tier->benefits); ?></td>
                <td style="text-align: right;">
                    <a href="<?php echo e(route('crm.memberships.edit', $tier->id)); ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                </td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada data tier membership.</td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\memberships\index.blade.php ENDPATH**/ ?>
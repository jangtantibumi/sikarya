<?php $__env->startSection('title', 'Voucher & Promo - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Voucher & Promo</h1>
        <p>Kelola kode voucher diskon untuk pelanggan.</p>
    </div>
    <div>
        <a href="<?php echo e(route('crm.vouchers.create')); ?>" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Voucher</a>
    </div>
</div>

<div class="table-wrapper">
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Voucher</th>
                    <th>Nilai Diskon</th>
                    <th>Masa Berlaku</th>
                    <th>Terpakai</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td style="font-family: monospace; font-size: 14px; font-weight: 700; color: var(--crm-primary);">
                        <?php echo e($voucher->code); ?>

                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?php echo e($voucher->name); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Min. Beli: Rp <?php echo e(number_format($voucher->min_purchase, 0, ',', '.')); ?></div>
                    </td>
                    <td style="color: var(--text-accent); font-weight: 700;">
                        <?php echo e($voucher->type === 'percentage' ? number_format($voucher->value, 0) . '%' : 'Rp ' . number_format($voucher->value, 0, ',', '.')); ?>

                    </td>
                    <td style="color: #475569; font-size: 13px;">
                        <?php echo e($voucher->valid_from ? $voucher->valid_from->format('d/m/Y') : '-'); ?> - 
                        <?php echo e($voucher->valid_until ? $voucher->valid_until->format('d/m/Y') : '-'); ?>

                    </td>
                    <td style="color: #475569; font-size: 13px; font-weight: 500;">
                        <?php echo e($voucher->uses_count); ?> / <?php echo e($voucher->max_uses ?? '∞'); ?>

                    </td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($voucher->is_active): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Nonaktif</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="<?php echo e(route('crm.vouchers.edit', $voucher->id)); ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada data voucher.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\vouchers\index.blade.php ENDPATH**/ ?>
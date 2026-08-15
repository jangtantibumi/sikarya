<?php $__env->startSection('title', 'Reservations - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Reservations</h1>
        <p>Manajemen pemesanan meja restoran</p>
    </div>
    <div>
        <a href="<?php echo e(route('crm.reservations.create')); ?>" class="btn btn-primary"><i class="ph ph-plus"></i> Buat Reservasi</a>
    </div>
</div>

<div class="table-wrapper">
    <div style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 12px; background: rgba(255,255,255,0.3);">
        <form action="<?php echo e(route('crm.reservations.index')); ?>" method="GET" style="display: flex; gap: 12px; width: 100%;">
            <input type="date" name="date" class="form-control" style="max-width: 200px;" value="<?php echo e(request('date')); ?>">
            <select name="status" class="form-control" style="max-width: 200px;">
                <option value="">Semua Status</option>
                <option value="Pending" <?php echo e(request('status') == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="Confirmed" <?php echo e(request('status') == 'Confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                <option value="Completed" <?php echo e(request('status') == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                <option value="Cancelled" <?php echo e(request('status') == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="ph ph-faders"></i> Filter</button>
        </form>
    </div>
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Tgl / Waktu</th>
                    <th>Customer</th>
                    <th>Pax / Meja</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?php echo e($res->reservation_date->format('d/m/Y')); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-clock"></i> <?php echo e(date('H:i', strtotime($res->reservation_time))); ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--crm-primary);"><?php echo e(optional($res->customer)->name ?? 'Unknown'); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-phone"></i> <?php echo e(optional($res->customer)->phone); ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?php echo e($res->pax); ?> Orang</div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($res->table_preference): ?>
                            <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><i class="ph ph-chair"></i> Meja: <?php echo e($res->table_preference); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($res->status == 'Pending'): ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php elseif($res->status == 'Confirmed'): ?>
                            <span class="badge badge-info">Confirmed</span>
                        <?php elseif($res->status == 'Completed'): ?>
                            <span class="badge badge-success">Completed</span>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="background: #e2e8f0; color: #475569;">Cancelled</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="<?php echo e(route('crm.reservations.edit', $res->id)); ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Edit</a>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada data reservasi.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservations->hasPages()): ?>
    <div style="padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5);">
        <div style="color: #64748b; font-size: 13px; font-weight: 500;">
            Showing <?php echo e($reservations->firstItem() ?? 0); ?> to <?php echo e($reservations->lastItem() ?? 0); ?> of <?php echo e($reservations->total()); ?> entries
        </div>
        <div style="display: flex; gap: 6px;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservations->onFirstPage()): ?>
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</span>
            <?php else: ?>
                <a href="<?php echo e($reservations->previousPageUrl()); ?>" class="btn btn-outline" style="padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservations->hasMorePages()): ?>
                <a href="<?php echo e($reservations->nextPageUrl()); ?>" class="btn btn-outline" style="padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></a>
            <?php else: ?>
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\reservations\index.blade.php ENDPATH**/ ?>
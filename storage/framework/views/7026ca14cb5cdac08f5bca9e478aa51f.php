<?php $__env->startSection('title', 'Feedback & Keluhan - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Feedback & Keluhan</h1>
        <p>Manajemen kepuasan pelanggan dan ulasan.</p>
    </div>
</div>

<div class="table-wrapper">
    <div style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 12px; background: rgba(255,255,255,0.3);">
        <form action="<?php echo e(route('crm.feedbacks.index')); ?>" method="GET" style="display: flex; gap: 12px; width: 100%;">
            <select name="status" class="form-control" style="max-width: 200px;">
                <option value="">Semua Status</option>
                <option value="Open" <?php echo e(request('status') == 'Open' ? 'selected' : ''); ?>>Open</option>
                <option value="In Progress" <?php echo e(request('status') == 'In Progress' ? 'selected' : ''); ?>>In Progress</option>
                <option value="Resolved" <?php echo e(request('status') == 'Resolved' ? 'selected' : ''); ?>>Resolved</option>
            </select>
            <select name="rating" class="form-control" style="max-width: 200px;">
                <option value="">Semua Rating (1-5)</option>
                <option value="5" <?php echo e(request('rating') == '5' ? 'selected' : ''); ?>>⭐⭐⭐⭐⭐ 5</option>
                <option value="4" <?php echo e(request('rating') == '4' ? 'selected' : ''); ?>>⭐⭐⭐⭐ 4</option>
                <option value="3" <?php echo e(request('rating') == '3' ? 'selected' : ''); ?>>⭐⭐⭐ 3</option>
                <option value="2" <?php echo e(request('rating') == '2' ? 'selected' : ''); ?>>⭐⭐ 2</option>
                <option value="1" <?php echo e(request('rating') == '1' ? 'selected' : ''); ?>>⭐ 1</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="ph ph-faders"></i> Filter</button>
        </form>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Kategori</th>
                    <th>Rating</th>
                    <th>Pesan</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?php echo e($fb->created_at->format('d/m/Y')); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><?php echo e($fb->created_at->diffForHumans()); ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--crm-primary);"><?php echo e(optional($fb->customer)->name ?? 'Unknown'); ?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><?php echo e(optional($fb->customer)->customer_code); ?></div>
                    </td>
                    <td>
                        <span style="background: rgba(12, 53, 39, 0.05); color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;"><?php echo e($fb->category); ?></span>
                    </td>
                    <td style="color: #f59e0b; font-size: 13px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=1; $i<=5; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php echo e($i <= $fb->rating ? '⭐' : '☆'); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </td>
                    <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #475569; font-size: 13px;" title="<?php echo e($fb->message); ?>">
                        <?php echo e($fb->message); ?>

                    </td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fb->status == 'Open'): ?>
                            <span class="badge badge-danger">Open</span>
                        <?php elseif($fb->status == 'In Progress'): ?>
                            <span class="badge badge-warning">In Progress</span>
                        <?php else: ?>
                            <span class="badge badge-success">Resolved</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                        <a href="<?php echo e(route('crm.feedbacks.show', $fb->id)); ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-radius: 8px;">Detail</a>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 48px; font-weight: 500;">Belum ada feedback atau komplain.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedbacks->hasPages()): ?>
    <div style="padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.5);">
        <div style="color: #64748b; font-size: 13px; font-weight: 500;">
            Showing <?php echo e($feedbacks->firstItem() ?? 0); ?> to <?php echo e($feedbacks->lastItem() ?? 0); ?> of <?php echo e($feedbacks->total()); ?> entries
        </div>
        <div style="display: flex; gap: 6px;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedbacks->onFirstPage()): ?>
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</span>
            <?php else: ?>
                <a href="<?php echo e($feedbacks->previousPageUrl()); ?>" class="btn btn-outline" style="padding: 6px 12px;"><i class="ph ph-caret-left"></i> Prev</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feedbacks->hasMorePages()): ?>
                <a href="<?php echo e($feedbacks->nextPageUrl()); ?>" class="btn btn-outline" style="padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></a>
            <?php else: ?>
                <span class="btn btn-ghost" style="opacity: 0.5; cursor: not-allowed; padding: 6px 12px;">Next <i class="ph ph-caret-right"></i></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\feedbacks\index.blade.php ENDPATH**/ ?>
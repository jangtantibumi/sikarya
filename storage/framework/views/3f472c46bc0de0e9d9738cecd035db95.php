<?php $__env->startSection('title', 'Finance — Fiscal Periods'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-3">

    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold text-white mb-0">
                <i class="bi bi-calendar3-range me-2 text-finance-accent"></i>
                Fiscal Periods
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($fiscalYear)): ?>
                    <small class="text-muted fs-6 ms-2">— <?php echo e($fiscalYear->name); ?></small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h1>
            <p class="text-muted small mb-0 mt-1">Manage and control accounting periods (open / close / lock)</p>
        </div>
        <a href="<?php echo e(route('finance.fiscal-years.index')); ?>" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Fiscal Years
        </a>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="card finance-card shadow-lg border-0">
        <div class="card-header finance-card-header d-flex align-items-center justify-content-between py-3">
            <span class="fw-semibold text-white">
                <i class="bi bi-table me-2"></i>Period List
            </span>
            <span class="badge bg-info fs-6"><?php echo e($periods->count()); ?> Periods</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0 finance-table">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Period</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="ps-4 text-muted"><?php echo e($period->period_number); ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        P<?php echo e(str_pad($period->period_number, 2, '0', STR_PAD_LEFT)); ?>

                                    </span>
                                </td>
                                <td class="fw-medium text-white"><?php echo e($period->name); ?></td>
                                <td class="text-muted small"><?php echo e($period->start_date->format('d M Y')); ?></td>
                                <td class="text-muted small"><?php echo e($period->end_date->format('d M Y')); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($period->status === 'open'): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success">
                                            <i class="bi bi-unlock me-1"></i>Open
                                        </span>
                                    <?php elseif($period->status === 'closed'): ?>
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning">
                                            <i class="bi bi-lock me-1"></i>Closed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger">
                                            <i class="bi bi-shield-lock me-1"></i>Locked
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($period->status === 'open'): ?>
                                        <form method="POST" action="<?php echo e(route('finance.fiscal-periods.update', $period->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="status" value="closed">
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                onclick="return confirm('Close period <?php echo e($period->name); ?>?')">
                                                <i class="bi bi-lock me-1"></i>Close
                                            </button>
                                        </form>
                                    <?php elseif($period->status === 'closed'): ?>
                                        <form method="POST" action="<?php echo e(route('finance.fiscal-periods.update', $period->id)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="status" value="open">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Re-open period <?php echo e($period->name); ?>?')">
                                                <i class="bi bi-unlock me-1"></i>Re-open
                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('finance.fiscal-periods.update', $period->id)); ?>" class="d-inline ms-1">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                            <input type="hidden" name="status" value="locked">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Lock period <?php echo e($period->name); ?>? This cannot be undone.')">
                                                <i class="bi bi-shield-lock me-1"></i>Lock
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="bi bi-shield-fill-lock"></i> Locked</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                    No periods found for this fiscal year.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('finance::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\Modules\Finance\resources\views\fiscal-periods\index.blade.php ENDPATH**/ ?>
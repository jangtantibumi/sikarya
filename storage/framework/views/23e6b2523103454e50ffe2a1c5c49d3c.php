<?php $__env->startSection('title', 'Birthday Reminder - Marketing CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Birthday Reminder & Automated Rewards</h1>
        <p>Identifikasi customer yang berulang tahun bulan ini dan berikan ucapan serta poin apresiasi otomatis.</p>
    </div>
    <a href="<?php echo e(route('crm.marketing.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="table-wrapper">
    <div style="padding: 20px; border-bottom: 1px solid var(--crm-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--crm-primary);">Customer Ulang Tahun Bulan Ini (<?php echo e(now()->format('F Y')); ?>)</h3>
        <span class="badge badge-success"><?php echo e(count($upcomingBirthdays)); ?> Customer</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Nomor HP / WhatsApp</th>
                    <th>Tanggal Ulang Tahun</th>
                    <th>Total Poin</th>
                    <th>Level Membership</th>
                    <th style="text-align: right;">Aksi Reward</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $upcomingBirthdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cust): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;"><?php echo e($cust->name); ?></div>
                            <div style="font-size: 12px; color: #64748b;"><?php echo e($cust->customer_code); ?></div>
                        </td>
                        <td><?php echo e($cust->phone); ?></td>
                        <td style="font-weight: 700; color: #d97706;"><i class="ph ph-cake"></i> <?php echo e($cust->birth_date ? $cust->birth_date->format('d F Y') : '-'); ?></td>
                        <td style="font-weight: 700; color: var(--crm-primary);"><?php echo e(number_format($cust->total_points)); ?> pts</td>
                        <td><span class="badge badge-success"><?php echo e($cust->membership_level); ?></span></td>
                        <td style="text-align: right;">
                            <form action="<?php echo e(route('crm.marketing.birthdays.reward', $cust->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px;"><i class="ph ph-gift"></i> Kirim Bonus +100 Pts</button>
                            </form>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 48px;">Tidak ada customer yang berulang tahun pada bulan ini.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\marketing\birthdays.blade.php ENDPATH**/ ?>
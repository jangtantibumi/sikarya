<?php $__env->startSection('title', 'Referral Program - Marketing CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Referral Program</h1>
        <p>Pantau performa program rujukan antar customer (Refer-a-Friend) dan klaim poin hadiah.</p>
    </div>
    <a href="<?php echo e(route('crm.marketing.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="table-wrapper">
    <div style="padding: 20px; border-bottom: 1px solid var(--crm-border); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--crm-primary);">Riwayat Transaksi Referral</h3>
        <span class="badge badge-success"><?php echo e($referrals->total()); ?> Rujukan Berhasil</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Customer Pemberi Referral (Referrer)</th>
                    <th>Customer Baru (Referee)</th>
                    <th>Reward Points</th>
                    <th>Status Reward</th>
                    <th>Tanggal Join</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ref): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;"><?php echo e($ref->referrer->name ?? 'Unknown'); ?></div>
                            <div style="font-size: 12px; color: #64748b;">Kode Ref: <span style="font-family: monospace; font-weight: 700;"><?php echo e($ref->referrer->referral_code ?? '-'); ?></span></div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;"><?php echo e($ref->referee->name ?? 'Unknown'); ?></div>
                            <div style="font-size: 12px; color: #64748b;"><?php echo e($ref->referee->phone ?? '-'); ?></div>
                        </td>
                        <td style="color: var(--text-accent); font-weight: 700;">+<?php echo e($ref->reward_points); ?> Pts</td>
                        <td><span class="badge badge-success"><?php echo e(ucfirst($ref->status)); ?></span></td>
                        <td><?php echo e($ref->created_at->format('d/m/Y H:i')); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 48px;">Belum ada data transaksi rujukan referral.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\marketing\referrals.blade.php ENDPATH**/ ?>
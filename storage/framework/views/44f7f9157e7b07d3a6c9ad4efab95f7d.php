<?php $__env->startSection('title', 'Coupon Engine - Marketing CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Coupon Engine</h1>
        <p>Generasi kupon transaksi tunggal dari voucher master dan validasi kode kupon.</p>
    </div>
    <a href="<?php echo e(route('crm.marketing.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form Generate Coupon -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Generate Kupon Baru</h3>

        <form action="<?php echo e(route('crm.marketing.coupons.generate')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Pilih Voucher Master</label>
                <select name="voucher_id" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                    <option value="">-- Pilih Voucher --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?> (Rp <?php echo e(number_format($v->value, 0, ',', '.')); ?>)</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="ph ph-ticket"></i> Generate Kode Kupon</button>
        </form>
    </div>

    <!-- Tabel Kupon Generated -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Daftar Kupon</h3>

        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Kode Kupon</th>
                        <th>Voucher Master</th>
                        <th>Nilai Diskon</th>
                        <th>Pemilik Customer</th>
                        <th>Status Penggunaan</th>
                        <th>Tgl Kadaluarsa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $coupons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cpn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: var(--crm-primary);"><?php echo e($cpn->coupon_code); ?></td>
                            <td><?php echo e($cpn->voucher->name ?? '-'); ?></td>
                            <td style="color: var(--text-accent); font-weight: 700;">Rp <?php echo e(number_format($cpn->discount_amount, 0, ',', '.')); ?></td>
                            <td><?php echo e($cpn->customer->name ?? 'Publik / Terbuka'); ?></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cpn->is_used): ?>
                                    <span class="badge badge-danger">Sudah Dipakai</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Belum Dipakai</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: #64748b;"><?php echo e($cpn->expires_at ? $cpn->expires_at->format('d/m/Y') : 'Tanpa Batas'); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada kupon yang di-generate.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\marketing\coupons.blade.php ENDPATH**/ ?>
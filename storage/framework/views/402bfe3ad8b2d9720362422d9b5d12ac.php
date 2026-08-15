<?php $__env->startSection('title', 'Promotion Engine - Marketing CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Promotion Engine</h1>
        <p>Atur aturan promosi diskon, batas transaksi minimum, dan periode berlaku promo.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?php echo e(route('crm.marketing.coupons')); ?>" class="btn btn-outline"><i class="ph ph-ticket"></i> Coupon Engine</a>
        <a href="<?php echo e(route('crm.marketing.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Form Buat Promo -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Buat Promosi Baru</h3>

        <form action="<?php echo e(route('crm.marketing.promotions.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Nama Promosi</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Promo Merdeka 45%" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Kode Promo</label>
                <input type="text" name="promo_code" class="form-control" placeholder="MERDEKA45" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border); font-family: monospace; text-transform: uppercase;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Tipe Diskon</label>
                    <select name="discount_type" class="form-control" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Nilai Diskon</label>
                    <input type="number" name="discount_value" class="form-control" placeholder="20 atau 50000" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Minimum Transaksi (Rp)</label>
                <input type="number" name="min_spend" class="form-control" value="0" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Berlaku Dari</label>
                    <input type="date" name="valid_from" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Berlaku Sampai</label>
                    <input type="date" name="valid_until" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--crm-border);">
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" name="is_active" value="1" id="is_active" checked>
                <label for="is_active" style="font-size: 13px; color: #334155; font-weight: 500;">Aktifkan Promo Ini</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="ph ph-plus"></i> Simpan Aturan Promosi</button>
        </form>
    </div>

    <!-- Tabel Daftar Promo -->
    <div style="background: var(--crm-bg-card); border: 1px solid var(--crm-border); border-radius: var(--crm-radius); padding: 24px; box-shadow: var(--crm-shadow);">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--crm-primary); margin-bottom: 20px; border-bottom: 1px solid var(--crm-border); padding-bottom: 12px;">Daftar Promosi Aktif</h3>

        <div style="overflow-x: auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Kode Promo</th>
                        <th>Judul Promo</th>
                        <th>Nilai Diskon</th>
                        <th>Min Spend</th>
                        <th>Periode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: var(--crm-primary);"><?php echo e($promo->promo_code); ?></td>
                            <td><strong style="color: #1e293b;"><?php echo e($promo->title); ?></strong></td>
                            <td style="color: var(--text-accent); font-weight: 700;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promo->discount_type === 'percentage'): ?>
                                    <?php echo e($promo->discount_value); ?>%
                                <?php else: ?>
                                    Rp <?php echo e(number_format($promo->discount_value, 0, ',', '.')); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>Rp <?php echo e(number_format($promo->min_spend, 0, ',', '.')); ?></td>
                            <td style="font-size: 12px; color: #64748b;">
                                <?php echo e($promo->valid_from ? $promo->valid_from->format('d/m/Y') : 'Tanpa Batas'); ?>

                                -
                                <?php echo e($promo->valid_until ? $promo->valid_until->format('d/m/Y') : 'Tanpa Batas'); ?>

                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promo->is_active): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Nonaktif</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 40px;">Belum ada aturan promosi yang dibuat.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\marketing\promotions.blade.php ENDPATH**/ ?>
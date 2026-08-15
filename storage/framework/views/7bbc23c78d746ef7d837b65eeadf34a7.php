<?php $__env->startSection('title', 'Tambah Voucher - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Voucher</h1>
        <p>Buat kode voucher diskon untuk pelanggan.</p>
    </div>
</div>

<div class="crm-card" style="max-width: 800px;">
    <form action="<?php echo e(route('crm.vouchers.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Kode Voucher</label>
                <input type="text" name="code" class="form-control" required value="<?php echo e(old('code')); ?>" placeholder="Misal: PROMO2024" style="font-family: monospace; text-transform: uppercase;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nama Voucher</label>
                <input type="text" name="name" class="form-control" required value="<?php echo e(old('name')); ?>">
            </div>
            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?php echo e(old('description')); ?></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tipe Diskon</label>
                <select name="type" class="form-control" required>
                    <option value="percentage">Persentase (%)</option>
                    <option value="fixed">Nominal Tetap (Rp)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nilai Diskon</label>
                <input type="number" step="0.01" name="value" class="form-control" required min="0" value="<?php echo e(old('value')); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Minimal Pembelanjaan (Rp)</label>
                <input type="number" name="min_purchase" class="form-control" required min="0" value="<?php echo e(old('min_purchase', 0)); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Maksimal Kuota (Opsional)</label>
                <input type="number" name="max_uses" class="form-control" min="1" value="<?php echo e(old('max_uses')); ?>" placeholder="Kosongkan jika unlimited">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Berlaku Dari (Opsional)</label>
                <input type="date" name="valid_from" class="form-control" value="<?php echo e(old('valid_from')); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Berlaku Sampai (Opsional)</label>
                <input type="date" name="valid_until" class="form-control" value="<?php echo e(old('valid_until')); ?>">
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" checked style="width: 18px; height: 18px; accent-color: var(--crm-primary);">
                    Voucher Aktif
                </label>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="<?php echo e(route('crm.vouchers.index')); ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\vouchers\create.blade.php ENDPATH**/ ?>
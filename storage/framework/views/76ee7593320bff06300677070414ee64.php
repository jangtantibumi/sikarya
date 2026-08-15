<?php $__env->startSection('title', 'Edit Voucher - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Voucher</h1>
        <p>Ubah kode voucher diskon untuk pelanggan.</p>
    </div>
    <a href="<?php echo e(route('crm.vouchers.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="crm-card" style="max-width: 800px;">
    <form action="<?php echo e(route('crm.vouchers.update', $voucher->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Kode Voucher</label>
                <input type="text" name="code" class="form-control" required value="<?php echo e(old('code', $voucher->code)); ?>" style="font-family: monospace; text-transform: uppercase;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nama Voucher</label>
                <input type="text" name="name" class="form-control" required value="<?php echo e(old('name', $voucher->name)); ?>">
            </div>
            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $voucher->description)); ?></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tipe Diskon</label>
                <select name="type" class="form-control" required>
                    <option value="percentage" <?php echo e($voucher->type === 'percentage' ? 'selected' : ''); ?>>Persentase (%)</option>
                    <option value="fixed" <?php echo e($voucher->type === 'fixed' ? 'selected' : ''); ?>>Nominal Tetap (Rp)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nilai Diskon</label>
                <input type="number" step="0.01" name="value" class="form-control" required min="0" value="<?php echo e(old('value', (float)$voucher->value)); ?>">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Minimal Pembelanjaan (Rp)</label>
                <input type="number" name="min_purchase" class="form-control" required min="0" value="<?php echo e(old('min_purchase', (float)$voucher->min_purchase)); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Maksimal Kuota (Opsional)</label>
                <input type="number" name="max_uses" class="form-control" min="1" value="<?php echo e(old('max_uses', $voucher->max_uses)); ?>" placeholder="Kosongkan jika unlimited">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Berlaku Dari (Opsional)</label>
                <input type="date" name="valid_from" class="form-control" value="<?php echo e(old('valid_from', $voucher->valid_from ? $voucher->valid_from->format('Y-m-d') : '')); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Berlaku Sampai (Opsional)</label>
                <input type="date" name="valid_until" class="form-control" value="<?php echo e(old('valid_until', $voucher->valid_until ? $voucher->valid_until->format('Y-m-d') : '')); ?>">
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?php echo e($voucher->is_active ? 'checked' : ''); ?> style="width: 18px; height: 18px; accent-color: var(--crm-primary);">
                    Voucher Aktif
                </label>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>

<form action="<?php echo e(route('crm.vouchers.destroy', $voucher->id)); ?>" method="POST" style="margin-top: 24px;" onsubmit="return confirm('Yakin ingin menghapus voucher ini?');">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
    <button type="submit" class="btn btn-danger"><i class="ph ph-trash"></i> Hapus Voucher</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\vouchers\edit.blade.php ENDPATH**/ ?>
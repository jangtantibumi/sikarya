<?php $__env->startSection('title', 'Tambah Loyalty Rule - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Loyalty Rule</h1>
        <p>Tentukan parameter perhitungan poin pelanggan.</p>
    </div>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="<?php echo e(route('crm.loyalties.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label class="form-label">Nama Aturan (Misal: Reguler Poin 10rb)</label>
            <input type="text" name="rule_name" class="form-control" required value="<?php echo e(old('rule_name')); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Minimal Pembelanjaan (Rp)</label>
            <input type="number" name="spending_amount" class="form-control" required min="0" value="<?php echo e(old('spending_amount')); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Poin yang Didapat</label>
            <input type="number" name="points_awarded" class="form-control" required min="0" value="<?php echo e(old('points_awarded')); ?>">
        </div>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #1e293b; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" checked style="width: 18px; height: 18px; accent-color: var(--crm-primary);">
                Aturan Aktif
            </label>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="<?php echo e(route('crm.loyalties.index')); ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\loyalties\create.blade.php ENDPATH**/ ?>
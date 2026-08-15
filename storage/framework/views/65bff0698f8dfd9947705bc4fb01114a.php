<?php $__env->startSection('title', 'Tambah Membership - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Membership Tier</h1>
        <p>Buat tingkatan membership baru.</p>
    </div>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="<?php echo e(route('crm.memberships.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label class="form-label">Nama Tier (Misal: Gold)</label>
            <input type="text" name="name" class="form-control" required value="<?php echo e(old('name')); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Minimal Poin</label>
            <input type="number" name="min_points" class="form-control" required min="0" value="<?php echo e(old('min_points', 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Diskon (%)</label>
            <input type="number" step="0.01" name="discount_percentage" class="form-control" required min="0" max="100" value="<?php echo e(old('discount_percentage', 0)); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Benefit (Opsional)</label>
            <textarea name="benefits" class="form-control" rows="3"><?php echo e(old('benefits')); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Warna Badge</label>
            <select name="color_badge" class="form-control">
                <option value="guest">Guest (Abu-abu)</option>
                <option value="silver">Silver (Perak)</option>
                <option value="gold">Gold (Emas)</option>
                <option value="platinum">Platinum (Ungu)</option>
                <option value="diamond">Diamond (Biru)</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px;">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="<?php echo e(route('crm.memberships.index')); ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\memberships\create.blade.php ENDPATH**/ ?>
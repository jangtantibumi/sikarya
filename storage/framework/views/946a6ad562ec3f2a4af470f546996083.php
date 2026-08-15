<?php $__env->startSection('title', 'Edit Reservasi - CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Reservasi</h1>
        <p>Ubah detail atau status reservasi.</p>
    </div>
    <a href="<?php echo e(route('crm.reservations.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="crm-card" style="max-width: 600px;">
    <form action="<?php echo e(route('crm.reservations.update', $reservation->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        
        <div class="form-group">
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-control" required>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($customer->id); ?>" <?php echo e(old('customer_id', $reservation->customer_id) == $customer->id ? 'selected' : ''); ?>>
                        <?php echo e($customer->name); ?> (<?php echo e($customer->phone); ?>)
                    </option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tanggal Reservasi</label>
                <input type="date" name="reservation_date" class="form-control" required value="<?php echo e(old('reservation_date', $reservation->reservation_date->format('Y-m-d'))); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Jam / Waktu</label>
                <input type="time" name="reservation_time" class="form-control" required value="<?php echo e(old('reservation_time', date('H:i', strtotime($reservation->reservation_time)))); ?>">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Jumlah Pax (Orang)</label>
                <input type="number" name="pax" class="form-control" required min="1" value="<?php echo e(old('pax', $reservation->pax)); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Preferensi Meja (Opsional)</label>
                <input type="text" name="table_preference" class="form-control" value="<?php echo e(old('table_preference', $reservation->table_preference)); ?>" placeholder="Contoh: Dekat jendela">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Permintaan Khusus</label>
            <textarea name="special_requests" class="form-control" rows="3"><?php echo e(old('special_requests', $reservation->special_requests)); ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="Pending" <?php echo e($reservation->status == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="Confirmed" <?php echo e($reservation->status == 'Confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                <option value="Completed" <?php echo e($reservation->status == 'Completed' ? 'selected' : ''); ?>>Completed</option>
                <option value="Cancelled" <?php echo e($reservation->status == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>

<form action="<?php echo e(route('crm.reservations.destroy', $reservation->id)); ?>" method="POST" style="margin-top: 24px;" onsubmit="return confirm('Yakin ingin menghapus reservasi ini?');">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
    <button type="submit" class="btn btn-danger"><i class="ph ph-trash"></i> Hapus Reservasi</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\reservations\edit.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Tambah Customer - CRM Portal'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-title-group">
        <h1>Tambah Customer Baru</h1>
        <p>Masukkan data customer. Kode customer akan dibuat otomatis (CUST-YYYY-XXXX).</p>
    </div>
    <a href="<?php echo e(route('crm.customers.index')); ?>" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
</div>

<div class="crm-card" style="max-width: 800px;">
    <form action="<?php echo e(route('crm.customers.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required placeholder="Contoh: Budi Santoso">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nomor WhatsApp / HP *</label>
                <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>" required placeholder="Contoh: 08123456789">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Email (Opsional)</label>
                <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" placeholder="Contoh: budi@email.com">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Gender (Opsional)</label>
                <select name="gender" class="form-control">
                    <option value="">Pilih Gender</option>
                    <option value="male" <?php echo e(old('gender') == 'male' ? 'selected' : ''); ?>>Laki-laki</option>
                    <option value="female" <?php echo e(old('gender') == 'female' ? 'selected' : ''); ?>>Perempuan</option>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tanggal Lahir (Opsional)</label>
                <input type="date" name="birth_date" class="form-control" value="<?php echo e(old('birth_date')); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['birth_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Alamat Lengkap (Opsional)</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Alamat pengiriman / domisili"><?php echo e(old('address')); ?></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan khusus, preferensi menu, alergi, dsb."><?php echo e(old('notes')); ?></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div style="color: var(--crm-danger); font-size: 12px; margin-top: 6px;"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.05);">
            <a href="<?php echo e(route('crm.customers.index')); ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Customer</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('crm.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\customers\create.blade.php ENDPATH**/ ?>
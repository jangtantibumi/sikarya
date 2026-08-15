<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Poin - Customer Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root { --erp-primary: #0C3527; --erp-bg: #F8FAFC; }
        body { font-family: 'Inter', sans-serif; background: var(--erp-bg); color: #1e293b; padding-bottom: 80px; }
        .portal-header { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; padding: 16px 20px; display: flex; align-items: center; gap: 12px; }
        .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 600px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-top: 1px solid rgba(0,0,0,0.08); display: flex; justify-content: space-around; padding: 10px 0; z-index: 200; }
        .bottom-nav-item { display: flex; flex-direction: column; align-items: center; color: #64748b; text-decoration: none !important; font-size: 11px; font-weight: 600; }
        .bottom-nav-item i { font-size: 20px; margin-bottom: 2px; }
        .bottom-nav-item.active { color: var(--erp-primary); }
        .history-card { background: white; border-radius: 16px; padding: 16px 20px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,0.03); box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>

<div style="max-width: 600px; margin: 0 auto; background: var(--erp-bg); min-height: 100vh;">
    <div class="portal-header">
        <a href="<?php echo e(route('portal.dashboard')); ?>" style="color: #1e293b; font-size: 20px; text-decoration: none;"><i class="ph ph-arrow-left"></i></a>
        <h5 style="font-weight: 800; margin: 0; color: #1e293b;">Riwayat Loyalty Point</h5>
    </div>

    <div style="padding: 16px 20px;">
        <div style="background: var(--erp-primary); color: white; border-radius: 16px; padding: 20px; margin-bottom: 20px; text-align: center;">
            <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8; font-weight: 600;">Total Poin Terkumpul</div>
            <div style="font-size: 36px; font-weight: 800; margin-top: 4px;"><?php echo e(number_format($customer->total_points)); ?> pts</div>
        </div>

        <h6 style="font-weight: 800; font-size: 15px; margin-bottom: 16px; color: #1e293b;">Catatan Masuk & Keluar Poin</h6>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="history-card">
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: #1e293b;"><?php echo e($ph->description); ?></div>
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;"><?php echo e($ph->created_at->format('d M Y, H:i')); ?></div>
                </div>
                <div style="font-weight: 800; font-size: 16px; <?php echo e($ph->points > 0 ? 'color: var(--text-accent);' : 'color: #dc2626;'); ?>">
                    <?php echo e($ph->points > 0 ? '+' : ''); ?><?php echo e($ph->points); ?> pts
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div style="text-align: center; padding: 40px; color: #94a3b8; font-size: 14px;">Belum ada riwayat poin.</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div style="margin-top: 16px;">
            <?php echo e($histories->links()); ?>

        </div>
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="<?php echo e(route('portal.dashboard')); ?>" class="bottom-nav-item"><i class="ph-fill ph-house"></i><span>Home</span></a>
        <a href="<?php echo e(route('portal.vouchers')); ?>" class="bottom-nav-item"><i class="ph-fill ph-ticket"></i><span>Voucher</span></a>
        <a href="<?php echo e(route('portal.loyalty')); ?>" class="bottom-nav-item active"><i class="ph-fill ph-coins"></i><span>Poin</span></a>
        <a href="<?php echo e(route('portal.invoices')); ?>" class="bottom-nav-item"><i class="ph-fill ph-receipt"></i><span>Transaksi</span></a>
        <a href="<?php echo e(route('portal.profile')); ?>" class="bottom-nav-item"><i class="ph-fill ph-user"></i><span>Profil</span></a>
    </div>
</div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\portal\loyalty.blade.php ENDPATH**/ ?>
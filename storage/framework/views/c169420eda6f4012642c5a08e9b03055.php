<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($invitation->title); ?></title>
</head>
<body style="margin:0;background:#0b0d10;color:#f4f5f7;font-family:Arial,sans-serif;">
    <div style="max-width:620px;margin:0 auto;padding:32px 20px;">
        <div style="background:#15181e;border:1px solid #30343d;border-radius:18px;padding:28px;">
            <div style="color:#f2c94c;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Suba-Arch Alumni Network</div>
            <h1 style="font-size:25px;line-height:1.25;margin:14px 0 8px;"><?php echo e($invitation->title); ?></h1>
            <p style="color:#b6bbc5;line-height:1.7;margin:0 0 22px;">Halo <?php echo e($alumni->name); ?>,</p>
            <p style="color:#eef0f4;line-height:1.7;white-space:pre-line;"><?php echo e($invitation->message); ?></p>
            <div style="margin:24px 0;padding:18px;background:#0f1115;border-radius:12px;border-left:3px solid #f2c94c;">
                <div style="margin-bottom:8px;"><strong>Waktu:</strong> <?php echo e($invitation->event_at?->timezone(config('app.timezone'))->format('d M Y, H:i')); ?> WIB</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invitation->location): ?>
                    <div><strong>Lokasi:</strong> <?php echo e($invitation->location); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invitation->registration_url): ?>
                <a href="<?php echo e($invitation->registration_url); ?>" style="display:inline-block;background:#f2c94c;color:#111318;text-decoration:none;font-weight:700;padding:12px 18px;border-radius:10px;">Konfirmasi Kehadiran</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p style="color:#8e949f;font-size:12px;line-height:1.6;margin:28px 0 0;">Undangan ini dikirim ke alumni yang mengaktifkan penerimaan undangan event di portal Suba-Arch.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\emails\alumni-event-invitation.blade.php ENDPATH**/ ?>
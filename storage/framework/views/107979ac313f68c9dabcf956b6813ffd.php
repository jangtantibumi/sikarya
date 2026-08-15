<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - <?php echo e($user->name); ?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: var(--text-accent); }
        .header p { margin: 5px 0 0 0; color: #666; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 5px; }
        .info-table .label { font-weight: bold; width: 150px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .details-table th { background-color: #f9fafb; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #ecfdf5; }
        .footer { margin-top: 50px; text-align: right; }
        .signature { margin-top: 80px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SLIP GAJI</h1>
        <p>Northstar Group</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Karyawan</td>
            <td>: <?php echo e($user->name); ?></td>
            <td class="label">Periode</td>
            <td>: <?php echo e(\Carbon\Carbon::parse($payroll->period_start)->format('d M Y')); ?> - <?php echo e(\Carbon\Carbon::parse($payroll->period_end)->format('d M Y')); ?></td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td>: <?php echo e($user->employee_code); ?></td>
            <td class="label">Tipe Pekerjaan</td>
            <td>: <?php echo e($user->employment_type); ?></td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: <?php echo e($user->job_title); ?></td>
            <td class="label">Divisi</td>
            <td>: <?php echo e($user->divisionLabel()); ?></td>
        </tr>
    </table>

    <table class="details-table">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th style="text-align: right;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gaji Pokok</td>
                <td style="text-align: right;"><?php echo e(number_format($payroll->base_amount, 0, ',', '.')); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $payroll->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($item->description); ?> (<?php echo e(ucfirst($item->type)); ?>)</td>
                <td style="text-align: right;"><?php echo e($item->type === 'deduction' ? '-' : ''); ?><?php echo e(number_format($item->amount, 0, ',', '.')); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr class="total-row">
                <td>Total Gaji Bersih (Take Home Pay)</td>
                <td style="text-align: right;">Rp <?php echo e(number_format($payroll->net_amount, 0, ',', '.')); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: <?php echo e(\Carbon\Carbon::now()->format('d M Y H:i')); ?></p>
        <p>Mengetahui,</p>
        <div class="signature">
            <p>HR Department</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\pdf\salary-slip.blade.php ENDPATH**/ ?>
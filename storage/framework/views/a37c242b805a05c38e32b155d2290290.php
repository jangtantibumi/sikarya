<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Data Customer - CRM</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; }
        .header h1 { margin: 0 0 5px; color: #111; font-size: 20px; }
        .header p { margin: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; color: #111; text-transform: uppercase; font-size: 11px; }
        tr:nth-child(even) { background-color: #fbfbfb; }
        .footer { text-align: right; font-size: 10px; color: #777; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer;">Print PDF</button>
    </div>

    <div class="header">
        <h1>Laporan Data Customer CRM</h1>
        <p>Tanggal Dicetak: <?php echo e(now()->format('d F Y, H:i')); ?></p>
        <p>Total Customer: <?php echo e($customers->count()); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Customer</th>
                <th>Nama Lengkap</th>
                <th>Kontak (HP/Email)</th>
                <th>Membership</th>
                <th>Total Point</th>
                <th>Total Spending</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cust): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($cust->customer_code); ?></td>
                    <td>
                        <strong><?php echo e($cust->name); ?></strong><br>
                        <span style="font-size: 10px; color: #666;"><?php echo e($cust->gender ? ucfirst($cust->gender) : '-'); ?> • <?php echo e($cust->birth_date ? $cust->birth_date->format('d/m/Y') : '-'); ?></span>
                    </td>
                    <td>
                        <?php echo e($cust->phone); ?><br>
                        <span style="font-size: 10px; color: #666;"><?php echo e($cust->email ?? '-'); ?></span>
                    </td>
                    <td><?php echo e($cust->membership_level); ?></td>
                    <td><?php echo e(number_format($cust->total_points)); ?> pts</td>
                    <td>Rp <?php echo e(number_format($cust->total_spending, 0, ',', '.')); ?></td>
                    <td><?php echo e($cust->is_active ? 'Aktif' : 'Nonaktif'); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr><td colspan="8" style="text-align: center;">Tidak ada data customer yang sesuai dengan filter.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>SubaArch ERP - Modul CRM & Customer Portal</p>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\crm\customers\export_pdf.blade.php ENDPATH**/ ?>
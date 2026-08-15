<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Kerja - <?php echo e($override_name ?? $user->name); ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 16px; color: #000; line-height: 1.6; padding: 40px; }
        .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 20px; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 2px; }
        .header p { margin: 5px 0 0 0; font-size: 14px; }
        .title { text-align: center; font-weight: bold; font-size: 20px; text-decoration: underline; margin-bottom: 30px; }
        .content { margin-bottom: 40px; text-align: justify; }
        .data-table { margin-bottom: 20px; margin-left: 40px; }
        .data-table td { padding: 5px; }
        .data-table .label { width: 200px; }
        .footer { margin-top: 60px; text-align: right; }
        .signature { margin-top: 100px; font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e(strtoupper($company_name)); ?></h1>
        <p>Jakarta Selatan, Indonesia</p>
    </div>

    <div class="title">
        SURAT KETERANGAN KERJA (PAKLARING)
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, selaku perwakilan dari <strong><?php echo e($company_name); ?></strong>, menerangkan dengan sesungguhnya bahwa:</p>

        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td>: <strong><?php echo e($override_name ?? $user->name); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Karyawan</td>
                <td>: <?php echo e($override_code ?? $user->employee_code); ?></td>
            </tr>
            <tr>
                <td class="label">Jabatan Terakhir</td>
                <td>: <?php echo e($override_position ?? $user->job_title); ?></td>
            </tr>
            <tr>
                <td class="label">Divisi</td>
                <td>: <?php echo e($override_division ?? $user->divisionLabel()); ?></td>
            </tr>
        </table>

        <p>Telah bekerja di <strong><?php echo e($company_name); ?></strong> terhitung mulai tanggal <strong><?php echo e($join_date); ?></strong> sampai dengan tanggal <strong><?php echo e($resign_date); ?></strong>.</p>

        <p><?php echo e($content); ?></p>

        <p>Demikian Surat Keterangan Kerja ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="footer">
        <p>Jakarta, <?php echo e($today_date); ?></p>
        <p>Hormat kami,</p>
        <div class="signature">
            <p><?php echo e($hr_name); ?></p>
        </div>
        <p style="margin: 0;"><?php echo e($company_name); ?></p>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\pdf\paklaring.blade.php ENDPATH**/ ?>
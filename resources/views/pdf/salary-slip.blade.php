<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $user->name }}</title>
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
            <td>: {{ $user->name }}</td>
            <td class="label">Periode</td>
            <td>: {{ \Carbon\Carbon::parse($payroll->period_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($payroll->period_end)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td>: {{ $user->employee_code }}</td>
            <td class="label">Tipe Pekerjaan</td>
            <td>: {{ $user->employment_type }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $user->job_title }}</td>
            <td class="label">Divisi</td>
            <td>: {{ $user->divisionLabel() }}</td>
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
                <td style="text-align: right;">{{ number_format($payroll->base_amount, 0, ',', '.') }}</td>
            </tr>
            @foreach($payroll->items as $item)
            <tr>
                <td>{{ $item->description }} ({{ ucfirst($item->type) }})</td>
                <td style="text-align: right;">{{ $item->type === 'deduction' ? '-' : '' }}{{ number_format($item->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total Gaji Bersih (Take Home Pay)</td>
                <td style="text-align: right;">Rp {{ number_format($payroll->net_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>
        <p>Mengetahui,</p>
        <div class="signature">
            <p>HR Department</p>
        </div>
    </div>
</body>
</html>

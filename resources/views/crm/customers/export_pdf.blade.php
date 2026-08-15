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
        <p>Tanggal Dicetak: {{ now()->format('d F Y, H:i') }}</p>
        <p>Total Customer: {{ $customers->count() }}</p>
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
            @forelse($customers as $i => $cust)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $cust->customer_code }}</td>
                    <td>
                        <strong>{{ $cust->name }}</strong><br>
                        <span style="font-size: 10px; color: #666;">{{ $cust->gender ? ucfirst($cust->gender) : '-' }} • {{ $cust->birth_date ? $cust->birth_date->format('d/m/Y') : '-' }}</span>
                    </td>
                    <td>
                        {{ $cust->phone }}<br>
                        <span style="font-size: 10px; color: #666;">{{ $cust->email ?? '-' }}</span>
                    </td>
                    <td>{{ $cust->membership_level }}</td>
                    <td>{{ number_format($cust->total_points) }} pts</td>
                    <td>Rp {{ number_format($cust->total_spending, 0, ',', '.') }}</td>
                    <td>{{ $cust->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align: center;">Tidak ada data customer yang sesuai dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>SubaArch ERP - Modul CRM & Customer Portal</p>
    </div>
</body>
</html>

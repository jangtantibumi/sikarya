<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Digital Membership Card - {{ $customer->name }}</title>
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
        
        .digital-card {
            background: linear-gradient(135deg, #0C3527 0%, #17523f 100%);
            color: white;
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 20px 40px rgba(12, 53, 39, 0.25);
            margin: 24px 20px;
            position: relative;
            overflow: hidden;
        }

        .qr-box {
            background: white;
            padding: 16px;
            border-radius: 20px;
            width: 180px;
            margin: 24px auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }

        .qr-box img { width: 100%; height: auto; border-radius: 8px; }
    </style>
</head>
<body>

<div style="max-width: 600px; margin: 0 auto; background: var(--erp-bg); min-height: 100vh;">
    <div class="portal-header">
        <a href="{{ route('portal.dashboard') }}" style="color: #1e293b; font-size: 20px; text-decoration: none;"><i class="ph ph-arrow-left"></i></a>
        <h5 style="font-weight: 800; margin: 0; color: #1e293b;">Kartu Membership Digital</h5>
    </div>

    <div class="digital-card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 16px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; color: #D9EFE9;">ERP CRM VIP MEMBER</div>
                <div style="font-size: 22px; font-weight: 800; margin-top: 2px;">{{ $customer->name }}</div>
            </div>
            <span style="background: #D9EFE9; color: var(--text-accent); font-weight: 800; font-size: 12px; padding: 6px 14px; border-radius: 100px;">
                {{ $customer->membership_level }}
            </span>
        </div>

        <div class="qr-box">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ $customer->customer_code }}" alt="QR Member">
            <div style="font-family: monospace; font-size: 14px; font-weight: 800; color: var(--text-accent); margin-top: 8px;">{{ $customer->customer_code }}</div>
        </div>

        <div style="text-align: center; font-size: 12px; color: rgba(255,255,255,0.8); margin-top: 16px;">
            Tunjukkan QR Code ini ke kasir saat bertransaksi untuk mendapatkan poin & diskon member.
        </div>
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="{{ route('portal.dashboard') }}" class="bottom-nav-item"><i class="ph-fill ph-house"></i><span>Home</span></a>
        <a href="{{ route('portal.vouchers') }}" class="bottom-nav-item"><i class="ph-fill ph-ticket"></i><span>Voucher</span></a>
        <a href="{{ route('portal.loyalty') }}" class="bottom-nav-item"><i class="ph-fill ph-coins"></i><span>Poin</span></a>
        <a href="{{ route('portal.invoices') }}" class="bottom-nav-item"><i class="ph-fill ph-receipt"></i><span>Transaksi</span></a>
        <a href="{{ route('portal.profile') }}" class="bottom-nav-item"><i class="ph-fill ph-user"></i><span>Profil</span></a>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Profil - Customer Portal</title>
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
        .card-profile { background: white; border-radius: 20px; padding: 24px; margin: 20px; border: 1px solid rgba(0,0,0,0.03); box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1px solid rgba(0,0,0,0.1); background: #f8fafc; margin-bottom: 16px; }
        .btn-primary { background: var(--erp-primary); border: none; border-radius: 12px; padding: 12px 24px; font-weight: 700; width: 100%; }
    </style>
</head>
<body>

<div style="max-width: 600px; margin: 0 auto; background: var(--erp-bg); min-height: 100vh;">
    <div class="portal-header">
        <a href="{{ route('portal.dashboard') }}" style="color: #1e293b; font-size: 20px; text-decoration: none;"><i class="ph ph-arrow-left"></i></a>
        <h5 style="font-weight: 800; margin: 0; color: #1e293b;">Edit Profil</h5>
    </div>

    @if(session('success'))
        <div style="margin: 20px 20px 0;">
            <div class="alert" style="background: rgba(12, 53, 39, 0.1); color: var(--text-accent); border-radius: 12px; font-weight: 600; font-size: 14px;">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="card-profile">
        <form action="{{ route('portal.profile.update') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Kode Customer</label>
                <input type="text" class="form-control" value="{{ $customer->customer_code }}" disabled style="opacity: 0.7;">
            </div>

            <div class="form-group mb-3">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
            </div>

            <div class="form-group mb-3">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Nomor HP (Autentikasi Login)</label>
                <input type="text" class="form-control" value="{{ $customer->phone }}" disabled style="opacity: 0.7;">
            </div>

            <div class="form-group mb-3">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option value="male" {{ $customer->gender === 'male' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ $customer->gender === 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-6">
                    <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Tgl Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '' }}">
                </div>
            </div>

            <div class="form-group mb-4">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Alamat Tempat Tinggal</label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
        </form>
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="{{ route('portal.dashboard') }}" class="bottom-nav-item"><i class="ph-fill ph-house"></i><span>Home</span></a>
        <a href="{{ route('portal.vouchers') }}" class="bottom-nav-item"><i class="ph-fill ph-ticket"></i><span>Voucher</span></a>
        <a href="{{ route('portal.loyalty') }}" class="bottom-nav-item"><i class="ph-fill ph-coins"></i><span>Poin</span></a>
        <a href="{{ route('portal.invoices') }}" class="bottom-nav-item"><i class="ph-fill ph-receipt"></i><span>Transaksi</span></a>
        <a href="{{ route('portal.profile') }}" class="bottom-nav-item active"><i class="ph-fill ph-user"></i><span>Profil</span></a>
    </div>
</div>
</body>
</html>

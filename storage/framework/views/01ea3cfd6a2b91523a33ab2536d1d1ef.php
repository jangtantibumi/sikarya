<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Northstar OS - Login</title>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --accent: #0C3527; /* Green color for enterprise text */
            --accent-dark: #379f5b; /* Button color */
            --accent-hover: #2e8b4e;
            --text-main: #334155;
            --text-muted: #64748B;
            --border: #E2E8F0;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Left Side */
        .left-panel {
            flex: 1;
            background-color: #1a1a1a;
            color: white;
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            background-image: 
                linear-gradient(rgba(26, 26, 26, 0.9), rgba(26, 26, 26, 0.9)),
                url('data:image/svg+xml;utf8,<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h40v40H0z" fill="none"/><path d="M0 39.5h40M39.5 0v40" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></svg>');
            overflow: hidden;
        }
        /* Wavy Background Graphic (Abstract) */
        .left-panel::after {
            content: '';
            position: absolute;
            top: 10%;
            left: -10%;
            width: 120%;
            height: 80%;
            background: radial-gradient(circle at 50% 50%, rgba(74, 222, 128, 0.05) 0%, transparent 50%);
            z-index: 0;
            pointer-events: none;
        }
        
        .logo-area { z-index: 1; display: flex; align-items: center; gap: 10px; }
        .logo-area img { height: 32px; filter: brightness(0) invert(1); }
        .logo-text { font-size: 20px; font-weight: 800; color: white; letter-spacing: -0.5px; }

        .content-area { z-index: 1; max-width: 500px; margin-top: -10vh; }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 24px;
        }
        
        .content-area h1 {
            font-size: 42px;
            line-height: 1.2;
            margin: 0 0 20px 0;
            font-weight: 800;
        }
        .content-area h1 span { color: var(--accent); }
        .content-area p {
            color: #9ca3af;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
        }

        .footer-area { z-index: 1; font-size: 12px; color: #6b7280; }

        /* Right Side */
        .right-panel {
            flex: 1;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        
        .login-box {
            width: 100%;
            max-width: 400px;
        }
        
        .login-box h2 {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 8px 0;
        }
        .login-box p {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0 0 32px 0;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--accent-dark);
            box-shadow: 0 0 0 3px rgba(55, 159, 91, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: var(--accent-dark);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(55, 159, 91, 0.2); }
        .error { color: #ef4444; margin-bottom: 15px; background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px solid #f87171; font-size: 13px; }

        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel { min-height: 100vh; padding: 40px 20px; }
            .right-panel { min-height: 100vh; }
            .content-area h1 { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="left-panel">
        <div class="logo-area">
            <img src="<?php echo e(asset('images/sikarya-logo.png')); ?>" alt="Logo">
            <div class="logo-text">Northstar OS</div>
        </div>
        
        <div class="content-area">
            <div class="badge">
                <i class="fa-solid fa-shield-halved"></i> AKSES INTERNAL
            </div>
            <h1>Sistem operasional terpadu kelas <span>enterprise.</span></h1>
            <p>Kelola absensi, laporan, tugas, dan cuti karyawan Anda dalam satu pintu yang aman, rapi, dan terkontrol.</p>
        </div>
        
        <div class="footer-area">
            &copy; 2026 Northstar Group. Hak Cipta Dilindungi.
        </div>
    </div>
    
    <div class="right-panel">
        <div class="login-box">
            <h2>Selamat Datang</h2>
            <p>Masukkan email dan kata sandi Anda untuk mengakses sistem.</p>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><div class="error"><?php echo e($errors->first()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <form method="post" action="<?php echo e(route('master-demo.login.attempt')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label>Alamat Email</label>
                    <input class="form-control" name="username" autocomplete="username" required autofocus placeholder="nama@email.com">
                </div>
                <div class="form-group">
                    <label>Kata Sandi</label>
                    <input class="form-control" name="password" type="password" autocomplete="current-password" required placeholder="Password">
                </div>
                <button class="btn" type="submit">Masuk ke Sistem</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\master-demo-login.blade.php ENDPATH**/ ?>
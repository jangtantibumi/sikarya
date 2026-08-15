<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111113">
    <meta name="application-name" content="SubaArch ERP">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SubaArch ERP">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>Akses Internal — ERP Suba Arch</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css?v={{ filemtime(public_path('css/styles.css')) }}">
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            background:
                radial-gradient(circle at 50% 15%, rgba(242, 201, 76, .14), transparent 32%),
                linear-gradient(145deg, #060607, #0d0d10 58%, #050506);
        }
        .access-shell {
            width: min(460px, 100%);
            padding: 1px;
            border-radius: 24px;
            background: linear-gradient(145deg, rgba(242, 201, 76, .65), rgba(255,255,255,.08), rgba(242, 201, 76, .18));
            box-shadow: 0 34px 90px rgba(0,0,0,.65);
        }
        .access-card {
            padding: 38px;
            border-radius: 23px;
            background: rgba(20,20,22,.96);
            backdrop-filter: blur(24px);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
            letter-spacing: .12em;
            font-weight: 700;
        }
        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(242,201,76,.38);
            border-radius: 13px;
            color: #f2c94c;
            background: rgba(242,201,76,.1);
        }
        h1 { margin: 0 0 10px; font-size: 25px; }
        .intro { margin: 0 0 28px; color: #9f9fa8; line-height: 1.65; font-size: 14px; }
        label { display: block; margin-bottom: 9px; color: #d4d4d8; font-size: 13px; }
        .password-wrap { position: relative; }
        input {
            width: 100%;
            padding: 14px 48px 14px 15px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 12px;
            outline: 0;
            color: #fff;
            background: rgba(255,255,255,.045);
            font: inherit;
        }
        input:focus { border-color: #f2c94c; box-shadow: 0 0 0 3px rgba(242,201,76,.1); }
        .toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            padding: 7px;
            border: 0;
            color: #aaa;
            background: transparent;
            cursor: pointer;
        }
        .error { margin: 10px 0 0; color: #ff736b; font-size: 12px; }
        .submit {
            width: 100%;
            margin-top: 18px;
            padding: 14px;
            border: 0;
            border-radius: 12px;
            color: #131313;
            font: 600 14px 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f2c94c, #ffd968);
            box-shadow: 0 12px 28px rgba(242,201,76,.16);
            cursor: pointer;
        }
        .security-note {
            display: flex;
            gap: 9px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,.08);
            color: #777782;
            font-size: 11px;
            line-height: 1.55;
        }
        .install-app-btn {
            display: none;
            width: 100%;
            margin-top: 10px;
            padding: 12px;
            border: 1px solid rgba(242,201,76,.32);
            border-radius: 12px;
            color: #f2c94c;
            font: 600 13px 'Outfit', sans-serif;
            background: rgba(242,201,76,.08);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="access-shell">
        <section class="access-card">
            <div class="brand">
                <div class="brand-mark">SA</div>
                <span>SUBA ARCH</span>
            </div>
            <h1>Portal Internal Perusahaan</h1>
            <p class="intro">Masukkan password akses ERP yang dibagikan resmi oleh perusahaan. Setelah itu, Anda tetap harus masuk menggunakan OTP pribadi yang dikirim ke email terdaftar.</p>

            <form method="POST" action="{{ route('erp-access.verify') }}">
                @csrf
                <label for="access_password">Password akses perusahaan</label>
                <div class="password-wrap">
                    <input id="access_password" name="access_password" type="password" autocomplete="current-password" maxlength="255" autofocus required>
                    <button class="toggle" type="button" id="toggle-password" aria-label="Tampilkan atau sembunyikan password">Lihat</button>
                </div>
                @error('access_password')
                    <p class="error">{{ $message }}</p>
                @enderror
                <button class="submit" type="submit">Lanjut ke Login OTP</button>
                <button class="install-app-btn" id="install-app-btn" type="button">Instal SubaArch ERP di perangkat ini</button>
            </form>

            <div class="security-note">
                <span>◈</span>
                <span>Akses berlaku selama {{ $sessionHours }} jam pada sesi browser ini. Jangan membagikan password melalui kanal publik.</span>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const input = document.getElementById('access_password');
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            this.textContent = visible ? 'Lihat' : 'Sembunyikan';
        });
    </script>
    <script src="/js/pwa.js?v={{ filemtime(public_path('js/pwa.js')) }}"></script>
</body>
</html>

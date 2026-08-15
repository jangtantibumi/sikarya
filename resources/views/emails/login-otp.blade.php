<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode OTP ERP Suba-Arch</title>
</head>
<body style="margin:0;background:#090909;color:#ffffff;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#090909;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#1c1c1e;border:1px solid #343434;border-radius:18px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 30px 12px;">
                            <div style="color:#f2c94c;font-size:13px;font-weight:700;letter-spacing:.08em;">SUBA-ARCH ERP</div>
                            <h1 style="margin:14px 0 8px;font-size:24px;line-height:1.25;">Verifikasi login Anda</h1>
                            <p style="margin:0;color:#a9a9ad;font-size:14px;line-height:1.6;">Gunakan kode berikut untuk melanjutkan ke dashboard pribadi Anda.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 30px;">
                            <div style="padding:18px;text-align:center;background:#111113;border:1px solid rgba(242,201,76,.28);border-radius:14px;color:#f2c94c;font-size:34px;font-weight:800;letter-spacing:.26em;">
                                {{ $otp }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:4px 30px 30px;color:#a9a9ad;font-size:13px;line-height:1.65;">
                            Kode berlaku selama <strong style="color:#ffffff;">{{ $expiresMinutes }} menit</strong> dan hanya dapat digunakan satu kali.
                            Jangan membagikan kode ini kepada siapa pun, termasuk pihak yang mengaku sebagai administrator.
                            Jika Anda tidak meminta login, abaikan email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

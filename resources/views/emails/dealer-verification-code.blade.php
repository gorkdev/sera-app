<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sera - E-posta doğrulama kodu</title>
</head>
<body style="margin:0;padding:0;background:#f6f7f9;font-family:Inter,Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7f9;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb;">
                            <div style="font-size:18px;font-weight:700;letter-spacing:-0.02em;">
                                <span style="color:#16a34a;">Sera</span>
                                <span style="color:#6b7280;font-weight:600;font-size:12px;margin-left:6px;">B2B</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <div style="font-size:16px;font-weight:700;margin:0 0 8px 0;">E-posta doğrulama kodunuz</div>
                            <div style="font-size:14px;line-height:1.6;color:#374151;margin:0 0 16px 0;">
                                Bayi hesabınızı doğrulamak için aşağıdaki 6 haneli kodu girin.
                                Kod <strong>{{ $expiresMinutes }} dakika</strong> boyunca geçerlidir.
                            </div>

                            <div style="text-align:center;margin:18px 0 18px 0;">
                                <div style="display:inline-block;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:12px;padding:14px 18px;">
                                    <span style="font-size:28px;letter-spacing:0.35em;font-weight:800;color:#111827;">{{ $code }}</span>
                                </div>
                            </div>

                            <div style="font-size:12px;line-height:1.6;color:#6b7280;margin-top:14px;">
                                Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#ffffff;border-top:1px solid #e5e7eb;">
                            <div style="font-size:12px;color:#6b7280;">
                                © {{ date('Y') }} Sera. Tüm hakları saklıdır.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>


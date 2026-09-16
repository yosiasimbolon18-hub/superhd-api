<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:24px; margin:0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden;">
        <tr>
            <td style="background:#1a1a1a; padding:24px; text-align:center;">
                <span style="color:#ffcc00; font-size:20px; font-weight:bold;">SUPER HD KLINER</span>
            </td>
        </tr>
        <tr>
            <td style="padding:28px;">
                <h2 style="margin-top:0; color:#1a1a1a;">Halo, {{ $namaPic }} 👋</h2>
                <p style="color:#333; line-height:1.6;">
                    Pengajuan akun distributor untuk <b>{{ $namaPerusahaan }}</b> sudah kami
                    <b style="color:#0a8a3f;">setujui</b>. Berikut akun untuk login ke portal distributor:
                </p>
                <table cellpadding="8" style="background:#f7f7f7; border-radius:6px; margin:16px 0; width:100%;">
                    <tr>
                        <td style="color:#666;">Username</td>
                        <td style="font-family:monospace; font-weight:bold;">{{ $username }}</td>
                    </tr>
                    <tr>
                        <td style="color:#666;">Password Sementara</td>
                        <td style="font-family:monospace; font-weight:bold;">{{ $tempPassword }}</td>
                    </tr>
                </table>
                <p style="color:#333; line-height:1.6;">
                    Demi keamanan, silakan segera login dan ganti password kamu setelah masuk pertama kali.
                </p>
                <p style="color:#999; font-size:12px; margin-top:32px;">
                    Email ini dikirim otomatis oleh sistem Super HD Kliner. Jika kamu tidak merasa
                    mengajukan pendaftaran ini, abaikan email ini.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

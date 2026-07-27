<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your SEÑAS Account</title>
</head>
<body style="margin:0;padding:0;background:#eef2ff;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2ff;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 25px 80px rgba(15,23,42,0.12);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);padding:40px 32px;text-align:center;color:#ffffff;">
                            <h1 style="margin:0;font-size:28px;font-weight:800;letter-spacing:-0.03em;">Verify Your Email Address</h1>
                            <p style="margin:12px auto 0;max-width:420px;font-size:15px;line-height:1.75;opacity:.9;">Please verify your email address to activate your SEÑAS teacher dashboard account.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 24px;">
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.75;">Hi {{ $notifiable->name }},</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.75;color:#4b5563;">Thank you for registering for a teacher account on SEÑAS! Click the button below to confirm your email address and activate your account. This link will expire in 60 minutes.</p>

                            <div style="text-align:center;margin:0 0 32px;">
                                <a href="{{ $url }}" style="display:inline-block;padding:16px 28px;border-radius:999px;background:#1C3D7A;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;">Verify Email Address</a>
                            </div>

                            <p style="margin:0 0 18px;font-size:15px;line-height:1.75;color:#4b5563;">If you did not create a SEÑAS account, no further action is required.</p>

                            <div style="padding:18px 20px;border-radius:18px;background:#f8fafc;border:1px solid #e5e7eb;color:#475569;font-size:14px;line-height:1.7;">
                                <strong style="display:block;margin-bottom:8px;color:#111827;">Security Notice:</strong>
                                Keep your login details confidential. SEÑAS staff will never ask for your password.
                            </div>

                            <p style="margin:32px 0 0;font-size:15px;line-height:1.75;color:#6b7280;">Thank you,<br>The SEÑAS Team</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:22px 32px 28px;color:#6b7280;font-size:12px;line-height:1.6;text-align:center;">
                            If the button does not work, copy and paste the following link into your browser:<br>
                            <a href="{{ $url }}" style="color:#1d4ed8;word-break:break-all;">{{ $url }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

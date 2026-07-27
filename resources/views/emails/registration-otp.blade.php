<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEÑAS Verification Code</title>
</head>
<body style="margin:0;padding:0;background:#eef2ff;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2ff;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 25px 80px rgba(15,23,42,0.12);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);padding:40px 32px;text-align:center;color:#ffffff;">
                            <h1 style="margin:0;font-size:28px;font-weight:800;letter-spacing:-0.03em;">Verification Code</h1>
                            <p style="margin:12px auto 0;max-width:420px;font-size:15px;line-height:1.75;opacity:.9;">Enter this code to verify your email before your account is created.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 24px;">
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.75;">Hello,</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.75;color:#4b5563;">You are registering for a teacher account on SEÑAS. Please use the 6-digit verification code below to verify your email address. This code expires in 15 minutes.</p>

                            <div style="text-align:center;margin:0 0 32px;">
                                <div style="display:inline-block;padding:18px 36px;border-radius:16px;background:#f1f5f9;border:2px dashed #0d326b;color:#0d326b;font-size:32px;font-weight:900;letter-spacing:8px;">
                                    {{ $otp }}
                                </div>
                            </div>

                            <p style="margin:0 0 18px;font-size:15px;line-height:1.75;color:#4b5563;">If you did not request this verification code, please ignore this email.</p>

                            <div style="padding:18px 20px;border-radius:18px;background:#f8fafc;border:1px solid #e5e7eb;color:#475569;font-size:14px;line-height:1.7;">
                                <strong style="display:block;margin-bottom:8px;color:#111827;">Security Notice:</strong>
                                Never share this code with anyone. SEÑAS staff will never ask for your verification code.
                            </div>

                            <p style="margin:32px 0 0;font-size:15px;line-height:1.75;color:#6b7280;">Thank you,<br>The SEÑAS Team</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

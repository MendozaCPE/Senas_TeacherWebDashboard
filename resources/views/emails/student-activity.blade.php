<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notifTitle }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(13,50,107,0.10); }
        .header { background: linear-gradient(135deg, #0d326b 0%, #1a5fb4 100%); padding: 28px 32px; }
        .header-logo { font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; }
        .header-sub { font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 2px; font-weight: 500; }
        .body { padding: 32px; }
        .greeting { font-size: 15px; color: #1e293b; font-weight: 600; margin-bottom: 20px; }
        .notif-card { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
        .notif-title { font-size: 16px; font-weight: 800; color: #0d326b; margin: 0 0 8px 0; line-height: 1.3; }
        .notif-message { font-size: 14px; color: #475569; line-height: 1.6; margin: 0; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #0d326b, #1a5fb4); color: #ffffff !important; text-decoration: none; padding: 13px 28px; border-radius: 10px; font-size: 14px; font-weight: 700; margin-bottom: 24px; }
        .divider { border: none; border-top: 1px solid #f1f5f9; margin: 0 0 24px 0; }
        .footer { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .footer a { color: #0d326b; text-decoration: none; }
        .unsubscribe { font-size: 11px; color: #cbd5e1; margin-top: 8px; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo">SEÑAS</div>
        <div class="header-sub">Teacher Portal — Student Activity Alert</div>
    </div>

    {{-- Body --}}
    <div class="body">
        <p class="greeting">Hi {{ $teacherName }},</p>

        <div class="notif-card">
            <p class="notif-title">{{ $notifTitle }}</p>
            <p class="notif-message">{{ $notifMessage }}</p>
        </div>

        @if($actionUrl)
            <a href="{{ $actionUrl }}" class="cta-btn">View Student Report →</a>
        @endif

        <hr class="divider">

        <div class="footer">
            <p>You're receiving this email because you have <strong>Email Alerts</strong> enabled in your SEÑAS Teacher Portal settings.</p>
            <p class="unsubscribe">
                To stop receiving these emails, go to
                <a href="{{ url('/settings#notifications') }}">Settings → Notifications</a>
                and turn off Email Alerts.
            </p>
        </div>
    </div>

</div>
</body>
</html>

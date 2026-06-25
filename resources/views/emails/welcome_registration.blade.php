<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Bio-Pacific</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello {{ $firstName ?: $userName }},</p>

    <p>Welcome to Bio-Pacific. Your portal account has been created successfully.</p>

    @if($facilityName)
        <p>Facility: <strong>{{ $facilityName }}</strong></p>
    @endif

    <p>Before you can access your account, please verify your email address:</p>

    <p>
        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:10px 16px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:6px;">
            Verify Email Address
        </a>
    </p>

    <p style="color:#6b7280;font-size:14px;">
        If the button does not work, copy and paste this link into your browser:<br>
        <span style="word-break:break-all;">{{ $verificationUrl }}</span>
    </p>

    <p>After verifying, you can sign in here: <a href="{{ $dashboardUrl }}" style="color:#0d9488;">Open Portal Dashboard</a></p>

    @if($preEmploymentUrl)
        <p>If you are completing pre-employment steps, continue here after verifying: <a href="{{ $preEmploymentUrl }}" style="color:#0d9488;">Pre-Employment Portal</a></p>
    @endif

    <p style="color:#6b7280;font-size:14px;">If you did not create this account, please contact your facility administrator.</p>
</body>
</html>

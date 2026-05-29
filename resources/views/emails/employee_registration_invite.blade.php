<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portal Registration</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello {{ $employeeName }},</p>

    <p>You have been invited to create your Bio-Pacific HR portal account.</p>

    <p><strong>Registration code:</strong> {{ $registrationCode }}</p>

    <p>
        <a href="{{ $registrationUrl }}" style="display:inline-block;padding:10px 16px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:6px;">
            Create Your Account
        </a>
    </p>

    <p>When registering, use your full name, work email address, and either your employee number or the last 4 digits of your Social Security number to verify your identity.</p>

    @if($expiresAt)
        <p style="color:#6b7280;font-size:14px;">This code expires on {{ $expiresAt->timezone(config('app.timezone'))->format('F j, Y g:i A T') }}.</p>
    @endif

    <p style="color:#6b7280;font-size:14px;">If you did not expect this message, please contact your facility administrator.</p>
</body>
</html>

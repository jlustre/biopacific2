<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicant Portal Registration</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Hello {{ $applicantName }},</p>

    <p>You have been invited to create your Bio-Pacific applicant portal account.</p>

    @if(!empty($sponsorName))
        <p><strong>Your sponsor:</strong> {{ $sponsorName }}</p>
        <p style="color:#374151;font-size:14px;">When you register, confirm this sponsor name is correct. If it is wrong, do not complete registration and contact your hiring contact.</p>
    @endif

    <p><strong>Registration code:</strong> {{ $registrationCode }}</p>

    <p>
        <a href="{{ $registrationUrl }}" style="display:inline-block;padding:10px 16px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:6px;">
            Create Your Account
        </a>
    </p>

    <p>Use the same name and email address from your job application when registering.</p>

    @if($preEmploymentUrl)
        <p>After creating your account, continue your pre-employment steps here:</p>
        <p>
            <a href="{{ $preEmploymentUrl }}" style="color:#0d9488;">Open Pre-Employment Portal</a>
        </p>
    @endif

    @if($applicantCode)
        <p style="color:#6b7280;font-size:14px;">Pre-employment reference code: {{ $applicantCode }}</p>
    @endif

    @if($expiresAt)
        <p style="color:#6b7280;font-size:14px;">This registration code expires on {{ $expiresAt->timezone(config('app.timezone'))->format('F j, Y g:i A T') }}.</p>
    @endif

    <p style="color:#6b7280;font-size:14px;">If you did not apply with Bio-Pacific, please ignore this email.</p>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <p>Hello {{ $employeeName }},</p>

    <p>
        Your {{ $isLicense ? 'credential' : 'document' }}
        <strong>{{ $documentName }}</strong> has been
        <strong>approved</strong>
        @if(!empty($reviewerName))
            by {{ $reviewerName }}
        @endif.
    </p>

    <p>
        <a href="{{ $isLicense ? $credentialsUrl : $documentsUrl }}" style="display:inline-block;background:#0f766e;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
            {{ $isLicense ? 'View My Credentials' : 'View My Documents' }}
        </a>
    </p>

    <p style="color:#64748b;font-size:13px;">
        If the button does not work, copy and paste this link:<br>
        {{ $isLicense ? $credentialsUrl : $documentsUrl }}
    </p>
</body>
</html>

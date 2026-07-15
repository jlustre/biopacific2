<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document returned for correction</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <p>Hello {{ $employeeName }},</p>

    <p>
        Your {{ $isLicense ? 'credential' : 'document' }}
        <strong>{{ $documentName }}</strong> was
        <strong>returned for correction</strong>
        @if(!empty($reviewerName))
            by {{ $reviewerName }}
        @endif.
    </p>

    <p style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;">
        <strong>Reviewer notes:</strong><br>
        {{ $notes }}
    </p>

    <p>Please upload a corrected file and submit it again for approval. A task has also been added to your My Tasks list.</p>

    <p>
        <a href="{{ $actionUrl }}" style="display:inline-block;background:#0f766e;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
            {{ $isLicense ? 'Open My Credentials' : 'Open My Documents' }}
        </a>
    </p>

    <p style="color:#64748b;font-size:13px;">
        If the button does not work, copy and paste this link:<br>
        {{ $actionUrl }}
    </p>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Training approved</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <p>Hello {{ $employeeName }},</p>

    <p>
        Your training <strong>{{ $trainingName }}</strong> has been
        <strong>approved and marked complete</strong>
        @if($isHiring)
            for your hiring (one-time) requirement.
        @else
            for assessment period: <strong>{{ $periodLabel }}</strong>.
        @endif
    </p>

    <p>
        <a href="{{ $checklistUrl }}" style="display:inline-block;background:#0f766e;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
            View training checklist
        </a>
    </p>

    <p style="color:#64748b;font-size:13px;">If the button does not work, copy and paste this link:<br>{{ $checklistUrl }}</p>
</body>
</html>

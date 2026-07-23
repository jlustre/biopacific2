<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $taskTitle }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <p>Hello {{ $employeeName }},</p>

    <p>{{ $reviewerName }} assigned you a required training task.</p>

    <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:8px;padding:12px;">
        <strong>{{ $trainingName }}</strong><br>
        {{ $taskMessage }}
        @if($dueDate)
            <br><strong>Due:</strong> {{ $dueDate }}
        @endif
    </div>

    <p>This task is also available in My Tasks and My Messages.</p>

    <p>
        <a href="{{ $actionUrl }}" style="display:inline-block;background:#0f766e;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;">
            Open Training
        </a>
    </p>

    <p style="color:#64748b;font-size:13px;">
        If the button does not work, copy and paste this link:<br>
        {{ $actionUrl }}
    </p>
</body>
</html>

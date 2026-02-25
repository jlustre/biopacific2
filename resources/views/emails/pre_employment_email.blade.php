<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Employment Portal</title>
</head>

<body style="margin:0; padding:0; background:#f6f7fb; font-family: Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td
                            style="background:linear-gradient(135deg,#0f766e,#0d9488); padding:20px 24px; color:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:18px; font-weight:700;">Bio-Pacific</td>
                                    <td align="right" style="font-size:13px; opacity:0.9;">Pre-Employment</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 12px 24px;">
                            <h1 style="margin:0 0 10px 0; font-size:24px; line-height:1.3;">Complete Your Pre-Employment
                                Steps</h1>
                            <p style="margin:0; font-size:15px; line-height:1.6; color:#374151;">
                                Hello {{ $applicantName ?? 'Applicant' }},
                                <br>
                                Your pre-employment process is ready. Please use the secure link below to continue.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 24px 24px 24px;">
                            @php
                            $code = $applicantCode ?? '';
                            $preEmploymentUrl = $preEmploymentUrl ?? ($code ? route('pre-employment.index', ['code' =>
                            $code]) : url('/pre-employment'));
                            @endphp
                            <a href="{{ $preEmploymentUrl }}"
                                style="display:inline-block; background:#0d9488; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:8px; font-weight:700; font-size:14px;">
                                Open Pre-Employment Portal
                            </a>
                            <p style="margin:12px 0 0 0; font-size:13px; color:#6b7280;">
                                If the button does not work, copy and paste this link into your browser:
                                <br>
                                <span style="word-break:break-all;">{{ $preEmploymentUrl }}</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <div
                                style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:14px;">
                                <p style="margin:0; font-size:13px; color:#374151;">
                                    <strong>Your code:</strong> {{ $applicantCode ?? 'Not available' }}
                                </p>
                                <p style="margin:8px 0 0 0; font-size:13px; color:#6b7280;">
                                    This code helps prefill your name and email during registration or login.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <p style="margin:0; font-size:13px; color:#6b7280;">
                                Need help? Contact HR at <a href="mailto:hr@biopacific.com"
                                    style="color:#0d9488; text-decoration:none;">hr@biopacific.com</a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="background:#111827; color:#9ca3af; text-align:center; font-size:12px; padding:16px 24px;">
                            &copy; {{ date('Y') }} Bio-Pacific. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Job Application - Secure Access Required</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .alert {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .alert strong {
            color: #92400e;
        }

        .info-box {
            background: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-box h3 {
            margin: 0 0 10px 0;
            color: #374151;
        }

        .info-box p {
            margin: 5px 0;
            color: #6b7280;
        }

        .secure-button {
            display: inline-block;
            background: #10b981;
            color: white !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }

        .secure-button:hover {
            background: #059669;
        }

        .security-notice {
            background: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ $facilityName }}</div>
            <h1>New Job Application Received</h1>
        </div>

        <div class="alert">
            <strong>🔒 HIPAA-Compliant Secure Access Required</strong><br>
            This email contains no personal information. Click the secure link below to view details.
        </div>

        <div class="info-box">
            <h3>Application Summary</h3>
            <p><strong>Application ID:</strong> #{{ $applicationId }}</p>
            <p><strong>Position:</strong> {{ $jobTitle }}</p>
            <p><strong>Submitted:</strong> {{ $submittedAt }}</p>
            <p><strong>Expires:</strong> {{ $expiresAt }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $secureUrl }}" class="secure-button">
                🔐 View Secure Application Details
            </a>
        </div>

        <div class="security-notice">
            <h4>🛡️ Security Information</h4>
            <ul>
                <li>This secure link expires in 24 hours</li>
                <li>Access is logged for HIPAA compliance</li>
                <li>No personal information is transmitted via email</li>
                <li>All data is encrypted and stored securely</li>
            </ul>
        </div>

        <div class="footer">
            <p>This is an automated HIPAA-compliant notification from {{ $facilityName }}.</p>
            <p>For security reasons, personal information is not included in this email.</p>
        </div>
    </div>
</body>

</html>
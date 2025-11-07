@component('mail::message')
# 🔒 New Secure Tour Request

**{{ $facilityName }}** has received a new Book a Tour request.

@component('mail::panel')
⚠️ **CONFIDENTIAL - AUTHORIZED PERSONNEL ONLY**

This email contains a secure link to protected health information (PHI).
**DO NOT FORWARD** this email. Unauthorized access is prohibited under HIPAA
and may result in legal action.
@endcomponent

@component('mail::panel')
**📋 Request Details:**
- **Request ID:** #{{ $requestId }}
- **Submitted:** {{ $submittedAt->format('M d, Y \a\t g:i A') }}
- **Status:** Pending Review
- **Link Expires:** {{ $expiresAt ? $expiresAt->format('M d, Y \a\t g:i A') : 'manually revoked' }}

⚠️ **HIPAA Notice:** This request contains protected health information (PHI). Access details via secure link only.
@endcomponent

@component('mail::button', ['url' => $secureUrl, 'color' => 'success'])
🔐 View Secure Request Details
@endcomponent

## 🛡️ Security & Compliance Information

- **Staff Verification Required:** You will need to verify your staff credentials before accessing
- **Secure Access:** This link requires authorized facility staff email verification
- **Time Limited:** Link expires after {{ config('app.secure_access_hours', 72) }} hours
- **Audit Trail:** All access attempts are logged for HIPAA compliance
- **IP Monitoring:** Suspicious access patterns are detected and reported

## ⚠️ IMPORTANT SECURITY NOTICE

**FOR AUTHORIZED FACILITY STAFF ONLY**

- This email is intended solely for authorized personnel of {{ $facilityName }}
- Do not share, forward, or discuss the contents with unauthorized individuals
- If you received this email in error, delete it immediately and notify your IT department
- Unauthorized access to protected health information is a federal crime
- All access is monitored and logged for compliance with HIPAA regulations

---

@component('mail::subcopy')
**Security Reminder:** If you're having trouble clicking the "View Secure Request Details" button, copy and paste the
URL below into your web browser. Only access from authorized devices and networks.

[{{ $secureUrl }}]({{ $secureUrl }})

**Report Security Issues:** If you suspect unauthorized access or have security concerns, contact your facility's IT
security team immediately.
@endcomponent

Thanks,<br>
{{ config('app.name') }} Security System
@endcomponent
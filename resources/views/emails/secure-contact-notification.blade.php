@component('mail::message')
# New Secure Contact Inquiry - {{ $facilityName }}

Hello,

You have received a new contact form inquiry. For HIPAA compliance, the inquiry details are stored securely and can only
be accessed via the secure link below.

@component('mail::panel')
### Inquiry Summary:
- **Facility:** {{ $facilityName }}
- **Received:** {{ $safeData['created_at']->format('M j, Y g:i A') }}
- **Has Message:** {{ $safeData['has_message'] ? 'Yes' : 'No' }}
- **Consent Given:** {{ $safeData['consent_given'] ? 'Yes' : 'No' }}
- **PHI-Free Confirmed:** {{ $safeData['no_phi_confirmed'] ? 'Yes' : 'No' }}
@endcomponent

@component('mail::button', ['url' => $secureViewUrl])
View Secure Inquiry Details
@endcomponent

**Important Security Information:**
- This link is valid for 24 hours only
- Contact details are encrypted and stored securely
- Access is logged for audit purposes
- No personal health information (PHI) is included in this email

@component('mail::subcopy')
**HIPAA Compliance:** This notification contains no protected health information (PHI). All sensitive details are
encrypted and can only be accessed through the secure link above. If you cannot access the link, please contact your
system administrator.

**Link expires:** {{ $inquiry->token_expires_at->format('M j, Y g:i A') }}
@endcomponent

Thank you,<br>
{{ $facilityName }} Secure Notification System
@endcomponent
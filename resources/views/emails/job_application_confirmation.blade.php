@component('mail::message')
# Application Received

Hello {{ $applicantName }},

Thank you for applying to **{{ $facilityName }}**. We have received your job application for **{{ $positionTitle }}**.

@component('mail::panel')
**Submitted:** {{ $submittedAt }}

Our hiring team will review your application and contact you if your qualifications match our current needs.
@endcomponent

If you did not submit this application, please contact {{ $facilityName }} as soon as possible.

Thank you,<br>
{{ $facilityName }} Hiring Team
@endcomponent

@component('mail::message')
@if(!empty($customMessage))
@foreach(array_filter(preg_split('/\R\R+/', trim($customMessage))) as $paragraph)
{{ $paragraph }}

@endforeach
@elseif($expiryTier === 'expired')
# Expired Document – Action Required

Hello {{ $employeeName }},

Your **{{ $documentType }}** document at **{{ $facilityName }}** has **expired**{{ $expiryDateClause }}. Please renew or replace this document as soon as possible.
@elseif($expiryTier === 'urgent')
# Document Expiring Soon – Urgent

Hello {{ $employeeName }},

Your **{{ $documentType }}** document at **{{ $facilityName }}** is **expiring within the next 30 days**{{ $expiryDateClause }}. Please take action promptly to avoid a lapse.
@else
# Document Expiring – Reminder

Hello {{ $employeeName }},

Your **{{ $documentType }}** document at **{{ $facilityName }}** will expire within the next 120 days{{ $expiryDateClause }}. Please plan ahead to renew or replace it before the expiration date.
@endif

@component('mail::panel')
**Document type:** {{ $documentType }}

@if($fileName)
**File:** {{ $fileName }}
@endif

@if($effectiveStart)
**Effective date:** {{ $effectiveStart }}
@endif

@if($expiresAt)
**Expires:** {{ $expiresAt }}
@endif

@if($comments)
**Notes:** {{ $comments }}
@endif
@endcomponent

You can review your documents in the member portal:

@component('mail::button', ['url' => $memberDocumentsUrl])
View My Documents
@endcomponent

@if($sentByName)
This notification was sent by {{ $sentByName }} from the facility HR team.
@endif

If you have questions about this document, please contact your facility administrator.

Thank you,<br>
{{ $facilityName }} HR Team
@endcomponent

@component('mail::message')
# Document Review Requested

An employee has uploaded a document that requires your verification and approval.

@if(!empty($customMessage))
@foreach(array_filter(preg_split('/\R\R+/', trim($customMessage))) as $paragraph)
{{ $paragraph }}

@endforeach
@else
**{{ $employeeName }}** ({{ $employeeNum }}) submitted a document at **{{ $facilityName }}** for review.
@endif

@component('mail::panel')
**Reason for upload:** {{ $submissionReason }}

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
**Employee notes:** {{ $comments }}
@endif

**Submitted by:** {{ $submittedByName }}
@endcomponent

@component('mail::button', ['url' => $reviewUrl])
Review Document
@endcomponent

Please verify the document and mark it as approved or rejected in the employee record.

Thank you,<br>
{{ config('app.name') }} HR Management
@endcomponent

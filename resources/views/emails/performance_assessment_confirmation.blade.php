@component('mail::message')

@if(($notificationPurpose ?? 'employee_confirmation') === 'reviewer_returned')

# {{ ucfirst($assessmentLabel) }} sent back for your review



Hello,



**{{ $employeeName }}** sent their {{ $assessmentLabel }} at **{{ $facilityName }}** back to you for updates.



@if($periodLabel)

**Assessment period:** {{ $periodLabel }}

@endif



Please sign in to the HR portal, review the employee's comments, update the assessment as needed, and resubmit it for employee confirmation when ready.



@component('mail::button', ['url' => $confirmationUrl])

Open Assessment Checklist

@endcomponent

@elseif(($notificationPurpose ?? 'employee_confirmation') === 'reviewer_approval')

# {{ ucfirst($assessmentLabel) }} ready for your approval



Hello,



**{{ $employeeName }}** signed their {{ $assessmentLabel }} at **{{ $facilityName }}** and it is waiting for your review and signature.



@if($periodLabel)

**Assessment period:** {{ $periodLabel }}

@endif



Please sign in to the HR portal, review the assessment, and sign to mark it completed.



@component('mail::button', ['url' => $confirmationUrl])

Review and Complete Assessment

@endcomponent

@elseif(($notificationPurpose ?? 'employee_confirmation') === 'employee_resubmitted')

# Updated {{ $assessmentLabel }} ready for your review



Hello {{ $employeeName }},



Your reviewer at **{{ $facilityName }}** has updated your {{ $assessmentLabel }} and sent it back for your review and signature.



@if($periodLabel)

**Assessment period:** {{ $periodLabel }}

@endif



Please sign in to the HR portal, review the changes, and sign to confirm.



@component('mail::button', ['url' => $confirmationUrl])

Review and Sign

@endcomponent

@else

# {{ ucfirst($assessmentLabel) }} ready for your review



Hello {{ $employeeName }},



Your {{ $assessmentLabel }} at **{{ $facilityName }}** has been submitted and is waiting for your confirmation.



@if($periodLabel)

**Assessment period:** {{ $periodLabel }}

@endif



Please sign in to the HR portal, review the assessment, add any employee comments, and save your acknowledgement.



@component('mail::button', ['url' => $confirmationUrl])

Review and Confirm

@endcomponent

@endif



If the button does not work, copy and paste this link into your browser:



{{ $confirmationUrl }}



Thanks,<br>

{{ config('app.name') }}

@endcomponent


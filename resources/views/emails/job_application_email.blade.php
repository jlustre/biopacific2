@component('mail::message')
# New Job Application for {{ $data['facility']['name'] }}

Hello,

You have received a new job application. Below are the details:

@component('mail::panel')
### Position:
**{{ $data['job_title'] }}**
@endcomponent

@component('mail::panel')
### Applicant Details:
- **Name:** {{ $data['first_name'] }} {{ $data['last_name'] }}
- **Email:** {{ $data['email'] }}
- **Phone:** {{ $data['phone'] ?? 'Not provided' }}
@endcomponent

@if(!empty($data['cover_letter']))
@component('mail::panel')
### Cover Letter:
{{ $data['cover_letter'] }}
@endcomponent
@endif

@if(!empty($data['resume_path']))
@component('mail::panel')
### Resume:
The applicant's resume is attached to this email.
@endcomponent
@endif

@component('mail::button', ['url' => url('/admin/facilities/webcontents/careers/' . $data['job_opening_id'] .
'/applications')])
View Application in Admin Panel
@endcomponent

Thank you,<br>
{{ $data['facility']['name'] }} Hiring Team
@endcomponent
@component('mail::message')
# New Tour Request for {{ $data['facility']['name'] }}

Hello,

You have received a new Book a Tour request. Below are the details:

@component('mail::panel')
### Request Details:
- **Name:** {{ $data['full_name'] }}
- **Relationship:** {{ $data['relationship'] }}
- **Email:** {{ $data['email'] }}
- **Phone:** {{ $data['phone'] }}
- **Preferred Date:** {{ $data['preferred_date'] }}
- **Preferred Time:** {{ $data['preferred_time'] }}
- **Interests:** {{ implode(', ', $data['interests']) }}
@endcomponent

@if(!empty($data['message']))
@component('mail::panel')
### Additional Message:
{{ $data['message'] }}
@endcomponent
@endif

@component('mail::button', ['url' => url('/admin/tour-requests')])
View Request in Admin Panel
@endcomponent

Thank you,<br>
{{ config('app.name') }}
@endcomponent
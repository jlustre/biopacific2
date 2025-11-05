@component('mail::message')
# New Contact Form Inquiry for {{ $data['facility']['name'] }}

Hello,

You have received a new contact form inquiry. Below are the details:

@component('mail::panel')
### Contact Details:
- **Name:** {{ $data['full_name'] }}
- **Email:** {{ $data['email'] }}
- **Phone:** {{ $data['phone'] ?? 'Not provided' }}
@endcomponent

@component('mail::panel')
### Message:
{{ $data['message'] }}
@endcomponent

@component('mail::button', ['url' => url('/admin/inquiries')])
View Inquiry in Admin Panel
@endcomponent

Thank you,<br>
{{ $data['facility']['name'] }}
@endcomponent
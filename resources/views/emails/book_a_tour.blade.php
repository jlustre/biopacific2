@component('mail::message')
# Book a Tour Request

You have received a new Book a Tour request. Here are the details:

- **Name:** {{ $name }}
- **Email:** {{ $email }}
- **Phone:** {{ $phone }}
- **Preferred Date:** {{ $preferred_date }}
- **Message:**

{{ $message }}

@component('mail::button', ['url' => $url])
View Request
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
@extends('layouts.dashboard', ['title' => 'Email Template'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $emailTemplate->name }}</h1>
            <p class="text-gray-600">Category: {{ $emailTemplate->category }}</p>
        </div>
        <div class="flex gap-2">
            @if(request('reply_to'))
            <form method="POST" action="{{ route('admin.email-templates.send-reply', $emailTemplate) }}">
                @csrf
                <input type="hidden" name="reply_to" value="{{ request('reply_to') }}">
                <input type="hidden" name="job_application_id" value="{{ request('job_application_id') }}">
                <input type="hidden" name="applicant_name" value="{{ request('applicant_name') }}">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-paper-plane mr-2"></i>Send via App
                </button>
            </form>
            <button type="button" onclick="openReplyComposer()"
                class="bg-amber-600 hover:bg-amber-700 text-white font-medium py-2 px-4 rounded-lg transition">
                <i class="fas fa-envelope mr-2"></i>Open Mail Client
            </button>
            @endif
            <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ request('reply_to') ? route('admin.email-templates.index', ['reply_to' => request('reply_to'), 'job_application_id' => request('job_application_id'), 'applicant_name' => request('applicant_name')]) : route('admin.email-templates.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    @if(request('reply_to'))
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-blue-900">
                    This email will be sent to <strong>{{ request('applicant_name') }}</strong> at <strong>{{
                        request('reply_to') }}</strong>
                </p>
                <p class="text-xs text-blue-700 mt-1">
                    Use "Send via App" to send through the server (shows in Mailpit), or "Open Mail Client" to use your
                    local email app.
                </p>
            </div>
        </div>
    </div>
    @endif

    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border p-6 space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Subject</h2>
            @if(request('reply_to'))
            <p class="text-gray-700 bg-green-50 border border-green-200 rounded p-3">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>{{ $filledSubject }}
            </p>
            @if($filledSubject !== $emailTemplate->subject)
            <details class="mt-2">
                <summary class="text-xs text-gray-600 cursor-pointer hover:text-gray-900">Show Original Template
                </summary>
                <p class="text-gray-500 text-xs mt-2 italic">{{ $emailTemplate->subject }}</p>
            </details>
            @endif
            @else
            <p class="text-gray-700">{{ $emailTemplate->subject }}</p>
            @endif
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Body</h2>
            @if(request('reply_to'))
            <pre
                class="whitespace-pre-wrap text-gray-700 bg-green-50 border border-green-200 rounded-md p-4">{{ $filledBody }}</pre>
            @if($filledBody !== $emailTemplate->body)
            <details class="mt-2">
                <summary class="text-xs text-gray-600 cursor-pointer hover:text-gray-900">Show Original Template
                </summary>
                <pre
                    class="whitespace-pre-wrap text-gray-500 text-xs bg-gray-100 rounded-md p-3 mt-2 italic">{{ $emailTemplate->body }}</pre>
            </details>
            @endif
            @else
            <pre
                class="whitespace-pre-wrap text-gray-700 bg-gray-50 border rounded-md p-4">{{ $emailTemplate->body }}</pre>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700">Status:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $emailTemplate->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $emailTemplate->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        @if(request('reply_to') && $jobApplication)
        <div class="border-t pt-4 mt-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Replaced Placeholders</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{first_name}</span>
                    <p class="text-gray-900 mt-1">{{ $jobApplication->first_name }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{last_name}</span>
                    <p class="text-gray-900 mt-1">{{ $jobApplication->last_name }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{facility_name}</span>
                    <p class="text-gray-900 mt-1">{{ $jobApplication->jobOpening?->facility?->name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{job_title}</span>
                    <p class="text-gray-900 mt-1">{{ $jobApplication->jobOpening?->title ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{application_id}</span>
                    <p class="text-gray-900 mt-1">{{ $jobApplication->id }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{applicant_code}</span>
                    <p class="text-gray-900 mt-1">{{ $jobApplication->applicant_code ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <span class="text-gray-600 font-medium">{pre_employment_link}</span>
                    <p class="text-gray-900 mt-1">{{ url('/pre-employment') }}{{ $jobApplication->applicant_code ? '?c='
                        . urlencode($jobApplication->applicant_code) : '' }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if(request('reply_to'))
<script>
    function openReplyComposer() {
    const replyTo = "{{ request('reply_to') }}";
    const subject = "{{ str_replace('"', '\\"', $filledSubject) }}";
    const body = `{{ addslashes($filledBody) }}`;
    
    // Create mailto link with filled-in template content
    const mailtoLink = `mailto:${replyTo}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    
    // Open default email client
    window.location.href = mailtoLink;
}
</script>
@endif

@endsection
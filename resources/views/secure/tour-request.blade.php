@extends('layouts.secure')

@section('title', 'Secure Tour Request #' . $tourRequest->id)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Security Header --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">🔒 Secure HIPAA-Compliant Access</h3>
                    <p class="text-sm text-blue-700 mt-1">This tour request contains protected health information (PHI).
                        Access logged for compliance.</p>
                </div>
            </div>
        </div>

        {{-- Request Details Card --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Tour Request #{{ $tourRequest->id }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $facility->name }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Pending Review
                        </span>
                        <p class="text-sm text-gray-500 mt-1">Submitted {{ $tourRequest->created_at->format('M d, Y \a\t
                            g:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Contact Information --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="text-sm text-gray-900">{{ $tourRequest->full_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Relationship</dt>
                                <dd class="text-sm text-gray-900">{{ $tourRequest->relationship ?: 'Not specified' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="text-sm text-gray-900">{{ $tourRequest->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900">{{ $tourRequest->email }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Tour Details --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tour Details</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Preferred Date</dt>
                                <dd class="text-sm text-gray-900">{{
                                    \Carbon\Carbon::parse($tourRequest->preferred_date)->format('l, F j, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Preferred Time</dt>
                                <dd class="text-sm text-gray-900">{{ $tourRequest->preferred_time }}</dd>
                            </div>
                            @if($tourRequest->interests && count($tourRequest->interests) > 0)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Areas of Interest</dt>
                                <dd class="text-sm text-gray-900">
                                    @foreach($tourRequest->interests as $interest)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-1">
                                        {{ $interest }}
                                    </span>
                                    @endforeach
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Message Section --}}
                @if($tourRequest->message)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Additional Message</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $tourRequest->message }}</p>
                    </div>
                </div>
                @endif

                {{-- Compliance Information --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Compliance Information</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-yellow-800">Access Token:</span>
                                <p class="text-yellow-700 font-mono text-xs mt-1">{{ substr($tourRequest->access_token,
                                    0, 16) }}...</p>
                            </div>
                            <div>
                                <span class="font-medium text-yellow-800">Expires:</span>
                                <p class="text-yellow-700 mt-1">{{ $tourRequest->expires_at ?
                                    $tourRequest->expires_at->format('M d, Y \a\t g:i A') : 'Never' }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-yellow-800">Consent Given:</span>
                                <p class="text-yellow-700 mt-1">{{ $tourRequest->consent ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="window.print()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Request
                    </button>
                    <a href="mailto:{{ $tourRequest->email }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z" />
                        </svg>
                        Contact Requestor
                    </a>
                </div>
            </div>
        </div>

        {{-- Security Footer --}}
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>🔒 This page contains protected health information (PHI) and is HIPAA compliant.</p>
            <p>Access is logged and monitored. Do not share this URL.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Log access for audit trail
fetch('{{ route("secure.tour-request.log-access", ["token" => $tourRequest->access_token]) }}', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        access_time: new Date().toISOString(),
        user_agent: navigator.userAgent
    })
});
</script>
@endpush
@endsection
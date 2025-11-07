@extends('layouts.secure')

@section('title', 'Secure Inquiry Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Secure Inquiry Details</h1>
                    <p class="text-gray-600">{{ $facility->name ?? 'Bio-Pacific Healthcare' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Security Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-blue-800 mb-1">HIPAA-Compliant Secure Access</p>
                    <p class="text-blue-700">
                        This inquiry contains encrypted personal information. Access is logged for audit purposes.
                        Viewed by: <strong>{{ $viewedBy }}</strong> at {{ $accessedAt->format('M j, Y g:i A') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Inquiry Details -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Contact Inquiry #{{ $inquiry->id }}</h3>
                    <span class="text-sm text-gray-500">
                        Received {{ $inquiry->created_at->format('M j, Y g:i A') }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-6 space-y-6">
                <!-- Contact Information -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <div class="bg-gray-50 rounded-lg p-3 border">
                            <p class="text-gray-900 font-medium">{{ $inquiry->full_name }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="bg-gray-50 rounded-lg p-3 border">
                            <a href="mailto:{{ $inquiry->email }}"
                                class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $inquiry->email }}
                            </a>
                        </div>
                    </div>

                    @if($inquiry->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <div class="bg-gray-50 rounded-lg p-3 border">
                            <a href="tel:{{ $inquiry->phone }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $inquiry->phone }}
                            </a>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facility</label>
                        <div class="bg-gray-50 rounded-lg p-3 border">
                            <p class="text-gray-900 font-medium">{{ $facility->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $inquiry->message }}</p>
                    </div>
                </div>

                <!-- Consent Information -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Consent Given</p>
                            <p class="text-xs text-green-600">Contact permission granted</p>
                        </div>
                    </div>

                    <div class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-800">PHI-Free Confirmed</p>
                            <p class="text-xs text-blue-600">No protected health info included</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-4 border-t border-gray-200">
                    <a href="mailto:{{ $inquiry->email }}?subject=Re: Your Inquiry to {{ $facility->name }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Reply via Email
                    </a>

                    @if($inquiry->phone)
                    <a href="tel:{{ $inquiry->phone }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Call
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>This secure link expires on {{ $inquiry->token_expires_at->format('M j, Y g:i A') }}</p>
            <p>Access logged for HIPAA compliance and audit purposes</p>
        </div>
    </div>
</div>
@endsection
@extends('layouts.secure')

@section('title', 'Secure Job Application Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Job Application Details</h1>
                        <p class="text-sm text-gray-500 mt-1">Application #{{ $jobApplication->id }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            🔒 Secure Access
                        </span>
                        @if($jobApplication->status === 'pending')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Pending Review
                        </span>
                        @elseif($jobApplication->status === 'reviewed')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Reviewed
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="px-6 py-4 bg-blue-50 border-b border-gray-200">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">HIPAA-Compliant Secure Access</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            This information is accessed securely and all access is logged for compliance.
                            @if($jobApplication->expires_at)
                            This link expires on {{ $jobApplication->expires_at->format('M j, Y \a\t g:i A') }}.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Job Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Title Information</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Job Title</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $jobOpening->title ?? 'N/A' }}</p>
                    </div>
                    @if($facility)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Facility</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $facility->name }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium text-gray-500">Application Submitted</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $jobApplication->created_at->format('M j, Y \a\t g:i
                            A') }}</p>
                    </div>
                    @if($jobApplication->viewed_at)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Last Viewed</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $jobApplication->viewed_at->format('M j, Y \a\t g:i A')
                            }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Applicant Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Applicant Information</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">First Name</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $jobApplication->first_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Last Name</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $jobApplication->last_name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-sm text-gray-900 mt-1">
                            <a href="mailto:{{ $jobApplication->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $jobApplication->email }}
                            </a>
                        </p>
                    </div>
                    @if($jobApplication->phone)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-sm text-gray-900 mt-1">
                            <a href="tel:{{ $jobApplication->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $jobApplication->phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cover Letter -->
        @if($jobApplication->cover_letter)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Cover Letter</h2>
            </div>
            <div class="px-6 py-4">
                <div class="prose max-w-none">
                    <p class="text-sm text-gray-900 leading-relaxed whitespace-pre-wrap">{{
                        $jobApplication->cover_letter }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Resume -->
        @if($jobApplication->resume_path)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Resume</h2>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center space-x-3">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Resume File</p>
                        <p class="text-sm text-gray-500">{{ basename($jobApplication->resume_path) }}</p>
                    </div>
                    <div class="flex-1"></div>
                    <a href="{{ route('secure.job-application.download-resume', $jobApplication->access_token) }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Access Log -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Security & Access Log</h2>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div class="text-sm">
                    <span class="font-medium text-gray-500">Application Created:</span>
                    <span class="text-gray-900 ml-2">{{ $jobApplication->created_at->format('M j, Y \a\t g:i A')
                        }}</span>
                </div>
                @if($jobApplication->viewed_at)
                <div class="text-sm">
                    <span class="font-medium text-gray-500">First Viewed:</span>
                    <span class="text-gray-900 ml-2">{{ $jobApplication->viewed_at->format('M j, Y \a\t g:i A')
                        }}</span>
                </div>
                @endif
                @if($jobApplication->expires_at)
                <div class="text-sm">
                    <span class="font-medium text-gray-500">Access Expires:</span>
                    <span class="text-gray-900 ml-2">{{ $jobApplication->expires_at->format('M j, Y \a\t g:i A')
                        }}</span>
                </div>
                @endif
                <div class="text-sm">
                    <span class="font-medium text-gray-500">Current Access:</span>
                    <span class="text-gray-900 ml-2">{{ now()->format('M j, Y \a\t g:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-500 mt-8">
            <p>This page contains protected health information and is HIPAA compliant.</p>
            <p>All access is monitored and logged for security purposes.</p>
        </div>
    </div>
</div>
@endsection
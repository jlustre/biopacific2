@extends('layouts.secure')

@section('title', 'Staff Verification Required')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Staff Verification Required</h1>
                <p class="text-gray-600 mt-2">Please verify your staff credentials to access this secure information</p>
            </div>

            <!-- Security Notice -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-amber-800">HIPAA-Protected Information</h3>
                        <p class="text-sm text-amber-700 mt-1">
                            This link contains protected health information (PHI). Only authorized facility staff may
                            access this information.
                            Unauthorized access is prohibited under HIPAA regulations.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Verification Form -->
            @php
            $formAction = match($type ?? '') {
            'job-application' => route('secure.job-application.verify-staff', $token),
            'inquiry' => route('secure.inquiry.verify-staff', $token),
            default => route('secure.verify-staff', $token)
            };
            @endphp
            <form action="{{ $formAction }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="staff_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Staff Email Address
                    </label>
                    <input type="email" id="staff_email" name="staff_email" required value="{{ old('staff_email') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter your facility email address">
                    @error('staff_email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-sm mt-1">
                        Must be an email address authorized for {{ $facility->name ?? 'this facility' }}
                    </p>
                </div>

                <div>
                    <label for="access_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Access Reason
                    </label>
                    <select id="access_reason" name="access_reason" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Select reason for access</option>
                        @if(($type ?? '') === 'job-application' || ($jobApplication ?? false))
                        <option value="review">Review application</option>
                        <option value="hiring_decision">Hiring decision</option>
                        <option value="background_check">Background check</option>
                        <option value="scheduling">Scheduling interview</option>
                        <option value="processing">Processing application</option>
                        @elseif(($type ?? '') === 'tour-request' || ($tourRequest ?? false))
                        <option value="follow_up">Follow up on request</option>
                        <option value="scheduling">Scheduling tour</option>
                        <option value="processing">Processing request</option>
                        <option value="record_keeping">Record keeping/documentation</option>
                        <option value="compliance_review">Compliance review</option>
                        @elseif(($type ?? '') === 'inquiry' || ($inquiry ?? false))
                        <option value="follow_up">Follow up on inquiry</option>
                        <option value="processing">Processing inquiry</option>
                        <option value="record_keeping">Record keeping/documentation</option>
                        <option value="compliance_review">Compliance review</option>
                        <option value="response_preparation">Response preparation</option>
                        @else
                        <!-- Fallback options -->
                        <option value="follow_up">Follow up</option>
                        <option value="processing">Processing</option>
                        <option value="record_keeping">Record keeping</option>
                        @endif
                    </select>
                    @error('access_reason')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Information Being Accessed:</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        @if($tourRequest ?? false)
                        <p><strong>Type:</strong> Tour Request</p>
                        <p><strong>Facility:</strong> {{ $facility->name ?? 'N/A' }}</p>
                        <p><strong>Submitted:</strong> {{ $tourRequest->created_at->format('M j, Y \a\t g:i A') }}</p>
                        @elseif($inquiry ?? false)
                        <p><strong>Type:</strong> Contact Inquiry</p>
                        <p><strong>Facility:</strong> {{ $facility->name ?? 'N/A' }}</p>
                        <p><strong>Submitted:</strong> {{ $inquiry->created_at->format('M j, Y \a\t g:i A') }}</p>
                        @elseif($jobApplication ?? false)
                        <p><strong>Type:</strong> Job Application</p>
                        <p><strong>Title:</strong> {{ $jobApplication->jobOpening->title ?? 'N/A' }}</p>
                        <p><strong>Submitted:</strong> {{ $jobApplication->created_at->format('M j, Y \a\t g:i A') }}
                        </p>
                        @endif
                        <p><strong>Access Expires:</strong> {{ $expires_at ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- HIPAA Agreement -->
                <div class="flex items-start space-x-3">
                    <input type="checkbox" id="hipaa_agreement" name="hipaa_agreement" required
                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="hipaa_agreement" class="text-sm text-gray-700">
                        I acknowledge that I am an authorized staff member with legitimate business need to access this
                        protected health information.
                        I understand my obligations under HIPAA and facility policies regarding the protection of
                        patient information.
                        <span class="text-red-600">*</span>
                    </label>
                </div>
                @error('hipaa_agreement')
                <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    🔓 Verify Staff Access
                </button>
            </form>

            <!-- Security Information -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h4 class="font-medium text-gray-900 mb-3">🛡️ Security Information</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• All access attempts are logged for HIPAA compliance</li>
                    <li>• Only authorized facility staff may access this information</li>
                    <li>• Your verification will be valid for this browser session only</li>
                    <li>• Unauthorized access may result in disciplinary action</li>
                </ul>
            </div>

            <!-- Contact Support -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Having trouble accessing? Contact your facility administrator or IT support.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
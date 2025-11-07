@extends('layouts.secure')

@section('title', $title ?? 'Access Error')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <!-- Error Icon -->
            <div class="text-center mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833-.192 2.5 1.538 2.5z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $title ?? 'Access Error' }}</h1>
                <p class="text-gray-600 mt-2">{{ $message ?? 'An error occurred while trying to access this secure
                    information.' }}</p>
            </div>

            <!-- Error Details -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
                        <p class="text-sm text-red-700 mt-1">
                            This secure link is not accessible. This may be due to:
                        </p>
                        <ul class="text-sm text-red-700 mt-2 list-disc list-inside space-y-1">
                            <li>The link has expired (secure links are valid for 72 hours)</li>
                            <li>The {{ $type ?? 'information' }} has been removed or archived</li>
                            <li>The link has been used and is no longer valid</li>
                            <li>You may not have permission to access this information</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-4">
                        If you believe this is an error, please contact your facility administrator or try the
                        following:
                    </p>
                </div>

                <div class="space-y-3">
                    <button onclick="window.history.back()"
                        class="w-full bg-gray-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        ← Go Back
                    </button>

                    <a href="{{ url('/') }}"
                        class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors text-center block">
                        🏠 Return to Home Page
                    </a>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h4 class="font-medium text-gray-900 mb-3">🛡️ Security Notice</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• All access attempts are logged for security purposes</li>
                    <li>• Secure links automatically expire for your protection</li>
                    <li>• Only authorized personnel may access protected information</li>
                    <li>• Unauthorized access attempts may be reported</li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Need assistance? Contact your facility administrator or IT support team.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
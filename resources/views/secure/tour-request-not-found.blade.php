@extends('layouts.secure')

@section('title', 'Tour Request Not Found')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Tour Request Not Found
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                The tour request you're looking for doesn't exist or the access link has expired.
            </p>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>This could happen if:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>The secure access link has expired</li>
                            <li>The tour request ID is invalid</li>
                            <li>The request has been deleted</li>
                            <li>You don't have permission to view this request</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-sm text-gray-600 mb-4">
                Need help accessing this tour request?
            </p>
            <div class="space-y-2">
                <a href="{{ url('/') }}"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Return to Homepage
                </a>
                <a href="mailto:support@biopacific.com"
                    class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Contact Support
                </a>
            </div>
        </div>

        <div class="text-center text-xs text-gray-500">
            <p>🔒 This is a secure HIPAA-compliant system.</p>
            <p>All access attempts are logged for security purposes.</p>
        </div>
    </div>
</div>
@endsection
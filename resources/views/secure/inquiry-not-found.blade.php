@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
            <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>

            <h1 class="text-xl font-bold text-gray-900 mb-2">Access Denied</h1>
            <p class="text-gray-600 mb-6">{{ $error }}</p>

            <div class="text-sm text-gray-500 space-y-2">
                <p><strong>Possible reasons:</strong></p>
                <ul class="list-disc list-inside text-left space-y-1">
                    <li>The access link has expired (links are valid for 24 hours)</li>
                    <li>The link has been used and is no longer valid</li>
                    <li>The link was typed incorrectly</li>
                    <li>The inquiry has been deleted</li>
                </ul>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-3">Need a new access link?</p>
                <p class="text-xs text-gray-500">
                    Contact your system administrator or request a new secure link
                    from the admin dashboard.
                </p>
            </div>
        </div>

        <div class="mt-4 text-center text-xs text-gray-500">
            <p>Access attempts are logged for security purposes</p>
        </div>
    </div>
</div>
@endsection
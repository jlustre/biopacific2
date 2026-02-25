@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="bg-white rounded-lg shadow-md max-w-md w-full p-8">
        <div class="text-center">
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl"></i>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">No Facility Assigned</h1>

            <p class="text-gray-600 text-sm mb-6">
                {{ $message }}
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6 text-left">
                <p class="text-blue-900 text-sm">
                    <strong>Your Role:</strong> {{ ucfirst(str_replace('-', ' ', $userRole)) }}
                </p>
            </div>

            <a href="{{ route('user.dashboard') }}"
                class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Return to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
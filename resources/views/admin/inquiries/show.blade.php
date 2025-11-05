@extends('layouts.dashboard', ['title' => 'View Inquiry'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Inquiry Details</h1>
            <p class="text-gray-600">Submitted on {{ $inquiry->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>
        <a href="{{ route('admin.inquiries.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Message</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $inquiry->message }}</p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <p class="text-sm text-gray-900">{{ $inquiry->full_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <p class="text-sm text-gray-900">
                            <a href="mailto:{{ $inquiry->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $inquiry->email }}
                            </a>
                        </p>
                    </div>
                    @if($inquiry->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <p class="text-sm text-gray-900">
                            <a href="tel:{{ $inquiry->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $inquiry->phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Facility Information -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Facility</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility Name</label>
                        <p class="text-sm text-gray-900">{{ $inquiry->facility->name }}</p>
                    </div>
                    @if($inquiry->recipient)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recipient</label>
                        <p class="text-sm text-gray-900">{{ $inquiry->recipient }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Consent Information -->
            @if($inquiry->consent || $inquiry->no_phi)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Consent & Privacy</h3>
                <div class="space-y-3">
                    @if($inquiry->consent)
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-sm text-gray-700">Consent given for communication</span>
                    </div>
                    @endif
                    @if($inquiry->no_phi)
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                        <span class="text-sm text-gray-700">Confirmed no PHI in message</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    <a href="mailto:{{ $inquiry->email }}?subject=Re: Your Inquiry to {{ $inquiry->facility->name }}"
                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-reply mr-2"></i>Reply via Email
                    </a>
                    <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="POST" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this inquiry?')"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash mr-2"></i>Delete Inquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
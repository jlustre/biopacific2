@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @php
                    $facilityId = request('facility');
                    $backUrl = $facilityId
                    ? route('admin.facilities.webcontents.careers', ['facility' => $facilityId])
                    : route('admin.dashboard.index');
                    @endphp
                    <a href="{{ $backUrl }}" class="text-gray-500 hover:text-gray-700" aria-label="Back">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Description Templates</h1>
                        <p class="text-gray-600">Manage job description templates used in Career Management</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <livewire:admin.description-templates-manager />
    </div>
</div>
@endsection
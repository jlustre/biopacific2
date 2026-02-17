@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Careers Management</h1>
                        <p class="text-gray-600">Manage career opportunities for your facilities</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Facility Selection Dropdown -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.facilities.webcontents.careers') }}">
                <div class="mb-6">
                    <label for="facilitySelect" class="block text-sm font-semibold text-gray-700 mb-3">Select
                        Facility:</label>
                    <div class="relative w-full max-w-md">
                        <select id="facilitySelect" name="facility_id"
                            class="w-full pl-12 pr-12 py-4 border-2 border-gray-200 rounded-xl bg-white text-gray-700 font-medium focus:ring-3 focus:ring-teal-200 focus:border-teal-500 hover:border-gray-300 transition-all duration-200 appearance-none cursor-pointer shadow-sm text-sm sm:text-base"
                            onchange="this.form.submit()">
                            <option value="" class="text-gray-500">Choose a facility...</option>
                            @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}" @if(($facilityId ?? null)==$facility->id) selected
                                @endif>
                                {{ $facility->name }} - {{ $facility->city ?? 'N/A' }}, {{ $facility->state ?? 'N/A' }}
                            </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400 transition-colors duration-200" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-building text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 max-w-md">Select a facility to view and manage its career
                        opportunities</p>


                </div>
            </form>
        </div>

        <!-- Job Openings Management with Livewire -->
        @if(($facilityId ?? null))
        @php
        $facility = \App\Models\Facility::find($facilityId);
        @endphp

        @if($facility)
        <livewire:job-openings-form :facility="$facility" />
        @endif
        @else
        <div class="text-center text-gray-500 py-12 bg-white rounded-lg shadow">
            <i class="fas fa-building text-6xl mb-4"></i>
            <p class="text-xl">Please select a facility to manage job openings</p>
        </div>
        @endif
    </div>
</div>
@endsection
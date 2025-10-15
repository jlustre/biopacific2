@extends('layouts.dashboard', ['title' => 'Admin Dashboard'])

@section('content')
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <!-- Dashboard Header -->
    @include('admin.dashboard.partials.header')

    <!-- Dashboard Stats -->
    @include('admin.dashboard.partials.stats')

    <!-- Facilities by State -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div class="p-6 pb-2 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Our Facilities</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage all your facilities</p>
            </div>
        </div>
        <div class="p-6 pt-2">
            @if($facilitiesByState->count() > 0)
            <div class="space-y-2">
                @foreach($facilitiesByState as $state => $stateFacilities)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($stateFacilities as $facility)
                            @include('admin.dashboard.partials.facility_card', ['facility' => $facility])
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <flux:icon name="building-office-2" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No facilities found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Get started by adding your first facility.</p>
                <flux:button href="{{ route('admin.facilities.create') }}" icon="plus" variant="primary">
                    Add Your First Facility
                </flux:button>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    @include('admin.dashboard.partials.quick_actions')
</div>
@endsection
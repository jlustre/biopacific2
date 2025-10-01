@extends('layouts.dashboard')

@section('content')
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <!-- Dashboard Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your facilities and website content</p>
        </div>
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('admin.facilities.create') }}" icon="plus" variant="primary">
                Add New Facility
            </flux:button>
        </div>
    </div>

    <!-- Dashboard Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <flux:icon name="building-office-2" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Facilities</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $facilities->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <flux:icon name="check-circle" class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Facilities</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $facilities->where('is_active',
                        true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <flux:icon name="map-pin" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">States Covered</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $facilitiesByState->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <flux:icon name="globe-alt" class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Domains</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{
                        $facilities->pluck('domain')->unique()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Facilities by State -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Our Facilities</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage all your facilities</p>
            </div>
        </div>
        <div class="p-6">
            @if($facilitiesByState->count() > 0)
            <div class="space-y-6">
                @foreach($facilitiesByState as $state => $stateFacilities)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                    {{-- <div
                        class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-base font-medium text-gray-900 dark:text-white">
                            <span class="text-sm text-gray-500 dark:text-gray-400">({{ $stateFacilities->count() }}
                                facilities)</span>
                        </h3>
                    </div> --}}
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($stateFacilities as $facility)
                            <div
                                class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm hover:shadow-lg transition group overflow-hidden">
                                <!-- Facility ID badge (top left) -->
                                <div class="absolute top-2 left-2 z-10">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-200 text-yellow-900 shadow">
                                        ID: {{ $facility->id }}
                                    </span>
                                </div>
                                <!-- Active/Inactive badge (top right, closer to edge) -->
                                <div class="absolute top-2 right-2 z-10">
                                    @if($facility->is_active)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-800 dark:bg-green-900 dark:text-green-200 shadow">
                                        <flux:icon name="check-circle" class="w-4 h-4 mr-1" /> Active
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 shadow">
                                        <flux:icon name="x-circle" class="w-4 h-4 mr-1" /> Inactive
                                    </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 my-2">
                                    <div class="flex-shrink-0 bg-primary/10 rounded-xl p-1">
                                        <flux:icon name="building-office-2"
                                            class="w-8 h-8 text-primary text-teal-500" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{
                                            $facility->name }}</h4>
                                        @if($facility->tagline)
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">{{
                                            Str::limit($facility->tagline, 60) }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 mb-2 text-center">
                                    @if($facility->beds)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <flux:icon name="user-group" class="w-4 h-4 mr-1" /> {{ $facility->beds }}
                                        beds
                                    </span>
                                    @endif

                                    @if($facility->city || $facility->state)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <flux:icon name="map-pin" class="w-4 h-4 mr-1" />
                                        {{ $facility->city }}@if($facility->city && $facility->state), @endif{{
                                        $facility->state }}
                                    </span>
                                    @endif
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex space-x-2">
                                        <flux:button size="xs" href="{{ route('admin.facilities.edit', $facility) }}"
                                            icon="pencil-square" variant="ghost">
                                            Edit
                                        </flux:button>
                                        <flux:button size="xs" href="{{ route('facility.public', $facility) }}"
                                            icon="eye" variant="ghost">
                                            View
                                        </flux:button>
                                    </div>
                                    @if($facility->domain)
                                    <flux:button size="xs" href="http://{{ $facility->domain }}" target="_blank"
                                        icon="arrow-top-right-on-square" variant="ghost">
                                        Visit
                                    </flux:button>
                                    @endif
                                </div>
                            </div>
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
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:button href="{{ route('admin.facilities.index') }}" icon="building-office-2" variant="outline"
                class="justify-start">
                <div class="text-left">
                    <div class="font-medium">Manage Facilities</div>
                    <div class="text-sm text-gray-500">View, edit, and organize all facilities</div>
                </div>
            </flux:button>

            <flux:button href="{{ route('admin.facilities.create') }}" icon="plus" variant="outline"
                class="justify-start">
                <div class="text-left">
                    <div class="font-medium">Add New Facility</div>
                    <div class="text-sm text-gray-500">Create a new facility profile</div>
                </div>
            </flux:button>

            <flux:button icon="cog" variant="outline" class="justify-start">
                <div class="text-left">
                    <div class="font-medium">System Settings</div>
                    <div class="text-sm text-gray-500">Configure global settings</div>
                </div>
            </flux:button>
        </div>
    </div>
</div>
@endsection
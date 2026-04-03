
@extends('layouts.dashboard', ['title' => 'Admin Dashboard'])

@section('content')
<div class="flex flex-col gap-8 w-full max-w-7xl mx-auto py-8">
    <!-- Modern Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">Welcome back, {{ auth()->user()->name ?? 'Admin' }}!</h1>
            <p class="text-gray-500 dark:text-gray-400">Here's an overview of your administration panel.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 text-sm font-medium">
                <flux:icon name="calendar" class="w-4 h-4 mr-1" /> {{ now()->format('F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Interactive Stats -->
    <div class="px-4">
        @include('admin.dashboard.partials.stats')
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
        <!-- Facilities Overview -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow border border-blue-200 dark:border-gray-700 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon name="building-office-2" class="h-7 w-7 text-blue-600 dark:text-blue-400" />
                    <span class="text-lg font-semibold text-blue-900 dark:text-blue-200">Facilities</span>
                </div>
                <p class="text-4xl font-bold text-gray-900 dark:text-white mb-1">{{ $facilities->count() }}</p>
                <p class="text-gray-600 dark:text-gray-400">Total registered facilities</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.facilities.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition font-medium">
                    Manage Facilities <flux:icon name="arrow-right" class="w-4 h-4 ml-2" />
                </a>
            </div>
        </div>
        <!-- Active Facilities -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow border border-green-200 dark:border-gray-700 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon name="check-circle" class="h-7 w-7 text-green-600 dark:text-green-400" />
                    <span class="text-lg font-semibold text-green-900 dark:text-green-200">Active</span>
                </div>
                <p class="text-4xl font-bold text-gray-900 dark:text-white mb-1">{{ $facilities->where('is_active', true)->count() }}</p>
                <p class="text-gray-600 dark:text-gray-400">Currently active facilities</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.facilities.index', ['status' => 'active']) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition font-medium">
                    View Active <flux:icon name="arrow-right" class="w-4 h-4 ml-2" />
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4">
        @include('admin.dashboard.partials.quick_actions')
    </div>

        <!-- Recent Activity / Audit Log -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 mt-6 mx-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
            <a href="#" class="text-blue-600 hover:underline text-sm">View all</a>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentActivities ?? [] as $activity)
                <div class="py-3 flex items-center gap-3">
                    <flux:icon name="clock" class="w-5 h-5 text-gray-400" />
                    <div class="flex-1">
                        <div class="text-gray-900 dark:text-white font-medium">{{ $activity->description }}</div>
                        <div class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-gray-400">No recent activity to display.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
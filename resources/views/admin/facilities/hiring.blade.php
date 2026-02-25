@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto py-8 px-4">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $facility->name }} - Hiring Management</h1>
        <p class="text-gray-600 mt-2">Manage job openings, applicants, and new hire onboarding</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Active Positions</div>
            <div class="text-3xl font-bold text-teal-600 mt-2">{{ $stats['open_openings'] ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">of {{ $stats['total_openings'] ?? 0 }} openings</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Total Applicants</div>
            <div class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['total_applicants'] ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $stats['pending_applications'] ?? 0 }} pending</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Pre-Employment Submitted</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $stats['submitted_preemployment'] ?? 0 }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-semibold">Onboarding Complete</div>
            <div class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['completed_preemployment'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6" x-data="{ activeTab: 'overview' }">
        <div class="flex border-b border-gray-200 space-x-8">
            <button @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Overview
            </button>
            <button @click="activeTab = 'openings'"
                :class="activeTab === 'openings' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Job Openings
            </button>
            <button @click="activeTab = 'applicants'"
                :class="activeTab === 'applicants' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Applicants
            </button>
            <button @click="activeTab = 'preemployment'"
                :class="activeTab === 'preemployment' ? 'border-b-2 border-teal-600 text-teal-600' : 'text-gray-600 hover:text-gray-900'"
                class="pb-4 font-semibold transition">
                Pre-Employment
            </button>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" class="mt-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Hiring Pipeline Overview</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Applicants -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Applicants</h3>
                        <div class="space-y-3">
                            @forelse($applications->take(5) as $app)
                            <div class="border border-gray-200 rounded p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $app->first_name }} {{ $app->last_name
                                            }}</p>
                                        <p class="text-sm text-gray-600">{{ $app->jobOpening?->title ?? 'Unknown
                                            Position' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $app->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($app->status === 'rejected') bg-red-100 text-red-800
                                        @elseif($app->status === 'shortlisted') bg-green-100 text-green-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $app->status ?? 'pending')) }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.job-applications.show', $app) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                                        Review
                                    </a>
                                    <a href="{{ route('admin.job-applications.show', $app) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Change Status
                                    </a>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm">No applicants yet</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Pre-Employment -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Pre-Employment Submissions</h3>
                        <div class="space-y-3">
                            @forelse($preEmploymentApplications->take(5) as $pre)
                            <div class="border border-gray-200 rounded p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $pre->first_name }} {{ $pre->last_name
                                            }}</p>
                                        <p class="text-sm text-gray-600">{{ $pre->position_applied_for }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $pre->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($pre->status === 'submitted') bg-blue-100 text-blue-800
                                        @elseif($pre->status === 'completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($pre->status ?? 'draft') }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $pre->id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                                        Review
                                    </a>
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $pre->id]) }}"
                                        class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Change Status
                                    </a>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm">No pre-employment submissions yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Openings Tab -->
        <div x-show="activeTab === 'openings'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Job Openings</h2>
                    <a href="#" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 transition">
                        <i class="fas fa-plus mr-2"></i> New Opening
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Department</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applications</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Posted Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($jobOpenings as $opening)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $opening->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $opening->department ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-semibold @if($opening->active) bg-green-100 text-green-800 @else bg-gray-100 text-gray-800 @endif">
                                        @if($opening->active) Active @else Closed @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{
                                    $opening->applications->count() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $opening->posted_at?->format('M d, Y') ??
                                    'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="#" class="text-teal-600 hover:text-teal-700 font-semibold">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No job openings found. <a href="#"
                                        class="text-teal-600 hover:text-teal-700 font-semibold">Create one</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Applicants Tab -->
        <div x-show="activeTab === 'applicants'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">All Applicants</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applied Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($applications as $app)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $app->first_name }} {{
                                    $app->last_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $app->jobOpening?->title ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($app->status === 'rejected') bg-red-100 text-red-800
                                        @elseif($app->status === 'shortlisted') bg-green-100 text-green-800
                                        @elseif($app->status === 'interview') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $app->status ?? 'pending')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $app->email ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $app->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="#" class="text-teal-600 hover:text-teal-700 font-semibold">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No applications found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pre-Employment Tab -->
        <div x-show="activeTab === 'preemployment'" class="mt-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Pre-Employment Applications</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Submitted</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($preEmploymentApplications as $pre)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $pre->first_name }} {{
                                    $pre->last_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $pre->position_applied_for ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($pre->status === 'submitted') bg-blue-100 text-blue-800
                                        @elseif($pre->status === 'completed') bg-green-100 text-green-800
                                        @elseif($pre->status === 'returned') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($pre->status ?? 'draft') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $pre->email ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $pre->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $facility->id, 'application' => $pre->id]) }}"
                                        class="text-teal-600 hover:text-teal-700 font-semibold">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No pre-employment applications found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
@extends('layouts.dashboard', ['title' => 'Careers Management'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Careers Management</h1>
        <p class="text-gray-600 mt-2">Manage job openings and applications shown on facility websites.</p>
    </div>
    @if($facility ?? null)
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.facility.job_openings', $facility) }}"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-sm transition">
            <i class="fas fa-plus mr-2"></i> New Job Opening
        </a>
        <a href="{{ route('admin.facilities.webcontents.careers.templates', ['facility_id' => $facility->id]) }}"
            class="inline-flex items-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg font-semibold transition">
            <i class="fas fa-file-alt mr-2"></i> Templates
        </a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-center">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    @if(isset($scopedFacility) && $scopedFacility)
    <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
        Showing careers for <strong>{{ $scopedFacility->name }}</strong> only.
    </div>
    @endif

    @if(!empty($canFilterFacilities))
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.facilities.webcontents.careers') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Facility</label>
                <select name="facility_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    onchange="this.form.submit()">
                    <option value="">Choose a facility…</option>
                    @foreach($facilities as $f)
                    <option value="{{ $f->id }}" {{ (string) ($facilityId ?? '') === (string) $f->id ? 'selected' : '' }}>
                        {{ $f->name }} — {{ $f->city ?? 'N/A' }}, {{ $f->state ?? 'N/A' }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    @if(!($facility ?? null))
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <i class="fas fa-briefcase text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Select a facility</h3>
        <p class="text-gray-500 max-w-md mx-auto">Choose a facility above to view job openings and manage applications.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center">
                <i class="fas fa-briefcase text-slate-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total openings</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-door-open text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Open positions</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['open'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-teal-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-teal-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active listings</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-users text-amber-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Applications</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['applications'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.facilities.webcontents.careers') }}"
            class="flex flex-col lg:flex-row lg:items-end gap-3">
            <input type="hidden" name="facility_id" value="{{ $facility->id }}">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search title, department, or description…"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="w-full lg:w-36">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="filled" {{ request('status') === 'filled' ? 'selected' : '' }}>Filled</option>
                </select>
            </div>
            <div class="w-full lg:w-36">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Listing</label>
                <select name="active" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-semibold">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.facilities.webcontents.careers', ['facility_id' => $facility->id]) }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($jobOpenings->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <i class="fas fa-briefcase text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No job openings found</h3>
        <p class="text-gray-500 max-w-md mx-auto mb-6">
            @if(request()->hasAny(['search', 'status', 'active']))
            Try adjusting your filters, or create a new job opening.
            @else
            Get started by posting the first career opportunity for {{ $facility->name }}.
            @endif
        </p>
        <a href="{{ route('admin.facility.job_openings', $facility) }}"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold">
            <i class="fas fa-plus mr-2"></i> New Job Opening
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($jobOpenings as $job)
        @php
            $statusColors = [
                'open' => 'bg-green-500',
                'closed' => 'bg-gray-600',
                'filled' => 'bg-blue-500',
            ];
            $statusClass = $statusColors[$job->status] ?? 'bg-gray-500';
        @endphp
        <article class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
            <div class="p-5 flex flex-col flex-1">
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold text-white {{ $statusClass }}">
                        {{ ucfirst($job->status ?? 'open') }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $job->active ? 'bg-teal-100 text-teal-800' : 'bg-gray-200 text-gray-600' }}">
                        {{ $job->active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($job->applications_count > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        {{ $job->applications_count }} {{ \Illuminate\Support\Str::plural('application', $job->applications_count) }}
                    </span>
                    @endif
                </div>

                <h3 class="text-lg font-bold text-gray-900 leading-snug mb-2">{{ $job->title }}</h3>

                <div class="space-y-1.5 text-sm text-gray-600 mb-4">
                    @if($job->department)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-sitemap text-gray-400 w-4"></i>
                        <span>{{ $job->department }}</span>
                    </div>
                    @endif
                    @if($job->employment_type)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock text-gray-400 w-4"></i>
                        <span>{{ $job->employment_type }}</span>
                    </div>
                    @endif
                    @if($job->posted_at)
                    <div class="flex items-center gap-2">
                        <i class="far fa-calendar-alt text-gray-400 w-4"></i>
                        <span>Posted {{ $job->posted_at->format('M j, Y') }}</span>
                    </div>
                    @endif
                </div>

                @if($job->description)
                <p class="text-sm text-gray-500 line-clamp-3 mb-4 flex-1">
                    {{ \Illuminate\Support\Str::limit(strip_tags($job->description), 140) }}
                </p>
                @endif

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-100 mt-auto">
                    <a href="{{ route('admin.facility.job_openings.show', [$facility, $job]) }}"
                        class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition min-w-[4.5rem]">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                    <a href="{{ route('admin.facility.job_openings.edit', [$facility, $job]) }}"
                        class="flex-1 text-center px-3 py-2 text-sm font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg transition min-w-[4.5rem]">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    @if($job->applications_count > 0)
                    <a href="{{ route('admin.facilities.webcontents.careers.applications', $job) }}"
                        class="flex-1 text-center px-3 py-2 text-sm font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition min-w-[4.5rem]">
                        <i class="fas fa-inbox mr-1"></i> Apps
                    </a>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>
    @endif
    @endif
</div>
@endsection

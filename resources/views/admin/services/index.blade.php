@extends('layouts.dashboard', ['title' => 'Services Management'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Services Management</h1>
        <p class="text-gray-600 mt-2">Manage services displayed on facility websites.</p>
    </div>
    <a href="{{ route('admin.services.create', $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : []) }}"
        class="inline-flex items-center justify-center bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-sm transition">
        <i class="fas fa-plus mr-2"></i> Add Service
    </a>
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
        Showing global services and services for <strong>{{ $scopedFacility->name }}</strong>.
        Global services are read-only here; assign them from the facility editor.
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center">
                <i class="fas fa-concierge-bell text-slate-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total services</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-globe text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Global</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['global'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-star text-amber-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Featured</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['featured'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.services.index') }}"
            class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search name or description…"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="w-full lg:w-32">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Scope</label>
                <select name="scope" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="global" {{ request('scope') === 'global' ? 'selected' : '' }}>Global</option>
                    <option value="local" {{ request('scope') === 'local' ? 'selected' : '' }}>Facility</option>
                </select>
            </div>
            <div class="w-full lg:w-32">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            @if(!empty($canFilterFacilities))
            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Facility view</label>
                <select name="facility_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All services</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ (string) ($filterFacilityId ?? '') === (string) $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-semibold">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.services.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($services->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <i class="fas fa-concierge-bell text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No services found</h3>
        <p class="text-gray-500 max-w-md mx-auto mb-6">
            @if(request()->hasAny(['search', 'scope', 'status', 'facility_id']))
            Try adjusting your filters, or add a new service.
            @else
            Get started by creating your first service offering.
            @endif
        </p>
        <a href="{{ route('admin.services.create', $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : []) }}"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold">
            <i class="fas fa-plus mr-2"></i> Add Service
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($services as $service)
        @php
            $canEdit = empty($scopedFacilityId) || (!$service->is_global);
            $assignedFacilities = $service->facilities->pluck('name');
            if ($filterFacility ?? null) {
                $isAssigned = $service->facilities->contains('id', $filterFacility->id);
            }
        @endphp
        <article class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
            <div class="relative h-36 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                @if($service->image)
                <img src="{{ str_starts_with($service->image, 'http') ? $service->image : asset('storage/' . $service->image) }}"
                    alt="" class="w-full h-full object-cover">
                @elseif($service->icon)
                <div class="text-4xl text-teal-600 p-4">{!! $service->icon !!}</div>
                @else
                <i class="fas fa-hand-holding-heart text-4xl text-slate-300"></i>
                @endif
                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $service->is_active ? 'bg-green-500 text-white' : 'bg-gray-600 text-white' }}">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($service->is_global)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-500 text-white">Global</span>
                    @else
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Facility</span>
                    @endif
                    @if($service->is_featured)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white">Featured</span>
                    @endif
                </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="text-lg font-bold text-gray-900 leading-snug mb-2">{{ $service->name }}</h3>
                @if($service->short_description)
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $service->short_description }}</p>
                @endif
                <div class="space-y-1 text-xs text-gray-500 mb-4 flex-1">
                    @if($service->order !== null)
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-sort text-gray-400"></i>
                        <span>Display order: {{ $service->order }}</span>
                    </div>
                    @endif
                    @if(isset($isAssigned))
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-link text-gray-400"></i>
                        <span class="{{ $isAssigned ? 'text-teal-700 font-medium' : '' }}">
                            {{ $isAssigned ? 'Assigned to ' . $filterFacility->name : 'Not assigned to ' . $filterFacility->name }}
                        </span>
                    </div>
                    @elseif($assignedFacilities->isNotEmpty())
                    <div class="flex items-start gap-1.5">
                        <i class="fas fa-building text-gray-400 mt-0.5"></i>
                        <span class="line-clamp-2">{{ $assignedFacilities->join(', ') }}</span>
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                    @if($canEdit)
                    <a href="{{ route('admin.services.edit', $service) }}"
                        class="flex-1 text-center px-3 py-2 text-sm font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="inline"
                        onsubmit="return confirm('Delete this service?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition"
                            title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @else
                    <span class="flex-1 text-center px-3 py-2 text-xs text-gray-400 italic">Global (read-only)</span>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>
    @endif
</div>
@endsection

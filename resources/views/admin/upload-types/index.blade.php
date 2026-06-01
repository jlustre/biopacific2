@extends('layouts.dashboard', ['title' => 'Documents Management'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Documents Management</h1>
            <p class="text-sm text-slate-500">Manage upload/document types used across employee files.</p>
        </div>
        <a href="{{ route('admin.upload-types.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            <i class="fa-solid fa-plus mr-2"></i> New Document Type
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.upload-types.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="grid gap-3 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or description"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Department</label>
                <select name="department_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) request('department_id') === (string) $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Requires Expiry</label>
                <select name="requires_expiry" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">All</option>
                    <option value="1" {{ request('requires_expiry') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('requires_expiry') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">License & Certifications</label>
                <select name="is_license_or_certification" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">All</option>
                    <option value="1" {{ request('is_license_or_certification') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('is_license_or_certification') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
                <a href="{{ route('admin.upload-types.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    @php
        $activeSearch = trim((string) request('search', ''));
        $activeDepartment = $departments->firstWhere('id', (int) request('department_id'));
        $activeRequiresExpiry = request('requires_expiry');
        $activeLicenseCertification = request('is_license_or_certification');
        $hasActiveFilters = $activeSearch !== ''
            || request()->filled('department_id')
            || $activeRequiresExpiry === '0'
            || $activeRequiresExpiry === '1'
            || $activeLicenseCertification === '0'
            || $activeLicenseCertification === '1';
    @endphp

    @if($hasActiveFilters)
        <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-3">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active filters</span>
            @if($activeSearch !== '')
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Search: {{ $activeSearch }}</span>
            @endif
            @if($activeDepartment)
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Department: {{ $activeDepartment->name }}</span>
            @endif
            @if($activeRequiresExpiry === '1' || $activeRequiresExpiry === '0')
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Requires Expiry: {{ $activeRequiresExpiry === '1' ? 'Yes' : 'No' }}</span>
            @endif
            @if($activeLicenseCertification === '1' || $activeLicenseCertification === '0')
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">License & Certifications: {{ $activeLicenseCertification === '1' ? 'Yes' : 'No' }}</span>
            @endif
            <a href="{{ route('admin.upload-types.index') }}" class="ml-auto rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">Clear all</a>
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[860px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Requires Expiry</th>
                    <th class="px-4 py-3">License/Certification</th>
                    <th class="px-4 py-3">Departments</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($uploadTypes as $uploadType)
                    @php
                        $departmentIds = collect($uploadType->department_ids ?? [])->map(fn ($id) => (int) $id);
                        $departmentNames = $departments->whereIn('id', $departmentIds)->pluck('name')->values();
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $uploadType->name }}</td>
                        <td class="px-4 py-3">{{ $uploadType->requires_expiry ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3">{{ $uploadType->is_license_or_certification ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($departmentNames->isEmpty())
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">All departments</span>
                            @else
                                {{ $departmentNames->join(', ') }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.upload-types.edit', $uploadType) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50" title="Edit" aria-label="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @if(auth()->user()?->hasRole(['admin', 'super-admin']))
                                    <form method="POST" action="{{ route('admin.upload-types.destroy', $uploadType) }}" onsubmit="return confirm('Delete this document type?');" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" title="Delete" aria-label="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">No document types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $uploadTypes->links() }}
    </div>
</div>
@endsection

@extends('layouts.dashboard', ['title' => 'Documents Management'])

@section('content')
@php
    $tab = $tab ?? 'types';
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Documents Management</h1>
            <p class="text-sm text-slate-500">Manage document types and bulk-assign employee file documents to positions.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($tab === 'types' && auth()->user()?->hasRole(['admin', 'super-admin']))
                <form method="POST" action="{{ route('admin.upload-types.run-seeder') }}"
                      onsubmit="return confirm('Apply document types from seeder files to the database?\n\nGeneral types come from database/seeders/data/documents_management_general_types.php.\nEmployee file items are synced from the Employee file items tab.');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-rotate mr-2"></i> Apply seeder
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.upload-types.sync-seeder') }}"
                      onsubmit="return confirm('Export general document types into database/seeders/data/documents_management_general_types.php?\n\nThis overwrites that file. Commit it to git so migrate:fresh --seed restores your types.\n\nEmployee file items are not exported here (use Update items seeder on the Employee file items tab).');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                        <i class="fa-solid fa-database mr-2"></i> Update seeder
                    </button>
                </form>
            @endif
            @if($tab === 'items' && auth()->user()?->hasRole(['admin', 'super-admin']))
                <form method="POST" action="{{ route('admin.checklist-items.sync-seeder') }}"
                      onsubmit="return confirm('Export PART A–D employee file items into database/seeders/data/checklist_items.php?\n\nThis overwrites that file. Commit it to git so migrate:fresh --seed restores your items.\n\nPART E orientation items are not included.');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                        <i class="fa-solid fa-database mr-2"></i> Update items seeder
                    </button>
                </form>
            @endif
            @if($tab === 'types')
                <a href="{{ route('admin.upload-types.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    <i class="fa-solid fa-plus mr-2"></i> New document type
                </a>
            @else
                <a href="{{ route('admin.checklist-items.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    <i class="fa-solid fa-plus mr-2"></i> New employee file item
                </a>
            @endif
        </div>
    </div>

    <div class="flex gap-1 rounded-xl border border-slate-200 bg-slate-100 p-1">
        <a href="{{ route('admin.upload-types.index', ['tab' => 'items']) }}"
           class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition {{ $tab === 'items' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            Position assignments
        </a>
        <a href="{{ route('admin.upload-types.index', ['tab' => 'types']) }}"
           class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition {{ $tab === 'types' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            All document types
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    @if($tab === 'items')
        @include('admin.upload-types.partials.employee-file-items-tab')
    @else
        @php
            $positionLookup = $positions->keyBy('position_id');
            $hasEmployeeFileRows = $uploadTypes && $uploadTypes->contains(fn ($type) => $type->checklist_item_id);
        @endphp

        @if($hasEmployeeFileRows)
            @include('admin.upload-types.partials.bulk-assign-positions')
        @endif

        <form method="GET" action="{{ route('admin.upload-types.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4">
            <input type="hidden" name="tab" value="types">
            <div class="grid gap-3 md:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or description"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Employee file section</label>
                    <select name="checklist_section" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                        <option value="">All sections</option>
                        @foreach($employeeFileSections as $section)
                            <option value="{{ $section }}" {{ request('checklist_section') === $section ? 'selected' : '' }}>{{ $section }}</option>
                        @endforeach
                        <option value="general" {{ request('checklist_section') === 'general' ? 'selected' : '' }}>General document types</option>
                    </select>
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
                    <a href="{{ route('admin.upload-types.index', ['tab' => 'types']) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </div>
        </form>

        @php
            $activeSearch = trim((string) request('search', ''));
            $activeDepartment = $departments->firstWhere('id', (int) request('department_id'));
            $activeRequiresExpiry = request('requires_expiry');
            $activeLicenseCertification = request('is_license_or_certification');
            $activeChecklistSection = request('checklist_section');
            $hasActiveFilters = $activeSearch !== ''
                || request()->filled('department_id')
                || request()->filled('checklist_section')
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
                @if($activeChecklistSection !== null && $activeChecklistSection !== '')
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        Section: {{ $activeChecklistSection === 'general' ? 'General document types' : $activeChecklistSection }}
                    </span>
                @endif
                @if($activeRequiresExpiry === '1' || $activeRequiresExpiry === '0')
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Requires Expiry: {{ $activeRequiresExpiry === '1' ? 'Yes' : 'No' }}</span>
                @endif
                @if($activeLicenseCertification === '1' || $activeLicenseCertification === '0')
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">License & Certifications: {{ $activeLicenseCertification === '1' ? 'Yes' : 'No' }}</span>
                @endif
                <a href="{{ route('admin.upload-types.index', ['tab' => 'types']) }}" class="ml-auto rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">Clear all</a>
            </div>
        @endif

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        @if($hasEmployeeFileRows)
                            <th class="px-4 py-3 text-center">
                                <input type="checkbox" id="selectAllChecklistItems" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Select all employee file documents on this page">
                            </th>
                        @endif
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Employee file section</th>
                        @if($hasEmployeeFileRows)
                            <th class="px-4 py-3">Applicable positions</th>
                        @endif
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
                            $positionLabels = collect($uploadType->checklistItem?->position_ids ?? [])
                                ->map(fn ($positionId) => $positionLookup->get($positionId))
                                ->filter()
                                ->map(fn ($position) => $position->title)
                                ->values();
                        @endphp
                        <tr>
                            @if($hasEmployeeFileRows)
                                <td class="px-4 py-3 text-center">
                                    @if($uploadType->checklist_item_id)
                                        <input type="checkbox" name="checklist_item_ids[]" value="{{ $uploadType->checklist_item_id }}" form="bulkPositionForm"
                                            class="checklist-item-selector h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $uploadType->name }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($uploadType->checklist_section)
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $uploadType->checklist_section }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">General</span>
                                @endif
                            </td>
                            @if($hasEmployeeFileRows)
                                <td class="px-4 py-3">
                                    @if($uploadType->checklist_item_id)
                                        @if ($positionLabels->isEmpty())
                                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">All positions</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($positionLabels as $label)
                                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">Set on Positions page</span>
                                    @endif
                                </td>
                            @endif
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
                                    <a href="{{ route('admin.upload-types.show', $uploadType) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900" title="View" aria-label="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($uploadType->isEmployeeFileChecklistType() && $uploadType->checklist_item_id)
                                        <a href="{{ route('admin.checklist-items.edit', $uploadType->checklist_item_id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50" title="Edit employee file item" aria-label="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.upload-types.edit', $uploadType) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50" title="Edit" aria-label="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()?->hasRole(['admin', 'super-admin']) && ! $uploadType->isEmployeeFileChecklistType())
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
                            <td colspan="{{ $hasEmployeeFileRows ? 8 : 6 }}" class="px-4 py-10 text-center text-slate-500">No document types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $uploadTypes->links() }}
        </div>
    @endif

    @if(($tab ?? 'items') === 'items' || ! empty($hasEmployeeFileRows))
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const selectAll = document.getElementById('selectAllChecklistItems');
                const rowSelectors = Array.from(document.querySelectorAll('.checklist-item-selector'));

                if (!selectAll || rowSelectors.length === 0) {
                    return;
                }

                selectAll.addEventListener('change', () => {
                    rowSelectors.forEach((checkbox) => {
                        checkbox.checked = selectAll.checked;
                    });
                });

                rowSelectors.forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        selectAll.checked = rowSelectors.length > 0 && rowSelectors.every((item) => item.checked);
                    });
                });
            });
        </script>
        @endpush
    @endif
</div>
@endsection

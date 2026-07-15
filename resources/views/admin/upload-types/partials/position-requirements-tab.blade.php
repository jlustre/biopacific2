@php
    $positionsByDepartment = $positions->groupBy(fn ($position) => $position->department?->name ?? 'No department');
    $selectedDepartmentId = request('department_id');
    $overviewSearch = trim((string) request('search', ''));
    $hasOverviewFilters = $overviewSearch !== '' || filled($selectedDepartmentId);
@endphp

<div class="rounded-2xl border border-brand-200 bg-gradient-to-br from-brand-50/80 to-white p-5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-2xl">
            <h2 class="text-lg font-bold text-slate-900">Position document requirements</h2>
            <p class="mt-1 text-sm text-slate-600">
                Assign <strong>general compliance documents</strong> (I-9, licenses, CPR, etc.) to positions.
                Use presets for common groups like all staff, nurses, or CNAs — or pick individual documents and positions.
            </p>
            <p class="mt-2 text-xs text-slate-500">
                Employee file items (PART A–D) are managed on the
                <a href="{{ route('admin.upload-types.index', ['tab' => 'items']) }}" class="font-semibold text-brand-600 hover:text-brand-700">Employee file items</a> tab.
                Per-position fine-tuning is also available on each
                <a href="{{ route('admin.positions.index') }}" class="font-semibold text-brand-600 hover:text-brand-700">position record</a>.
            </p>
        </div>
        @if(auth()->user()?->hasRole(\App\Support\MemberPortalLayout::documentsManagementRoles()))
        <form method="POST" action="{{ route('admin.position-document-requirements.apply-defaults') }}"
              onsubmit="return confirm('Apply default document requirement sets from the system configuration?\n\nPositions that already have requirements will be skipped unless you uncheck that option.');"
              class="shrink-0 rounded-xl border border-slate-200 bg-white p-4">
            @csrf
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quick setup</p>
            <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="only_when_empty" value="1" checked class="h-4 w-4 rounded border-slate-300 text-brand-600">
                Skip positions that already have requirements
            </label>
            <label class="mt-1 flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="include_unmapped" value="1" checked class="h-4 w-4 rounded border-slate-300 text-brand-600">
                Apply &ldquo;all staff&rdquo; to unmapped positions
            </label>
            <button type="submit" class="mt-3 w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Apply default sets
            </button>
        </form>
        @endif
    </div>
</div>

<div class="rounded-2xl border border-teal-200 bg-white shadow-sm">
    <div class="border-b border-teal-100 bg-teal-50/70 p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Requirements for one position</h3>
                <p class="mt-1 text-sm text-slate-600">Choose a position to review and save its organization-wide required documents.</p>
            </div>
            <form method="GET" action="{{ route('admin.upload-types.index') }}" class="w-full lg:w-[28rem]">
                <input type="hidden" name="tab" value="requirements">
                <label for="requirement_position_id" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Position</label>
                <div class="flex gap-2">
                    <select id="requirement_position_id" name="position_id"
                            class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200"
                            onchange="this.form.submit()">
                        <option value="">Select a position…</option>
                        @foreach($positionsByDepartment as $departmentName => $departmentPositions)
                            <optgroup label="{{ $departmentName }}">
                                @foreach($departmentPositions as $position)
                                    <option value="{{ $position->id }}" @selected((int) request('position_id') === (int) $position->id)>
                                        {{ $position->title }}{{ $position->position_code ? ' ('.$position->position_code.')' : '' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800">Show</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedRequirementPosition)
        @php $selectedRequirementIds = collect($selectedRequirementUploadTypeIds ?? [])->map(fn ($id) => (int) $id); @endphp
        <form method="POST" action="{{ route('admin.position-document-requirements.sync-position', $selectedRequirementPosition) }}">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="font-bold text-slate-900">{{ $selectedRequirementPosition->title }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $selectedRequirementPosition->department?->name ?? 'No department' }}
                        · {{ $selectedRequirementIds->count() }} required document(s)
                    </p>
                </div>
                <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Save requirements
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-24 px-4 py-3 text-center">Required</th>
                            <th class="px-4 py-3">Document</th>
                            <th class="px-4 py-3">Department scope</th>
                            <th class="px-4 py-3 text-center">Tracks expiry</th>
                            <th class="px-4 py-3 text-center">License / certification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($generalUploadTypes as $type)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="upload_type_ids[]" value="{{ $type->id }}"
                                           @checked($selectedRequirementIds->contains((int) $type->id))
                                           class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $type->name }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    @if(empty($type->department_ids))
                                        Organization-wide
                                    @else
                                        {{ $departments->whereIn('id', collect($type->department_ids)->map(fn ($id) => (int) $id))->pluck('name')->join(', ') ?: 'Restricted' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $type->requires_expiry ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $type->requires_expiry ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $type->is_license_or_certification ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $type->is_license_or_certification ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No document types are available for this position.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    @else
        <div class="px-5 py-10 text-center text-sm text-slate-500">Select a position to see its required-document table.</div>
    @endif
</div>

<form method="POST" action="{{ route('admin.position-document-requirements.bulk') }}" id="positionRequirementsForm" class="space-y-6">
    @csrf
    @if($selectedDepartmentId)
        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        {{-- Documents column --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700">1. Choose documents</h3>
                <button type="button" class="text-xs font-semibold text-brand-600 hover:text-brand-700" data-select-all="upload_type_ids[]">Select all</button>
            </div>

            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Document presets</p>
            <div class="mb-5 grid gap-2 sm:grid-cols-2">
                @foreach($documentSetCatalog as $setKey => $set)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:border-brand-300 hover:bg-brand-50/40 has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50">
                    <input type="checkbox" name="document_set_keys[]" value="{{ $setKey }}" class="document-set-preset mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="min-w-0">
                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                            <i class="fa-solid {{ $set['icon'] ?? 'fa-file' }} text-brand-600"></i>
                            {{ $set['label'] }}
                        </span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $set['description'] }}</span>
                        <span class="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $set['document_count'] }} document(s)</span>
                    </span>
                </label>
                @endforeach
            </div>

            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Or select individual documents</p>
            <div class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-slate-200 p-2">
                @forelse($generalUploadTypes as $type)
                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-50">
                    <input type="checkbox" name="upload_type_ids[]" value="{{ $type->id }}" class="document-type-checkbox h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="min-w-0 flex-1 text-sm text-slate-800">{{ $type->name }}</span>
                    @if($type->is_license_or_certification)
                        <span class="shrink-0 rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700">License</span>
                    @endif
                    @if($type->requires_expiry)
                        <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">Expiry</span>
                    @endif
                </label>
                @empty
                <p class="px-2 py-4 text-center text-sm text-slate-500">No general document types found. Add types under the All document types tab.</p>
                @endforelse
            </div>
        </div>

        {{-- Positions column --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-700">2. Choose positions</h3>
                <button type="button" class="text-xs font-semibold text-brand-600 hover:text-brand-700" data-select-all="position_ids[]">Select all</button>
            </div>

            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Position groups</p>
            <div class="mb-5 grid gap-2 sm:grid-cols-2">
                @foreach($positionGroupCatalog as $groupKey => $group)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:border-cyan-300 hover:bg-cyan-50/40 has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-50">
                    <input type="checkbox" name="position_group_keys[]" value="{{ $groupKey }}" class="position-group-preset mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-900">{{ $group['label'] }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $group['description'] }}</span>
                        <span class="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $group['position_count'] }} position(s)</span>
                    </span>
                </label>
                @endforeach
            </div>

            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Or select individual positions</p>
            <div class="max-h-72 space-y-3 overflow-y-auto rounded-xl border border-slate-200 p-2">
                @foreach($positionsByDepartment as $departmentName => $departmentPositions)
                <div>
                    <p class="sticky top-0 bg-white px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $departmentName }}</p>
                    @foreach($departmentPositions as $position)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-50">
                        <input type="checkbox" name="position_ids[]" value="{{ $position->id }}" class="position-checkbox h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                        <span class="text-sm text-slate-800">{{ $position->title }}</span>
                        @if($position->position_code)
                            <span class="text-xs text-slate-400">({{ $position->position_code }})</span>
                        @endif
                    </label>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-700">3. Apply assignment</h3>
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <button type="submit" name="action" value="add"
                class="inline-flex items-center justify-center rounded-xl border border-brand-700 bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                <i class="fa-solid fa-plus mr-2"></i> Add to selected positions
            </button>
            <button type="submit" name="action" value="replace"
                onclick="return confirm('Replace ALL existing requirements on the selected positions with only the documents you chose?');"
                class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                <i class="fa-solid fa-arrows-rotate mr-2"></i> Replace requirements
            </button>
            <button type="submit" name="action" value="remove"
                onclick="return confirm('Remove the selected documents from the selected positions?');"
                class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-5 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                <i class="fa-solid fa-minus mr-2"></i> Remove from positions
            </button>
            <p class="text-xs text-slate-500 sm:ml-auto sm:max-w-xs sm:text-right">
                <strong>Add</strong> keeps existing requirements. <strong>Replace</strong> overwrites them. <strong>Remove</strong> unassigns only the documents you selected.
            </p>
        </div>
    </div>
</form>

{{-- Overview table --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-slate-100 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Current requirements by position</h3>
                <p class="text-xs text-slate-500">
                    @if($requirementOverview->total() === 0)
                        No matching positions
                    @else
                        Showing {{ $requirementOverview->firstItem() }}–{{ $requirementOverview->lastItem() }} of {{ $requirementOverview->total() }} position(s)
                    @endif
                </p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.upload-types.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end">
            <input type="hidden" name="tab" value="requirements">
            <div>
                <label for="requirements_overview_search" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input type="text"
                    id="requirements_overview_search"
                    name="search"
                    value="{{ $overviewSearch }}"
                    placeholder="Position, department, or document name..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
            </div>
            <div>
                <label for="requirements_overview_department" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Department</label>
                <select id="requirements_overview_department" name="department_id" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) $selectedDepartmentId === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Search
                </button>
                @if($hasOverviewFilters)
                    <a href="{{ route('admin.upload-types.index', ['tab' => 'requirements']) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3">Department</th>
                    <th class="px-4 py-3 text-center">Required docs</th>
                    <th class="px-4 py-3">Documents</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($requirementOverview as $row)
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $row['title'] }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $row['department'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex min-w-[2rem] justify-center rounded-full {{ $row['requirement_count'] > 0 ? 'bg-brand-100 text-brand-800' : 'bg-slate-100 text-slate-500' }} px-2.5 py-0.5 text-xs font-bold">
                            {{ $row['requirement_count'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($row['requirement_count'] === 0)
                            <span class="text-xs text-slate-400">No requirements assigned</span>
                        @else
                            <details class="group">
                                <summary class="cursor-pointer text-xs font-semibold text-brand-600 hover:text-brand-700">View {{ $row['requirement_count'] }} document(s)</summary>
                                <ul class="mt-2 max-h-32 space-y-0.5 overflow-y-auto text-xs text-slate-600">
                                    @foreach($row['documents'] as $doc)
                                        <li>{{ $doc['name'] }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.positions.edit', $row['position_id']) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Edit position</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                        @if($hasOverviewFilters)
                            No positions match your search. Try a different term or clear the filters.
                        @else
                            No active positions found.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requirementOverview->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $requirementOverview->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-select-all]').forEach((button) => {
            button.addEventListener('click', () => {
                const fieldName = button.getAttribute('data-select-all');
                const boxes = Array.from(document.querySelectorAll(`input[name="${fieldName}"]`));
                const allChecked = boxes.length > 0 && boxes.every((box) => box.checked);
                boxes.forEach((box) => { box.checked = !allChecked; });
            });
        });

        document.querySelectorAll('.document-set-preset').forEach((preset) => {
            preset.addEventListener('change', () => {
                if (preset.checked) {
                    document.querySelectorAll('.document-type-checkbox').forEach((box) => { box.checked = false; });
                }
            });
        });

        document.querySelectorAll('.document-type-checkbox').forEach((box) => {
            box.addEventListener('change', () => {
                if (box.checked) {
                    document.querySelectorAll('.document-set-preset').forEach((preset) => { preset.checked = false; });
                }
            });
        });

        document.querySelectorAll('.position-group-preset').forEach((preset) => {
            preset.addEventListener('change', () => {
                if (preset.checked) {
                    document.querySelectorAll('.position-checkbox').forEach((box) => { box.checked = false; });
                }
            });
        });

        document.querySelectorAll('.position-checkbox').forEach((box) => {
            box.addEventListener('change', () => {
                if (box.checked) {
                    document.querySelectorAll('.position-group-preset').forEach((preset) => { preset.checked = false; });
                }
            });
        });
    });
</script>
@endpush

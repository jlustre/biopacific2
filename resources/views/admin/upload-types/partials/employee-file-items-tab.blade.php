@php
    $positionLookup = $positions->keyBy('position_id');
@endphp

@include('admin.upload-types.partials.bulk-assign-positions')

<form method="GET" action="{{ route('admin.upload-types.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4">
    <input type="hidden" name="tab" value="items">
    <div class="grid gap-3 md:grid-cols-4">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..."
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Section</label>
            <select name="section" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">All sections</option>
                @foreach ($itemSections as $section)
                    <option value="{{ $section }}" {{ request('section') === $section ? 'selected' : '' }}>{{ $section }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
            <select name="doc_type_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">All categories</option>
                @foreach ($docTypes as $docType)
                    <option value="{{ $docType->id }}" {{ (string) request('doc_type_id') === (string) $docType->id ? 'selected' : '' }}>{{ $docType->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Filter</button>
            <a href="{{ route('admin.upload-types.index', ['tab' => 'items']) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </div>
    </div>
</form>

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
    <table class="w-full min-w-[1100px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3 text-center">
                    <input type="checkbox" id="selectAllChecklistItems" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" aria-label="Select all documents on this page">
                </th>
                <th class="px-4 py-3">Document</th>
                <th class="px-4 py-3">Section</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Applicable positions</th>
                <th class="px-4 py-3 text-center">Required</th>
                <th class="px-4 py-3 text-center">Expires</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($checklistItems as $checklistItem)
                @php
                    $positionLabels = collect($checklistItem->position_ids ?? [])
                        ->map(fn ($positionId) => $positionLookup->get($positionId))
                        ->filter()
                        ->map(fn ($position) => $position->title)
                        ->values();
                    $appliesToAllPositions = $checklistItem->position_ids === null;
                    $appliesToNoPositions = is_array($checklistItem->position_ids) && $checklistItem->position_ids === [];
                @endphp
                <tr>
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" name="checklist_item_ids[]" value="{{ $checklistItem->id }}" form="bulkPositionForm"
                            class="checklist-item-selector h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">{{ $checklistItem->name }}</div>
                        <div class="text-xs text-slate-500">Order: {{ $checklistItem->order ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $checklistItem->section ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $checklistItem->docType->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($appliesToAllPositions)
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">All positions</span>
                        @elseif ($appliesToNoPositions)
                            <span class="rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">No positions</span>
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach ($positionLabels as $label)
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $label }}</span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ ($checklistItem->is_required ?? true) ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                            {{ ($checklistItem->is_required ?? true) ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $checklistItem->isExpiring ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ $checklistItem->isExpiring ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.checklist-items.edit', $checklistItem) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50" title="Edit" aria-label="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.checklist-items.destroy', $checklistItem) }}" method="POST" class="inline-flex" onsubmit="return confirm('Delete this employee file item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" title="Delete" aria-label="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-slate-500">No employee file items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>
    {{ $checklistItems->links() }}
</div>

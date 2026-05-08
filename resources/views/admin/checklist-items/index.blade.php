@extends('layouts.dashboard', ['title' => 'Checklist Items Management'])

@section('content')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Checklist Items Management</h1>
        <p class="text-gray-600 mt-2">Manage checklist items and limit them to specific employee positions.</p>
    </div>
    <a href="{{ route('admin.checklist-items.create') }}"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
        <i class="fas fa-plus mr-2"></i> Create Checklist Item
    </a>
</div>

<div class="space-y-6">
    @if (session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-green-800"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
    </div>
    @endif

    @php
        $positionLookup = $positions->keyBy('position_id');
    @endphp

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form action="{{ route('admin.checklist-items.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                    <select name="section" id="section"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Sections</option>
                        @foreach ($sections as $section)
                        <option value="{{ $section }}" {{ request('section') === $section ? 'selected' : '' }}>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="doc_type_id" class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                    <select name="doc_type_id" id="doc_type_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Document Types</option>
                        @foreach ($docTypes as $docType)
                        <option value="{{ $docType->id }}" {{ (string) request('doc_type_id') === (string) $docType->id ? 'selected' : '' }}>{{ $docType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form id="bulkPositionForm" action="{{ route('admin.checklist-items.bulk-positions') }}" method="POST" class="space-y-4">
            @csrf
            <div class="flex flex-col xl:flex-row xl:items-end gap-4">
                <div class="xl:w-1/2">
                    <label for="bulk_position_ids" class="block text-sm font-medium text-gray-700 mb-1">Bulk Assign Positions</label>
                    <select name="position_ids[]" id="bulk_position_ids" multiple size="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @foreach ($positions as $position)
                        <option value="{{ $position->position_id }}">{{ $position->title }} ({{ $position->position_code }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Select one or more positions, then choose checklist rows below.</p>
                </div>
                <div class="flex-1 space-y-3">
                    <label class="flex items-center gap-3 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="apply_to_everyone" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Apply selected checklist items to everybody
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="bg-cyan-600 text-white px-5 py-2 rounded-lg hover:bg-cyan-700 transition font-semibold">
                            <i class="fas fa-layer-group mr-2"></i> Update Selected Items
                        </button>
                        <span class="text-sm text-gray-500 self-center">Use the checkboxes in the first column to choose items on this page.</span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">
                        <input type="checkbox" id="selectAllChecklistItems" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Checklist Item</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Section</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Document Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Applicable Positions</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">Expires</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($checklistItems as $checklistItem)
                @php
                    $positionLabels = collect($checklistItem->position_ids ?? [])
                        ->map(fn ($positionId) => $positionLookup->get($positionId))
                        ->filter()
                        ->map(fn ($position) => $position->title)
                        ->values();
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-center">
                        <input type="checkbox" name="checklist_item_ids[]" value="{{ $checklistItem->id }}" form="bulkPositionForm"
                            class="checklist-item-selector h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">{{ $checklistItem->name }}</div>
                        <div class="text-xs text-gray-500">Order: {{ $checklistItem->order ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ $checklistItem->section ?: 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $checklistItem->docType->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-700">
                        @if ($positionLabels->isEmpty())
                        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">All positions</span>
                        @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($positionLabels as $label)
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">{{ $label }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block {{ $checklistItem->isExpiring ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }} text-xs px-3 py-1 rounded-full">
                            {{ $checklistItem->isExpiring ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.checklist-items.edit', $checklistItem) }}" class="text-green-600 hover:text-green-900 text-sm font-medium">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.checklist-items.destroy', $checklistItem) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this checklist item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-600">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p>No checklist items found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-center">
        {{ $checklistItems->links() }}
    </div>
</div>
@endsection

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
                selectAll.checked = rowSelectors.every((item) => item.checked);
            });
        });
    });
</script>
@endpush
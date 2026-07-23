@php
    $selectedPositionIds = collect(old('position_ids', $checklistItem->position_ids ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div>
    <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Item name <span class="text-red-500">*</span></label>
    <input type="text" name="name" id="name" value="{{ old('name', $checklistItem->name) }}" required
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    @error('name')
    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <div>
        <label for="section" class="block text-sm font-semibold text-gray-900 mb-2">Section <span class="text-red-500">*</span></label>
        @php
            $employeeFileSections = \App\Services\ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS;
            $selectedSection = old('section', $checklistItem->section);
        @endphp
        <select name="section" id="section" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Select PART A–D…</option>
            @foreach ($employeeFileSections as $section)
                <option value="{{ $section }}" @selected($selectedSection === $section)>{{ $section }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">PART E orientation items are not documents and are managed separately.</p>
        @error('section')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="doc_type_id" class="block text-sm font-semibold text-gray-900 mb-2">Document Type <span class="text-red-500">*</span></label>
        <select name="doc_type_id" id="doc_type_id" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Select a document type...</option>
            @foreach ($docTypes as $docType)
            <option value="{{ $docType->id }}" {{ (int) old('doc_type_id', $checklistItem->doc_type_id) === $docType->id ? 'selected' : '' }}>
                {{ $docType->name }}
            </option>
            @endforeach
        </select>
        @error('doc_type_id')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="is_required" class="block text-sm font-semibold text-gray-900 mb-2">Is Required</label>
        @php
            $isRequired = filter_var(old('is_required', $checklistItem->is_required ?? true), FILTER_VALIDATE_BOOLEAN);
        @endphp
        <select name="is_required" id="is_required"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="1" {{ $isRequired ? 'selected' : '' }}>Yes — required for applicable positions</option>
            <option value="0" {{ ! $isRequired ? 'selected' : '' }}>No — optional document</option>
        </select>
        @error('is_required')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="order" class="block text-sm font-semibold text-gray-900 mb-2">Display Order</label>
        <input type="number" min="1" name="order" id="order" value="{{ old('order', $checklistItem->order) }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        @error('order')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex items-center gap-3">
    <input type="checkbox" name="isExpiring" id="isExpiring" value="1" {{ old('isExpiring', $checklistItem->isExpiring) ? 'checked' : '' }}
        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
    <label for="isExpiring" class="text-sm font-semibold text-gray-900">This item expires and should track expiration dates</label>
</div>

<div>
    <div class="flex items-center justify-between mb-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-900">Applicable Positions</label>
            <p class="text-sm text-gray-600 mt-1">Leave all unchecked to make this item apply to everybody.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 border border-gray-200 rounded-lg p-4 bg-gray-50">
        @foreach ($positions as $position)
        <label class="flex items-start gap-3 bg-white border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition">
            <input type="checkbox" name="position_ids[]" value="{{ $position->position_id }}"
                {{ in_array($position->position_id, $selectedPositionIds, true) ? 'checked' : '' }}
                class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900">{{ $position->title }}</span>
                <span class="block text-xs text-gray-600">{{ $position->position_code }}{{ $position->dept_code ? ' • ' . $position->dept_code : '' }}</span>
            </span>
        </label>
        @endforeach
    </div>
    @error('position_ids')
    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
    @error('position_ids.*')
    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
@extends('layouts.dashboard', ['title' => 'Create Position'])

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Create Position</h1>
    <p class="text-gray-600 mt-2">Add a new job position</p>
</div>
<div class="max-w-2xl">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <form action="{{ route('admin.positions.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Title Field -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Position Title <span
                        class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" placeholder="e.g., Registered Nurse"
                    value="{{ old('title') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department Field -->
            <div>
                <label for="department_id" class="block text-sm font-semibold text-gray-900 mb-2">Department <span
                        class="text-red-500">*</span></label>
                <select name="department_id" id="department_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select a department...</option>
                    @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id')==$dept->id ? 'selected' : '' }}>
                        {{ $dept->name }} ({{ ucfirst($dept->type) }})
                    </option>
                    @endforeach
                </select>
                @error('department_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reports_to_position_id" class="block text-sm font-semibold text-gray-900 mb-2">Reports To Position</label>
                <select name="reports_to_position_id" id="reports_to_position_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">No reporting position</option>
                    @foreach ($reportingPositions as $reportingPosition)
                    <option value="{{ $reportingPosition->id }}" {{ old('reports_to_position_id') == $reportingPosition->id ? 'selected' : '' }}>
                        {{ $reportingPosition->title }}
                    </option>
                    @endforeach
                </select>
                @error('reports_to_position_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea name="description" id="description" rows="6" placeholder="Enter position description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Required Documents (by position)</label>
                <p class="text-xs text-gray-500 mb-2">Choose document types required for this position. After selecting a department, only document types available for that department are shown.</p>
                <p class="text-xs text-gray-500 mb-3">
                    PART A–D checklist documents are managed under
                    <a href="{{ route('admin.upload-types.index', ['tab' => 'items']) }}" class="font-semibold text-blue-600 hover:text-blue-800">Documents Management → Employee file items</a>.
                </p>
                @php
                    $selectedRequiredIds = old('required_upload_type_ids', []);
                @endphp
                <div id="position-required-documents" class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 p-3 space-y-2 bg-gray-50">
                    <p id="position-required-documents-placeholder" class="text-sm text-gray-500">Select a department to load available document types.</p>
                </div>
                @error('required_upload_type_ids')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('required_upload_type_ids.*')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Create Position
                </button>
                <a href="{{ route('admin.positions.index') }}"
                    class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const container = document.getElementById('position-required-documents');
        const placeholder = document.getElementById('position-required-documents-placeholder');
        const departmentSelect = document.getElementById('department_id');
        const uploadTypesUrl = @json(route('admin.positions.upload-types-by-department'));
        const selectedIds = @json(array_map('intval', old('required_upload_type_ids', [])));

        function renderUploadTypes(types) {
            if (!container) return;
            container.querySelectorAll('label[data-upload-type]').forEach((node) => node.remove());

            if (!types.length) {
                if (placeholder) {
                    placeholder.textContent = 'No document types available for this department.';
                    placeholder.classList.remove('hidden');
                }
                return;
            }

            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            types.forEach((type) => {
                const label = document.createElement('label');
                label.className = 'flex items-start gap-3 p-2 rounded hover:bg-white';
                label.dataset.uploadType = String(type.id);

                const checked = selectedIds.includes(type.id) ? 'checked' : '';
                const expiryBadge = type.requires_expiry
                    ? '<span class="ml-1 inline-flex items-center rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-amber-800">Expiry required</span>'
                    : '';
                const description = type.description
                    ? `<span class="block text-xs text-gray-500 mt-0.5">${type.description}</span>`
                    : '';

                label.innerHTML = `
                    <input type="checkbox" name="required_upload_type_ids[]" value="${type.id}" class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" ${checked}>
                    <span class="text-sm text-gray-800">
                        <span class="font-medium">${type.name}</span>
                        ${expiryBadge}
                        ${description}
                    </span>
                `;
                container.appendChild(label);
            });
        }

        async function loadForDepartment(departmentId) {
            if (!departmentId) {
                container.querySelectorAll('label[data-upload-type]').forEach((node) => node.remove());
                if (placeholder) {
                    placeholder.textContent = 'Select a department to load available document types.';
                    placeholder.classList.remove('hidden');
                }
                return;
            }

            const response = await fetch(`${uploadTypesUrl}?department_id=${encodeURIComponent(departmentId)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            renderUploadTypes(data.upload_types || []);
        }

        if (departmentSelect) {
            departmentSelect.addEventListener('change', () => loadForDepartment(departmentSelect.value));
            if (departmentSelect.value) {
                loadForDepartment(departmentSelect.value);
            }
        }
    })();
</script>
@endsection
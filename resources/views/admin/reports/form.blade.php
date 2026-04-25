@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ isset($report) ? 'Edit' : 'Create' }} Report</h1>
        <a href="{{ url('/admin/reports') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">&larr; Back to Reports List</a>
    </div>
    @php
        $categories = \App\Models\ReportCategory::orderBy('name')->get();
    @endphp
    <form method="POST" action="{{ isset($report) ? route('admin.reports.update', $report) : route('admin.reports.store') }}">
        @csrf
        @if(isset($report)) @method('PUT') @endif
        <div class="mb-4">
            <label class="block font-semibold mb-1">Category</label>
            <select name="category_id" class="form-select bg-teal-50 w-full border border-teal-500 px-2 py-1" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $report->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Name</label>
            <input type="text" name="name" class="form-input bg-teal-50 w-full border border-teal-500 px-2 py-1" value="{{ old('name', $report->name ?? '') }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" class="form-input bg-teal-50 w-full border border-teal-500 px-2 py-1">{{ old('description', $report->description ?? '') }}</textarea>
        </div>
        <div class="mb-4">
            <div class="flex items-center justify-between">
                <label class="block font-semibold">SQL Template</label>
                <div class="flex items-center gap-2 mb-1">
                    <button type="button" id="validate-sql-btn" class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Validate SQL</button>
                    @php $sqlValue = old('sql_template', $report->sql_template ?? ''); @endphp
                    @if(!empty($sqlValue))
                        <button type="button" id="copy-sql-btn" class="px-2 py-1 bg-teal-600 text-white rounded text-xs">Copy SQL</button>
                    @endif
                </div>
            </div>
            @if(!empty($sqlValue))
                <div id="copy-sql-message" class="hidden mt-1">
                    <div class="bg-yellow-100 border border-yellow-500 text-yellow-900 px-4 py-2 rounded text-sm font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        SQL Copied
                    </div>
                </div>
            @endif
            <textarea name="sql_template" id="sql_template" class="form-input w-full border border-teal-500 px-2 py-1" rows="5" required>{{ $sqlValue }}</textarea>
            <div class="text-xs text-gray-500 mt-1">Use <code>:param</code> for parameters. Example: <code>SELECT * FROM bp_employees WHERE last_name LIKE :last_name</code></div>
        </div>
        <!-- Modal for SQL validation result -->
        <div id="sql-validation-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
            <div class="bg-white p-6 rounded shadow w-full max-w-md">
                <h2 class="text-lg font-bold mb-2">SQL Validation Result</h2>
                <div id="sql-validation-message" class="mb-4"></div>
                <div class="flex justify-end">
                    <button type="button" id="close-sql-modal" class="px-4 py-2 bg-blue-600 text-white rounded">Close</button>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <div class="flex items-center justify-between">
                <label class="block font-semibold mb-1">Parameters (JSON)</label>
                <button type="button" id="validate-params-btn" class="ml-2 px-3 py-1 bg-blue-600 text-white rounded text-xs">Validate Parameters</button>
            </div>
            <textarea name="parameters" id="parameters_json" class="form-input w-full border border-teal-500 px-2 py-1" rows="3">{{ old('parameters', isset($report) ? json_encode($report->parameters) : '[]') }}</textarea>
            <div class="text-xs text-gray-500 mt-1">Example: <code>[{"name":"last_name","label":"Last Name","type":"text"}]</code></div>
        </div>
        <!-- Modal for Parameters validation result -->
        <div id="params-validation-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
            <div class="bg-white p-6 rounded shadow w-full max-w-md">
                <h2 class="text-lg font-bold mb-2">Parameters Validation Result</h2>
                <div id="params-validation-message" class="mb-4"></div>
                <div class="flex justify-end">
                    <button type="button" id="close-params-modal" class="px-4 py-2 bg-blue-600 text-white rounded">Close</button>
                </div>
            </div>
        </div>
        <div class="mb-4 flex gap-8">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox border border-teal-500 px-2 py-1" {{ old('is_active', $report->is_active ?? true) ? 'checked' : '' }}> Active
            </label>
        </div>

        <div class="mb-4">
            <label class="block font-bold mb-2">Report Visibility</label>
            @php
                $allRoles = [
                    'admin' => 'Admin',
                    'facility-admin' => 'Facility Admin',
                    'facility-editor' => 'Facility Editor',
                    'regular-user' => 'Regular User',
                    'hrrd' => 'HR Regional Director',
                    'facility-dsd' => 'Facility DSD',
                ];
                $allFacilities = \App\Models\Facility::orderBy('name')->get();
                $selectedVisibility = old('visibility', $report->visibility ?? 'admin');
                $selectedRoles = old('visible_roles', isset($report) ? $report->visible_roles_collection->all() : []);
                $selectedFacilities = old('visible_facilities', isset($report) ? $report->visible_facilities_collection->all() : []);
            @endphp
            <div class="space-y-2" x-data="{ visibility: '{{ $selectedVisibility }}', facilitiesType: '{{ (count($selectedFacilities) > 0 && $selectedVisibility === 'facilities') ? 'specific' : 'all' }}' }">
                <!-- Roles Visibility -->
                <div class="mb-2">
                    <span class="font-semibold text-teal-600">Who can run this report?</span>
                    <div class="flex flex-col gap-2 mt-1">
                        <label class="inline-flex items-center">
                            <input type="radio" name="visibility" value="admin" class="form-radio border border-teal-500 px-2 py-1" x-model="visibility" {{ $selectedVisibility == 'admin' ? 'checked' : '' }}>
                            <span class="ml-2">Admins only</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="visibility" value="all" class="form-radio border border-teal-500 px-2 py-1" x-model="visibility" {{ $selectedVisibility == 'all' ? 'checked' : '' }}>
                            <span class="ml-2">All authenticated users</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="visibility" value="roles" class="form-radio border border-teal-500 px-2 py-1" x-model="visibility" {{ $selectedVisibility == 'roles' ? 'checked' : '' }}>
                            <span class="ml-2">Specific roles</span>
                        </label>
                    </div>
                    <div class="ml-6 mt-2" x-show="visibility === 'roles'">
                        <div class="font-semibold mb-1 text-teal-600">Select Roles</div>
                        <div class="flex flex-wrap gap-4">
                            @foreach($allRoles as $roleKey => $roleLabel)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="visible_roles[]" value="{{ $roleKey }}" class="form-checkbox border border-teal-500 px-2 py-1" {{ in_array($roleKey, $selectedRoles) ? 'checked' : '' }}>
                                    <span class="ml-2">{{ $roleLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Facilities Visibility -->
                <div class="mt-4">
                    <span class="font-semibold">Facility restriction</span>
                    <div class="flex flex-col gap-2 mt-1">
                        <label class="inline-flex items-center">
                            <input type="radio" name="facilities_type" value="all" class="form-radio border border-teal-500 px-2 py-1" x-model="facilitiesType" {{ (count($selectedFacilities) === 0 || $selectedVisibility !== 'facilities') ? 'checked' : '' }}>
                            <span class="ml-2">All facilities</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="facilities_type" value="specific" class="form-radio border border-teal-500 px-2 py-1" x-model="facilitiesType" {{ (count($selectedFacilities) > 0 && $selectedVisibility === 'facilities') ? 'checked' : '' }}>
                            <span class="ml-2">Specific facilities</span>
                        </label>
                    </div>
                    <div class="ml-6 mt-2" x-show="facilitiesType === 'specific'">
                        <div class="font-semibold mb-1">Select Facilities</div>
                        <div class="flex flex-wrap gap-4 max-h-48 overflow-y-auto border border-teal-200 rounded p-2 bg-white/80">
                            @foreach($allFacilities as $facility)
                                <label class="inline-flex items-center w-1/2">
                                    <input type="checkbox" name="visible_facilities[]" value="{{ $facility->id }}" class="form-checkbox border border-teal-500 px-2 py-1" {{ in_array($facility->id, $selectedFacilities) ? 'checked' : '' }}>
                                    <span class="ml-2">{{ $facility->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancel</a>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const validateBtn = document.getElementById('validate-sql-btn');
    const copyBtn = document.getElementById('copy-sql-btn');
    const sqlField = document.getElementById('sql_template');
    const modal = document.getElementById('sql-validation-modal');
    const messageDiv = document.getElementById('sql-validation-message');
    const closeModal = document.getElementById('close-sql-modal');
    if (validateBtn) {
        validateBtn.addEventListener('click', function() {
            const sql = sqlField.value;
            fetch("{{ route('admin.reports.validate-sql') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ sql })
            })
            .then(res => res.json())
            .then(data => {
                if (data.valid) {
                    messageDiv.innerHTML = '<span class="text-green-600 font-semibold">Valid SQL!</span>';
                } else {
                    messageDiv.innerHTML = '<span class="text-red-600 font-semibold">Invalid SQL:</span> ' + data.error;
                }
                modal.classList.remove('hidden');
            })
            .catch(() => {
                messageDiv.innerHTML = '<span class="text-red-600 font-semibold">An error occurred while validating.</span>';
                modal.classList.remove('hidden');
            });
        });
    }
    if (copyBtn) {
        const copyMsg = document.getElementById('copy-sql-message');
        copyBtn.addEventListener('click', function() {
            if (sqlField.value) {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText(sqlField.value).then(function() {
                        if (copyMsg) {
                            copyMsg.classList.remove('hidden');
                            setTimeout(() => { copyMsg.classList.add('hidden'); }, 1800);
                        }
                    });
                } else {
                    // Fallback for older browsers
                    sqlField.select();
                    document.execCommand('copy');
                    if (copyMsg) {
                        copyMsg.classList.remove('hidden');
                        setTimeout(() => { copyMsg.classList.add('hidden'); }, 1800);
                    }
                }
            }
        });
    }
    // Modal close logic for SQL validation
    if (closeModal) {
        closeModal.onclick = function() {
            if (modal) modal.classList.add('hidden');
        };
    }
    if (modal) {
        modal.addEventListener('click', function(e) { if(e.target === modal) modal.classList.add('hidden'); });
    }
    // Parameters validation modal logic
    const validateParamsBtn = document.getElementById('validate-params-btn');
    const paramsField = document.getElementById('parameters_json');
    const paramsModal = document.getElementById('params-validation-modal');
    const paramsMsg = document.getElementById('params-validation-message');
    const closeParamsModal = document.getElementById('close-params-modal');
    if (validateParamsBtn) {
        validateParamsBtn.addEventListener('click', function() {
            let value = paramsField.value;
            let valid = false;
            let reason = '';
            try {
                let parsed = JSON.parse(value);
                if (Array.isArray(parsed)) {
                    valid = parsed.every(p => typeof p === 'object' && p !== null && 'name' in p && 'type' in p && 'label' in p);
                    if (!valid) {
                        reason = 'Each item must be an object with name, label, and type.';
                    }
                } else {
                    reason = 'Parameters JSON must be an array.';
                }
            } catch (e) {
                reason = 'Invalid JSON: ' + e.message;
            }
            if (valid) {
                paramsMsg.innerHTML = '<span class="text-green-600 font-semibold">Valid Parameters JSON!</span>';
            } else {
                paramsMsg.innerHTML = '<span class="text-red-600 font-semibold">Invalid Parameters JSON:</span> ' + reason;
            }
            paramsModal.classList.remove('hidden');
        });
    }
    // Modal close logic for Parameters validation
    if (closeParamsModal) {
        closeParamsModal.onclick = function() {
            if (paramsModal) paramsModal.classList.add('hidden');
        };
    }
    if (paramsModal) {
        paramsModal.addEventListener('click', function(e) { if(e.target === paramsModal) paramsModal.classList.add('hidden'); });
    }
});
</script>
@endsection

@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">{{ isset($report) ? 'Edit' : 'Create' }} Report</h1>
    <form method="POST" action="{{ isset($report) ? route('admin.reports.update', $report) : route('admin.reports.store') }}">
        @csrf
        @if(isset($report)) @method('PUT') @endif
        <div class="mb-4">
            <label class="block font-semibold mb-1">Name</label>
            <input type="text" name="name" class="form-input w-full border border-teal-500 px-2 py-1" value="{{ old('name', $report->name ?? '') }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" class="form-input w-full border border-teal-500 px-2 py-1">{{ old('description', $report->description ?? '') }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">SQL Template</label>
            <textarea name="sql_template" class="form-input w-full border border-teal-500 px-2 py-1" rows="5" required>{{ old('sql_template', $report->sql_template ?? '') }}</textarea>
            <div class="text-xs text-gray-500 mt-1">Use <code>:param</code> for parameters. Example: <code>SELECT * FROM bp_employees WHERE last_name LIKE :last_name</code></div>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Parameters (JSON)</label>
            <textarea name="parameters" class="form-input w-full border border-teal-500 px-2 py-1" rows="3">{{ old('parameters', isset($report) ? json_encode($report->parameters) : '[]') }}</textarea>
            <div class="text-xs text-gray-500 mt-1">Example: <code>[{"name":"last_name","label":"Last Name","type":"text"}]</code></div>
        </div>
        <div class="mb-4 flex gap-8">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox border border-teal-500 px-2 py-1" {{ old('is_active', $report->is_active ?? true) ? 'checked' : '' }}> Active
            </label>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Report Visibility</label>
            <div class="space-y-2">
                <label class="inline-flex items-center">
                    <input type="radio" name="visibility" value="admin" class="form-radio border border-teal-500 px-2 py-1" {{ old('visibility', $report->visibility ?? 'admin') == 'admin' ? 'checked' : '' }}>
                    <span class="ml-2">Admins only</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="visibility" value="all" class="form-radio border border-teal-500 px-2 py-1" {{ old('visibility', $report->visibility ?? '') == 'all' ? 'checked' : '' }}>
                    <span class="ml-2">All authenticated users</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="visibility" value="roles" class="form-radio border border-teal-500 px-2 py-1" {{ old('visibility', $report->visibility ?? '') == 'roles' ? 'checked' : '' }}>
                    <span class="ml-2">Specific roles</span>
                </label>
                <div class="ml-6" style="margin-top: 0.5rem;" x-show="document.querySelector('input[name=\'visibility\']:checked')?.value === 'roles'">
                    <label class="block font-semibold mb-1">Select Roles</label>
                    <select name="visible_roles[]" multiple class="form-multiselect w-full mt-2 border border-teal-500 px-2 py-1">
                        @php $roles = [
                            'facility-admin', 'facility-editor', 'regular-user', 'hrrd', 'facility-dsd', 'admin'
                        ]; @endphp
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ in_array($role, old('visible_roles', isset($report) ? $report->visible_roles_collection->all() : [])) ? 'selected' : '' }}>{{ ucwords(str_replace('-', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                    <div class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple roles.</div>
                </div>
                <div x-data="{ showFacilities: {{ old('enable_facility_visibility', isset($report) && !empty($report->visible_facilities) ? 'true' : 'false' ) }} }">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="enable_facility_visibility" value="1" class="form-checkbox border border-teal-500 px-2 py-1" x-model="showFacilities">
                        <span class="ml-2">Specific facilities</span>
                    </label>
                    <div class="ml-6 mt-2" x-show="showFacilities">
                        <select name="visible_facilities[]" multiple class="form-multiselect w-full border border-teal-500 px-2 py-1">
                            @php $facilities = [
                                1 => 'Almaden Healthcare and Rehabilitation Center',
                                2 => 'Autumn Hills Healthcare Center',
                                3 => 'Creekside Healthcare Center',
                                4 => 'Driftwood Healthcare Center- Hayward',
                                5 => 'Driftwood Healthcare Center-Santa Cruz',
                                6 => 'Fremont Healthcare Center',
                                7 => 'Fruitvale Healthcare Center',
                                8 => 'Glendale Transitional Care Center',
                                9 => 'Hayward Hills Healthcare Center',
                                10 => 'Inglewood Healthcare Center',
                                11 => 'La Crescenta Healthcare',
                                12 => 'Monterey Palms Healthcare Center',
                                13 => 'Palm Springs Healthcare & Rehabilitation Center',
                                14 => 'Pine Ridge Healthcare Center',
                                15 => 'Santa Monica Healthcare Center',
                                16 => 'Skyline Healthcare Center-San Jose',
                                17 => 'Vale Healthcare Center',
                                99 => 'Bio-Pacific Corporation',
                            ]; @endphp
                            @foreach($facilities as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, old('visible_facilities', isset($report) ? $report->visible_facilities_collection->all() : [])) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple facilities.</div>
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
@endsection

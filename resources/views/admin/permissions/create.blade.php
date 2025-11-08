@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create New Permission</h1>
        <p class="text-gray-600">Add a new permission to the system</p>
    </div>
    <a href="{{ route('admin.permissions.index') }}"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back to Permissions
    </a>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.permissions.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permission Information</h3>

            <div class="space-y-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Permission Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" name="category"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="updatePermissionPreview()" required>
                        <option value="">Select a category...</option>
                        @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ old('category')==$value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                        <option value="custom" {{ old('category')=='custom' ? 'selected' : '' }}>
                            Custom (enter full permission name)
                        </option>
                    </select>
                    @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Permission Action <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., view, create, edit, delete" onkeyup="updatePermissionPreview()" required>
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500" id="category-help">
                        Enter the action part of the permission (e.g., 'view', 'create', 'edit', 'delete')
                    </p>
                </div>

                <div id="preview-section" class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                    style="display: none;">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Permission Preview</h4>
                    <div class="text-sm">
                        <span class="text-blue-700">Final permission name: </span>
                        <code id="permission-preview"
                            class="bg-blue-100 px-2 py-1 rounded font-mono text-blue-900"></code>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Common Permission Examples</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($categories as $value => $label)
                <div class="border border-gray-200 rounded-lg p-3">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ $label }}</h4>
                    <div class="space-y-1 text-xs text-gray-600">
                        @switch($value)
                        @case('facilities')
                        <div>• view facilities</div>
                        <div>• create facilities</div>
                        <div>• edit facilities</div>
                        <div>• delete facilities</div>
                        @break
                        @case('users')
                        <div>• view users</div>
                        <div>• create users</div>
                        <div>• manage users</div>
                        @break
                        @case('content')
                        <div>• view content</div>
                        <div>• create content</div>
                        <div>• publish content</div>
                        @break
                        @case('roles')
                        <div>• view roles</div>
                        <div>• create roles</div>
                        <div>• assign roles</div>
                        @break
                        @default
                        <div>• view {{ $value }}</div>
                        <div>• manage {{ $value }}</div>
                        @endswitch
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Create Permission</h3>
                    <p class="text-sm text-gray-600">Review your settings and create the permission</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.permissions.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Create Permission
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function updatePermissionPreview() {
    const category = document.getElementById('category').value;
    const name = document.getElementById('name').value;
    const previewSection = document.getElementById('preview-section');
    const previewElement = document.getElementById('permission-preview');
    const helpElement = document.getElementById('category-help');
    
    if (category && name) {
        let permissionName;
        if (category === 'custom') {
            permissionName = name;
            helpElement.textContent = 'Enter the complete permission name';
        } else {
            permissionName = name.toLowerCase() + ' ' + category;
            helpElement.textContent = `Permission will be: "${name.toLowerCase()} ${category}"`;
        }
        
        previewElement.textContent = permissionName;
        previewSection.style.display = 'block';
    } else {
        previewSection.style.display = 'none';
        if (category === 'custom') {
            helpElement.textContent = 'Enter the complete permission name';
        } else {
            helpElement.textContent = 'Enter the action part of the permission (e.g., \'view\', \'create\', \'edit\', \'delete\')';
        }
    }
}

// Update preview on page load if there are old values
document.addEventListener('DOMContentLoaded', function() {
    updatePermissionPreview();
});
</script>
@endsection
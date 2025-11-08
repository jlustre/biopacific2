@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Role: {{ ucwords(str_replace('-', ' ', $role->name)) }}</h1>
        <p class="text-gray-600">Modify role permissions and settings</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.roles.show', $role) }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-eye mr-2"></i> View
        </a>
        <a href="{{ route('admin.roles.index') }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 {{ in_array($role->name, ['web-admin', 'admin']) ? 'bg-gray-100' : '' }}"
                    {{ in_array($role->name, ['web-admin', 'admin']) ? 'readonly' : '' }}
                required>
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if(in_array($role->name, ['web-admin', 'admin']))
                <p class="mt-1 text-sm text-yellow-600">
                    <i class="fas fa-shield-alt mr-1"></i>
                    This is a protected role. The name cannot be changed.
                </p>
                @else
                <p class="mt-1 text-sm text-gray-500">
                    Use lowercase letters, numbers, and hyphens only.
                </p>
                @endif
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900">Role Information</h4>
                        <div class="mt-2 text-sm text-blue-800">
                            <p><strong>Users with this role:</strong> {{ $role->users->count() }}</p>
                            <p><strong>Current permissions:</strong> {{ $role->permissions->count() }}</p>
                            <p><strong>Created:</strong> {{ $role->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
            <p class="text-sm text-gray-600 mb-4">Select the permissions this role should have</p>

            <div class="space-y-6">
                @foreach($permissions as $category => $categoryPermissions)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-md font-medium text-gray-900">
                            {{ ucfirst($category) }} Permissions
                        </h4>
                        <div class="text-sm text-gray-500">
                            {{ $categoryPermissions->count() }} permissions
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($categoryPermissions as $permission)
                        <label class="flex items-start space-x-3 p-2 rounded hover:bg-gray-50">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{
                                in_array($permission->id, old('permissions',
                            $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ ucwords(str_replace(['_', '-'], ' ', $permission->name)) }}
                                </span>
                                <p class="text-xs text-gray-500">{{ $permission->name }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="mt-3 flex space-x-2">
                        <button type="button" class="text-xs text-blue-600 hover:text-blue-800"
                            onclick="selectAllInCategory('{{ $category }}')">
                            Select All
                        </button>
                        <button type="button" class="text-xs text-gray-600 hover:text-gray-800"
                            onclick="deselectAllInCategory('{{ $category }}')">
                            Deselect All
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            @error('permissions')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Update Role</h3>
                    <p class="text-sm text-gray-600">Save changes to this role</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.roles.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Update Role
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function selectAllInCategory(category) {
    const categoryDiv = event.target.closest('.border');
    const checkboxes = categoryDiv.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllInCategory(category) {
    const categoryDiv = event.target.closest('.border');
    const checkboxes = categoryDiv.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}
</script>
@endsection
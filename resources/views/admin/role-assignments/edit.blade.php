@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manage Roles for {{ $user->name }}</h1>
        <p class="text-gray-600">Assign or remove roles for this user</p>
    </div>
    <a href="{{ route('admin.role-assignments.index') }}"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back to Assignments
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- User Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>

        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-xl font-medium text-gray-700">{{ $user->initials }}</span>
            </div>
            <div>
                <h4 class="text-lg font-medium text-gray-900">{{ $user->name }}</h4>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <p class="text-sm text-gray-500">Member since {{ $user->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-tag text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Current Roles</p>
                        <p class="text-lg font-bold text-blue-900">{{ $user->roles->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-key text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">Total Permissions</p>
                        <p class="text-lg font-bold text-green-900">{{
                            $user->roles->flatMap->permissions->unique('id')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Last Activity</p>
                        <p class="text-lg font-bold text-purple-900">{{ $user->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Assignment Form -->
    <form action="{{ route('admin.role-assignments.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Role Assignment</h3>

            <div class="space-y-4">
                @foreach($roles as $role)
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{
                            $user->hasRole($role->name) ? 'checked' : '' }}>
                        <label for="role-{{ $role->id }}" class="flex-1 cursor-pointer">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $role->permissions->count() }} permissions | {{ $role->users->count() }} users
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center space-x-2">
                        @if($role->name === 'admin')
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-shield-alt mr-1"></i> Protected
                        </span>
                        @endif

                        <button type="button" onclick="toggleRoleDetails({{ $role->id }})"
                            class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>

                <!-- Role Details (Hidden by default) -->
                <div id="role-details-{{ $role->id }}" class="hidden ml-7 p-4 bg-gray-50 rounded-lg">
                    <h5 class="text-sm font-medium text-gray-900 mb-2">Permissions for {{ ucwords(str_replace('-', ' ',
                        $role->name)) }}:</h5>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($role->permissions as $permission)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            {{ $permission->name }}
                        </span>
                        @endforeach
                    </div>
                    @if($role->permissions->count() == 0)
                    <p class="text-sm text-gray-500 italic">No permissions assigned to this role</p>
                    @endif
                </div>
                @endforeach
            </div>

            @error('roles')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Current Permissions Preview -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Permissions</h3>

            @php
            $currentPermissions = $user->roles->flatMap->permissions->unique('id');
            @endphp

            @if($currentPermissions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($currentPermissions->groupBy(function($permission) {
                $parts = explode(' ', $permission->name);
                return ucfirst($parts[count($parts) - 1]);
                }) as $category => $permissions)
                <div class="border border-gray-200 rounded-lg p-3">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ $category }}</h4>
                    <div class="space-y-1">
                        @foreach($permissions as $permission)
                        <div class="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded">
                            {{ $permission->name }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-key text-gray-300 text-3xl mb-2"></i>
                <p class="text-gray-500">No permissions assigned</p>
                <p class="text-sm text-gray-400">Assign roles to grant permissions</p>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Save Changes</h3>
                    <p class="text-sm text-gray-600">Update the user's role assignments</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.role-assignments.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Update Roles
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Additional Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Actions</h3>

        <div class="flex space-x-4">
            <a href="{{ route('admin.users.edit', $user) }}"
                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-user-edit mr-2"></i> Edit User Profile
            </a>

            <a href="{{ route('admin.users.show', $user) }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-eye mr-2"></i> View User Details
            </a>
        </div>
    </div>
</div>

<script>
    function toggleRoleDetails(roleId) {
    const details = document.getElementById(`role-details-${roleId}`);
    details.classList.toggle('hidden');
}

// Add warning for protected roles
document.addEventListener('DOMContentLoaded', function() {
    const protectedRoles = ['admin'];
    const checkboxes = document.querySelectorAll('input[name="roles[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const roleLabel = this.closest('.flex').querySelector('label .text-sm');
            const roleName = roleLabel.textContent.toLowerCase().replace(/\s+/g, '-');
            
            if (protectedRoles.some(role => roleName.includes(role)) && this.checked) {
                if (!confirm('You are assigning a protected role with administrative privileges. Are you sure?')) {
                    this.checked = false;
                }
            }
        });
    });
});
</script>
@endsection
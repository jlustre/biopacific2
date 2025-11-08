@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Role: {{ ucwords(str_replace('-', ' ', $role->name)) }}</h1>
        <p class="text-gray-600">View role details and permissions</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.roles.edit', $role) }}"
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('admin.roles.index') }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Role Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-tag text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600">Role Name</p>
                        <p class="text-lg font-bold text-blue-900">{{ $role->name }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-key text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600">Permissions</p>
                        <p class="text-lg font-bold text-green-900">{{ $role->permissions->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600">Assigned Users</p>
                        <p class="text-lg font-bold text-purple-900">{{ $role->users->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar text-gray-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Created</p>
                        <p class="text-lg font-bold text-gray-900">{{ $role->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($role->name, ['web-admin', 'admin']))
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-shield-alt text-yellow-600 mt-1 mr-3"></i>
                <div>
                    <h4 class="text-sm font-medium text-yellow-900">Protected Role</h4>
                    <p class="mt-1 text-sm text-yellow-800">
                        This is a system-protected role with special privileges. Some operations may be restricted.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Permissions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Assigned Permissions ({{ $role->permissions->count() }})</h3>
        </div>

        @if($role->permissions->count() > 0)
        <div class="p-6">
            @php
            $groupedPermissions = $role->permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return ucfirst($parts[count($parts) - 1]);
            });
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($groupedPermissions as $category => $permissions)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-900 mb-3">
                        {{ $category }} ({{ $permissions->count() }})
                    </h4>
                    <div class="space-y-2">
                        @foreach($permissions as $permission)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="text-sm font-medium text-gray-900">
                                {{ ucwords(str_replace(['_', '-'], ' ', $permission->name)) }}
                            </span>
                            <span class="text-xs text-gray-500 font-mono">{{ $permission->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="p-6 text-center">
            <div class="flex flex-col items-center justify-center py-8">
                <i class="fas fa-key text-gray-300 text-4xl mb-4"></i>
                <p class="text-lg font-medium text-gray-900">No permissions assigned</p>
                <p class="text-sm text-gray-500">This role doesn't have any permissions yet</p>
                <a href="{{ route('admin.roles.edit', $role) }}"
                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Permissions
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Users with this role -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Users with this role ({{ $role->users->count() }})</h3>
                @if($role->users->count() > 0)
                <a href="{{ route('admin.role-assignments.index', ['role' => $role->name]) }}"
                    class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i> Manage Assignments
                </a>
                @endif
            </div>
        </div>

        @if($role->users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Other Roles
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($role->users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 2)
                                        }}</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles->where('id', '!=', $role->id) as $otherRole)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $otherRole->name }}
                                </span>
                                @endforeach
                                @if($user->roles->where('id', '!=', $role->id)->count() == 0)
                                <span class="text-sm text-gray-500 italic">No other roles</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900"
                                title="Edit User">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-6 text-center">
            <div class="flex flex-col items-center justify-center py-8">
                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                <p class="text-lg font-medium text-gray-900">No users assigned</p>
                <p class="text-sm text-gray-500">No users have been assigned to this role yet</p>
                <a href="{{ route('admin.role-assignments.index') }}"
                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Assign Users
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                <p class="text-sm text-gray-600">Manage this role</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.roles.edit', $role) }}"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Role
                </a>
                <a href="{{ route('admin.role-assignments.index', ['role' => $role->name]) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-user-tag mr-2"></i> Manage Assignments
                </a>
                @if(!in_array($role->name, ['web-admin', 'admin']) && $role->users->count() == 0)
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block"
                    onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <i class="fas fa-trash mr-2"></i> Delete Role
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
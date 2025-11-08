@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Permission: {{ $permission->name }}</h1>
        <p class="text-gray-600">View permission details and assignments</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.permissions.edit', $permission) }}"
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('admin.permissions.index') }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Permission Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Permission Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-key text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600">Permission Name</p>
                        <p class="text-lg font-bold text-green-900 break-all">{{ $permission->name }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-tag text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600">Assigned Roles</p>
                        <p class="text-lg font-bold text-blue-900">{{ $permission->roles->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600">Total Users</p>
                        <p class="text-lg font-bold text-purple-900">{{ $permission->roles->sum(fn($role) =>
                            $role->users->count()) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-calendar text-gray-600 mt-1 mr-3"></i>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Timeline</h4>
                    <div class="mt-2 text-sm text-gray-600">
                        <p><strong>Created:</strong> {{ $permission->created_at->format('M d, Y \a\t H:i') }}</p>
                        <p><strong>Last Updated:</strong> {{ $permission->updated_at->format('M d, Y \a\t H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array($permission->name, ['access admin panel', 'manage users', 'manage roles', 'manage permissions']))
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-shield-alt text-yellow-600 mt-1 mr-3"></i>
                <div>
                    <h4 class="text-sm font-medium text-yellow-900">Protected Permission</h4>
                    <p class="mt-1 text-sm text-yellow-800">
                        This is a system-protected permission that is critical for system functionality. Some operations
                        may be restricted.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Assigned Roles -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Assigned to Roles ({{ $permission->roles->count() }})</h3>
        </div>

        @if($permission->roles->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Users Count
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Other Permissions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($permission->roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-tag text-blue-600 text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $role->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $role->users->count() }} users
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ $role->permissions->count() - 1 }} other permissions
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.roles.show', $role) }}" class="text-blue-600 hover:text-blue-900"
                                title="View Role">
                                <i class="fas fa-eye"></i>
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
                <i class="fas fa-user-tag text-gray-300 text-4xl mb-4"></i>
                <p class="text-lg font-medium text-gray-900">Not assigned to any role</p>
                <p class="text-sm text-gray-500">This permission hasn't been assigned to any roles yet</p>
                <a href="{{ route('admin.roles.index') }}"
                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-user-tag mr-2"></i> Assign to Roles
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Users with this Permission -->
    @if($permission->roles->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Users with this Permission ({{ $permission->roles->sum(fn($role) => $role->users->count()) }})
            </h3>
        </div>

        @php
        $allUsers = $permission->roles->flatMap(fn($role) => $role->users)->unique('id');
        @endphp

        @if($allUsers->count() > 0)
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
                            Via Roles
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($allUsers->take(10) as $user)
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
                                @foreach($user->roles->whereIn('id', $permission->roles->pluck('id')) as $role)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $role->name }}
                                </span>
                                @endforeach
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

        @if($allUsers->count() > 10)
        <div class="px-6 py-3 bg-gray-50 text-center text-sm text-gray-500">
            Showing 10 of {{ $allUsers->count() }} users.
            <a href="{{ route('admin.role-assignments.index') }}" class="text-blue-600 hover:text-blue-800">
                View all users
            </a>
        </div>
        @endif
        @else
        <div class="p-6 text-center">
            <div class="flex flex-col items-center justify-center py-8">
                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                <p class="text-lg font-medium text-gray-900">No users have this permission</p>
                <p class="text-sm text-gray-500">This permission hasn't been granted to any users yet</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                <p class="text-sm text-gray-600">Manage this permission</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.permissions.edit', $permission) }}"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Permission
                </a>
                <a href="{{ route('admin.roles.index') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-user-tag mr-2"></i> Manage Roles
                </a>
                @if(!in_array($permission->name, ['access admin panel', 'manage users', 'manage roles', 'manage
                permissions']) && $permission->roles->count() == 0)
                <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline-block"
                    onsubmit="return confirm('Are you sure you want to delete this permission? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <i class="fas fa-trash mr-2"></i> Delete Permission
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
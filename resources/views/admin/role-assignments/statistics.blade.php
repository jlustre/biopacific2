@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Role Assignment Statistics</h1>
        <p class="text-gray-600">Overview of user role distribution and statistics</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.role-assignments.index') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Assignments
        </a>
        <a href="{{ route('admin.users.create') }}"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-user-plus mr-2"></i> Add User
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Users Card -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        <!-- Total Roles Card -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-tag text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Roles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $roles->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Users Without Roles Card -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Users Without Roles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $usersWithoutRoles }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Distribution Chart -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Distribution</h3>
        <div class="space-y-4">
            @foreach($roleDistribution as $role)
            <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                    <span class="text-sm font-medium text-gray-700 w-32">
                        {{ ucfirst($role['name']) }}
                    </span>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full 
                                {{ $role['name'] === 'admin' ? 'bg-red-500' : '' }}
                                {{ $role['name'] === 'facility-admin' ? 'bg-blue-500' : '' }}
                                {{ $role['name'] === 'facility-editor' ? 'bg-green-500' : '' }}
                                {{ $role['name'] === 'regular-user' ? 'bg-gray-500' : '' }}
                                {{ !in_array($role['name'], ['admin', 'facility-admin', 'facility-editor', 'regular-user']) ? 'bg-purple-500' : '' }}"
                                style="width: {{ $role['percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-900">{{ $role['count'] }}</span>
                        <span class="text-sm text-gray-500">({{ $role['percentage'] }}%)</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Detailed Role Information -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Detailed Role Information</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User Count
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Percentage
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Permissions Count
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $role->name === 'facility-admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $role->name === 'facility-editor' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $role->name === 'regular-user' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ !in_array($role->name, ['admin', 'facility-admin', 'facility-editor', 'regular-user']) ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="font-medium">{{ $role->users_count }}</span>
                                @if($role->users_count > 0)
                                <a href="{{ route('admin.role-assignments.users-for-role', $role) }}"
                                    class="ml-2 text-blue-600 hover:text-blue-800 text-xs">
                                    View Users
                                </a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $totalUsers > 0 ? round(($role->users_count / $totalUsers) * 100, 1) : 0 }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $role->permissions->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('admin.roles.show', $role) }}"
                                    class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50"
                                    data-tooltip="View Role Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.roles.edit', $role) }}"
                                    class="text-green-600 hover:text-green-800 p-1 rounded hover:bg-green-50"
                                    data-tooltip="Edit Role">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.roles.create') }}"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-plus text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Create New Role</p>
                    <p class="text-sm text-gray-500">Add a new role with permissions</p>
                </div>
            </a>

            <a href="{{ route('admin.permissions.index') }}"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-key text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Manage Permissions</p>
                    <p class="text-sm text-gray-500">View and edit permissions</p>
                </div>
            </a>

            <a href="{{ route('admin.role-assignments.index') }}"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users-cog text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Assign Roles</p>
                    <p class="text-sm text-gray-500">Manage user role assignments</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Enhanced Tooltips Styles -->
<style>
    [data-tooltip] {
        position: relative;
    }

    [data-tooltip]:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1f2937;
        color: white;
        padding: 6px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        opacity: 1;
        pointer-events: none;
    }

    [data-tooltip]:hover::before {
        content: '';
        position: absolute;
        bottom: 115%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #1f2937;
        z-index: 1000;
        opacity: 1;
        pointer-events: none;
    }
</style>
@endsection
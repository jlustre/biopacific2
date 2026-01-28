@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
        <p class="text-gray-600">Manage system roles and their permissions</p>
    </div>
    <a href="{{ route('admin.roles.create') }}"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Create Role
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">All Roles ({{ $roles->count() }})</h3>
            <div class="flex space-x-2">
                <a href="{{ route('admin.role-assignments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-user-tag mr-1"></i> Manage Assignments
                </a>
                <a href="{{ route('admin.permissions.index') }}" class="text-green-600 hover:text-green-800 text-sm">
                    <i class="fas fa-key mr-1"></i> Manage Permissions
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Permissions
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Users Count
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($roles as $role)
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
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @if($role->permissions->count() > 0)
                            @foreach($role->permissions->take(3) as $permission)
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $permission->name }}
                            </span>
                            @endforeach
                            @if($role->permissions->count() > 3)
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                +{{ $role->permissions->count() - 3 }} more
                            </span>
                            @endif
                            @else
                            <span class="text-sm text-gray-500 italic">No permissions assigned</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $role->users->count() }} users
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.roles.show', $role) }}" class="text-blue-600 hover:text-blue-900"
                                title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.roles.edit', $role) }}"
                                class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($role->name !== 'admin')
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this role?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-gray-400" title="Protected role">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center py-8">
                            <i class="fas fa-user-tag text-gray-300 text-4xl mb-4"></i>
                            <p class="text-lg font-medium">No roles found</p>
                            <p class="text-sm">Create your first role to get started</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($roles->count() > 0)
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Role Overview</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-tag text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Roles</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $roles->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-key text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Total Permissions</p>
                    <p class="text-2xl font-bold text-green-900">{{ $roles->sum(fn($role) =>
                        $role->permissions->count()) }}</p>
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
                    <p class="text-2xl font-bold text-purple-900">{{ $roles->sum(fn($role) => $role->users->count()) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
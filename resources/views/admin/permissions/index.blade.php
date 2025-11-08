@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Permission Management</h1>
        <p class="text-gray-600">Manage system permissions and their assignments</p>
    </div>
    <a href="{{ route('admin.permissions.create') }}"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Create Permission
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-key text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Permissions</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $permissions->flatten()->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-layer-group text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Categories</p>
                    <p class="text-2xl font-bold text-green-900">{{ $permissions->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-tag text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-600">Assigned Permissions</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $permissions->flatten()->sum(fn($p) =>
                        $p->roles->count()) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex space-x-4 text-sm">
            <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-user-tag mr-1"></i> Manage Roles
            </a>
            <a href="{{ route('admin.role-assignments.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-users mr-1"></i> Role Assignments
            </a>
        </div>
    </div>

    <!-- Permissions by Category -->
    <div class="space-y-6">
        @foreach($permissions as $category => $categoryPermissions)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $category }} Permissions ({{ $categoryPermissions->count() }})
                    </h3>
                    <div class="text-sm text-gray-500">
                        {{ $categoryPermissions->sum(fn($p) => $p->roles->count()) }} total role assignments
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Permission Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assigned Roles
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
                        @foreach($categoryPermissions as $permission)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-key text-green-600 text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ ucwords(str_replace(['_', '-'], ' ', $permission->name)) }}
                                        </div>
                                        <div class="text-sm text-gray-500 font-mono">{{ $permission->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($permission->roles->count() > 0)
                                    @foreach($permission->roles->take(3) as $role)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $role->name }}
                                    </span>
                                    @endforeach
                                    @if($permission->roles->count() > 3)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        +{{ $permission->roles->count() - 3 }} more
                                    </span>
                                    @endif
                                    @else
                                    <span class="text-sm text-gray-500 italic">Not assigned to any role</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                $userCount = $permission->roles->sum(fn($role) => $role->users->count());
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $userCount > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $userCount }} users
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.permissions.show', $permission) }}"
                                        class="text-blue-600 hover:text-blue-900" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.permissions.edit', $permission) }}"
                                        class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!in_array($permission->name, ['access admin panel', 'manage users', 'manage
                                    roles', 'manage permissions']))
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this permission?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-gray-400" title="Protected permission">
                                        <i class="fas fa-shield-alt"></i>
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    @if($permissions->flatten()->count() == 0)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center py-8">
            <i class="fas fa-key text-gray-300 text-4xl mb-4"></i>
            <p class="text-lg font-medium text-gray-900">No permissions found</p>
            <p class="text-sm text-gray-500">Create your first permission to get started</p>
            <a href="{{ route('admin.permissions.create') }}"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Create Permission
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
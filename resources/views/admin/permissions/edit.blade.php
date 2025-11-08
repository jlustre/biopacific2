@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Permission: {{ $permission->name }}</h1>
        <p class="text-gray-600">Modify permission settings</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.permissions.show', $permission) }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-eye mr-2"></i> View
        </a>
        <a href="{{ route('admin.permissions.index') }}"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permission Information</h3>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Permission Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $permission->name) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 {{ in_array($permission->name, ['access admin panel', 'manage users', 'manage roles', 'manage permissions']) ? 'bg-gray-100' : '' }}"
                    {{ in_array($permission->name, ['access admin panel', 'manage users', 'manage roles', 'manage
                permissions']) ? 'readonly' : '' }}
                required>
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if(in_array($permission->name, ['access admin panel', 'manage users', 'manage roles', 'manage
                permissions']))
                <p class="mt-1 text-sm text-yellow-600">
                    <i class="fas fa-shield-alt mr-1"></i>
                    This is a protected permission. The name cannot be changed.
                </p>
                @else
                <p class="mt-1 text-sm text-gray-500">
                    Use lowercase letters, numbers, spaces, and hyphens only.
                </p>
                @endif
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900">Permission Information</h4>
                        <div class="mt-2 text-sm text-blue-800">
                            <p><strong>Assigned to roles:</strong> {{ $permission->roles->count() }}</p>
                            <p><strong>Total users with permission:</strong> {{ $permission->roles->sum(fn($role) =>
                                $role->users->count()) }}</p>
                            <p><strong>Created:</strong> {{ $permission->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($permission->roles->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assigned to Roles</h3>
            <div class="space-y-3">
                @foreach($permission->roles as $role)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-tag text-blue-600 text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('-', ' ',
                                $role->name)) }}</div>
                            <div class="text-sm text-gray-500">{{ $role->users->count() }} users</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.roles.show', $role) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        View Role <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Update Permission</h3>
                    <p class="text-sm text-gray-600">Save changes to this permission</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.permissions.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i> Update Permission
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
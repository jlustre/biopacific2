@extends('layouts.dashboard')
@section('title', 'User Management')

@section('content')
<div class="container mx-auto p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage system users and their permissions</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i>Add New User
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 mb-4 rounded-lg border border-green-200">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Search and Filter Section -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div class="sm:col-span-2 lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Users</label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by name or email..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Role Filter -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Filter by Role</label>
                    <select name="role" id="role"
                        class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" {{ request('role')==='all' ? 'selected' : '' }}>All Roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role')===$role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Facility Filter -->
                <div>
                    <label for="facility" class="block text-sm font-medium text-gray-700 mb-1">Filter by
                        Facility</label>
                    <select name="facility" id="facility"
                        class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" {{ request('facility')==='all' ? 'selected' : '' }}>All Facilities</option>
                        <option value="corporate" {{ request('facility')==='corporate' ? 'selected' : '' }}>Bio-Pacific
                            Corporate</option>
                        @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" {{ request('facility')==$facility->id ? 'selected' : '' }}>
                            {{ $facility->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                </div>
                <div class="text-sm text-gray-600 text-center sm:text-right">
                    Showing {{ $users->count() }} of {{ $users->total() }} users
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden lg:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Facility</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @foreach($user->roles as $role)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $role->name === 'facility-admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $role->name === 'facility-editor' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $role->name === 'regular-user' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ !in_array($role->name, ['admin', 'facility-admin', 'facility-editor', 'regular-user']) ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ ucfirst($role->name) }}
                            </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $user->facility ? $user->facility->name : 'Bio-Pacific Corporate' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <!-- Edit Icon -->
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50 transition-all duration-200"
                                    data-tooltip="Edit User">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <!-- Delete Icon -->
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition-all duration-200"
                                        data-tooltip="Delete User">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">No users found</p>
                            <p class="text-sm">Try adjusting your search or filter criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse($users as $user)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <!-- User Info -->
                        <div class="flex items-center mb-2">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-medium text-sm">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>

                        <!-- Role and Facility Info -->
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($user->roles as $role)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $role->name === 'admin' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $role->name === 'facility-admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $role->name === 'facility-editor' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $role->name === 'regular-user' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ !in_array($role->name, ['admin', 'facility-admin', 'facility-editor', 'regular-user']) ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ ucfirst($role->name) }}
                            </span>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                <i class="fas fa-building mr-1"></i>
                                {{ $user->facility ? $user->facility->name : 'Bio-Pacific Corporate' }}
                            </span>
                        </div>

                        <!-- ID Badge -->
                        <div class="text-xs text-gray-500">
                            User ID: #{{ $user->id }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 ml-4">
                        <!-- Edit Icon -->
                        <a href="{{ route('admin.users.edit', $user) }}"
                            class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50 transition-all duration-200"
                            data-tooltip="Edit User">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                        <!-- Delete Icon -->
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition-all duration-200"
                                data-tooltip="Delete User">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-lg font-medium">No users found</p>
                <p class="text-sm">Try adjusting your search or filter criteria.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="mt-6">
        {{ $users->links() }}
    </div>
    @endif
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

    /* Search input focus styles */
    .search-input:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Mobile responsiveness improvements */
    @media (max-width: 640px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        /* Adjust badge sizes for mobile */
        .mobile-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Improve button spacing on mobile */
        .mobile-buttons {
            gap: 0.5rem;
        }
    }

    /* Tablet adjustments */
    @media (min-width: 641px) and (max-width: 1023px) {
        .tablet-layout {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<!-- Enhanced Search Functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    const roleFilter = document.getElementById('role');
    const facilityFilter = document.getElementById('facility');
    
    if (roleFilter) {
        roleFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (facilityFilter) {
        facilityFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }

    // Clear search input button
    const searchInput = document.getElementById('search');
    if (searchInput && searchInput.value) {
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600';
        clearButton.innerHTML = '<i class="fas fa-times"></i>';
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.form.submit();
        });
        
        const searchContainer = searchInput.parentElement;
        searchContainer.appendChild(clearButton);
    }
});
</script>
@endsection
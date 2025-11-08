@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Role Assignments</h1>
        <p class="text-gray-600">Manage user role assignments and permissions</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.role-assignments.statistics') }}"
            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-chart-pie mr-2"></i> Statistics
        </a>
        <a href="{{ route('admin.users.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-user-plus mr-2"></i> Add User
        </a>
    </div>
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

    <!-- Quick Navigation -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex space-x-4 text-sm">
            <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-user-tag mr-1"></i> Manage Roles
            </a>
            <a href="{{ route('admin.permissions.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-key mr-1"></i> Manage Permissions
            </a>
            <a href="{{ route('admin.users.index') }}" class="text-purple-600 hover:text-purple-800">
                <i class="fas fa-users mr-1"></i> Manage Users
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>

        <form method="GET" action="{{ route('admin.role-assignments.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Filter by Role</label>
                    <select name="role" id="role"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role')==$role->name ? 'selected' : '' }}>
                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Users</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Name or email..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-end">
                    <div class="flex space-x-2 w-full">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex-1">
                            <i class="fas fa-search mr-2"></i> Filter
                        </button>
                        <a href="{{ route('admin.role-assignments.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow p-6" id="bulk-actions" style="display: none;">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>

        <form id="bulk-form" method="POST" action="{{ route('admin.role-assignments.bulk-assign') }}">
            @csrf
            <div class="flex items-center space-x-4">
                <div>
                    <label for="bulk-role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role_id" id="bulk-role"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                        <option value="">Select Role...</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ ucwords(str_replace('-', ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="bulk-action" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select name="action" id="bulk-action"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                        <option value="assign">Assign Role</option>
                        <option value="remove">Remove Role</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Apply to Selected
                    </button>
                    <button type="button" onclick="clearSelection()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Clear Selection
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Users ({{ $users->total() }})
                    @if(request('role'))
                    - Filtered by: {{ ucwords(str_replace('-', ' ', request('role'))) }}
                    @endif
                </h3>
                <div class="text-sm text-gray-500">
                    <span id="selected-count">0</span> selected
                </div>
            </div>
        </div>

        @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Current Roles
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Permissions Count
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Login
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                class="user-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">{{ $user->initials }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                    <button type="button" onclick="quickRemoveRole({{ $user->id }}, {{ $role->id }})"
                                        class="ml-1 text-blue-600 hover:text-blue-800" title="Remove role">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                                @endforeach
                                @else
                                <span class="text-sm text-gray-500 italic">No roles assigned</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                            $permissionCount = $user->roles->flatMap->permissions->unique('id')->count();
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $permissionCount > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $permissionCount }} permissions
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.role-assignments.edit', $user) }}"
                                    class="text-blue-600 hover:text-blue-900" title="Manage Roles">
                                    <i class="fas fa-user-tag"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="text-yellow-600 hover:text-yellow-900" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <div class="relative inline-block">
                                    <button type="button" onclick="toggleQuickAssign({{ $user->id }})"
                                        class="text-green-600 hover:text-green-900" title="Quick Assign Role">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <div id="quick-assign-{{ $user->id }}"
                                        class="hidden absolute right-0 top-6 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                                        <div class="p-2">
                                            <select class="w-full border border-gray-300 rounded px-2 py-1 text-xs"
                                                onchange="quickAssignRole({{ $user->id }}, this.value)">
                                                <option value="">Select role to assign...</option>
                                                @foreach($roles as $role)
                                                @if(!$user->hasRole($role->name))
                                                <option value="{{ $role->id }}">{{ ucwords(str_replace('-', ' ',
                                                    $role->name)) }}</option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @else
        <div class="p-6 text-center">
            <div class="flex flex-col items-center justify-center py-8">
                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                <p class="text-lg font-medium text-gray-900">No users found</p>
                <p class="text-sm text-gray-500">
                    @if(request('role') || request('search'))
                    Try adjusting your filters or
                    <a href="{{ route('admin.role-assignments.index') }}"
                        class="text-blue-600 hover:text-blue-800">clear filters</a>
                    @else
                    Create your first user to get started
                    @endif
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    // Bulk selection functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    updateBulkActions();
});

document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const count = selectedCheckboxes.length;
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    selectedCount.textContent = count;
    
    if (count > 0) {
        bulkActions.style.display = 'block';
        // Update hidden inputs for bulk form
        const form = document.getElementById('bulk-form');
        const existingInputs = form.querySelectorAll('input[name="user_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        selectedCheckboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });
    } else {
        bulkActions.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(checkbox => checkbox.checked = false);
    document.getElementById('select-all').checked = false;
    updateBulkActions();
}

// Quick assign/remove functionality
function toggleQuickAssign(userId) {
    const dropdown = document.getElementById(`quick-assign-${userId}`);
    dropdown.classList.toggle('hidden');
    
    // Hide other dropdowns
    document.querySelectorAll('[id^="quick-assign-"]:not([id="quick-assign-' + userId + '"])').forEach(el => {
        el.classList.add('hidden');
    });
}

function quickAssignRole(userId, roleId) {
    if (!roleId) return;
    
    fetch('{{ route("admin.role-assignments.quick-assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: userId,
            role_id: roleId,
            action: 'assign'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function quickRemoveRole(userId, roleId) {
    if (!confirm('Are you sure you want to remove this role?')) return;
    
    fetch('{{ route("admin.role-assignments.quick-assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: userId,
            role_id: roleId,
            action: 'remove'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Hide quick assign dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick*="toggleQuickAssign"]') && !event.target.closest('[id^="quick-assign-"]')) {
        document.querySelectorAll('[id^="quick-assign-"]').forEach(el => {
            el.classList.add('hidden');
        });
    }
});
</script>
@endsection
@extends('layouts.dashboard', ['title' => 'Position Portal Roles'])

@section('content')
<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Position Portal Roles</h1>
        <p class="text-gray-600 mt-2">Map job positions to portal roles assigned automatically when employees self-register.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.positions.index') }}"
            class="inline-flex items-center justify-center whitespace-nowrap bg-white text-gray-700 px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition font-semibold">
            <i class="fas fa-briefcase mr-2"></i> Positions
        </a>
        <form method="POST" action="{{ route('admin.position-portal-roles.sync-seeder') }}" class="inline"
            onsubmit="return confirm('Export every position portal role mapping into database/seeders/data/position_portal_role_mappings.php?\n\nThis overwrites that file. Commit it to git so migrate:fresh --seed restores the current mappings.');">
            @csrf
            <button type="submit"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-amber-300 bg-amber-50 px-5 py-2 font-semibold text-amber-900 transition hover:bg-amber-100">
                <i class="fas fa-database mr-2"></i> Update Seeder
            </button>
        </form>
        <form method="POST" action="{{ route('admin.position-portal-roles.sync-defaults') }}" class="inline">
            @csrf
            <button type="submit"
                class="inline-flex items-center justify-center whitespace-nowrap bg-amber-600 text-white px-5 py-2 rounded-lg hover:bg-amber-700 transition font-semibold"
                onclick="return confirm('Import default mappings from leadership configuration? Existing mappings for the same positions will be updated.');">
                <i class="fas fa-sync mr-2"></i> Sync Defaults
            </button>
        </form>
        <a href="{{ route('admin.position-portal-roles.create') }}"
            class="inline-flex items-center justify-center whitespace-nowrap bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
            <i class="fas fa-plus mr-2"></i> Add Mapping
        </a>
    </div>
</div>

<div class="space-y-6 mt-6">
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <ul class="text-red-700 list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-green-800"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form action="{{ route('admin.position-portal-roles.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Search position title..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Portal Role</label>
                <select name="role" id="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                        {{ \App\Models\User::roleDisplayLabel($role->name) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Portal Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($mappings as $mapping)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                        {{ $mapping->position?->title ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $mapping->position?->department?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800">
                            {{ \App\Models\User::roleDisplayLabel($mapping->role_name) }}
                        </span>
                        <div class="text-[11px] text-gray-500 mt-1">{{ $mapping->role_name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($mapping->is_active)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Active</span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('admin.position-portal-roles.edit', $mapping) }}" class="text-blue-600 hover:text-blue-800 font-semibold mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.position-portal-roles.destroy', $mapping) }}" class="inline"
                            onsubmit="return confirm('Remove this position role mapping?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        No mappings yet.
                        <a href="{{ route('admin.position-portal-roles.create') }}" class="text-blue-600 hover:underline font-semibold">Add one</a>
                        or
                        <form method="POST" action="{{ route('admin.position-portal-roles.sync-defaults') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:underline font-semibold">sync defaults</button>
                        </form>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($mappings->hasPages())
    <div class="flex justify-center">
        {{ $mappings->links() }}
    </div>
    @endif
</div>
@endsection

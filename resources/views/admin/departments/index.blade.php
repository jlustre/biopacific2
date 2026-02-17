@extends('layouts.dashboard', ['title' => 'Departments Management'])

@section('content')
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Departments Management</h1>
        <p class="text-gray-600 mt-2">Manage organizational departments and their types</p>
    </div>
    <a href="{{ route('admin.departments.create') }}"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
        <i class="fas fa-plus mr-2"></i> Create Department
    </a>
</div>

<div class="space-y-6">
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h3 class="text-red-800 font-semibold mb-2">Validation Errors</h3>
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

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form action="{{ route('admin.departments.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" placeholder="Search by name or description..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach ($types as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}" {{ request('type')==$typeKey ? 'selected' : '' }}>
                            {{ $typeLabel }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Departments Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Positions</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($departments as $department)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <span class="font-semibold text-gray-900">{{ $department->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="inline-block {{ $department->type === 'facility' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }} text-xs px-3 py-1 rounded-full font-medium">
                            {{ ucfirst($department->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">
                            {{ $department->positions_count }} position(s)
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ isset($department->description) && strlen($department->description) > 0 ?
                        (strlen($department->description) > 50 ? substr($department->description, 0, 50) . '...' :
                        $department->description) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.departments.show', $department) }}"
                            class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.departments.edit', $department) }}"
                            class="text-green-600 hover:text-green-900 text-sm font-medium">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.departments.destroy', $department) }}" method="POST"
                            class="inline-block"
                            onsubmit="return confirm('Are you sure you want to delete this department? You must reassign all positions first.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-600">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p>No departments found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $departments->links() }}
    </div>
</div>
@endsection
@extends('layouts.dashboard', ['title' => 'Positions Management'])

@section('content')
@php
    $isDon = auth()->user()?->hasRole('don') ?? false;
@endphp
<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Positions Management</h1>
        <p class="text-gray-600 mt-2">Manage job positions and their departments</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        @unless($isDon)
        <a href="{{ route('admin.departments.index') }}"
            class="inline-flex items-center justify-center whitespace-nowrap bg-white text-gray-700 px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition font-semibold">
            <i class="fas fa-sitemap mr-2"></i> Departments Management
        </a>
        <a href="{{ route('admin.positions.create') }}"
            class="inline-flex items-center justify-center whitespace-nowrap bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
            <i class="fas fa-plus mr-2"></i> Create Position
        </a>
        @endunless
    </div>
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
        <form action="{{ route('admin.positions.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" placeholder="Search by title or description..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department" id="department"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ $isDon ? 'Nursing' : 'All Departments' }}</option>
                        @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string)($selectedDepartmentId ?? request('department')) === (string) $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
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

    <!-- Positions Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Reports To</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($positions as $position)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <span class="font-semibold text-gray-900 text-sm">{{ $position->title }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">
                            {{ $position->department->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-xs">
                        {{ $position->reportsToPosition->title ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-xs">
                        {{ isset($position->description) && strlen($position->description) > 0 ?
                        (strlen($position->description) > 50 ? substr($position->description, 0, 50) . '...' :
                        $position->description) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                        <a href="{{ route('admin.positions.show', $position) }}"
                            class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50 transition-all duration-200"
                            data-tooltip="View Position"
                            aria-label="View Position">
                            <i class="fas fa-eye text-sm"></i>
                            <span class="sr-only">View Position</span>
                        </a>
                        <a href="{{ route('admin.positions.edit', $position) }}"
                            class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-50 transition-all duration-200"
                            data-tooltip="Edit Position"
                            aria-label="Edit Position">
                            <i class="fas fa-edit text-sm"></i>
                            <span class="sr-only">Edit Position</span>
                        </a>
                        <form action="{{ route('admin.positions.destroy', $position) }}" method="POST"
                            class="inline-block"
                            onsubmit="return confirm('Are you sure you want to delete this position?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition-all duration-200"
                                data-tooltip="Delete Position"
                                aria-label="Delete Position">
                                <i class="fas fa-trash text-sm"></i>
                                <span class="sr-only">Delete Position</span>
                            </button>
                        </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-600">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p>No positions found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $positions->links() }}
    </div>
</div>

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
        color: #fff;
        padding: 6px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
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
        pointer-events: none;
    }
</style>
@endsection
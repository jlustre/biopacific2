@extends('layouts.dashboard', ['title' => 'General Inquiries'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">General Inquiries</h1>
        <p class="text-gray-600">Manage and review general inquiries from facility websites.</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('admin.inquiries.index') }}"
            class="flex flex-col space-y-4 lg:space-y-0 lg:flex-row lg:items-center lg:gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-0">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name, email, or phone..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
            </div>

            <!-- Facility Filter -->
            <div class="w-full lg:w-48">
                <select name="facility"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility')==$facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.inquiries.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Desktop Table / Mobile Cards -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <!-- Desktop Table View (hidden on mobile) -->
        <div class="hidden lg:block">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Facility</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Message Preview</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $inquiry->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $inquiry->email }}</div>
                                    @if($inquiry->phone)
                                    <div class="text-sm text-gray-500">{{ $inquiry->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $inquiry->facility->name }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($inquiry->message, 100) }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $inquiry->created_at->format('M j, Y g:i A')
                                }}</td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this inquiry?')"
                                            class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-question-circle text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No inquiries found</h3>
                                    <p class="text-gray-500">No general inquiries have been submitted yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View (visible on mobile and tablet) -->
        <div class="block lg:hidden">
            @forelse($inquiries as $inquiry)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900">{{ $inquiry->full_name }}</h3>
                    <span class="text-xs text-gray-500">{{ $inquiry->created_at->format('M j, Y') }}</span>
                </div>

                <div class="space-y-1 mb-3">
                    <div class="text-sm text-gray-600">{{ $inquiry->email }}</div>
                    @if($inquiry->phone)
                    <div class="text-sm text-gray-600">{{ $inquiry->phone }}</div>
                    @endif
                    <div class="text-sm font-medium text-gray-900">{{ $inquiry->facility->name }}</div>
                </div>

                <div class="text-sm text-gray-700 mb-3">
                    {{ Str::limit($inquiry->message, 120) }}
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                        class="text-indigo-600 hover:text-indigo-900 text-sm">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this inquiry?')"
                            class="text-red-600 hover:text-red-900 text-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <i class="fas fa-question-circle text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No inquiries found</h3>
                    <p class="text-gray-500">No general inquiries have been submitted yet.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($inquiries->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $inquiries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
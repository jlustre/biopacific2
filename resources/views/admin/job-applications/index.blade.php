@extends('layouts.dashboard', ['title' => 'Job Applications'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Job Applications</h1>
        <p class="text-gray-600">Manage and review job applications from career postings.</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <form id="filterForm" method="GET" action="{{ route('admin.job-applications.index') }}"
            class="flex flex-col space-y-4 lg:space-y-0 lg:flex-row lg:items-center lg:gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-0">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                    placeholder="Search by name, email, phone, or job title..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
            </div>

            <!-- Filters Row -->
            <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:gap-4">
                <!-- Facility Filter -->
                <div class="w-full sm:w-48">
                    <select name="facility" id="facilityFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">All Facilities</option>
                        @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" {{ request('facility')==$facility->id ? 'selected' : '' }}>
                            {{ $facility->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-48">
                    <select name="status" id="statusFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status')==$status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.job-applications.index') }}" id="clearFilters"
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
                                Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Job Opening</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date Applied</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($jobApplications as $application)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $application->first_name }} {{
                                        $application->last_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $application->email }}</div>
                                    @if($application->phone)
                                    <div class="text-sm text-gray-500">{{ $application->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $application->jobOpening->title ??
                                    'N/A' }}</div>
                                @if($application->jobOpening && $application->jobOpening->facility)
                                <div class="text-sm text-gray-500">{{ $application->jobOpening->facility->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($application->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($application->status === 'reviewed') bg-blue-100 text-blue-800
                                    @elseif($application->status === 'interview') bg-purple-100 text-purple-800
                                    @elseif($application->status === 'pre-employment') bg-teal-100 text-teal-800
                                    @elseif($application->status === 'hired') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $application->created_at->format('M j, Y g:i
                                A') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $application->jobOpening->facility->id ?? $application->facility_id, 'application' => $application->id]) }}"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <form action="{{ route('admin.job-applications.destroy', $application) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this application?')"
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
                                    <i class="fas fa-briefcase text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No applications found</h3>
                                    <p class="text-gray-500">No job applications have been submitted yet.</p>
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
            @forelse($jobApplications as $application)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900">{{ $application->first_name }} {{
                        $application->last_name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($application->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($application->status === 'reviewed') bg-blue-100 text-blue-800
                        @elseif($application->status === 'interview') bg-purple-100 text-purple-800
                        @elseif($application->status === 'pre-employment') bg-teal-100 text-teal-800
                        @elseif($application->status === 'hired') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($application->status) }}
                    </span>
                </div>

                <div class="space-y-1 mb-3">
                    <div class="text-sm text-gray-600">{{ $application->email }}</div>
                    @if($application->phone)
                    <div class="text-sm text-gray-600">{{ $application->phone }}</div>
                    @endif
                    <div class="text-sm font-medium text-gray-900">{{ $application->jobOpening->title ?? 'N/A' }}</div>
                    @if($application->jobOpening && $application->jobOpening->facility)
                    <div class="text-sm text-gray-600">{{ $application->jobOpening->facility->name }}</div>
                    @endif
                    <div class="text-xs text-gray-500">{{ $application->created_at->format('M j, Y g:i A') }}</div>
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <a href="{{ route('admin.facility.pre-employment.review', ['facility' => $application->jobOpening->facility->id ?? $application->facility_id, 'application' => $application->id]) }}"
                        class="text-indigo-600 hover:text-indigo-900 text-sm">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <form action="{{ route('admin.job-applications.destroy', $application) }}" method="POST"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('Are you sure you want to delete this application?')"
                            class="text-red-600 hover:text-red-900 text-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <i class="fas fa-briefcase text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No applications found</h3>
                    <p class="text-gray-500">No job applications have been submitted yet.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($jobApplications->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $jobApplications->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const facilityFilter = document.getElementById('facilityFilter');
    const statusFilter = document.getElementById('statusFilter');
    const filterForm = document.getElementById('filterForm');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    let searchTimeout;

    // Auto-submit form when search input changes (with debounce)
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            filterForm.submit();
        }, 500); // Wait 500ms after user stops typing
    });

    // Auto-submit form when filters change
    facilityFilter.addEventListener('change', function() {
        filterForm.submit();
    });

    statusFilter.addEventListener('change', function() {
        filterForm.submit();
    });

    // Clear all filters functionality
    clearFiltersBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Clear all form fields
        searchInput.value = '';
        facilityFilter.selectedIndex = 0;
        statusFilter.selectedIndex = 0;
        
        // Submit the cleared form
        filterForm.submit();
    });

    // Show loading state during form submission
    filterForm.addEventListener('submit', function() {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
        submitBtn.disabled = true;
        
        // Re-enable after a short delay in case of errors
        setTimeout(function() {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });
});
</script>

@endsection
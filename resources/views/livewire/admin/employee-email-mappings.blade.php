<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Employee Email Mappings</h1>
        <p class="text-gray-600">Manage employee email assignments for different communication categories.</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <!-- Configuration Warnings -->
    @if($warnings->isNotEmpty())
    <div class="mb-4">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Configuration Warnings ({{ $warnings->count() }})
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach($warnings as $warning)
                            <li class="flex items-center justify-between">
                                <span>
                                    <strong>{{ $warning['facility'] }}</strong> - {{ $warning['category'] }}:
                                    {{ $warning['message'] }}
                                </span>
                                @if($warning['type'] === 'danger')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 ml-2">
                                    Critical
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                    Warning
                                </span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <div class="flex flex-col space-y-4 lg:space-y-0 lg:flex-row lg:items-center lg:justify-between lg:gap-4">
            <!-- Search and Filters -->
            <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:gap-4 flex-1">
                <!-- Search -->
                <div class="flex-1 min-w-0">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Search by name, email, or position..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" />
                </div>

                <!-- Filters Row -->
                <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:gap-4">
                    <!-- Facility Filter -->
                    <div class="w-full sm:w-48">
                        <select wire:model.live="selectedFacility"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Facilities</option>
                            @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="w-full sm:w-48">
                        <select wire:model.live="selectedCategory"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Add Button -->
            <div class="flex justify-end lg:justify-start">
                <div class="relative group">
                    <button wire:click="create"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        <span class="hidden sm:inline">Add Employee Mapping</span>
                        <span class="sm:hidden">Add Mapping</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50">
        <div class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            Loading...
        </div>
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
                                Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Facility</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($employeeMappings as $mapping)
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center">
                                    <div>
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $mapping->employee_name }}
                                            </div>
                                            @if($mapping->is_primary && $mapping->is_active)
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Primary
                                            </span>
                                            @elseif($mapping->is_primary && !$mapping->is_active)
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Primary (Inactive)
                                            </span>
                                            @endif

                                            @php
                                            $effectivePrimary =
                                            \App\Models\EmployeeEmailMapping::getEffectivePrimary($mapping->facility_id,
                                            $mapping->category);
                                            $isEffectivePrimary = $effectivePrimary && $effectivePrimary->id ===
                                            $mapping->id && !$mapping->is_primary;
                                            @endphp

                                            @if($isEffectivePrimary)
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Effective Primary
                                            </span>
                                            @endif
                                        </div>
                                        @if($mapping->position)
                                        <div class="text-sm text-gray-500">{{ $mapping->position }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ \Illuminate\Support\Str::limit($mapping->facility->name, 16, '...') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($mapping->category === 'book-a-tour') bg-green-100 text-green-800
                                    @elseif($mapping->category === 'inquiry') bg-blue-100 text-blue-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    {{ $categories[$mapping->category] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $mapping->employee_email }}</td>
                            <td class="px-6 py-4">
                                <div class="relative group">
                                    <button wire:click="toggleStatus({{ $mapping->id }})" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium cursor-pointer transition-colors duration-150
                                            {{ $mapping->is_active 
                                                ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                                : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        @if($mapping->is_active)
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        @else
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        @endif
                                        {{ $mapping->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                        Click to {{ $mapping->is_active ? 'deactivate' : 'activate' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    @if(!$mapping->is_primary && $mapping->is_active)
                                    <!-- Make Primary Button -->
                                    <div class="relative group">
                                        <button wire:click="makePrimary({{ $mapping->id }})"
                                            onclick="return confirm('Make {{ $mapping->employee_name }} the primary contact? This will remove primary status from the current primary.')"
                                            class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition duration-150 ease-in-out cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                                </path>
                                            </svg>
                                        </button>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                            Make Primary Contact
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Edit Button -->
                                    <div class="relative group">
                                        <button wire:click="edit({{ $mapping->id }})"
                                            class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition duration-150 ease-in-out cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                            Edit Mapping
                                        </div>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="relative group">
                                        <button wire:click="delete({{ $mapping->id }})"
                                            onclick="return confirm('Are you sure you want to delete this mapping?')"
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-150 ease-in-out cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                            Delete Mapping
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m8-8v2m0 6V9.5M9 21h6">
                                        </path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No employee mappings found</h3>
                                    <p class="text-gray-500">Get started by creating your first employee email mapping.
                                    </p>
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
            @forelse($employeeMappings as $mapping)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 text-sm">
                <!-- Employee Name and Primary Status -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <h3 class="text-sm font-medium text-gray-900">{{ $mapping->employee_name }}</h3>
                        @if($mapping->is_primary && $mapping->is_active)
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            Primary
                        </span>
                        @elseif($mapping->is_primary && !$mapping->is_active)
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                            Primary (Inactive)
                        </span>
                        @endif

                        @php
                        $effectivePrimary = \App\Models\EmployeeEmailMapping::getEffectivePrimary($mapping->facility_id,
                        $mapping->category);
                        $isEffectivePrimary = $effectivePrimary && $effectivePrimary->id === $mapping->id &&
                        !$mapping->is_primary;
                        @endphp

                        @if($isEffectivePrimary)
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                            Effective Primary
                        </span>
                        @endif
                    </div>

                    <!-- Status Toggle -->
                    <div class="relative group">
                        <button wire:click="toggleStatus({{ $mapping->id }})" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors duration-150
                                    {{ $mapping->is_active 
                                        ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                        : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                            @if($mapping->is_active)
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            @else
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            @endif
                            {{ $mapping->is_active ? 'Active' : 'Inactive' }}
                        </button>
                        <!-- Tooltip -->
                        <div
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                            Click to {{ $mapping->is_active ? 'deactivate' : 'activate' }}
                        </div>
                    </div>
                </div>

                <!-- Position -->
                @if($mapping->position)
                <p class="text-sm text-gray-600 mb-2">{{ $mapping->position }}</p>
                @endif

                <!-- Email -->
                <p class="text-sm text-gray-900 mb-2">
                    <span class="font-medium">Email:</span> {{ $mapping->employee_email }}
                </p>

                <!-- Facility and Category -->
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="text-sm text-gray-600">
                        <span class="font-medium">Facility:</span> {{ \Illuminate\Support\Str::limit($mapping->facility->name, 16, '...') }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($mapping->category === 'book-a-tour') bg-green-100 text-green-800
                            @elseif($mapping->category === 'inquiry') bg-blue-100 text-blue-800
                            @else bg-purple-100 text-purple-800 @endif">
                        {{ $categories[$mapping->category] }}
                    </span>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-2">
                    @if(!$mapping->is_primary && $mapping->is_active)
                    <!-- Make Primary Button -->
                    <div class="relative group">
                        <button wire:click="makePrimary({{ $mapping->id }})"
                            onclick="return confirm('Make {{ $mapping->employee_name }} the primary contact? This will remove primary status from the current primary.')"
                            class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition duration-150 ease-in-out cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                </path>
                            </svg>
                        </button>
                        <!-- Tooltip -->
                        <div
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                            Make Primary Contact
                        </div>
                    </div>
                    @endif

                    <!-- Edit Button -->
                    <div class="relative group">
                        <button wire:click="edit({{ $mapping->id }})"
                            class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition duration-150 ease-in-out cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                        <!-- Tooltip -->
                        <div
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                            Edit Mapping
                        </div>
                    </div>

                    <!-- Delete Button -->
                    <div class="relative group">
                        <button wire:click="delete({{ $mapping->id }})"
                            onclick="return confirm('Are you sure you want to delete this mapping?')"
                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition duration-150 ease-in-out cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                        <!-- Tooltip -->
                        <div
                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                            Delete Mapping
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m8-8v2m0 6V9.5M9 21h6">
                        </path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No employee mappings found</h3>
                    <p class="text-gray-500">Get started by creating your first employee email mapping.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($employeeMappings->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $employeeMappings->links() }}
        </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <div
                class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ $editMode ? 'Edit Employee Mapping' : 'Add Employee Mapping' }}
                        </h3>

                        <!-- Facility -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facility *</label>
                            <select wire:model="facility_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Facility</option>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            </select>
                            @error('facility_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select wire:model="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Employee Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee Name *</label>
                            <input type="text" wire:model="employee_name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="John Doe">
                            @error('employee_name') <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Employee Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee Email *</label>
                            <input type="email" wire:model="employee_email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="john.doe@biopacific.com">
                            @error('employee_email') <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Position -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                            <input type="text" wire:model="position"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Manager, Coordinator, etc.">
                            @error('position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Checkboxes -->
                        <div class="flex items-center space-x-6 mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_primary"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Primary Contact</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_active"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition duration-150 ease-in-out">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </button>
                        <button type="button" wire:click="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition duration-150 ease-in-out">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
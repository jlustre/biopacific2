<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Email Recipients Management</h1>
        <p class="text-gray-600">Manage public-facing email addresses for different communication categories.</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
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
                        placeholder="Search by email addresses or category..."
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
                        <span class="hidden sm:inline">Add Email Recipient</span>
                        <span class="sm:hidden">Add Recipient</span>
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
                                Facility</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Primary Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Alternative Emails</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($emailRecipients as $recipient)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $recipient->facility->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($recipient->category === 'book-a-tour') bg-green-100 text-green-800
                                        @elseif($recipient->category === 'inquiry') bg-blue-100 text-blue-800
                                        @else bg-purple-100 text-purple-800 @endif">
                                    {{ $categories[$recipient->category] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $recipient->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="space-y-1">
                                    @if($recipient->email_alt_1)
                                    <div>{{ $recipient->email_alt_1 }}</div>
                                    @endif
                                    @if($recipient->email_alt_2)
                                    <div>{{ $recipient->email_alt_2 }}</div>
                                    @endif
                                    @if(!$recipient->email_alt_1 && !$recipient->email_alt_2)
                                    <span class="text-gray-400 italic">No alternatives</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <!-- Edit Button -->
                                    <div class="relative group">
                                        <button wire:click="edit({{ $recipient->id }})"
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
                                            Edit Recipient
                                        </div>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="relative group">
                                        <button wire:click="delete({{ $recipient->id }})"
                                            onclick="return confirm('Are you sure you want to delete this email recipient?')"
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
                                            Delete Recipient
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-1">No email recipients found</h3>
                                    <p class="text-gray-500">Get started by creating your first email recipient.</p>
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
            @forelse($emailRecipients as $recipient)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                <!-- Facility and Category -->
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900">{{ $recipient->facility->name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($recipient->category === 'book-a-tour') bg-green-100 text-green-800
                            @elseif($recipient->category === 'inquiry') bg-blue-100 text-blue-800
                            @else bg-purple-100 text-purple-800 @endif">
                        {{ $categories[$recipient->category] }}
                    </span>
                </div>

                <!-- Primary Email -->
                <div class="mb-2">
                    <span class="text-sm font-medium text-gray-700">Primary:</span>
                    <span class="text-sm text-gray-900">{{ $recipient->email }}</span>
                </div>

                <!-- Alternative Emails -->
                @if($recipient->email_alt_1 || $recipient->email_alt_2)
                <div class="mb-3">
                    <span class="text-sm font-medium text-gray-700">Alternatives:</span>
                    <div class="text-sm text-gray-600 mt-1">
                        @if($recipient->email_alt_1)
                        <div>{{ $recipient->email_alt_1 }}</div>
                        @endif
                        @if($recipient->email_alt_2)
                        <div>{{ $recipient->email_alt_2 }}</div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-2">
                    <!-- Edit Button -->
                    <div class="relative group">
                        <button wire:click="edit({{ $recipient->id }})"
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
                            Edit Recipient
                        </div>
                    </div>

                    <!-- Delete Button -->
                    <div class="relative group">
                        <button wire:click="delete({{ $recipient->id }})"
                            onclick="return confirm('Are you sure you want to delete this email recipient?')"
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
                            Delete Recipient
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No email recipients found</h3>
                    <p class="text-gray-500">Get started by creating your first email recipient.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($emailRecipients->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $emailRecipients->links() }}
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
                            {{ $editMode ? 'Edit Email Recipient' : 'Add Email Recipient' }}
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

                        <!-- Primary Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Email *</label>
                            <input type="email" wire:model="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="contact@facility.com">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alternative Email 1 -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Email 1</label>
                            <input type="email" wire:model="email_alt_1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="support@facility.com">
                            @error('email_alt_1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Alternative Email 2 -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Email 2</label>
                            <input type="email" wire:model="email_alt_2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="info@facility.com">
                            @error('email_alt_2') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
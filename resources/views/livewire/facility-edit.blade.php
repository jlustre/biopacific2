<div class="min-h-screen bg-gray-50">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-8">
        <!-- Facility Dropdown -->
        <div class="mb-6 lg:w-1/2">
            <label for="facility-select" class="block text-sm font-medium text-gray-700 mb-2">Select Facility</label>
            <select id="facility-select" wire:model="facility.id"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                @foreach($facilities as $facilityOption)
                <option value="{{ $facilityOption->id }}">{{ $facilityOption->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <nav class="flex space-x-4">
                <button type="button" wire:click="switchTab('basic')" class="tab-button"
                    :class="{'active': activeTab === 'basic'}">Basic</button>
                <button type="button" wire:click="switchTab('contact')" class="tab-button"
                    :class="{'active': activeTab === 'contact'}">Contact</button>
                <!-- Add other tabs as needed -->
            </nav>

            <!-- Tab Content -->
            <div class="tab-content">
                @if($activeTab === 'basic')
                @include('admin.facilities.edit-tabs.basic', ['facility' => $facility])
                @elseif($activeTab === 'contact')
                @include('admin.facilities.edit-tabs.contact', ['facility' => $facility])
                @endif
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit"
                class="text-white px-8 py-3 rounded-lg transition-colors font-medium bg-teal-500 hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Save Changes
            </button>
        </div>
    </form>
</div>
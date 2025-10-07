{{-- Services Tab --}}
<div id="services-content" class="tab-pane hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Services</h3>
            <a href="{{ route('admin.services.create', ['facility_id' => $facility->id]) }}"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Service</a>
        </div>
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($allServices->unique('title') as $service)
            <div class="flex items-center gap-2 justify-between">
                <label class="flex items-center gap-2 flex-1">
                    <input type="checkbox" name="services[]" value="{{ $service->id }}" {{
                        $facility->services->contains($service->id) ? 'checked' : '' }}>
                    <span class="font-medium">{{ $service->title }}</span>
                </label>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.services.edit', $service->id) }}"
                        class="ml-2 text-blue-600 hover:underline text-sm">Edit</a>
                    <form method="POST" action="" class="inline-block"
                        onsubmit="return confirmDeleteService({{ $service->facilities()->count() }});">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="ml-2 text-red-600 hover:underline text-sm bg-transparent border-none p-0">Delete</button>
                    </form>
                    <script>
                        function confirmDeleteService(facilityCount) {
                            if (facilityCount > 0) {
                                return confirm('Warning: This service is still assigned to ' + facilityCount + ' facility(s). Are you sure you want to delete?');
                            }
                            return confirm('Are you sure you want to delete this service?');
                        }
                    </script>
                </div>
            </div>
            @endforeach
        </div>
        <!-- Service creation is now global. Use the Add Service page. -->
    </div>
</div>
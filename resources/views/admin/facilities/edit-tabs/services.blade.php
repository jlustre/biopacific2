{{-- Services Tab --}}
<div id="services-content" class="tab-pane hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Services</h3>
            <a href="{{ route('admin.services.create', ['facility_id' => $facility->id]) }}"
                class="text-white px-4 py-2 rounded-lg transition-colors font-medium bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                style="border: 2px solid var(--color-accent);">Add Service</a>
        </div>
        <div class="mb-6">
            <h4 class="text-md font-bold text-teal-700 mb-2">Global Services</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach($allServices->where('is_global', true)->sortBy('order') as $service)
                <div class="flex items-center gap-2 justify-between">
                    <label class="flex items-center gap-2 flex-1">
                        <input type="checkbox" name="services[]" value="{{ $service->id }}" {{
                            $facility->services->contains($service->id) ? 'checked' : '' }}>
                        <span class="font-medium">{{ $service->name }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.services.edit', $service->id) }}"
                            class="ml-2 text-blue-600 hover:underline text-sm">Edit</a>
                    </div>
                </div>
                @endforeach
            </div>
            <h4 class="text-md font-bold text-teal-700 mb-2">Facility Specific Services</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($allServices->where('is_global', false)->sortBy('order') as $service)
                <div class="flex items-center gap-2 justify-between">
                    <label class="flex items-center gap-2 flex-1">
                        <input type="checkbox" name="services[]" value="{{ $service->id }}" {{
                            $facility->services->contains($service->id) ? 'checked' : '' }}>
                        <span class="font-medium">{{ $service->name }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.services.edit', $service->id) }}"
                            class="ml-2 text-blue-600 hover:underline text-sm">Edit</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <!-- Service creation is now global. Use the Add Service page. -->
    </div>
</div>
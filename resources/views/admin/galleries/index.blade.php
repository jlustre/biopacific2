@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 pb-6">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Galleries Management</h1>
                        <p class="text-gray-600">Manage photo galleries for your facilities</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Images Listing -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Gallery Images for {{ $facility->name }}</h2>
                <a href="{{ route('admin.facilities.galleries.create', ['facility' => $facility->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg shadow hover:bg-teal-700 focus:outline-none">
                    <i class="fas fa-plus mr-2"></i> Add Gallery Image
                </a>
            </div>
            <x-facility-select :facilities="$facilities" type="gallery" :selected="$facility->id" />
            @if(empty($facility) || !$facility->id)
            <h3 class="text-lg font-medium text-gray-900 mb-2">Galleries Management</h3>
            <p class="text-gray-500 mb-6">Select a facility above to manage its photo galleries. This page will
                allow you to:</p>
            <ul class="text-sm text-gray-600 space-y-2 max-w-md mx-auto text-left">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Upload and organize facility photos
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Create themed photo albums
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Add captions and descriptions
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    Set featured images for galleries
                </li>
            </ul>
            @endif
            <h2 class="text-xl font-bold text-gray-900 mb-4 text-center mt-4">{{ $facility->name }}</h2>
            @if($images->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-image text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No images found</h3>
                <p class="text-gray-500">There are no images in this facility's gallery yet.</p>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($images as $image)
                <div class="bg-gray-50 border rounded-lg p-4 flex flex-col items-center relative">
                    <div class="relative w-full h-40 mb-2">
                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $image->title }}"
                            class="w-full h-full object-cover rounded">
                        <button type="button"
                            class="w-6 h-6 flex items-center justify-center bg-red-600 bg-opacity-90 text-white rounded-full hover:bg-red-700 text-md absolute right-2 bottom-2 shadow-lg cursor-pointer"
                            style="z-index:2;" onclick="submitDeleteImage({{ $image->id }})">
                            <span class="sr-only">Delete</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="text-sm text-gray-700 font-semibold mb-1">{{ $image->title }}</div>
                    @if($image->caption)
                    <div class="text-xs text-gray-500 mb-1">{{ $image->caption }}</div>
                    @endif
                    <div class="text-xs text-gray-400 mb-2">Uploaded: {{ $image->created_at->format('M d, Y') }}</div>

                </div>
                @endforeach
            </div>
            <form id="delete-image-form" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
            <script>
                function submitDeleteImage(imageId) {
                    if (confirm('Are you sure you want to delete this image?')) {
                        var form = document.getElementById('delete-image-form');
                        form.action = '{{ url('/admin/gallery') }}/' + imageId;
                        form.submit();
                    }
                }
            </script>
            @endif
        </div>
    </div>
</div>
</div>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const facilitySelect = document.querySelector('[name="facility_id"]');
        const galleryContent = document.querySelector('.gallery-content');
        const defaultState = document.querySelector('.default-state');

        // Restore the last selected facility from localStorage
        const savedFacilityId = localStorage.getItem('selectedFacilityId');
        if (savedFacilityId && facilitySelect) {
            facilitySelect.value = savedFacilityId;
            facilitySelect.dispatchEvent(new Event('change'));
        }

        facilitySelect?.addEventListener('change', function() {
            const facilityId = this.value;

            if (facilityId) {
                // Save the selected facility ID to localStorage
                localStorage.setItem('selectedFacilityId', facilityId);

                // Update the UI to reflect the selected facility
                galleryContent?.classList.remove('hidden');
                defaultState?.classList.add('hidden');

                // Optionally, load gallery images for the selected facility
                // loadGalleryImages(facilityId);
            } else {
                // Clear the saved facility ID if no facility is selected
                localStorage.removeItem('selectedFacilityId');

                galleryContent?.classList.add('hidden');
                defaultState?.classList.remove('hidden');
            }
        });
    });
</script>
@endsection
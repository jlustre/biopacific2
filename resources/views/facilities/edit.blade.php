@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Edit Facility</h1>
            <p class="text-gray-600 mt-2">Update facility information</p>
        </div>

        <form action="{{ route('facilities.update', $facility) }}" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-md rounded-lg p-6">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Facility Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Facility Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $facility->name) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                        required>
                    @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $facility->slug) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror"
                        required>
                    @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $facility->city) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('city') border-red-500 @enderror"
                        required>
                    @error('city')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                    <input type="text" id="state" name="state" value="{{ old('state', $facility->state) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('state') border-red-500 @enderror"
                        required>
                    @error('state')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Beds -->
                <div>
                    <label for="beds" class="block text-sm font-medium text-gray-700 mb-2">Number of Beds</label>
                    <input type="number" id="beds" name="beds" value="{{ old('beds', $facility->beds) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('beds') border-red-500 @enderror"
                        min="0">
                    @error('beds')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ...existing code... -->

                <!-- Current Hero Image -->
                @if($facility->hero_image_url)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Hero Image</label>
                    <div class="mb-4">
                        <img src="{{ $facility->hero_image_url }}" alt="Current hero image"
                            class="h-32 w-auto rounded-lg border">
                    </div>
                </div>
                @endif

                <!-- Hero Image -->
                <div class="md:col-span-2">
                    <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $facility->hero_image_url ? 'Update Hero Image' : 'Hero Image' }}
                    </label>
                    <input type="file" id="hero_image" name="hero_image" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hero_image') border-red-500 @enderror">
                    @error('hero_image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">
                        Accepted formats: JPG, JPEG, PNG. Max size: 2MB
                        @if($facility->hero_image_url)
                        <br>Leave empty to keep current image.
                        @endif
                    </p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $facility->description) }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t">
                <div class="flex gap-4">
                    <a href="{{ route('facilities.show', $facility) }}"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </a>
                    <a href="{{ route('facilities.index') }}"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Back to List
                    </a>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Update Facility
                    </button>
                </div>
            </div>
        </form>

        <!-- Delete Section -->
        <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-2">Danger Zone</h3>
            <p class="text-red-700 mb-4">Once you delete a facility, there is no going back. Please be certain.</p>

            <form action="{{ route('facilities.destroy', $facility) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this facility? This action cannot be undone.')"
                class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Delete Facility
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});
</script>
@endsection
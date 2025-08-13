@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Add New Facility</h1>
            <p class="text-gray-600 mt-2">Create a new facility record</p>
        </div>

        <form action="{{ route('facilities.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Facility Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Facility Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror"
                           required>
                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('city') border-red-500 @enderror"
                           required>
                    @error('city')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                    <input type="text" id="state" name="state" value="{{ old('state') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('state') border-red-500 @enderror"
                           required>
                    @error('state')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Beds -->
                <div>
                    <label for="beds" class="block text-sm font-medium text-gray-700 mb-2">Number of Beds</label>
                    <input type="number" id="beds" name="beds" value="{{ old('beds') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('beds') border-red-500 @enderror"
                           min="0">
                    @error('beds')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ranking Position -->
                <div>
                    <label for="ranking_position" class="block text-sm font-medium text-gray-700 mb-2">Ranking Position</label>
                    <input type="number" id="ranking_position" name="ranking_position" value="{{ old('ranking_position') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ranking_position') border-red-500 @enderror"
                           min="1">
                    @error('ranking_position')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ranking Total -->
                <div>
                    <label for="ranking_total" class="block text-sm font-medium text-gray-700 mb-2">Ranking Total</label>
                    <input type="number" id="ranking_total" name="ranking_total" value="{{ old('ranking_total') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ranking_total') border-red-500 @enderror"
                           min="1">
                    @error('ranking_total')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hero Image -->
                <div class="md:col-span-2">
                    <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                    <input type="file" id="hero_image" name="hero_image" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hero_image') border-red-500 @enderror">
                    @error('hero_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Accepted formats: JPG, JPEG, PNG. Max size: 2MB</p>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <a href="{{ route('facilities.index') }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Create Facility
                </button>
            </div>
        </form>
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

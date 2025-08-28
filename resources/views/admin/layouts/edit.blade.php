@extends('layouts.dashboard')

@section('title', 'Edit Layout Template')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Layout Template</h1>
        <p class="text-gray-600 mt-1">Modify the sections and configuration for this layout template</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.layouts.show', $template->id) }}"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Back to Details
        </a>
        <a href="{{ route('admin.layouts.preview', $template->id) }}"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-eye"></i>
            Preview
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.layouts.update', $template->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $template->name) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="preview_image" class="block text-sm font-medium text-gray-700 mb-2">Preview Image
                        URL</label>
                    <input type="url" id="preview_image" name="preview_image"
                        value="{{ old('preview_image', $template->preview_image) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('preview_image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Describe this layout template...">{{ old('description', $template->description) }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ?
                    'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring
                    focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Active (available for use)</span>
                </label>
            </div>
        </div>

        {{-- Section Configuration --}}
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Livewire Section Management</h2>
            <livewire:layout-builder :templateId="$template->id" />
        </div>

        {{-- Save Actions --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Changes will be applied to all facilities using this template
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.layouts.show', $template->id) }}"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function(e) {
        // Optional: You could add slug generation logic here if needed
    });

    // Section ordering (if you want drag & drop functionality)
    // This could be enhanced with sortable.js or similar
</script>
@endpush
@endsection
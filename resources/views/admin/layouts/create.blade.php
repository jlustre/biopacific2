@extends('layouts.dashboard')

@section('title', 'Create New Layout Template')

@section('content')
<div class="bg-white min-h-screen">
    <!-- Header -->
    <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.layouts.index') }}"
               class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create New Layout Template</h1>
                <p class="mt-1 text-sm text-gray-500">Design a new layout template for your facilities</p>
            </div>
        </div>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.layouts.store') }}" method="POST" class="max-w-4xl mx-auto">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Basic Information -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>

                        <!-- Template Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview Image -->
                        <div class="mb-4">
                            <label for="preview_image" class="block text-sm font-medium text-gray-700 mb-2">Preview Image URL</label>
                            <input type="url" id="preview_image" name="preview_image" value="{{ old('preview_image') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('preview_image') border-red-500 @enderror">
                            @error('preview_image')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">Optional: URL to a preview image of this layout</p>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active Template
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Make this template available for selection</p>
                        </div>
                    </div>
                </div>

                <!-- Section Configuration -->
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Section Configuration</h2>

                        <!-- Available Sections -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Select Sections *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($availableSections as $section)
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <div class="flex items-start">
                                            <input type="checkbox" id="section_{{ $section->slug }}"
                                                   name="sections[]" value="{{ $section->slug }}"
                                                   {{ in_array($section->slug, old('sections', [])) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                            <div class="ml-3 flex-1">
                                                <label for="section_{{ $section->slug }}" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                                    {{ $section->name }}
                                                </label>
                                                @if($section->description)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $section->description }}</p>
                                                @endif
                                                @if(!empty($section->variants))
                                                    <div class="mt-2">
                                                        <p class="text-xs text-gray-500 mb-1">Variants:</p>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach($section->variants as $variant)
                                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                                    {{ ucfirst($variant) }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('sections')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Default Configuration -->
                        <div class="mb-6">
                            <label for="default_config_json" class="block text-sm font-medium text-gray-700 mb-2">Default Configuration (JSON)</label>
                            <textarea id="default_config_json" name="default_config_json" rows="8"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm @error('default_config') border-red-500 @enderror"
                                      placeholder='{"hero": {"variant": "default"}, "about": {"variant": "default"}}'>{{ old('default_config_json') }}</textarea>
                            @error('default_config')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">Optional: JSON configuration for section variants and settings</p>
                        </div>

                        <!-- Section Order Preview -->
                        <div id="sectionOrder" class="hidden">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Section Order Preview</h3>
                            <div id="sectionOrderList" class="space-y-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.layouts.index') }}"
                   class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    Cancel
                </a>

                <div class="flex gap-3">
                    <button type="submit" name="action" value="save"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Create Template
                    </button>
                    <button type="submit" name="action" value="save_and_preview"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        Create & Preview
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    // This is just for preview, actual slug is generated server-side
});

// Update section order preview
function updateSectionOrderPreview() {
    const checkedSections = document.querySelectorAll('input[name="sections[]"]:checked');
    const orderDiv = document.getElementById('sectionOrder');
    const orderList = document.getElementById('sectionOrderList');

    if (checkedSections.length === 0) {
        orderDiv.classList.add('hidden');
        return;
    }

    orderDiv.classList.remove('hidden');
    orderList.innerHTML = '';

    checkedSections.forEach((checkbox, index) => {
        const sectionSlug = checkbox.value;
        const sectionLabel = checkbox.closest('.border').querySelector('label').textContent.trim();

        const orderItem = document.createElement('div');
        orderItem.className = 'flex items-center gap-3 p-2 bg-blue-50 rounded';
        orderItem.innerHTML = `
            <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                ${index + 1}
            </div>
            <span class="text-sm font-medium text-gray-900">${sectionLabel}</span>
            <span class="text-xs text-gray-500">(${sectionSlug})</span>
        `;

        orderList.appendChild(orderItem);
    });
}

// Add event listeners to all section checkboxes
document.querySelectorAll('input[name="sections[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateSectionOrderPreview);
});

// Initial preview update
updateSectionOrderPreview();

// Validate JSON before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const jsonTextarea = document.getElementById('default_config_json');
    const jsonValue = jsonTextarea.value.trim();

    if (jsonValue) {
        try {
            JSON.parse(jsonValue);
        } catch (error) {
            e.preventDefault();
            alert('Invalid JSON in Default Configuration field. Please check your syntax.');
            jsonTextarea.focus();
            return false;
        }
    }
});
</script>
@endpush

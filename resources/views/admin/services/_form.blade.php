@php
$isEdit = isset($service);
$isScoped = !empty($scopedFacilityId);
@endphp

<form action="{{ $isEdit ? route('admin.services.update', $service) : route('admin.services.store') }}" method="POST" class="space-y-5">
    @csrf
    @if($isEdit)
    @method('PUT')
    @endif

    @if($isScoped)
    <input type="hidden" name="facility_id" value="{{ $scopedFacilityId }}">
    <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
        This service will be created for your facility only (not global).
    </div>
    @endif

    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Service name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" required
            value="{{ old('name', $service->name ?? '') }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
    </div>

    <div>
        <label for="short_description" class="block text-sm font-semibold text-gray-700 mb-1">Short description</label>
        <input type="text" name="short_description" id="short_description"
            value="{{ old('short_description', $service->short_description ?? '') }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            maxlength="255">
    </div>

    @if(!$isScoped)
    <div class="flex items-center gap-3">
        <input type="checkbox" name="is_global" id="is_global" value="1"
            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
            {{ old('is_global', $service->is_global ?? true) ? 'checked' : '' }}>
        <label for="is_global" class="text-sm font-medium text-gray-700">Global service (available to all facilities)</label>
    </div>
    @endif

    <div>
        <label for="detailed_description" class="block text-sm font-semibold text-gray-700 mb-1">Detailed description</label>
        <textarea name="detailed_description" id="detailed_description" rows="8"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">{{ old('detailed_description', $service->detailed_description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="icon" class="block text-sm font-semibold text-gray-700 mb-1">Icon (SVG or class)</label>
            <input type="text" name="icon" id="icon"
                value="{{ old('icon', $service->icon ?? '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                placeholder="e.g. fas fa-heart">
        </div>
        <div>
            <label for="image" class="block text-sm font-semibold text-gray-700 mb-1">Image path or URL</label>
            <input type="text" name="image" id="image"
                value="{{ old('image', $service->image ?? '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                placeholder="storage path or https://...">
        </div>
    </div>

    <div>
        <label for="order" class="block text-sm font-semibold text-gray-700 mb-1">Display order</label>
        <input type="number" name="order" id="order" min="0"
            value="{{ old('order', $service->order ?? '') }}"
            class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
    </div>

    <div class="flex flex-wrap gap-6">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_featured" value="1"
                class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                {{ old('is_featured', $service->is_featured ?? false) ? 'checked' : '' }}>
            <span class="text-sm font-medium text-gray-700">Featured on website</span>
        </label>
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1"
                class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}>
            <span class="text-sm font-medium text-gray-700">Active</span>
        </label>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
        <a href="{{ route('admin.services.index', $isScoped ? ['facility_id' => $scopedFacilityId] : []) }}"
            class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900">Cancel</a>
        <button type="submit"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-sm transition">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Service' : 'Create Service' }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.querySelector('#detailed_description');
    if (el && window.ClassicEditor) {
        ClassicEditor.create(el, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'],
            table: { contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'] }
        }).catch(console.error);
    }
});
</script>
@endpush

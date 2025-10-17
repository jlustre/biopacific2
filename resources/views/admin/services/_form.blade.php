@php
$isEdit = isset($service);
@endphp
<form action="{{ $isEdit ? route('admin.services.update', $service) : route('admin.services.store') }}" method="POST">
    @csrf
    @if($isEdit)
    @method('PUT')
    @endif
    <div class="mb-4">
        <label class="block font-medium mb-1">Name</label>
        <input type="text" name="name" class="w-full border border-gray-300 rounded px-2 py-1" required
            value="{{ old('name', $service->name ?? '') }}">
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Short Description</label>
        <input type="text" name="short_description" class="w-full border border-gray-300 rounded px-2 py-1"
            value="{{ old('short_description', $service->short_description ?? '') }}">
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Global Service?</label>
        <input type="checkbox" name="is_global" value="1" {{ old('is_global', $service->is_global ?? false) ? 'checked'
        : '' }}> Yes
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Detailed Description</label>
        <textarea name="detailed_description" id="detailed_description"
            class="w-full border border-gray-300 rounded px-2 py-1 rtf-editor"
            rows="8">{{ old('detailed_description', $service->detailed_description ?? '') }}</textarea>
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Icon (SVG or URL)</label>
        <input type="text" name="icon" class="w-full border border-gray-300 rounded px-2 py-1"
            value="{{ old('icon', $service->icon ?? '') }}">
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Image URL</label>
        <input type="text" name="image_url" class="w-full border border-gray-300 rounded px-2 py-1"
            value="{{ old('image_url', $service->image_url ?? '') }}">
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Order</label>
        <input type="number" name="order" class="w-full border border-gray-300 rounded px-2 py-1"
            value="{{ old('order', $service->order ?? '') }}">
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Featured?</label>
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $service->is_featured ?? false) ?
        'checked' : '' }}> Yes
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Active?</label>
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active ?? true) ? 'checked' :
        '' }}> Yes
    </div>
    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">{{ $isEdit ? 'Update Service' : 'Add Service'
        }}</button>
    <a href="{{ route('admin.services.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
</form>
</form>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.ClassicEditor) {
            ClassicEditor.create(document.querySelector('#detailed_description'), {
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'
                ],
                table: {
                    contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                }
            }).catch(error => { console.error(error); });
        } else {
            console.error('ClassicEditor is not defined. CKEditor 5 CDN may not be loaded.');
        }
    });
</script>
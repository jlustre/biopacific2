@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b mb-6">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <h1 class="text-2xl font-bold text-gray-900">Add Gallery Image for {{ $facility->name }}</h1>
            <p class="text-gray-600">Upload a new image to this facility's gallery.</p>
        </div>
    </div>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                    <input type="file" name="image" id="image" accept="image/*" required
                        class="block w-full border border-gray-300 rounded-lg p-2"
                        onchange="previewGalleryImage(event)">
                    <div id="image-preview" class="mt-4"></div>
                </div>
                <div class="mb-4">
                    <label for="caption" class="block text-sm font-medium text-gray-700 mb-2">Caption (optional)</label>
                    <input type="text" name="caption" id="caption"
                        class="block w-full border border-gray-300 rounded-lg p-2" maxlength="255">
                </div>
                <div class="flex items-center justify-end">
                    <a href="{{ route('admin.facilities.galleries.index', ['facility' => $facility->id]) }}"
                        class="mr-4 text-gray-500 hover:text-gray-700">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Upload
                        Image</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    function previewGalleryImage(event) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'max-w-xs max-h-64 rounded shadow border border-gray-300';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
@endsection
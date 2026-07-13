@extends('layouts.dashboard', ['title' => 'Upload Gallery Image'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Upload Image</h1>
        <p class="text-gray-600 mt-2">Add a photo for {{ $facility->name }}. Choose whether it appears on the website, portal, or both.</p>
    </div>
    <a href="{{ route('admin.facilities.galleries.index', ['facility' => $facility->id]) }}"
        class="inline-flex items-center text-gray-600 hover:text-gray-900 font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to gallery
    </a>
</div>
@endsection

@section('content')
<div class="max-w-2xl">
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <form method="POST" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="space-y-5">
      @csrf
      <input type="hidden" name="facility_id" value="{{ $facility->id }}">

      <div>
        <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">Image file</label>
        <input type="file" name="image" id="image" accept="image/*" required
          class="block w-full text-sm border border-gray-300 rounded-lg p-2 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 file:font-semibold hover:file:bg-teal-100"
          onchange="previewGalleryImage(event)">
        @error('image')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <div id="image-preview" class="mt-4"></div>
      </div>

      <div>
        <label for="caption" class="block text-sm font-semibold text-gray-700 mb-2">Caption <span class="font-normal text-gray-400">(optional)</span></label>
        <input type="text" name="caption" id="caption" value="{{ old('caption') }}"
          class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          maxlength="255" placeholder="Short description shown with the photo">
        @error('caption')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      @include('admin.partials.content-visibility-field', [
        'visibilityValue' => old('visibility', 'both'),
        'visibilityWrapperClass' => '',
        'visibilityLabelClass' => 'block text-sm font-semibold text-gray-700 mb-2',
        'visibilitySelectClass' => 'block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500',
        'visibilityHelpClass' => 'mt-1 text-xs text-gray-500',
        'visibilityHelp' => 'Website = public facility gallery. Portal = employee Facility Galleries. Both = all surfaces.',
      ])

      <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('admin.facilities.galleries.index', ['facility' => $facility->id]) }}"
          class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900">Cancel</a>
        <button type="submit"
          class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-sm transition">
          <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Image
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function previewGalleryImage(event) {
  const preview = document.getElementById('image-preview');
  preview.innerHTML = '';
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = document.createElement('img');
    img.src = e.target.result;
    img.className = 'max-w-full max-h-72 rounded-lg shadow border border-gray-200';
    preview.appendChild(img);
  };
  reader.readAsDataURL(file);
}
</script>
@endpush

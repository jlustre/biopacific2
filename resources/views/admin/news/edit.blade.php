@extends('layouts.dashboard')

@section('content')
<div class="max-w-lg mx-auto p-4 bg-white rounded-xl shadow">
    @if(session('notification'))
    <div
        class="mb-4 p-2 rounded text-white {{ session('notification.type') === 'success' ? 'bg-green-500' : 'bg-red-500' }}">
        {{ session('notification.message') }}
    </div>
    @endif
    @if(session('success'))
    <div class="mb-4 p-2 rounded text-white bg-green-500">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-2 rounded text-white bg-red-500">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <h1 class="text-xl font-bold mb-2">Edit News</h1>
    <form method="POST" action="{{ route('admin.news.update', $news) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" class="w-full border border-gray-700 rounded px-2 py-1"
                value="{{ $news->title }}" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Summary</label>
            <textarea name="summary" class="w-full border border-gray-700 rounded px-2 py-1" rows="2"
                required>{{ old('summary', $news->summary) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Content</label>
            <textarea name="content" class="w-full border border-gray-700 rounded px-2 py-1" rows="4"
                required>{{ $news->content }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Image</label>
            <input type="file" name="image" id="image-input" accept="image/*"
                class="w-full border border-gray-700 rounded px-2 py-1">
            <div class="mt-2">
                <img id="image-preview" src="{{ $news->image ? asset('storage/' . $news->image) : '' }}"
                    alt="Image Preview" class="max-h-32 rounded border"
                    style="display: {{ $news->image ? 'block' : 'none' }};">
                @if($news->image)
                <button type="button" id="delete-image-btn" class="ml-2 text-red-600 hover:text-red-800"
                    title="Delete Image">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                @endif
            </div>
        </div>
        <div class="flex items-center mb-4">
            <input type="checkbox" name="is_global" value="1" class="form-checkbox h-4 w-4" id="is-global-checkbox"
                @if($news->is_global) checked @endif>
            <label class="ml-2 text-sm">Global News</label>
        </div>
        <div id="facility-select-group" class="mb-4">
            <label class="block text-sm font-medium mb-1">Facilities (select one or more)</label>
            <div class="text-xs text-red-400 mb-1">Hold <strong>Ctrl</strong> (Windows) or <strong>Cmd</strong> (Mac)
                to select multiple facilities.</div>
            <select name="facility_ids[]" class="w-full border border-gray-700 rounded px-2 py-1" multiple>
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" @if(isset($news->facilities) &&
                    $news->facilities->contains($facility->id)) selected @endif>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Published At</label>
            <input type="date" name="published_at" class="w-full border border-gray-700 rounded px-2 py-1"
                value="{{ $news->published_at ? \Carbon\Carbon::parse($news->published_at)->format('Y-m-d') : '' }}">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border border-gray-700 rounded px-2 py-1">
                <option value="1" @if($news->status) selected @endif>Published</option>
                <option value="0" @if(!$news->status) selected @endif>Draft</option>
            </select>
        </div>
        <div class="flex gap-2 justify-end mt-2">
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">Update</button>
            <a href="{{ route('admin.news.index') }}" class="text-gray-600 hover:underline text-sm">Cancel</a>
        </div>
    </form>

    @if($news->image)
    <form id="delete-image-form" method="POST" action="{{ route('admin.news.destroy', $news) }}" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    <script>
        document.getElementById('delete-image-btn').addEventListener('click', function() {
                            if (confirm('Are you sure you want to delete this image?')) {
                                document.getElementById('delete-image-form').submit();
                            }
                        });
    </script>
    @endif


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('image-input');
            const preview = document.getElementById('image-preview');
            const isGlobalCheckbox = document.getElementById('is-global-checkbox');
            const facilitySelectGroup = document.getElementById('facility-select-group');
            function toggleFacilitySelect() {
                if (isGlobalCheckbox.checked) {
                    facilitySelectGroup.style.display = 'none';
                } else {
                    facilitySelectGroup.style.display = 'block';
                }
            }
            if (isGlobalCheckbox && facilitySelectGroup) {
                isGlobalCheckbox.addEventListener('change', toggleFacilitySelect);
                toggleFacilitySelect(); // Initial state
            }
            if (input) {
                input.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            preview.src = event.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                });
            }
        });
    </script>
</div>
@endsection
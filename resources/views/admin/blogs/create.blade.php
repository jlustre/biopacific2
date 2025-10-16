@extends('layouts.dashboard')

@section('content')
<div class="max-w-3xl mx-auto p-4 bg-white rounded-xl shadow relative">
    <a href="{{ route('admin.facilities.webcontents.blogs') }}"
        class="absolute top-4 right-4 text-gray-500 hover:text-red-600 text-2xl" title="Close">
        &times;
    </a>
    @if (session('success'))
    <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
    <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <h1 class="text-xl font-bold mb-2">{{ isset($editMode) && $editMode ? 'Edit Blog' : 'Add Blog' }}</h1>
    <form method="POST"
        action="{{ isset($editMode) && $editMode ? route('admin.blogs.update', $blog->id) : route('admin.blogs.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if(isset($editMode) && $editMode)
        @method('PUT')
        @endif
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" class="w-full border border-gray-700 rounded px-2 py-1"
                value="{{ old('title', $blog->title ?? '') }}" required>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Content</label>
            <textarea name="content" id="ckeditor" class="w-full border border-gray-700 rounded px-2 py-1 rtf-editor"
                rows="10">{{ old('content', $blog->content ?? '') }}</textarea>
        </div>


        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Author</label>
            <select name="author" class="w-full border border-gray-700 rounded px-2 py-1">
                @php $users = \App\Models\User::all(); @endphp
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ (old('author', $blog->author ?? auth()->id()) == $user->id) ?
                    'selected' : '' }}>
                    {{ $user->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border border-gray-700 rounded px-2 py-1">
                <option value="draft" @if(old('status', $blog->status ?? '')=='draft' ) selected @endif>Draft</option>
                <option value="published" @if(old('status', $blog->status ?? '')=='published' ) selected
                    @endif>Published</option>
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Global Blog?</label>
            <input type="checkbox" name="is_global" id="is_global" value="1" @if(old('is_global', $blog->is_global ??
            1)) checked @endif
            onchange="toggleFacilities()">
        </div>
        <div class="mb-6" id="facilities_group" style="display: none;">
            <label class="block text-sm font-medium mb-1">Facilities</label>
            <select name="facility_ids[]" class="w-full border border-gray-700 rounded px-2 py-1" multiple>
                @foreach(\App\Models\Facility::all() as $facility)
                <option value="{{ $facility->id }}" {{ (collect(old('facility_ids', isset($blog) ? $blog->
                    facilities->pluck('id')->toArray() : []))->contains($facility->id)) ?
                    'selected' : '' }}>
                    {{ $facility->name }}
                </option>
                @endforeach
            </select>
            <small class="text-gray-500">Hold Ctrl (Windows) or Command (Mac) to select multiple facilities.</small>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Photo 1</label>
            <input type="file" name="photo1" id="photo1" accept="image/*"
                class="w-full border border-gray-700 rounded px-2 py-1" onchange="previewImage(event, 'preview1')">
            <div class="mt-2">
                <img id="preview1"
                    src="{{ isset($editMode) && $editMode && !empty($blog->photo1) ? asset('storage/' . $blog->photo1) : '#' }}"
                    alt="Photo 1 Preview"
                    class="max-h-32 border rounded {{ isset($editMode) && $editMode && !empty($blog->photo1) ? '' : 'hidden' }}">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Photo 2</label>
            <input type="file" name="photo2" id="photo2" accept="image/*"
                class="w-full border border-gray-700 rounded px-2 py-1" onchange="previewImage(event, 'preview2')">
            <div class="mt-2">
                <img id="preview2"
                    src="{{ isset($editMode) && $editMode && !empty($blog->photo2) ? asset('storage/' . $blog->photo2) : '#' }}"
                    alt="Photo 2 Preview"
                    class="max-h-32 border rounded {{ isset($editMode) && $editMode && !empty($blog->photo2) ? '' : 'hidden' }}">
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Active?</label>
            <input type="checkbox" name="is_active" value="1" @if(old('is_active', $blog->is_active ?? 1)) checked
            @endif>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Version</label>
            <input type="text" name="version" class="w-full border border-gray-700 rounded px-2 py-1"
                value="{{ old('version', $blog->version ?? '1.0') }}">
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Published At</label>
            <input type="datetime-local" name="published_at" class="w-full border border-gray-700 rounded px-2 py-1"
                value="{{ old('published_at', isset($blog) && $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
        </div>
        <input type="hidden" name="new_version" id="new_version" value="0">
        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm"
            id="blogSubmitBtn">
            {{ isset($editMode) && $editMode ? 'Update' : 'Save' }} Blog
        </button>
        <a href="{{ route('admin.blogs.index') }}" class="text-gray-600 hover:underline text-sm ml-4">Cancel</a>
</div>

</form>
</div>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    function previewImage(event, previewId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            preview.classList.add('hidden');
        }
    }
    function toggleFacilities() {
        var isGlobal = document.getElementById('is_global').checked;
        var facilitiesGroup = document.getElementById('facilities_group');
        facilitiesGroup.style.display = isGlobal ? 'none' : 'block';
    }
    document.addEventListener('DOMContentLoaded', function () {
        ClassicEditor.create(document.querySelector('#ckeditor'))
            .then(editor => {
                // Update textarea value on change
                editor.model.document.on('change:data', () => {
                    document.querySelector('#ckeditor').value = editor.getData();
                });
            })
            .catch(error => { console.error(error); });
        toggleFacilities();

        // Custom modal for update/create/cancel
        var submitBtn = document.getElementById('blogSubmitBtn');
        if (submitBtn && {{ isset($editMode) && $editMode ? 'true' : 'false' }}) {
            // Create modal HTML
                var currentVersion = document.querySelector('input[name="version"]').value;
                var modalHtml = `
                    <div id="blogActionModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50" style="display:none;">
                        <div class="bg-white rounded shadow-lg p-6 w-80 text-center">
                            <h2 class="text-lg font-bold mb-4">Choose Blog Update Action</h2>
                            <p class="mb-4">You are updating version <span class='font-bold'>${currentVersion}</span>.<br>Do you want to update this version or create a new version?</p>
                            <div class="flex justify-between">
                                <button id="modalUpdateBtn" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Update</button>
                                <button id="modalCreateBtn" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Create</button>
                                <button id="modalCancelBtn" class="bg-gray-400 text-white px-3 py-1 rounded hover:bg-gray-500">Cancel</button>
                            </div>
                        </div>
                    </div>
                `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            var modal = document.getElementById('blogActionModal');
            var updateBtn = document.getElementById('modalUpdateBtn');
            var createBtn = document.getElementById('modalCreateBtn');
            var cancelBtn = document.getElementById('modalCancelBtn');

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'flex';
            });
            updateBtn.addEventListener('click', function() {
                document.getElementById('new_version').value = '0';
                modal.style.display = 'none';
                submitBtn.form.submit();
            });
            createBtn.addEventListener('click', function() {
                document.getElementById('new_version').value = '1';
                // Try to increment version field
                let versionInput = document.querySelector('input[name="version"]');
                if (versionInput) {
                    let currentVersion = versionInput.value.trim();
                    let nextVersion = '1.0';
                    if (/^\d+(\.\d+)?$/.test(currentVersion)) {
                        let parts = currentVersion.split('.');
                        if (parts.length === 2) {
                            nextVersion = parts[0] + '.' + (parseInt(parts[1]) + 1);
                        } else {
                            nextVersion = (parseInt(currentVersion) + 1) + '.0';
                        }
                    }
                    versionInput.value = nextVersion;
                }
                modal.style.display = 'none';
                submitBtn.form.submit();
            });
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
    });
</script>
@endsection
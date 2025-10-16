<div>
    <h2 class="text-xl font-bold mb-4">{{ $blogId ? 'Edit Blog' : 'Create Blog' }}</h2>
    @if (session('success'))
    <div class="bg-green-100 text-green-800 p-2 mb-2">{{ session('success') }}</div>
    @endif
    <form wire:submit.prevent="save">
        <div class="mb-2">
            <label class="block">Title</label>
            <input type="text" wire:model="title" class="border rounded w-full p-2" required>
        </div>
        <div class="mb-2">
            <label class="block">Content</label>
            <textarea id="ckeditor" wire:model.defer="content" class="border rounded w-full p-2" required></textarea>
        </div>
        <div class="mb-2">
            <label class="block">Author</label>
            <input type="text" wire:model="author" class="border rounded w-full p-2">
        </div>
        <div class="mb-2">
            <label class="block">Status</label>
            <select wire:model="status" class="border rounded w-full p-2">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="block">Global Blog?</label>
            <input type="checkbox" wire:model="is_global">
        </div>
        <div class="mb-2">
            <label class="block">Facility</label>
            @php
            $facilities = \App\Models\Facility::all();
            @endphp
            @include('components.facility-select', ['facilities' => $facilities, 'type' => 'blog', 'selected' =>
            $facility_id])
        </div>
        <div class="mb-2">
            <label class="block">Photo 1</label>
            <input type="text" wire:model="photo1" class="border rounded w-full p-2">
        </div>
        <div class="mb-2">
            <label class="block">Photo 2</label>
            <input type="text" wire:model="photo2" class="border rounded w-full p-2">
        </div>
        <div class="mb-2">
            <label class="block">Active?</label>
            <input type="checkbox" wire:model="is_active">
        </div>
        <div class="mb-2">
            <label class="block">Version</label>
            <input type="text" wire:model="version" class="border rounded w-full p-2">
        </div>
        <div class="mb-2">
            <label class="block">Published At</label>
            <input type="datetime-local" wire:model="published_at" class="border rounded w-full p-2">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">{{ $blogId ? 'Update' : 'Create'
            }}</button>
    </form>

    <!-- CKEditor 5 integration -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            if (window.ClassicEditor) {
                ClassicEditor.create(document.querySelector('#ckeditor'), {
                    toolbar: [
                        'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'
                    ],
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                    }
                }).then(editor => {
                    editor.model.document.on('change:data', () => {
                        window.livewire.emit('setContent', editor.getData());
                    });
                }).catch(error => { console.error(error); });
            } else {
                console.error('ClassicEditor is not defined. CKEditor 5 CDN may not be loaded.');
            }
        });
        window.livewire.on('setContent', function(data) {
            @this.set('content', data);
        });
    </script>
</div>
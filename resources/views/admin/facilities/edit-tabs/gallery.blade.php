<div id="gallery" class="tab-pane hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Gallery Images</h3>
        <div class="mb-4">
            @if(session('error'))
            <div id="gallery-error-message"
                class="mb-2 text-red-600 bg-red-100 border border-red-300 rounded px-4 py-2 flex justify-between items-center">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="document.getElementById('gallery-error-message').style.display='none'"
                    class="ml-4 text-red-700 hover:text-red-900 font-bold">&times;</button>
            </div>
            @endif
            <div class="flex items-center space-x-4">
                <form action="{{ route('admin.gallery.upload', ['facility' => $facility->id]) }}" method="POST"
                    enctype="multipart/form-data" class="flex items-center space-x-4">
                    @csrf
                    <input type="file" name="image" id="gallery-image-input" accept="image/*" required
                        class="border rounded px-2 py-1" />
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload
                        Image</button>
                </form>
                <form action="{{ route('admin.gallery.clear', ['facility' => $facility->id]) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete ALL gallery images for this facility? This cannot be undone.')">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Clear All
                        Images</button>
                </form>
            </div>
            <div id="gallery-image-preview" class="mt-2"></div>
            Image</button>
            </form>
            <div id="gallery-image-preview" class="mt-2"></div>
            <script>
                document.getElementById('gallery-image-input').addEventListener('change', function(event) {
                    const preview = document.getElementById('gallery-image-preview');
                    preview.innerHTML = '';
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `<img src='${e.target.result}' alt='Preview' class='h-24 w-24 object-cover rounded border' />`;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            </script>
        </div>
        <div class="bg-white rounded shadow p-4">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-2 py-1">Preview</th>
                        <th class="px-2 py-1">Filename</th>
                        <th class="px-2 py-1">Order</th>
                        <th class="px-2 py-1">Featured</th>
                        <th class="px-2 py-1">Active</th>
                        <th class="px-2 py-1">Uploaded At</th>
                        <th class="px-2 py-1">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facility->galleryImages as $image)
                    <tr>
                        <td class="border px-2 py-1">
                            <img src="{{ asset('storage/' . $image->image_url) }}" alt="Gallery Image"
                                class="h-16 w-16 object-cover rounded" />
                        </td>
                        <td class="border px-2 py-1">{{ $image->title }}</td>
                        <td class="border px-2 py-1 flex items-center space-x-2">
                            {{ $image->order }}
                            <form
                                action="{{ route('admin.gallery.move', ['image' => $image->id, 'direction' => 'up']) }}"
                                method="POST" style="display:inline">
                                @csrf
                                <button type="submit" title="Move Up" class="text-gray-500 hover:text-blue-600"><svg
                                        xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7" />
                                    </svg></button>
                            </form>
                            <form
                                action="{{ route('admin.gallery.move', ['image' => $image->id, 'direction' => 'down']) }}"
                                method="POST" style="display:inline">
                                @csrf
                                <button type="submit" title="Move Down" class="text-gray-500 hover:text-blue-600"><svg
                                        xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg></button>
                            </form>
                        </td>
                        <td class="border px-2 py-1">{{ $image->is_featured ? 'Yes' : 'No' }}</td>
                        <td class="border px-2 py-1">{{ $image->is_active ? 'Yes' : 'No' }}</td>
                        <td class="border px-2 py-1">{{ $image->created_at->format('Y-m-d H:i') }}</td>
                        <td class="border px-2 py-1">
                            <a href="{{ asset('storage/' . $image->image_url) }}" target="_blank"
                                class="text-blue-600 hover:underline">View</a>
                            <!-- Delete form moved to edit.blade.php outside the main form -->
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No gallery images found for this
                            facility.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div>
    <h2 class="text-xl font-bold mb-4">Blogs Management</h2>
    @if (session('success'))
    <div class="bg-green-100 text-green-800 p-2 mb-2">{{ session('success') }}</div>
    @endif
    <a href="{{ route('admin.blogs.create') }}"
        class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Create New Blog</a>
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="border px-2 py-1">Title</th>
                <th class="border px-2 py-1">Author</th>
                <th class="border px-2 py-1">Status</th>
                <th class="border px-2 py-1">Global/Facility</th>
                <th class="border px-2 py-1">Active</th>
                <th class="border px-2 py-1">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($blogs as $blog)
            <tr>
                <td class="border px-2 py-1">{{ $blog->title }}</td>
                <td class="border px-2 py-1">{{ $blog->author }}</td>
                <td class="border px-2 py-1">{{ $blog->status }}</td>
                <td class="border px-2 py-1">{{ $blog->is_global ? 'Global' : 'Facility' }}</td>
                <td class="border px-2 py-1">{{ $blog->is_active ? 'Yes' : 'No' }}</td>
                <td class="border px-2 py-1">
                    <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="text-blue-600">Edit</a>
                    <button wire:click="delete({{ $blog->id }})" class="text-red-600 ml-2"
                        onclick="return confirm('Delete this blog?')">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
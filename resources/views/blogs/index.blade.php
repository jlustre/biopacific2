@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto p-4 bg-white rounded-xl shadow">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Blog List</h1>
        <a href="{{ route('admin.blogs.create') }}"
            class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">Add Blog</a>
    </div>
    @if (session('success'))
    <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif
    @if ($blogs->isEmpty())
    <div class="text-gray-600">No blogs found.</div>
    @else
    <table class="min-w-full border">
        <thead>
            <tr>
                <th class="px-2 py-1 border">Title</th>
                <th class="px-2 py-1 border">Author</th>
                <th class="px-2 py-1 border">Status</th>
                <th class="px-2 py-1 border">Version</th>
                <th class="px-2 py-1 border">Published At</th>
                <th class="px-2 py-1 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($blogs as $blog)
            <tr>
                <td class="px-2 py-1 border">{{ $blog->title }}</td>
                <td class="px-2 py-1 border">{{ optional($blog->authorUser)->name ?? $blog->author }}</td>
                <td class="px-2 py-1 border">{{ $blog->status }}</td>
                <td class="px-2 py-1 border">{{ $blog->version }}</td>
                <td class="px-2 py-1 border">{{ $blog->published_at }}</td>
                <td class="px-2 py-1 border">
                    <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                        class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 text-xs">Edit</a>
                    <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" style="display:inline;"
                        onsubmit="return confirm('Are you sure you want to delete this blog?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 text-xs">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
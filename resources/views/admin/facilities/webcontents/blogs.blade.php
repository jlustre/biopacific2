@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Blogs Management</h1>
                        <p class="text-gray-600">Manage blog posts for your facilities</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Facility Selection -->
        @include('admin.facilities.webcontents.partials.facility_dropdown', ['facilities' => $facilities])

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

        <!-- Placeholder Content -->
        <div class="bg-white rounded-lg shadow p-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Blog Management</h2>
                <a href="{{ route('admin.blogs.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Blog</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded shadow">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Scope</th>
                            <th class="px-4 py-2">Published At</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blogs as $blog)
                        <tr>
                            <td class="border px-4 py-2">{{ $blog->title }}</td>
                            <td class="border px-4 py-2">{{ $blog->is_global ? 'Global' : 'Local' }}</td>
                            <td class="border px-4 py-2 text-sm">{{ $blog->published_at ?
                                $blog->published_at->diffForHumans() : '-' }}</td>
                            <td class="border px-4 py-2">
                                <span
                                    class="px-2 py-1 rounded {{ $blog->status == 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $blog->status == 'published' ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="border px-4 py-2 flex gap-2">
                                <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                                    class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this blog?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-6">No blogs found. Create your first
                                blog post!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
@endsection
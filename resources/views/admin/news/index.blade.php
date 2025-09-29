@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">News Management</h1>
        <a href="{{ route('admin.news.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add News</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded shadow">
            <thead>
                <tr>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Scope</th>
                    <th class="px-4 py-2">Facility</th>
                    <th class="px-4 py-2">Published At</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($news as $item)
                <tr>
                    <td class="border px-4 py-2">{{ $item->title }}</td>
                    <td class="border px-4 py-2">{{ ucfirst($item->scope) }}</td>
                    <td class="border px-4 py-2">{{ $item->facility ? $item->facility->name : '-' }}</td>
                    <td class="border px-4 py-2">{{ $item->published_at ? $item->published_at->format('Y-m-d') : '-' }}
                    </td>
                    <td class="border px-4 py-2">
                        <span
                            class="px-2 py-1 rounded {{ $item->status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $item->status ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="border px-4 py-2 flex gap-2">
                        <a href="{{ route('admin.news.edit', $item) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.news.destroy', $item) }}" method="POST"
                            onsubmit="return confirm('Delete this news item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
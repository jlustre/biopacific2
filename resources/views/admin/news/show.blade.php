@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">News Details</h1>
    <div class="bg-white rounded shadow p-4">
        <div class="mb-2">
            <span class="font-semibold">Title:</span> {{ $news->title }}
        </div>
        <div class="mb-2">
            <span class="font-semibold">Content:</span> {{ $news->content }}
        </div>
        <div class="mb-2">
            <span class="font-semibold">Scope:</span> {{ ucfirst($news->scope) }}
        </div>
        <div class="mb-2">
            <span class="font-semibold">Facility:</span> {{ $news->facility ? $news->facility->name : '-' }}
        </div>
        <div class="mb-2">
            <span class="font-semibold">Published At:</span> {{ $news->published_at ?
            $news->published_at->format('Y-m-d') : '-' }}
        </div>
        <div class="mb-2">
            <span class="font-semibold">Status:</span> <span
                class="px-2 py-1 rounded {{ $news->status ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{
                $news->status ? 'Published' : 'Draft' }}</span>
        </div>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('admin.news.edit', $news) }}"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</a>
            <a href="{{ route('admin.news.index') }}" class="text-gray-600 hover:underline">Back to List</a>
        </div>
    </div>
</div>
@endsection
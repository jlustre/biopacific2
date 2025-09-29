@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Edit News</h1>
    <form action="{{ route('admin.news.update', $news) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block font-semibold mb-1">Title</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2" value="{{ $news->title }}" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Content</label>
            <textarea name="content" class="w-full border rounded px-3 py-2" rows="5"
                required>{{ $news->content }}</textarea>
        </div>
        <div>
            <label class="block font-semibold mb-1">Scope</label>
            <select name="scope" class="w-full border rounded px-3 py-2">
                <option value="company" @if($news->scope=='company') selected @endif>Company-wide</option>
                <option value="facility" @if($news->scope=='facility') selected @endif>Facility-specific</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Facility (if applicable)</label>
            <select name="facility_id" class="w-full border rounded px-3 py-2">
                <option value="">None</option>
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" @if($news->facility_id==$facility->id) selected @endif>{{
                    $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Published At</label>
            <input type="date" name="published_at" class="w-full border rounded px-3 py-2"
                value="{{ $news->published_at ? $news->published_at->format('Y-m-d') : '' }}">
        </div>
        <div>
            <label class="block font-semibold mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                <option value="1" @if($news->status) selected @endif>Published</option>
                <option value="0" @if(!$news->status) selected @endif>Draft</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
            <a href="{{ route('admin.news.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
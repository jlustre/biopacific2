@extends('layouts.dashboard')
@section('title', 'Add Testimonial for ' . $facility->name)
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Add Testimonial</h1>
    <form method="POST" action="{{ route('admin.facilities.testimonials.store', $facility->id) }}">
        @csrf
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required value="{{ old('name') }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Role</label>
            <input type="text" name="role" class="w-full border rounded px-3 py-2" value="{{ old('role') }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Avatar URL</label>
            <input type="url" name="avatar" class="w-full border rounded px-3 py-2" value="{{ old('avatar') }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Rating</label>
            <input type="number" name="rating" min="1" max="5" class="w-full border rounded px-3 py-2" required
                value="{{ old('rating', 5) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Title</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2" required value="{{ old('title') }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Text</label>
            <textarea name="text" class="w-full border rounded px-3 py-2" rows="4" required>{{ old('text') }}</textarea>
        </div>
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/80">Save</button>
        <a href="{{ route('admin.facilities.testimonials.index', $facility->id) }}"
            class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection
@extends('layouts.dashboard')
@section('title', 'Edit Testimonial for ' . $facility->name)
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Testimonial</h1>
    <form method="POST" action="{{ route('admin.facilities.testimonials.update', [$facility->id, $testimonial->id]) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required
                value="{{ old('name', $testimonial->name) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Role</label>
            <input type="text" name="role" class="w-full border rounded px-3 py-2"
                value="{{ old('role', $testimonial->role) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Avatar URL</label>
            <input type="url" name="avatar" class="w-full border rounded px-3 py-2"
                value="{{ old('avatar', $testimonial->avatar) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Rating</label>
            <input type="number" name="rating" min="1" max="5" class="w-full border rounded px-3 py-2" required
                value="{{ old('rating', $testimonial->rating) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Title</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2" required
                value="{{ old('title', $testimonial->title) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Text</label>
            <textarea name="text" class="w-full border rounded px-3 py-2" rows="4"
                required>{{ old('text', $testimonial->text) }}</textarea>
        </div>
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/80">Update</button>
        <a href="{{ route('admin.facilities.testimonials.index', $facility->id) }}"
            class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection
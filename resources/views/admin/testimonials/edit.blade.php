@extends('layouts.dashboard')
@section('title', 'Edit Testimonial for ' . $facility->name)
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Edit Testimonial</h1>
    <form method="POST" action="{{ route('admin.facilities.testimonials.update', [$facility->id, $testimonial->id]) }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required
                value="{{ old('name', $testimonial->name) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Relationship</label>
            <input type="text" name="relationship" class="w-full border rounded px-3 py-2"
                value="{{ old('relationship', $testimonial->relationship) }}">
        </div>
        <div class="mb-4 flex items-center gap-4">
            <div>
                <label class="block mb-1 font-semibold">Photo</label>
                <input type="file" name="photo" accept="image/*" class="block">
                <input type="hidden" name="photo_url" value="{{ $testimonial->photo_url }}">
            </div>
            <div class="flex flex-col items-center">
                <span class="block mb-1 font-semibold">Preview</span>
                @php
                $photoUrl = old('photo_url', $testimonial->photo_url ?? null);
                @endphp
                @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="Photo" class="w-16 h-16 rounded-full object-cover border">
                @else
                <svg class="w-16 h-16 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" />
                </svg>
                @endif
            </div>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Rating</label>
            <input type="number" name="rating" min="1" max="5" class="w-full border rounded px-3 py-2" required
                value="{{ old('rating', $testimonial->rating) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Title</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2"
                value="{{ old('title', $testimonial->title) }}">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Quote</label>
            <textarea name="quote" class="w-full border rounded px-3 py-2"
                rows="4">{{ old('quote', $testimonial->quote) }}</textarea>
        </div>
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/80">Update</button>
        <a href="{{ route('admin.facilities.testimonials.index', $facility->id) }}"
            class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection
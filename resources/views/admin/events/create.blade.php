@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Add Event</h1>
    <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block font-semibold mb-1">Title</label>
            <input type="text" name="title" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" class="w-full border rounded px-3 py-2" rows="5" required></textarea>
        </div>
        <div>
            <label class="block font-semibold mb-1">Scope</label>
            <select name="scope" class="w-full border rounded px-3 py-2">
                <option value="company">Company-wide</option>
                <option value="facility">Facility-specific</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Facility (if applicable)</label>
            <select name="facility_id" class="w-full border rounded px-3 py-2">
                <option value="">None</option>
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Event Date</label>
            <input type="date" name="event_date" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block font-semibold mb-1">Location</label>
            <input type="text" name="location" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block font-semibold mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                <option value="1">Published</option>
                <option value="0">Draft</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.events.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
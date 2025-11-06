@extends('layouts.admin')
@section('title', 'Add Event')
@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold mb-6">Add Event</h2>
    @if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="title">Title</label>
            <input type="text" name="title" id="title" class="w-full border rounded px-3 py-2"
                value="{{ old('title') }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="summary">Summary</label>
            <textarea name="summary" id="summary" class="w-full border rounded px-3 py-2" rows="3"
                required>{{ old('summary') }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="content">Content</label>
            <textarea name="content" id="content" class="w-full border rounded px-3 py-2" rows="6"
                required>{{ old('content') }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="image">Image</label>
            <input type="file" name="image" id="image" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="event_date">Event Date</label>
            <input type="date" name="event_date" id="event_date" class="w-full border rounded px-3 py-2"
                value="{{ old('event_date') }}">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="facility_id">Facility</label>
            <select name="facility_id" id="facility_id" class="w-full border rounded px-3 py-2">
                <option value="">Company-wide</option>
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" {{ old('facility_id')==$facility->id ? 'selected' : '' }}>{{
                    $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1" for="status">Status</label>
            <select name="status" id="status" class="w-full border rounded px-3 py-2">
                <option value="1" {{ old('status')=='1' ? 'selected' : '' }}>Published</option>
                <option value="0" {{ old('status')=='0' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded font-semibold hover:bg-primary/90">Save
            Event</button>
    </form>
</div>
@endsection
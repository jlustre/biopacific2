@extends('layouts.dashboard', ['title' => 'Create Event'])

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Create Event</h1>
    <p class="text-gray-600 mt-2">Add a new event</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Title Field -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Event Title <span
                        class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" placeholder="e.g., Annual Conference"
                    value="{{ old('title') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event Date Field -->
            <div>
                <label for="event_date" class="block text-sm font-semibold text-gray-900 mb-2">Event Date</label>
                <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('event_date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location Field -->
            <div>
                <label for="location" class="block text-sm font-semibold text-gray-900 mb-2">Location</label>
                <input type="text" name="location" id="location" placeholder="e.g., Main Convention Center"
                    value="{{ old('location') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('location')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea name="description" id="description" rows="6" placeholder="Enter event description..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Create Event
                </button>
                <a href="{{ route('admin.events.index') }}"
                    class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.dashboard', ['title' => $event->name])

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $event->title }}</h1>
        <p class="text-gray-600 mt-2">Event Details</p>
    </div>
    <div class="space-x-2">
        <a href="{{ route('admin.events.edit', $event) }}"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('admin.events.index') }}"
            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm font-semibold">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="md:col-span-2 space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Event Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-semibold text-gray-600">Title</p>
                    <p class="text-gray-900">{{ $event->title }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Date</p>
                    <p class="text-gray-900">
                        @if($event->event_date)
                        {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                        @else
                        <span class="text-gray-400 italic">Not specified</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Location</p>
                    <p class="text-gray-900">{{ $event->location ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600">Description</p>
                    <p class="text-gray-900 mt-1">
                        @if($event->description)
                        {!! nl2br(e($event->description)) !!}
                        @else
                        <span class="text-gray-400 italic">No description provided</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Information -->
    <div class="space-y-6">
        <!-- Metadata -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Metadata</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600">ID</p>
                    <p class="text-gray-900 font-mono">{{ $event->id }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Created</p>
                    <p class="text-gray-900">{{ $event->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Updated</p>
                    <p class="text-gray-900">{{ $event->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
            <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-semibold text-sm">
                    <i class="fas fa-trash mr-2"></i> Delete Event
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
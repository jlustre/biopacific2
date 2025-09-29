@extends('layouts.dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold mb-6">Manage News & Events for {{ $facility->name }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- News Section -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">News</h2>
                <a href="{{ route('admin.news.create', ['facility_id' => $facility->id]) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add News</a>
            </div>
            <div class="bg-white rounded shadow p-4">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-2 py-1">Title</th>
                            <th class="px-2 py-1">Published At</th>
                            <th class="px-2 py-1">Status</th>
                            <th class="px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $item)
                        <tr>
                            <td class="border px-2 py-1">{{ $item->title }}</td>
                            <td class="border px-2 py-1">{{ $item->published_at ? $item->published_at->format('Y-m-d') :
                                '-' }}</td>
                            <td class="border px-2 py-1">{{ $item->status ? 'Published' : 'Draft' }}</td>
                            <td class="border px-2 py-1">
                                <a href="{{ route('admin.news.edit', $item) }}"
                                    class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.news.destroy', $item) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete this news item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-gray-500 text-center py-4">No news found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Events Section -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Events</h2>
                <a href="{{ route('admin.events.create', ['facility_id' => $facility->id]) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Event</a>
            </div>
            <div class="bg-white rounded shadow p-4">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-2 py-1">Title</th>
                            <th class="px-2 py-1">Event Date</th>
                            <th class="px-2 py-1">Location</th>
                            <th class="px-2 py-1">Status</th>
                            <th class="px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td class="border px-2 py-1">{{ $event->title }}</td>
                            <td class="border px-2 py-1">{{ $event->event_date ? $event->event_date->format('Y-m-d') :
                                '-' }}</td>
                            <td class="border px-2 py-1">{{ $event->location }}</td>
                            <td class="border px-2 py-1">{{ $event->status ? 'Published' : 'Draft' }}</td>
                            <td class="border px-2 py-1">
                                <a href="{{ route('admin.events.edit', $event) }}"
                                    class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete this event?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline ml-2">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-gray-500 text-center py-4">No events found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
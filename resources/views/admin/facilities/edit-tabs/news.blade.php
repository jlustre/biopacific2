<div id="news-content" class="tab-pane hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">News & Events</h3>
        <div class="mb-4">
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
                    @forelse($facility->news as $item)
                    <tr>
                        <td class="border px-2 py-1">{{ $item->title }}</td>
                        <td class="border px-2 py-1">{{ $item->published_at ?
                            \Carbon\Carbon::parse($item->published_at)->format('Y-m-d') : '-' }}</td>
                        <td class="border px-2 py-1">{{ $item->status ? 'Published' : 'Draft' }}</td>
                        <td class="border px-2 py-1">
                            <a href="{{ route('admin.news.edit', $item) }}"
                                class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.news.destroy', $item) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline ml-2"
                                    onclick="return confirm('Delete this news item?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No news or events found for this
                            facility.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
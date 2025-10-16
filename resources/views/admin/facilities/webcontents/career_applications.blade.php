@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Job Applications for: {{ $jobOpening->title }}</h2>
            <a href="{{ route('admin.facilities.webcontents.careers') }}"
                class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Careers</a>
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $app->applicant_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $app->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $app->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($app->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $app->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                            <!-- View Details Modal Trigger -->
                            <button type="button" class="text-blue-600 hover:underline"
                                onclick="showAppModal({{ $app->id }})">View</button>
                            <!-- Download Resume -->
                            @if($app->resume_path)
                            <a href="{{ asset('storage/' . $app->resume_path) }}" target="_blank"
                                class="text-indigo-600 hover:underline">Resume</a>
                            @endif
                            <!-- Update Status Form -->
                            <form method="POST"
                                action="{{ route('admin.facilities.webcontents.careers.applications.update', [$jobOpening, $app]) }}"
                                class="inline">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()"
                                    class="text-xs rounded border-gray-300">
                                    <option value="pending" @if($app->status=='pending') selected @endif>Pending
                                    </option>
                                    <option value="reviewed" @if($app->status=='reviewed') selected @endif>Reviewed
                                    </option>
                                    <option value="accepted" @if($app->status=='accepted') selected @endif>Accepted
                                    </option>
                                    <option value="rejected" @if($app->status=='rejected') selected @endif>Rejected
                                    </option>
                                </select>
                            </form>
                            <!-- Delete Form -->
                            <form method="POST"
                                action="{{ route('admin.facilities.webcontents.careers.applications.destroy', [$jobOpening, $app]) }}"
                                onsubmit="return confirm('Delete this application?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No applications found.</td>
                    </tr>
                    @endforelse
                    <!-- Application Details Modal (hidden by default, shown via JS) -->
                    <div id="appModal"
                        class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
                        <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg mx-auto">
                            <h3 class="text-xl font-bold mb-4">Application Details</h3>
                            <div id="appModalContent"></div>
                            <button onclick="closeAppModal()"
                                class="mt-4 bg-primary text-white px-4 py-2 rounded">Close</button>
                        </div>
                    </div>
                    <script>
                        function showAppModal(appId) {
                    // Fetch details via AJAX (or inline if you prefer)
                    fetch(`/admin/facilities/webcontents/careers/applications/${appId}/details`)
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('appModalContent').innerHTML = html;
                            document.getElementById('appModal').classList.remove('hidden');
                            document.getElementById('appModal').classList.add('flex');
                        });
                }
                function closeAppModal() {
                    document.getElementById('appModal').classList.add('hidden');
                    document.getElementById('appModal').classList.remove('flex');
                }
                    </script>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    ClassicEditor.create(document.querySelector('textarea.rtf-editor'), {
        toolbar: [
            'heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'
        ],
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
        }
    }).catch(error => { console.error(error); });
</script>
@endsection
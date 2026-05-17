@extends('layouts.dashboard', ['title' => 'Job Applications'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Applications</h1>
        <p class="text-gray-600 mt-2">{{ $jobOpening->title }} · {{ $jobOpening->facility?->name }}</p>
    </div>
    <a href="{{ route('admin.facilities.webcontents.careers', ['facility_id' => $jobOpening->facility_id]) }}"
        class="inline-flex items-center text-gray-600 hover:text-gray-900 font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to careers
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-center">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if($applications->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <i class="fas fa-inbox text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No applications yet</h3>
        <p class="text-gray-500">Applicants will appear here when they apply for this position.</p>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Applicant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $app->first_name }} {{ $app->last_name }}</div>
                            <div class="text-sm text-gray-500">{{ $app->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $app->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($app->status === 'accepted') bg-green-100 text-green-800
                                @elseif($app->status === 'rejected') bg-red-100 text-red-800
                                @elseif($app->status === 'reviewed') bg-blue-100 text-blue-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ ucfirst($app->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $app->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button type="button" onclick="showAppModal({{ $app->id }})"
                                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                    View
                                </button>
                                @if($app->resume_path)
                                <a href="{{ route('admin.facilities.webcontents.careers.applications.preview-resume', [$jobOpening, $app]) }}"
                                    target="_blank" class="px-3 py-1.5 text-sm font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg">
                                    Resume
                                </a>
                                <a href="{{ route('admin.facilities.webcontents.careers.applications.download-resume', [$jobOpening, $app]) }}"
                                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                    Download
                                </a>
                                @endif
                                <form method="POST"
                                    action="{{ route('admin.facilities.webcontents.careers.applications.update', [$jobOpening, $app]) }}"
                                    class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()"
                                        class="text-sm border border-gray-300 rounded-lg px-2 py-1.5">
                                        <option value="pending" @selected($app->status === 'pending')>Pending</option>
                                        <option value="reviewed" @selected($app->status === 'reviewed')>Reviewed</option>
                                        <option value="accepted" @selected($app->status === 'accepted')>Accepted</option>
                                        <option value="rejected" @selected($app->status === 'rejected')>Rejected</option>
                                    </select>
                                </form>
                                <form method="POST"
                                    action="{{ route('admin.facilities.webcontents.careers.applications.destroy', [$jobOpening, $app]) }}"
                                    onsubmit="return confirm('Delete this application?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<div id="appModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
        <button type="button" onclick="closeAppModal()"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        <h3 class="text-xl font-bold text-gray-900 mb-4">Application Details</h3>
        <div id="appModalContent"></div>
        <button type="button" onclick="closeAppModal()"
            class="mt-6 w-full px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-semibold">
            Close
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showAppModal(appId) {
    fetch(`/admin/facilities/webcontents/careers/applications/${appId}/details`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('appModalContent').innerHTML = html;
            const modal = document.getElementById('appModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
}
function closeAppModal() {
    const modal = document.getElementById('appModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush

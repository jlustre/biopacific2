@extends('layouts.dashboard')

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Scheduled Reports</h1>
        <p class="text-gray-600 mt-2">Manage and schedule automated report generation using CRON expressions.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.index') }}" target="_blank"
            class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition font-semibold flex items-center">
            <i class="fas fa-list mr-2"></i> Reports Management
        </a>
        <a href="{{ route('admin.scheduled-reports.create') }}"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold flex items-center">
            <i class="fas fa-plus mr-2"></i> Schedule New Report
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow overflow-x-auto p-4">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            <i class="fas fa-check-circle mr-2"></i>{!! session('success') !!}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <i class="fas fa-times-circle mr-2"></i>{!! session('error') !!}
        </div>
    @endif

    @if(session('download_url'))
        <!-- Modal for download link -->
        <div id="downloadModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full relative">
                <button onclick="document.getElementById('downloadModal').style.display='none'" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl">&times;</button>
                <div class="flex flex-col items-center">
                    <i class="fas fa-file-download text-4xl text-blue-600 mb-4"></i>
                    <h2 class="text-lg font-bold mb-2">Report Ready</h2>
                    <p class="mb-4">Your scheduled report has been generated.</p>
                    <a href="{{ session('download_url') }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center">
                        <i class="fas fa-download mr-2"></i> Download {{ session('download_label') }}
                    </a>
                </div>
            </div>
        </div>
        <script>
            // Auto-close modal after 15 seconds
            setTimeout(function() {
                var modal = document.getElementById('downloadModal');
                if (modal) modal.style.display = 'none';
            }, 15000);
        </script>
    @endif

    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
        <div>
            <label class="block text-xs font-semibold mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48" placeholder="Name or report...">
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Status</label>
            <select name="status" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-32">
                <option value="">All</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Report</label>
            <select name="report_id" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48">
                <option value="">All Reports</option>
                @foreach($reports as $report)
                    <option value="{{ $report->id }}" {{ request('report_id') == $report->id ? 'selected' : '' }}>{{ $report->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Filter</button>
            <a href="{{ route('admin.scheduled-reports.index') }}" class="px-3 py-1 bg-gray-300 text-gray-800 rounded text-sm ml-2">Reset</a>
        </div>
    </form>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Run</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($scheduledReports as $sr)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $sr->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $sr->report->name ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $sr->status === 'active' ? 'bg-green-100 text-green-800' : ($sr->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($sr->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $sr->next_run_at ? $sr->next_run_at->format('Y-m-d H:i') : '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <a href="{{ route('admin.scheduled-reports.edit', $sr) }}" class="mx-1 text-blue-600 hover:text-blue-800" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('admin.scheduled-reports.history', $sr) }}" class="mx-1 text-indigo-600 hover:text-indigo-800" title="History">
                        <i class="fas fa-history"></i>
                    </a>
                    @php
                        $latestRun = $sr->runs()->orderByDesc('executed_at')->first();
                    @endphp
                    @if($latestRun)
                        <a href="{{ route('admin.scheduled-report-runs.show', $latestRun) }}" class="mx-1 text-teal-600 hover:text-teal-800" title="View Latest Report">
                            <i class="fas fa-file-alt"></i>
                        </a>
                    @else
                        <span class="mx-1 text-gray-400" title="No report available"><i class="fas fa-file-alt"></i></span>
                    @endif
                    <form action="{{ route('admin.scheduled-reports.destroy', $sr) }}" method="POST" class="inline-block mx-1" onsubmit="return confirm('Delete this scheduled report?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.scheduled-reports.run', $sr) }}" method="POST" class="inline-block mx-1">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-800" title="Run Now">
                            <i class="fas fa-play"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No scheduled reports found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4">{{ $scheduledReports->links() }}</div>
</div>
@endsection

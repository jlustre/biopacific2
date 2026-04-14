@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Scheduled Report Runs</h1>
        <p class="text-gray-600 mt-2">View, archive, or delete all report runs.</p>
    </div>
    <a href="{{ route('admin.scheduled-reports.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to Scheduled Reports
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Search and Filter Form -->
        <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold mb-1">Report Name</label>
                <input type="text" name="report_name" value="{{ request('report_name') }}" class="form-input w-48 border border-teal-500 px-2 py-1 rounded-sm" placeholder="Search report name">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Status</label>
                <select name="status" class="form-select w-32 border border-teal-500 px-2 py-1 rounded-sm">
                    <option value="">All</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-36 border border-teal-500 px-2 py-1 rounded-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-36 border border-teal-500 px-2 py-1 rounded-sm">
            </div>
            <div>
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">Filter</button>
                <a href="{{ route('admin.scheduled-report-runs.index') }}" class="ml-2 text-gray-500 hover:underline">Reset</a>
            </div>
        </form>

        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Report</th>
                    <th class="px-4 py-2">Run At</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $run->id }}</td>
                    <td class="px-4 py-2">{{ $run->scheduledReport->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $run->executed_at }}</td>
                    <td class="px-4 py-2">{{ ucfirst($run->status) }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <a href="{{ route('admin.scheduled-report-runs.show', $run) }}" class="text-blue-600 hover:underline">View</a>
                        <form action="{{ route('admin.scheduled-report-runs.archive', $run) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:underline" onclick="return confirm('Archive this run?')">Archive</button>
                        </form>
                        <form action="{{ route('admin.scheduled-report-runs.destroy', $run) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this run?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-gray-500">No runs found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $runs->links() }}</div>
    </div>
</div>
@endsection

@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Scheduled Report History</h1>
        <p class="text-gray-600 mt-2">All generated reports for this schedule.</p>
    </div>
    <a href="{{ route('admin.scheduled-reports.index') }}"
        class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to List
    </a>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Executed At</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Download</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($runs as $run)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">{{ $run->executed_at }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $run->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($run->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap flex gap-2 items-center">
                    <a href="{{ route('admin.scheduled-report-runs.show', $run) }}" class="text-teal-600 hover:text-teal-800" title="View Report">
                        <i class="fas fa-file-alt"></i>
                    </a>
                    <form action="{{ route('admin.scheduled-report-runs.destroy', $run) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this report run?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete Report Run">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs text-red-600">{{ $run->error_message }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No runs found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

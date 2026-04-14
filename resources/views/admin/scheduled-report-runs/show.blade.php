@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Scheduled Report Run Details</h1>
    <a href="{{ route('admin.scheduled-report-runs.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to Runs
    </a>
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Run #{{ $run->id }}</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            <dt class="font-semibold">Report</dt>
            <dd>{{ $run->scheduledReport->name ?? '-' }}</dd>
            <dt class="font-semibold">Executed At</dt>
            <dd>{{ $run->executed_at }}</dd>
            <dt class="font-semibold">Status</dt>
            <dd>{{ ucfirst($run->status) }}</dd>
            <dt class="font-semibold">Error Message</dt>
            <dd>{{ $run->error_message ?? '-' }}</dd>
            <dt class="font-semibold">Result Path</dt>
            <dd>{{ $run->result_path ?? '-' }}</dd>
        </dl>
        <div class="mt-6">
            <button type="button" onclick="showReportModal()" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 mr-4">View Generated Report</button>
            <a href="{{ route('admin.scheduled-report-runs.archive', $run) }}" class="text-yellow-600 hover:underline mr-4" onclick="event.preventDefault(); document.getElementById('archive-form').submit();">Archive</a>
            <form id="archive-form" action="{{ route('admin.scheduled-report-runs.archive', $run) }}" method="POST" class="hidden">
                @csrf
            </form>
            <a href="{{ route('admin.scheduled-report-runs.destroy', $run) }}" class="text-red-600 hover:underline" onclick="event.preventDefault(); if(confirm('Delete this run?')) document.getElementById('delete-form').submit();">Delete</a>
            <form id="delete-form" action="{{ route('admin.scheduled-report-runs.destroy', $run) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>

        <!-- Modal for Report Preview -->
        <div id="reportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full p-6 relative">
                <button onclick="closeReportModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                <h3 class="text-xl font-bold mb-4">Generated Report Preview</h3>
                <iframe id="reportFrame" src="{{ route('admin.scheduled-report-runs.show-report', $run) }}" class="w-full h-[60vh] border rounded"></iframe>
            </div>
        </div>
        <script>
        function showReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
        }
        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }
        </script>
        </div>
    </div>
</div>
@endsection

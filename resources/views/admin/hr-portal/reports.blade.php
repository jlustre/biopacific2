@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Available Reports</h1>
    @if($isAdmin)
        <div class="mb-4">
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-teal-600 text-white rounded">Go to Reports Management</a>
        </div>
    @endif
    <div class="p-6 bg-white rounded shadow">
        @if($reports->isEmpty())
            <div class="text-gray-600">No reports available for your account.</div>
        @else
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">Name</th>
                    <th class="px-3 py-2 border">Description</th>
                    <th class="px-3 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td class="px-3 py-2 border">{{ $report->name }}</td>
                    <td class="px-3 py-2 border">{{ $report->description }}</td>
                    <td class="px-3 py-2 border">
                        <a href="#" class="px-3 py-1 bg-blue-600 text-white rounded run-report-btn" data-id="{{ $report->id }}">Run</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    <!-- Modal for running reports can be added here if needed -->
</div>
@endsection

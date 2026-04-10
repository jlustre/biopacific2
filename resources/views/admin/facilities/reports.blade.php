@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <div class="flex items-center justify-between mb-2">
        <div></div>
        @if($isAdmin)
            <a href="{{ route('admin.reports.index') }}" class="inline-block px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Go to Reports Management2</a>
        @endif
    </div>
    <h1 class="text-2xl font-bold mb-4">Reports for {{ $facility->name }}</h1>
    <div class="bg-white p-6 rounded shadow">
        @if($reports->isEmpty())
            <div class="text-gray-600">No reports available for this facility.</div>
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
                        <a href="{{ route('admin.reports.show', $report->id) }}" class="px-3 py-1 bg-blue-600 text-white rounded">Run</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
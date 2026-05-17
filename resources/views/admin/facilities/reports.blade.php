@extends('layouts.dashboard')

@section('header')
<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <a href="{{ route('admin.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]) }}"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <i class="fas fa-arrow-left mr-2"></i> Back to Facility HR Dashboard
        </a>
        <p class="mt-3 text-sm text-slate-600">Run on-demand reports or schedule automated exports for {{ $facility->name }}.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(!empty($canScheduleReports))
        <a href="{{ route('admin.scheduled-reports.create', ['facility_id' => $facility->id]) }}"
            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i> Create New Report
        </a>
        <a href="{{ route('admin.scheduled-reports.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <i class="fas fa-clock mr-2"></i> Scheduled Reports
        </a>
        @endif
        @if($isAdmin)
        <a href="{{ route('admin.reports.index') }}"
            class="inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
            <i class="fas fa-cog mr-2"></i> Reports Management
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @if($reports->isEmpty())
        <div class="text-slate-600">No reports available for this facility.</div>
        @if(!empty($canScheduleReports))
        <p class="mt-3 text-sm text-slate-500">
            Use <strong>Create New Report</strong> above to schedule an automated report, or contact your administrator to add report templates.
        </p>
        @endif
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full border border-slate-200 text-sm">
            <thead>
                <tr class="bg-slate-50">
                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700">Name</th>
                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700">Description</th>
                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr class="hover:bg-slate-50/80">
                    <td class="border border-slate-200 px-3 py-2">{{ $report->name }}</td>
                    <td class="border border-slate-200 px-3 py-2">{{ $report->description }}</td>
                    <td class="border border-slate-200 px-3 py-2">
                        <a href="{{ route('admin.reports.show', $report->id) }}"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700">
                            <i class="fas fa-play mr-1.5 text-xs"></i> Run
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

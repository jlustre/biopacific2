@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <h1 class="text-2xl font-bold">Job Opening Details</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.facility.job_openings.edit', [$facility, $jobOpening]) }}"
                class="inline-block px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Edit</a>
            <a href="{{ route('admin.facility.job_openings', $facility) }}"
                class="inline-block px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Back to Listings</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div><span class="font-semibold text-slate-600">Title:</span> {{ $jobOpening->title }}</div>
            <div><span class="font-semibold text-slate-600">Status:</span> {{ ucfirst($jobOpening->status) }}</div>
            <div><span class="font-semibold text-slate-600">Employment Type:</span> {{ $jobOpening->employment_type ?: '—' }}</div>
            <div><span class="font-semibold text-slate-600">Department:</span> {{ $jobOpening->department ?: '—' }}</div>
            <div><span class="font-semibold text-slate-600">Reporting To:</span> {{ $jobOpening->reporting_to ?: '—' }}</div>
            <div><span class="font-semibold text-slate-600">Posted At:</span> {{ $jobOpening->posted_at?->format('M j, Y') ?: '—' }}</div>
            <div><span class="font-semibold text-slate-600">Expires At:</span> {{ $jobOpening->expires_at?->format('M j, Y') ?: '—' }}</div>
            <div><span class="font-semibold text-slate-600">Active:</span> {{ $jobOpening->active ? 'Yes' : 'No' }}</div>
        </div>

        <div>
            <h2 class="font-semibold text-slate-700 mb-2">Description</h2>
            @include('partials.job-description-html', ['content' => $jobOpening->description])
        </div>
    </div>
</div>
@endsection

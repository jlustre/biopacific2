@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">Job Opening Details</h1>
    <div class="bg-white p-4 rounded shadow">
        <div class="mb-2"><strong>Title:</strong> {{ $jobOpening->title }}</div>
        <div class="mb-2"><strong>Reporting To:</strong> {{ $jobOpening->reporting_to }}</div>
        <div class="mb-2"><strong>Status:</strong> {{ ucfirst($jobOpening->status) }}</div>
        <div class="mb-2"><strong>Description:</strong></div>
        <div class="mb-2">{{ $jobOpening->description }}</div>
        <div class="mb-2"><strong>Created By:</strong> {{ $jobOpening->created_by ? ($jobOpening->creator->name ??
            $jobOpening->created_by) : 'N/A' }}</div>
        <a href="{{ route('admin.facility.job_openings.edit', [$facility, $jobOpening]) }}"
            class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Edit</a>
        <a href="{{ route('admin.facility.job_openings', $facility) }}"
            class="inline-block mt-4 px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Back to Listings</a>
    </div>
</div>
@endsection
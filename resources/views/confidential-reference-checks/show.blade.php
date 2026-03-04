@extends('layouts.dashboard')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Reference Check Details</h1>
    <div class="bg-white p-6 rounded shadow">
        <div class="mb-2"><strong>ID:</strong> {{ $confidentialReferenceCheck->id }}</div>
        <div class="mb-2"><strong>User:</strong> {{ $confidentialReferenceCheck->user->name ?? '-' }}</div>
        <div class="mb-2"><strong>Facility:</strong> {{ $confidentialReferenceCheck->facility->name ?? '-' }}</div>
        <div class="mb-2"><strong>Reference Name:</strong> {{ $confidentialReferenceCheck->reference_name }}</div>
        <div class="mb-2"><strong>Relationship:</strong> {{ $confidentialReferenceCheck->relationship }}</div>
        <div class="mb-2"><strong>Comments:</strong> {{ $confidentialReferenceCheck->comments }}</div>
    </div>
    <div class="mt-4">
        <a href="{{ route('confidential-reference-checks.edit', $confidentialReferenceCheck) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('confidential-reference-checks.index') }}" class="btn btn-secondary ml-2">Back</a>
    </div>
</div>
@endsection

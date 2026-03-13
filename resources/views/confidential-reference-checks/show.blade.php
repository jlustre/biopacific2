<div class="mb-2"><strong>Employment From:</strong> {{ $confidentialReferenceCheck->employment_from }}</div>
<div class="mb-2"><strong>Employment To:</strong> {{ $confidentialReferenceCheck->employment_to }}</div>
<div class="mb-2"><strong>Salary:</strong> {{ $confidentialReferenceCheck->salary }}</div>
<div class="mb-2"><strong>Salary Per:</strong> {{ $confidentialReferenceCheck->salary_per }}</div>
<div class="mb-2"><strong>Description of Duties:</strong> {{ $confidentialReferenceCheck->duties_description }}</div>
<div class="mb-2"><strong>Performance Description:</strong> {{ $confidentialReferenceCheck->performance_description }}
</div>
<div class="mb-2"><strong>Date Contacted:</strong> {{ $confidentialReferenceCheck->date_contacted }}</div>
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
        <div class="mb-2"><strong>Reference Phone:</strong> {{ $confidentialReferenceCheck->reference_phone }}</div>
        <div class="mb-2"><strong>Reference Email:</strong> {{ $confidentialReferenceCheck->reference_email }}</div>
        <div class="mb-2"><strong>Company:</strong> {{ $confidentialReferenceCheck->company }}</div>
        <div class="mb-2"><strong>Signed:</strong> {{ $confidentialReferenceCheck->signed ? 'Yes' : 'No' }}</div>
        <div class="mb-2"><strong>Signed Date:</strong> {{ $confidentialReferenceCheck->signed_date }}</div>
    </div>
    <div class="mt-4">
        <a href="{{ route('reference-checks.edit', $confidentialReferenceCheck) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('reference-checks.index') }}" class="btn btn-secondary ml-2">Back</a>
    </div>
</div>
@endsection
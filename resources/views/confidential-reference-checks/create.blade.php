@extends('layouts.dashboard')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">New Confidential Reference Check</h1>
    <form action="{{ route('confidential-reference-checks.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1">User ID</label>
            <input type="number" name="user_id" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Facility ID</label>
            <input type="number" name="facility_id" class="form-input w-full">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Name</label>
            <input type="text" name="reference_name" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Relationship</label>
            <input type="text" name="relationship" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Comments</label>
            <textarea name="comments" class="form-input w-full"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('confidential-reference-checks.index') }}" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection
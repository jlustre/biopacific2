@extends('layouts.dashboard')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Confidential Reference Check</h1>
    <form action="{{ route('confidential-reference-checks.update', $confidentialReferenceCheck) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Name</label>
            <input type="text" name="reference_name" class="form-input w-full" value="{{ old('reference_name', $confidentialReferenceCheck->reference_name) }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Relationship</label>
            <input type="text" name="relationship" class="form-input w-full" value="{{ old('relationship', $confidentialReferenceCheck->relationship) }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Comments</label>
            <textarea name="comments" class="form-input w-full">{{ old('comments', $confidentialReferenceCheck->comments) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('confidential-reference-checks.index') }}" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection

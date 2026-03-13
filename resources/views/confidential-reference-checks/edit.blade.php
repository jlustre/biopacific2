<div class="mb-4">
    <label class="block font-semibold mb-1">Employment From</label>
    <input type="date" name="employment_from" class="form-input w-full"
        value="{{ old('employment_from', $confidentialReferenceCheck->employment_from) }}">
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Employment To</label>
    <input type="date" name="employment_to" class="form-input w-full"
        value="{{ old('employment_to', $confidentialReferenceCheck->employment_to) }}">
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Salary</label>
    <input type="number" step="0.01" name="salary" class="form-input w-full"
        value="{{ old('salary', $confidentialReferenceCheck->salary) }}">
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Salary Per</label>
    <select name="salary_per" class="form-input w-full">
        <option value="" {{ old('salary_per', $confidentialReferenceCheck->salary_per) == '' ? 'selected' : '' }}>Select
        </option>
        <option value="year" {{ old('salary_per', $confidentialReferenceCheck->salary_per) == 'year' ? 'selected' : ''
            }}>Year</option>
        <option value="hour" {{ old('salary_per', $confidentialReferenceCheck->salary_per) == 'hour' ? 'selected' : ''
            }}>Hour</option>
    </select>
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Description of Duties</label>
    <textarea name="duties_description"
        class="form-input w-full">{{ old('duties_description', $confidentialReferenceCheck->duties_description) }}</textarea>
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Performance Description</label>
    <textarea name="performance_description"
        class="form-input w-full">{{ old('performance_description', $confidentialReferenceCheck->performance_description) }}</textarea>
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Date Contacted</label>
    <input type="date" name="date_contacted" class="form-input w-full"
        value="{{ old('date_contacted', $confidentialReferenceCheck->date_contacted) }}">
</div>
@extends('layouts.dashboard')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Confidential Reference Check</h1>
    <form action="{{ route('confidential-reference-checks.update', $confidentialReferenceCheck) }}" method="POST"
        class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Name</label>
            <input type="text" name="reference_name" class="form-input w-full"
                value="{{ old('reference_name', $confidentialReferenceCheck->reference_name) }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Relationship</label>
            <input type="text" name="relationship" class="form-input w-full"
                value="{{ old('relationship', $confidentialReferenceCheck->relationship) }}" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Comments</label>
            <textarea name="comments"
                class="form-input w-full">{{ old('comments', $confidentialReferenceCheck->comments) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Phone</label>
            <input type="text" name="reference_phone" class="form-input w-full"
                value="{{ old('reference_phone', $confidentialReferenceCheck->reference_phone) }}">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Email</label>
            <input type="email" name="reference_email" class="form-input w-full"
                value="{{ old('reference_email', $confidentialReferenceCheck->reference_email) }}">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Company</label>
            <input type="text" name="company" class="form-input w-full"
                value="{{ old('company', $confidentialReferenceCheck->company) }}">
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" name="signed" id="signed" value="1" class="mr-2" {{ old('signed',
                $confidentialReferenceCheck->signed) ? 'checked' : '' }}>
            <label for="signed" class="font-semibold">Signed</label>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Signed Date</label>
            <input type="date" name="signed_date" class="form-input w-full"
                value="{{ old('signed_date', $confidentialReferenceCheck->signed_date) }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('confidential-reference-checks.index') }}" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection
<div class="mb-4">
    <label class="block font-semibold mb-1">Employment From</label>
    <input type="date" name="employment_from" class="form-input w-full">
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Employment To</label>
    <input type="date" name="employment_to" class="form-input w-full">
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Salary</label>
    <input type="number" step="0.01" name="salary" class="form-input w-full">
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Salary Per</label>
    <select name="salary_per" class="form-input w-full">
        <option value="">Select</option>
        <option value="year">Year</option>
        <option value="hour">Hour</option>
    </select>
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Description of Duties</label>
    <textarea name="duties_description" class="form-input w-full"></textarea>
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Performance Description</label>
    <textarea name="performance_description" class="form-input w-full"></textarea>
</div>
<div class="mb-4">
    <label class="block font-semibold mb-1">Date Contacted</label>
    <input type="date" name="date_contacted" class="form-input w-full">
</div>
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
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Phone</label>
            <input type="text" name="reference_phone" class="form-input w-full">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Reference Email</label>
            <input type="email" name="reference_email" class="form-input w-full">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Company</label>
            <input type="text" name="company" class="form-input w-full">
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" name="signed" id="signed" value="1" class="mr-2">
            <label for="signed" class="font-semibold">Signed</label>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Signed Date</label>
            <input type="date" name="signed_date" class="form-input w-full">
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('confidential-reference-checks.index') }}" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection
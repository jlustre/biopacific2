@extends('layouts.dashboard')

@section('content')
@php
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$isAdmin = $user && $user->hasRole('admin');
$isFacilityAdmin = $user && $user->hasRole(['facility-admin', 'facility-dsd']);
$facilities = $isAdmin ? \App\Models\Facility::all() : ($user && $user->facility ? collect([$user->facility]) :
collect());
$selectedFacility = $isAdmin ? null : ($user && $user->facility ? $user->facility->id : null);
@endphp
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">Gallery Image Upload</h1>
    @if($isAdmin)
    <form method="GET" action="" class="mb-4">
        <label for="facility-select" class="block font-semibold mb-1">Select Facility</label>
        <select id="facility-select" name="facility_id" class="form-select w-full"
            onchange="window.location='?facility_id='+this.value">
            <option value="">-- Choose Facility --</option>
            @foreach($facilities as $facility)
            <option value="{{ $facility->id }}" @if(request('facility_id')==$facility->id) selected @endif>{{
                $facility->name }}</option>
            @endforeach
        </select>
    </form>
    @endif
    @if($isAdmin && !request('facility_id'))
    <div class="text-red-600 mb-4">Please select a facility to upload images.</div>
    @else
    <form method="POST" action="{{ route('gallery.upload') }}" enctype="multipart/form-data"
        class="bg-white p-4 rounded shadow">
        @csrf
        <input type="hidden" name="facility_id" value="{{ $isAdmin ? request('facility_id') : $selectedFacility }}">
        <div class="mb-4">
            <label class="block font-semibold mb-1">Image</label>
            <input type="file" name="image" accept="image/*" required class="form-input w-full">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Title</label>
            <input type="text" name="title" class="form-input w-full" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" class="form-input w-full" rows="2"></textarea>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
    </form>
    @endif
</div>
@endsection
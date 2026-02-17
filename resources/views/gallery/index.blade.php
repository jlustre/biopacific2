@extends('layouts.dashboard')

@section('content')
@php
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$isAdmin = $isAdmin ?? ($user && $user->hasRole('admin'));
$facilities = $facilities ?? ($isAdmin ? \App\Models\Facility::all() : ($user && $user->facility ?
collect([$user->facility]) : collect()));
$facilityId = $facilityId ?? ($isAdmin ? request('facility_id') : ($user && $user->facility ? $user->facility->id :
null));
@endphp
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">Gallery</h1>
    @if($isAdmin)
    <form method="GET" action="" class="mb-4">
        <label for="facility-select" class="block font-semibold mb-1">Select Facility</label>
        <select id="facility-select" name="facility_id" class="form-select w-full"
            onchange="window.location='?facility_id='+this.value">
            <option value="">-- Choose Facility --</option>
            @foreach($facilities as $facility)
            <option value="{{ $facility->id }}" @if($facilityId==$facility->id) selected @endif>{{ $facility->name }}
            </option>
            @endforeach
        </select>
    </form>
    @endif
    <a href="{{ route('gallery.upload') }}@if($facilityId)?facility_id={{ $facilityId }}@endif"
        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mb-4 inline-block">Upload Image</a>
    @if(isset($images) && $images->count())
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($images as $img)
        <div class="bg-white rounded shadow p-2">
            <img src="{{ asset('storage/' . $img->image_url) }}" alt="{{ $img->title }}"
                class="w-full h-48 object-cover rounded mb-2">
            <div class="font-semibold">{{ $img->title }}</div>
            <div class="text-sm text-gray-600">{{ $img->description }}</div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-gray-500">No images found for this facility.</div>
    @endif
</div>
@endsection
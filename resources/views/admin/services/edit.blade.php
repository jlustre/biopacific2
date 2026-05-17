@extends('layouts.dashboard', ['title' => 'Edit Service'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Service</h1>
        <p class="text-gray-600 mt-2">{{ $service->name }}</p>
    </div>
    <a href="{{ route('admin.services.index', isset($scopedFacilityId) && $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : []) }}"
        class="inline-flex items-center text-gray-600 hover:text-gray-900 font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to services
    </a>
</div>
@endsection

@section('content')
<div class="max-w-3xl">
    @if($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        @include('admin.services._form')
    </div>
</div>
@endsection

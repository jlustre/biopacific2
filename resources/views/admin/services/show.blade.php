@extends('layouts.dashboard')
@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Service Details</h1>
    <div class="mb-4">
        <strong>Name:</strong> {{ $service->name }}
    </div>
    <div class="mb-4">
        <strong>Short Description:</strong> {{ $service->short_description }}
    </div>
    <div class="mb-4">
        <strong>Global Service?</strong> {{ $service->is_global ? 'Yes' : 'No' }}
    </div>
    <div class="mb-4">
        <strong>Detailed Description:</strong>
        <div class="prose max-w-none">{!! $service->detailed_description !!}</div>
    </div>
    <div class="mb-4">
        <strong>Icon:</strong> {{ $service->icon }}
    </div>
    <div class="mb-4">
        <strong>Image URL:</strong> {{ $service->image_url }}
    </div>
    <div class="mb-4">
        <strong>Order:</strong> {{ $service->order }}
    </div>
    <div class="mb-4">
        <strong>Featured?</strong> {{ $service->is_featured ? 'Yes' : 'No' }}
    </div>
    <div class="mb-4">
        <strong>Active?</strong> {{ $service->is_active ? 'Yes' : 'No' }}
    </div>
    <a href="{{ route('admin.services.index') }}" class="text-blue-600 hover:underline">Back to Services</a>
</div>
@endsection
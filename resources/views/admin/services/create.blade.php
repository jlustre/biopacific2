@extends('layouts.dashboard')

@section('content')
<div class="max-w-xl mx-auto mt-10 bg-white p-8 rounded shadow">
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-300">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded border border-red-300">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <h2 class="text-2xl font-bold mb-6">{{ isset($service) ? 'Edit Service' : 'Add New Service' }}</h2>
    <form method="POST"
        action="{{ isset($service) ? route('admin.services.update', $service->id) : route('admin.services.store') }}">
        @csrf
        @if(isset($service))
        @method('PUT')
        @endif
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Service Name</label>
            <input type="text" name="title" class="border rounded px-3 py-2 w-full" required
                value="{{ old('title', $service->title ?? '') }}">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Description</label>
            <input type="text" name="description" class="border rounded px-3 py-2 w-full"
                value="{{ old('description', $service->description ?? '') }}">
        </div>
        <div class="flex gap-4 mt-4">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">{{ isset($service) ? 'Update
                Service' : 'Add Service' }}</button>
            <a href="http://127.0.0.1:8000/admin/facilities/1/edit"
                class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Back to Facility</a>
        </div>
    </form>
</div>
@endsection
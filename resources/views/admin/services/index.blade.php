@extends('layouts.dashboard')
@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Services Management</h1>
    <a href="{{ route('admin.services.create') }}"
        class="bg-teal-600 text-white px-4 py-2 rounded mb-4 inline-block">Add Service</a>
    @if(session('success'))
    <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif
    <table class="min-w-full bg-white border rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">Order</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Short Description</th>
                <th class="px-4 py-2">Global?</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td class="border px-4 py-2">{{ $service->order }}</td>
                <td class="border px-4 py-2">{{ $service->name }}</td>
                <td class="border px-4 py-2">{{ $service->short_description }}</td>
                <td class="border px-4 py-2">{{ $service->is_global ? 'Yes' : 'No' }}</td>
                <td class="border px-4 py-2 flex gap-2">
                    <a href="{{ route('admin.services.edit', $service) }}"
                        class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST"
                        onsubmit="return confirm('Delete this service?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
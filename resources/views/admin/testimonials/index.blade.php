@extends('layouts.dashboard')
@section('title', 'Testimonials for ' . $facility->name)
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Testimonials for {{ $facility->name }}</h1>
    <a href="{{ route('admin.facilities.testimonials.create', $facility->id) }}"
        class="mb-4 inline-block bg-primary text-white px-4 py-2 rounded hover:bg-primary/80">Add Testimonial</a>
    @if(session('success'))
    <div class="mb-4 p-2 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <table class="w-full table-auto border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">Name</th>
                <th class="p-2">Role</th>
                <th class="p-2">Rating</th>
                <th class="p-2">Title</th>
                <th class="p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($testimonials as $t)
            <tr class="border-b">
                <td class="p-2">{{ $t->name }}</td>
                <td class="p-2">{{ $t->role }}</td>
                <td class="p-2">{{ $t->rating }}</td>
                <td class="p-2">{{ $t->title }}</td>
                <td class="p-2">
                    <a href="{{ route('admin.facilities.testimonials.edit', [$facility->id, $t->id]) }}"
                        class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('admin.facilities.testimonials.destroy', [$facility->id, $t->id]) }}"
                        method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline ml-2"
                            onclick="return confirm('Delete this testimonial?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
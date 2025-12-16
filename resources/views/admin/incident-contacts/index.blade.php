@extends('layouts.dashboard')
@section('title', 'Incident Response Contacts')
@section('content')
<div class="max-w-3xl mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Incident Response Contacts</h1>
        <a href="{{ route('admin.incident-contacts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Add
            Contact</a>
    </div>
    @if(session('success'))
    <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif
    <div class="bg-white rounded shadow p-6">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="text-left">Role</th>
                    <th class="text-left">Name</th>
                    <th class="text-left">Title</th>
                    <th class="text-left">Email</th>
                    <th class="text-left">Phone</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>{{ $contact->role }}</td>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->title }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>
                        <a href="{{ route('admin.incident-contacts.edit', $contact) }}" class="text-blue-600">Edit</a>
                        <form action="{{ route('admin.incident-contacts.destroy', $contact) }}" method="POST"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 ml-2"
                                onclick="return confirm('Delete this contact?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
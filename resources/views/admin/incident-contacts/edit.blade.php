@extends('layouts.dashboard')
@section('title', 'Edit Incident Contact')
@section('content')
<div class="max-w-xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Incident Response Contact</h1>
    <form method="POST" action="{{ route('admin.incident-contacts.update', $incidentContact) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.incident-contacts._form', ['contact' => $incidentContact])
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Contact</button>
            <a href="{{ route('admin.incident-contacts.index') }}" class="ml-4 text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection
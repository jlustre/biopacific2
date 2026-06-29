@extends('layouts.dashboard', ['title' => 'Edit Position Portal Role'])

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.position-portal-roles.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">&larr; Back to mappings</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">Edit Position Role Mapping</h1>
        <p class="text-gray-600 mt-1">{{ $mapping->position?->title }} &middot; {{ $mapping->position?->department?->name }}</p>
    </div>

    @include('admin.position-portal-roles._form', [
        'mapping' => $mapping,
        'roles' => $roles,
        'positions' => collect(),
        'action' => route('admin.position-portal-roles.update', $mapping),
        'method' => 'PUT',
    ])
</div>
@endsection

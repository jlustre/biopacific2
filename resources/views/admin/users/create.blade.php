@extends('layouts.dashboard', ['title' => 'Add New User'])

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Add New User</h1>
    <form action="{{ route('admin.users.store') }}" method="POST" class="max-w-lg mx-auto bg-white p-6 rounded shadow">
        @include('admin.users.partials.form')
    </form>
</div>
@endsection
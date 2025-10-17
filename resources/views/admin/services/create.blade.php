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
    <h2 class="text-2xl font-bold mb-6">Add New Service</h2>
    @include('admin.services._form')
</div>
@endsection
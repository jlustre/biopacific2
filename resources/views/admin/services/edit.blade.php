@extends('layouts.dashboard')
@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Service</h1>
    @include('admin.services._form')
</div>
@endsection
@extends('layouts.dashboard')

@section('content')
<h1 class="text-teal-600 text-2xl font-bold text-center mb-4">Edit BAA Vendor</h1>
@include('admin.baa-registry._form', ['vendor' => $vendor])
@endsection
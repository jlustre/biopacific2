@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Livewire Facility Uploads Minimal Test</h1>
    <div class="p-6 mb-6 bg-white rounded shadow">
        <livewire:admin.facilities.facility-uploads />
    </div>
</div>
@endsection

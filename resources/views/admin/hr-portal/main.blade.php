@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">HR Portal</h1>
    <div class="p-6 bg-white rounded shadow flex flex-col gap-4">
        <a href="{{ route('admin.hr-portal.reports') }}" class="px-4 py-2 bg-blue-600 text-white rounded w-64">Reports</a>
        <!-- Add other HR Portal main buttons here -->
    </div>
</div>
@endsection

@extends('layouts.member-portal')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @include('admin.facilities.employee.partials.employee-edit-shell')
</section>
@endsection

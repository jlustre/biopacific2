@extends('layouts.member-portal')

@push('head')
    @fluxAppearance
@endpush

@section('content')
    <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
@endsection

@push('scripts')
    @fluxScripts
@endpush

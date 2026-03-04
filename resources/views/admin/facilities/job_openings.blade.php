@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <h1 class="text-xl font-bold mb-6"><strong>{{ $facility->name }}</strong></h1>
    <livewire:job-listing-manager :facility="$facility" />
</div>
@endsection
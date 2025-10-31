@extends('layouts.admin')

@section('content')
{{-- Include the hero section partial --}}
@include('partials.hero.hero5', [
'facility' => $facility,
'neutral_light' => $neutral_light,
'neutral_dark' => $neutral_dark,
'primary' => $facility->colorScheme->primary ?? '#007bff',
'secondary' => $facility->colorScheme->secondary ?? '#6c757d',
'accent' => $facility->colorScheme->accent ?? '#28a745',
'activeSections' => $activeSections
])

{{-- Additional content can be added here --}}
@endsection
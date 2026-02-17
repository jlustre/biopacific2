@php
use Illuminate\Support\Facades\Auth;
$sections = $sections ?? [];
@endphp
{{-- filepath: resources/views/welcome.blade.php --}}



{{-- @extends('layouts.' . ($layoutTemplate ?? 'default-template')) --}}
@extends('layouts.' . ($layoutTemplate ?? 'default-template'))

@section('content')

@if(isset($sections) && is_array($sections) && in_array('hero', $sections))

@include('partials.hero.' . ($sectionVariances['hero'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@if(isset($sections) && is_array($sections) && in_array('about', $sections))
{{-- @include('partials.divider') --}}
@include('partials.about.' . ($sectionVariances['about'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@include('partials.whychoose')

@if(isset($sections) && is_array($sections) && in_array('services', $sections))
{{-- @include('partials.divider') --}}
@include('partials.services.' . ($sectionVariances['services'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent,
'services' => $services ?? []
])
@endif

@if(isset($sections) && is_array($sections) && in_array('gallery', $sections))
{{-- @include('partials.divider') --}}
@include('partials.gallery.' . ($sectionVariances['gallery'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@php
$newsItems = $newsItems ?? [];
@endphp
@if(is_array($sections) && in_array('news', $sections))
{{-- @include('partials.divider') --}}
@include('partials.news.' . ($sectionVariances['news'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent,
'newsItems' => $newsItems
])
@endif

@if(is_array($sections) && in_array('testimonials', $sections))
{{-- @include('partials.divider') --}}
@include('partials.testimonials.' . ($sectionVariances['testimonials'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent,
'testimonials' => $testimonials
])
@endif

@if(is_array($sections) && in_array('careers', $sections))
{{-- @include('partials.divider') --}}
@include('partials.careers.' . ($sectionVariances['careers'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@if(is_array($sections) && in_array('book', $sections))
{{-- @include('partials.divider') --}}
@include('partials.book.' . ($sectionVariances['book'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent,
'services' => $services,
'facility' => $facility
])
@endif

@if(is_array($sections) && in_array('contact', $sections))
{{-- @include('partials.divider') --}}
@include('partials.contact.' . ($sectionVariances['contact'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent,
'facilities' => $facilities ?? []
])
@endif

@php
$isBioPacific = false;
if (isset($facility)) {
if (is_array($facility) && isset($facility['id']) && $facility['id'] == 99) {
$isBioPacific = true;
} elseif (is_object($facility) && isset($facility->id) && $facility->id == 99) {
$isBioPacific = true;
}
}
@endphp

@if($isBioPacific && Auth::check())
@include('facility_sections.facilities-map', ['facilities' => $facilities ?? []])
@endif

@if(is_array($sections) && in_array('rooms', $sections))
{{-- @include('partials.divider') --}}
@include('partials.rooms.' . ($sectionVariances['rooms'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@if(is_array($sections) && in_array('faqs', $sections))
{{-- @include('partials.divider') --}}
@include('partials.faqs.' . ($sectionVariances['faqs'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@if(is_array($sections) && in_array('resources', $sections))
{{-- @include('partials.divider') --}}
@include('partials.resources.' . ($sectionVariances['resources'] ?? 'default'), [
'primary' => $primary,
'secondary' => $secondary,
'accent' => $accent
])
@endif

@endsection
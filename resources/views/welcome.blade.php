{{-- filepath: resources/views/welcome.blade.php --}}
@extends('layouts.' . ($layoutTemplate ?? 'default-template'))

@section('content')
@if(is_array($sections) && in_array('hero', $sections))
@include('partials.hero.' . ($sectionVariances['hero'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('about', $sections))
@include('partials.about.' . ($sectionVariances['about'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('services', $sections))
@include('partials.services.' . ($sectionVariances['services'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('rooms', $sections))
@include('partials.rooms.' . ($sectionVariances['rooms'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('gallery', $sections))
@include('partials.gallery.' . ($sectionVariances['gallery'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('news', $sections))
@include('partials.news.' . ($sectionVariances['news'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('testimonials', $sections))
@include('partials.testimonials.' . ($sectionVariances['testimonials'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('careers', $sections))
@include('partials.careers.' . ($sectionVariances['careers'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('contact', $sections))
@include('partials.contact.' . ($sectionVariances['contact'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('faqs', $sections))
@include('partials.faqs.' . ($sectionVariances['faqs'] ?? 'default'))
@endif

@if(is_array($sections) && in_array('resources', $sections))
@include('partials.resources.' . ($sectionVariances['resources'] ?? 'default'))
@endif
@endsection
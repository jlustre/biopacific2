@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <!-- Header -->
    @include('components.back-link-header', [
    'title_hdr' => 'Tour Request Details',
    'subtitle_hdr' => 'View details of the tour request',
    'preview' => false
    ])

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-bold mb-4">Tour Request Information</h2>
        <p><strong>Facility:</strong> {{ $tourRequest->facility->name }}</p>
        <p><strong>Full Name:</strong> {{ $tourRequest->full_name }}</p>
        <p><strong>Email:</strong> {{ $tourRequest->email }}</p>
        <p><strong>Phone:</strong> {{ $tourRequest->phone }}</p>
        <p><strong>Preferred Date:</strong> {{ $tourRequest->preferred_date }}</p>
        <p><strong>Preferred Time:</strong> {{ $tourRequest->preferred_time }}</p>
        <p><strong>Message:</strong> {{ $tourRequest->message }}</p>
    </div>
</div>
@endsection
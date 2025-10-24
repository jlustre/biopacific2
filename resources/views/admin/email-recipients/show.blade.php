@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <!-- Header -->
    @include('components.back-link-header', [
    'title_hdr' => 'Email Recipient Details',
    'subtitle_hdr' => 'View details of the email recipient',
    'preview' => false
    ])

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-bold mb-4">Email Recipient Information</h2>
        <p><strong>Facility:</strong> {{ $emailRecipient->facility->name }}</p>
        <p><strong>Category:</strong> {{ $emailRecipient->category }}</p>
        <p><strong>Email:</strong> {{ $emailRecipient->email }}</p>
    </div>
</div>
@endsection
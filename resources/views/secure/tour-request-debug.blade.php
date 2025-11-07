@extends('layouts.base')

@section('title', 'Debug Tour Request #' . $tourRequest->id)

@section('head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@section('body')
<div style="padding: 20px; background: white; margin: 20px;">
    <h1 style="color: red; font-size: 24px;">🔍 DEBUG Tour Request View</h1>

    <div style="border: 2px solid blue; padding: 15px; margin: 10px 0;">
        <h2>Tour Request Data:</h2>
        <p><strong>ID:</strong> {{ $tourRequest->id }}</p>
        <p><strong>Facility:</strong> {{ $facility->name }}</p>
        <p><strong>Created:</strong> {{ $tourRequest->created_at }}</p>
        <p><strong>Full Name:</strong> {{ $tourRequest->full_name }}</p>
        <p><strong>Email:</strong> {{ $tourRequest->email }}</p>
        <p><strong>Phone:</strong> {{ $tourRequest->phone }}</p>
        <p><strong>Preferred Date:</strong> {{ $tourRequest->preferred_date }}</p>
        <p><strong>Preferred Time:</strong> {{ $tourRequest->preferred_time }}</p>
        <p><strong>Message:</strong> {{ $tourRequest->message }}</p>
    </div>

    <div style="border: 2px solid green; padding: 15px; margin: 10px 0;">
        <h2>Interests:</h2>
        @if($tourRequest->interests && count($tourRequest->interests) > 0)
        <ul>
            @foreach($tourRequest->interests as $interest)
            <li>{{ $interest }}</li>
            @endforeach
        </ul>
        @else
        <p>No interests specified</p>
        @endif
    </div>

    <div style="border: 2px solid red; padding: 15px; margin: 10px 0;">
        <h2>Security Info:</h2>
        <p><strong>Access Token:</strong> {{ substr($tourRequest->access_token, 0, 16) }}...</p>
        <p><strong>Expires:</strong> {{ $tourRequest->expires_at }}</p>
        <p><strong>Is Accessible:</strong> {{ $tourRequest->isAccessible() ? 'Yes' : 'No' }}</p>
    </div>

    <p style="margin-top: 20px; color: green; font-weight: bold;">
        ✅ If you can see this page, the view rendering is working!
    </p>
</div>
@endsection
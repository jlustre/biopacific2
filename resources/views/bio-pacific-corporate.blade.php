@extends('layouts.default-template')

@section('content')
    @include('layouts.topnav')
    <div class="pt-20">
        <!-- Main content goes here -->
        <div class="max-w-4xl mx-auto p-6">
            <h1 class="text-3xl font-bold mb-4">Welcome to Bio-Pacific Corporate</h1>
            <p class="mb-6">This is the new home page. Please <a href="{{ route('login') }}" class="text-teal-600 underline">login</a> to access your account.</p>
            <!-- Add more content as needed -->
        </div>
    </div>
@endsection

@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center">Facility Access Login</h2>
        @if(session('error'))
        <div class="mb-4 text-red-600">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('facility.public.login.submit', ['facility' => $facility->slug]) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="password">Password</label>
                <input class="w-full px-3 py-2 border rounded" type="password" name="password" id="password" required
                    autofocus>
            </div>
            <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700" type="submit">Login</button>
        </form>
        <p class="mt-4 text-xs text-gray-500 text-center">
            This login is <strong>temporary</strong> and will be removed after testing.<br>
            <span class="block mt-2">Temporary password: <span class="font-mono">TempPass2025!</span></span>
            <span class="block mt-2 text-red-500">To remove this, delete the FacilityPublicLoginController,
                FacilityPublicPasswordMiddleware, and related routes/middleware after testing.</span>
        </p>
    </div>
</div>
@endsection
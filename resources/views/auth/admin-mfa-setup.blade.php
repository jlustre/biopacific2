@extends('layouts.guest')
@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white/90 rounded-xl shadow-lg p-8 border border-gray-300">
        <h2 class="text-xl font-bold mb-4">Set Up Multi-Factor Authentication</h2>
        <p class="mb-4">Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.), then
            enter the 6-digit code to enable MFA.</p>
        <div class="flex justify-center mb-4">
            <div>{!! $qrCodeUrl !!}</div>
        </div>
        @if (session('status'))
        <div class="mb-4 text-green-600 text-center font-semibold">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
        <div class="mb-4 text-red-600 text-center font-semibold">
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif
        <form method="POST" action="{{ route('admin.mfa.setup.store') }}">
            @csrf
            <div>
                <label for="one_time_password" class="block text-sm font-medium text-gray-700">Authentication
                    Code</label>
                <input id="one_time_password" name="one_time_password" type="text" required autofocus
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                @error('one_time_password')
                <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="btn btn-primary">Enable MFA</button>
            </div>
        </form>
    </div>
</div>
@endsection
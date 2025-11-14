@extends('layouts.user_dashboard', ['title' => 'Dashboard'])

@php
use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="flex flex-col gap-8 md:gap-12">
    <!-- User Welcome Card -->
    <div
        class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white rounded-xl shadow-lg p-6 flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl font-bold">
                {{ Auth::user()->initials }}
            </div>
            <div>
                <h2 class="text-2xl font-semibold">Welcome, {{ Auth::user()->name }}!</h2>
                <p class="text-sm opacity-80">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex flex-col gap-2">
            <a href="{{ route('settings.profile') }}"
                class="inline-block px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:bg-indigo-50 transition">Edit
                Profile</a>
            @if(Auth::user()->google2fa_secret)
            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold">MFA Enabled</span>
            @else
            <a href="{{ route('admin.mfa.setup.form') }}"
                class="inline-block px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg font-semibold shadow hover:bg-yellow-200 transition">Enable
                MFA</a>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    @include('partials.quick_stats')

    <!-- Recent Activity (placeholder) -->
    @include('partials.recent_activity')

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
</div>
@endsection
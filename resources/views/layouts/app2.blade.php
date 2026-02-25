@extends('layouts.base')

@section('body')
<div class="min-h-screen bg-gray-50 flex flex-col">
    {{-- Fixed Top Navigation Bar --}}
    <nav
        class="fixed top-0 left-0 right-0 bg-gradient-to-r from-teal-700 via-teal-600 to-teal-700 text-white shadow-lg z-50 h-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
            {{-- Logo and Brand --}}
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                    <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific"
                        class="w-8 h-8 filter brightness-0 invert">
                    <span class="text-lg font-bold hidden sm:inline">Bio-Pacific</span>
                </a>
            </div>

            {{-- Right Navigation Links --}}
            <div class="flex items-center gap-6">
                @auth
                <div class="flex items-center gap-4">
                    <span class="text-sm">{{ auth()->user()->name }}</span>
                    <div class="relative group">
                        <button class="text-white hover:text-teal-100 transition flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div
                            class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-lg shadow-xl">
                            <a href="{{ route('dashboard.index') }}"
                                class="block px-4 py-2 hover:bg-gray-100 first:rounded-t-lg">Dashboard</a>
                            <a href="{{ route('logout') }}" class="block px-4 py-2 hover:bg-gray-100 last:rounded-b-lg"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}"
                    class="text-teal-100 hover:text-white transition text-sm font-medium">Login</a>
                <a href="{{ route('register') }}"
                    class="bg-teal-500 hover:bg-teal-400 text-white px-4 py-2 rounded-lg transition text-sm font-medium">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="pt-20 flex-1 relative z-10 w-full">
        {{-- Flash Messages --}}
        @if ($errors->any())
        <div class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8 relative z-20">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if (session('success'))
        <div class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8 relative z-20">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        </div>
        @endif

        {{-- Content Yield --}}
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-8 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} Bio-Pacific. All rights reserved.</p>
        </div>
    </footer>
</div>

@livewireScripts
@endsection
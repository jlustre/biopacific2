@php
use Illuminate\Support\Facades\Auth;
@endphp
<div class="flex items-center space-x-6">
    <!-- Notifications -->
    <x-admin.webmaster-contact-notifications />
    <!-- Profile Dropdown -->
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center focus:outline-none">
            @php
            $profilePath = public_path('images/profile.png');
            $hasProfile = file_exists($profilePath);
            $userName = Auth::user()->name ?? 'Admin';
            $initials = collect(explode(' ', $userName))->map(function($w) { return strtoupper($w[0]); })->join('');
            @endphp
            @if($hasProfile)
            <img src="{{ asset('images/profile.png') }}" alt="Profile"
                class="h-8 w-8 rounded-full border bg-gray-200" />
            @else
            <span
                class="h-8 w-8 flex items-center justify-center rounded-full bg-teal-100 text-teal-700 font-bold text-lg border">{{
                $initials }}</span>
            @endif
            <span class="ml-2 text-gray-700 font-medium">{{ $userName }}</span>
            <svg class="ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" @click.away="open = false"
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg z-50"
            style="display: none;" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95">
            <a href="{{ route('settings.profile') }}"
                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="{{ route('settings.appearance') }}"
                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
            @if(auth()->user() && auth()->user()->hasRole('admin'))
            <a href="{{ route('dashboard.index') }}"
                class="block px-4 py-2 text-teal-700 hover:bg-gray-100 font-semibold">User Dashboard</a>
            <a href="{{ route('admin.dashboard.index') }}"
                class="block px-4 py-2 text-teal-700 hover:bg-gray-100 font-semibold">Admin Dashboard</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Log
                    Out</button>
            </form>
        </div>
    </div>
</div>
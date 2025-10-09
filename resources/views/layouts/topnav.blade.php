<nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 w-full z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.dashboard.index') }}" class="flex items-center">
                    <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-12 w-auto" />
                    <div class="flex flex-col items-start leading-tight -mt-1">
                        <span class="text-xl font-bold text-gray-900">Bio-Pacific</span>
                        <span class="text-sm text-teal-700 -mt-2">Administration</span>
                    </div>
                </a>
                <!-- Sidebar Toggle Button (Right of Logo/Name) -->
                <button x-data="{}" @click="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                    class="ml-2 bg-teal-100 text-teal-700 rounded-full shadow-lg p-1 hover:bg-teal-200 transition-all w-8 h-8 flex items-center justify-center">
                    <i class="fas fa-bars text-sm"></i>
                </button>
            </div>
            <!-- Topbar Right Section -->
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
                        $initials = collect(explode(' ', $userName))->map(function($w) { return strtoupper($w[0]);
                        })->join('');
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
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95">
                        <a href="{{ route('settings.profile') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="{{ route('settings.appearance') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Log
                                Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bio-Pacific Healthcare - Admin Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Alpine.js is included with Livewire, no need for separate include --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @livewireStyles
</head>

<body class="bg-gray-50">
    <!-- Go to Top Button -->
    <button x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = window.scrollY > 200 })"
        x-show="show" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed bottom-6 right-6 z-50 bg-primary text-white rounded-full shadow-lg p-3 hover:bg-primary/80 transition-all"
        style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Sidebar Toggle Button removed from sidebar -->
    <!-- Top Navigation (Fixed) -->
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
                        class="ml-2 bg-primary text-white rounded-full shadow-lg p-1 hover:bg-primary/80 transition-all w-8 h-8 flex items-center justify-center">
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                </div>
                <!-- Topbar Right Section -->
                <div class="flex items-center space-x-6">
                    <!-- Notifications -->
                    <button class="relative text-gray-600 hover:text-primary focus:outline-none">
                        <i class="fas fa-bell fa-lg"></i>
                        <span
                            class="absolute -top-1 -right-2 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">3</span>
                    </button>
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
                                class="h-8 w-8 flex items-center justify-center rounded-full bg-primary text-white font-bold text-lg border">{{
                                $initials }}</span>
                            @endif
                            <span class="ml-2 text-gray-700 font-medium">{{ $userName }}</span>
                            <svg class="ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
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
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Log Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Responsive Sidebar Layout -->
    <div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
        @toggle-sidebar.window="sidebarOpen = !sidebarOpen">
        <!-- Sidebar (Fixed) -->
        <aside
            class="bg-white border-r border-gray-200 w-64 space-y-6 py-7 px-2 fixed top-16 left-0 h-[calc(100vh-4rem)] transition duration-200 ease-in-out z-30"
            :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
            <nav class="flex flex-col space-y-2">
                <a href="{{ route('admin.dashboard.index') }}"
                    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('dashboard.*') ? 'bg-gray-100 font-bold' : '' }}">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @mouseenter="open = true" @mouseleave="open = false"
                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 rounded {{ request()->routeIs('admin.facilities.*') ? 'bg-gray-100 font-bold' : '' }}">
                        <i class="fas fa-building mr-2"></i> Facilities
                        <svg class="ml-auto h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" @mouseenter="open = true" @mouseleave="open = false"
                        class="absolute left-full top-0 mt-0 w-64 bg-white border border-gray-200 rounded shadow-lg z-50"
                        style="display: none;" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95">
                        <a href="{{ route('admin.facilities.index') }}"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-list mr-2"></i> All
                        </a>
                        <hr class="my-1">
                        <div style="max-height: 400px; overflow-y: auto;">
                            @php
                            $facilityList = isset($facilities) ? $facilities->sortBy('name') : [];
                            @endphp
                            <div style="overflow: visible; position: relative;">
                                @foreach($facilityList as $facility)
                                <div x-data="{ subOpen: false }" class="relative group" style="overflow: visible;"
                                    @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                    @php
                                    $abbr = $facility->name;
                                    if (\Str::contains($abbr, 'Driftwood') && \Str::contains($abbr, 'Hayward')) {
                                    $abbr = 'Driftwood HCC - Hywd';
                                    } elseif (\Str::contains($abbr, 'Driftwood') && \Str::contains($abbr, 'Santa Cruz'))
                                    {
                                    $abbr = 'Driftwood HCC - SCruz';
                                    } elseif (\Str::contains($abbr, 'Glendale Transitional Care Center')) {
                                    $abbr = 'Glendale TCC';
                                    } else {
                                    $abbr = str_replace('Health and Rehabilitation Center', 'HRC', $abbr);
                                    $abbr = str_replace('Health Care and Rehabilitation Center', 'HRC', $abbr);
                                    $abbr = str_replace('Health Care Center', 'HCC', $abbr);
                                    $abbr = str_replace('Healthcare Center', 'HCC', $abbr);
                                    }
                                    @endphp
                                    <a href="{{ route('admin.dashboard.facility', $facility->id) }}" target="_blank"
                                        class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-hospital mr-2"></i> {{ $abbr }}
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Add left margin and top padding for fixed sidebar and navbar -->
            <div :class="sidebarOpen ? 'pt-20 pl-64' : 'pt-20 pl-4'">
                <!-- Page Header -->
                @hasSection('header')
                <div class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        @yield('header')
                    </div>
                </div>
                @endif

                <!-- Main Content -->
                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @stack('scripts')
    @livewireScripts
</body>

</html>
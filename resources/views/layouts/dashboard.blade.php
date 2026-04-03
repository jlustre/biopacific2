<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title')@else Bio-Pacific Healthcare - Admin Dashboard @endif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/color-scheme-dropdown.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    @livewireStyles
    <link rel="icon" href="{{ asset('images/bplogo.png') }}" type="image/png">

</head>

<body class="min-h-screen antialiased"
    style="background-image: url('{{ asset('images/auth_background.jpg') }}'); background-size: cover; background-position: center;">
    <!-- Go to Top Button -->
    @include('layouts.partials.go_to_top')

    <!-- Top Navigation (Fixed) -->
    @include('layouts.topnav')
    <div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
        @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
        x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 })">

        <!-- Responsive Sidebar Layout -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1">
            <!-- Add left margin and top padding for fixed sidebar and navbar -->
            <div :class="sidebarOpen ? 'pt-20 pl-64' : 'pt-20 pl-4'">
                <!-- Page Header -->
                @hasSection('header')
                <div class="bg-white/60 shadow-sm border-b border-gray-200 rounded-xl">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        @yield('header')
                    </div>
                </div>
                @endif

                <!-- Main Content -->
                <main class="bg-white/60 rounded-xl max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @include('partials.screen-size-indicator')

    @stack('scripts')
    @livewireScripts(['vite' => true])
    <script src="/js/color-scheme-dropdown.js"></script>
</body>

</html>
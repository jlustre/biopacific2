<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bio-Pacific Healthcare - Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#047857',
                        secondary: '#1f2937',
                        accent: '#06b6d4',
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard.index') }}" class="flex items-center">
                        <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-8 w-auto" />
                        <span class="text-xl font-bold text-gray-900">Bio-Pacific Admin</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard.index') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.facilities.index') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.facilities.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                        <i class="fas fa-building mr-2"></i>
                        Facilities
                    </a>

                    <a href="{{ route('admin.layouts.index') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.layouts.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                        <i class="fas fa-th-large mr-2"></i>
                        Templates
                    </a>

                    <a href="{{ route('admin.layout-builder.index') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.layout-builder.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                        <i class="fas fa-paint-brush mr-2"></i>
                        Layout Builder
                    </a>

                    <a href="{{ route('admin.sections.index') }}"
                        class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.sections.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                        <i class="fas fa-puzzle-piece mr-2"></i>
                        Sections
                    </a>
                </div>
            </div>
        </div>
    </nav>

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

    @stack('scripts')
</body>

</html>
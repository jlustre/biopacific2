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
    @if (request()->routeIs('internal.login.form'))
    <main class="py-8">
        @else
        <!-- Go to Top Button -->
        @include('layouts.partials.go_to_top')

        <!-- Top Navigation (Fixed) -->
        @include('layouts.topnav')

        <!-- Responsive Sidebar Layout -->
        @auth
        @include('layouts.sidebar')
        @endauth
        @guest
        {{-- Optionally, show nothing or a guest sidebar --}}
        @endguest
        @include('partials.screen-size-indicator')
        <main class="py-8">
            @endif
            <div class="max-w-7xl mx-auto">
                @stack('content')
            </div>
        </main>

        @stack('scripts')
        @livewireScripts

        <script src="/js/color-scheme-dropdown.js"></script>
</body>

</html>
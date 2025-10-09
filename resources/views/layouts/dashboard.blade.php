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

<body class="min-h-screen antialiased"
    style="background-image: url('{{ asset('images/auth_background.jpg') }}'); background-size: cover; background-position: center;">
    <!-- Go to Top Button -->
    <button x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = window.scrollY > 200 })"
        x-show="show" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="fixed bottom-6 right-6 z-50 bg-primary text-white rounded-full shadow-lg p-3 hover:bg-primary/80 transition-all"
        style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Top Navigation (Fixed) -->
    @include('layouts.topnav')

    <!-- Responsive Sidebar Layout -->
    @include('layouts.sidebar')

    @stack('scripts')
    @livewireScripts
    <link rel="stylesheet" href="/css/color-scheme-dropdown.css">
    <script src="/js/color-scheme-dropdown.js"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Bio-Pacific'))</title>
    @stack('head')
    @yield('head')
</head>

<body>
    @yield('body')

    {{-- Scripts Section --}}
    @stack('scripts-before')

    {{-- Livewire Scripts (includes Alpine.js) --}}
    @livewireScripts

    {{-- Alpine.js Extensions/Plugins --}}
    @stack('alpine-plugins')

    {{-- Additional Scripts --}}
    @stack('scripts')

    {{-- Single Alpine.js Initialization --}}
    <script>
        // Ensure Alpine.js is only initialized once
        document.addEventListener('alpine:init', () => {
            
            // Add global fallback data to prevent undefined variable errors
            if (window.Alpine) {
                Alpine.store('global', {
                    toastOpen: false,
                    toastMsg: '',
                    showGoToTop: false
                });
            }
        });
        
        // Fallback: If Livewire doesn't include Alpine, load it
        if (typeof Alpine === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
            script.defer = true;
            document.head.appendChild(script);
            console.log('Alpine.js loaded as fallback');
        }
    </script>
</body>

</html>
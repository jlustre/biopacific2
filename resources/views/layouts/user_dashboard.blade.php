<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title')@else Bio-Pacific Healthcare - User Dashboard @endif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @livewireStyles
</head>

<body class="min-h-screen antialiased"
    style="background-image: url('@secureAsset('images/auth_background.jpg')'); background-size: cover; background-position: center;">
    @include('layouts.partials.go_to_top')
    @include('layouts.topnav_user')
    <!-- Responsive Sidebar Layout -->
    @include('layouts.sidebar_user')
    <main class="py-8">
        <div class="max-w-7xl mx-auto">
            @stack('content')
        </div>
    </main>
    @stack('scripts')
    @livewireScripts
</body>

</html>
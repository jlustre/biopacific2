<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>

<body class="min-h-screen antialiased"
    style="background-image: url('{{ asset('images/auth_background.jpg') }}'); background-size: cover; background-position: center;">
    <div class="bg-white/60 min-h-screen">
        <nav class="bg-primary text-white px-6 py-4 shadow">
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.news.index') }}" class="font-bold text-lg">Admin Dashboard</a>
                <div class="flex gap-4">
                    <a href="{{ route('admin.news.index') }}" class="hover:underline">News</a>
                    <a href="{{ route('admin.events.index') }}" class="hover:underline">Events</a>
                    @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.users.index') }}" class="hover:underline">User Management</a>
                    <a href="{{ url('admin/facilities') }}" class="hover:underline">Manage Facility</a>
                    @endif
                    <a href="/" class="hover:underline">Home</a>
                </div>
            </div>
        </nav>
        <main class="py-8">
            <div class="max-w-5xl mx-auto">
                @yield('content')
            </div>
        </main>
</body>

</html>
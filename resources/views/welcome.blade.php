<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-sm text-sm mb-6">
            @if (Route::has('login'))
                <nav class="flex items-start justify-end gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-block px-5 py-1.5 border border-gray-300 hover:border-gray-400 rounded-sm text-sm leading-normal transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 text-gray-700 dark:text-gray-300 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 rounded-sm text-sm leading-normal transition-colors">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-block px-5 py-1.5 border border-gray-300 hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500 rounded-sm text-sm leading-normal transition-colors">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <div class="text-2xl text-red-800 font-bold">
            Welcome Joey Lustre
        </div>

        @if (Route::has('login'))
            <div class="h-16 hidden lg:block"></div>
        @endif
    </body>
</html>

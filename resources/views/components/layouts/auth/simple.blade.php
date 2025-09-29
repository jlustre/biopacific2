<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased"
    style="background-image: url('{{ asset('images/auth_background.jpg') }}'); background-size: cover; background-position: center;">
    <div class="bg-white/60 border-2 border-gray-700 rounded-xl shadow-lg flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
        style="box-shadow: 0 4px 32px 0 rgba(0,0,0,0.12);">
        <div class="flex w-full max-w-sm flex-col gap-3">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                    <style>
                        .logo-img {
                            transform: scale(1.8);
                            display: inline-block;
                        }
                    </style>
                    <img src="{{ asset('images/bplogo.png') }}" class="logo-img" alt="Bio-Pacific Logo" {{ $attributes
                        }}>
                </span>
                <span class="text-xl font-bold">{{ config('app.name', 'Bio-Pacific') }}</span>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
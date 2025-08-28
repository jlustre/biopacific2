<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $facility['name'] ?? 'Bio-Pacific Healthcare' }}</title>

    {{-- Tenant-specific favicon --}}
    <link rel="icon" href="{{ app(\App\Services\TenantAssetService::class)->getFaviconUrl() }}">

    {{-- Dynamic theme colors --}}
    <style>
        :root {
            --color-primary: {
                    {
                    $facility['primary_color'] ?? '#047857'
                }
            }

            ;

            --color-secondary: {
                    {
                    $facility['secondary_color'] ?? '#1f2937'
                }
            }

            ;

            --color-accent: {
                    {
                    $facility['accent_color'] ?? '#06b6d4'
                }
            }

            ;
        }

            {
             ! ! app(\App\Services\TenantAssetService::class)->getCustomCSS() ! !
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased">
    {{-- Navigation --}}
    @include('partials.navigation', ['facility' => $facility ?? []])

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('partials.footer.footer', ['facility' => $facility ?? []])

    @livewireScripts
</body>

</html>
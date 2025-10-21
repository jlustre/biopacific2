<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ (is_object($facility) ? $facility->name : ($facility['name'] ?? null)) ?? 'Bio-Pacific Healthcare' }}
    </title>

    {{-- Tenant-specific favicon --}}
    <link rel="icon" href="{{ app(\App\Services\TenantAssetService::class)->getFaviconUrl() }}">

    {{-- Dynamic theme colors --}}
    @php
    // Ensure we have facility data for CSS variables
    $facilityColors = [];
    if (isset($facility) && is_object($facility)) {
    $facilityColors = $facility->toArray();
    } else {
    $facilityColors = $facility ?? [];
    }
    @endphp
    <style>
        :root {
            --color-primary: {
                    {
                    $facilityColors['primary_color'] ?? '#047857'
                }
            }

            ;

            --color-secondary: {
                    {
                    $facilityColors['secondary_color'] ?? '#1f2937'
                }
            }

            ;

            --color-accent: {
                    {
                    $facilityColors['accent_color'] ?? '#06b6d4'
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

@php
// Convert facility object to array for compatibility with partials
$facilityData = [];
if (isset($facility) && is_object($facility)) {
$facilityData = $facility->toArray();
} else {
$facilityData = $facility ?? [];
}
@endphp

<body class="font-sans antialiased">
    {{-- Navigation --}}
    @include('partials.navigation', ['facility' => $facilityData])

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('partials.footer.footer', ['facility' => $facilityData])

    @livewireScripts

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener("livewire:navigated", () => {
            if (window.Alpine && Alpine.initTree) {
                Alpine.initTree(document.body);
            }
        });
        document.addEventListener("livewire:load", () => {
            if (window.Alpine && Alpine.initTree) {
                Alpine.initTree(document.body);
            }
        });
    </script>
</body>

</html>
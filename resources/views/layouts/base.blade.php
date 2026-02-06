@php
$facility = $facility ?? ['facility_image' => 'images/default-facility.jpg'];
@endphp
<!DOCTYPE html>
<html lang="en">
@php
// Use plain meta_description text
$metaTitle = $facility['name'] ?? 'Facility Name';
$metaDescription = $facility['meta_description'] ?? '';
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $metaTitle }}" />
    <meta property="og:description" content="{{ $metaDescription }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ asset($facility['facility_image'] ?? 'images/default-og.jpg') }}" />

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $metaTitle }}" />
    <meta name="twitter:description" content="{{ $metaDescription }}" />
    <meta name="twitter:image" content="{{ asset($facility['facility_image'] ?? 'images/default-og.jpg') }}" />
    @php
    // --- 1. Define Helper Variables ---
    $facilityName = (is_array($facility) ? ($facility['name'] ?? 'Facility Name') : (is_object($facility) ?
    ($facility->name ?? 'Facility Name') : 'Facility Name'));

    @endphp

    <script type="application/ld+json">
        @json([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $facilityName ])
    </script>
    @stack('head')
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
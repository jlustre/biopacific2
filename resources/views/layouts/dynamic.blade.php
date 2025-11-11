@extends('layouts.base')

@section('title', (is_object($facility) ? $facility->name : ($facility['name'] ?? null)) ?? 'Bio-Pacific Healthcare')

@push('head')
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

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('body')

@php
// Convert facility object to array for compatibility with partials
$facilityData = [];
if (isset($facility) && is_object($facility)) {
$facilityData = $facility->toArray();
} else {
$facilityData = $facility ?? [];
}
@endphp

<body class="font-sans antialiased" style="padding-top: 72px;">
    {{-- Navigation --}}
    @include('partials.navigation', ['facility' => $facilityData])

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('partials.footer.footer', ['facility' => $facilityData])

    @endsection
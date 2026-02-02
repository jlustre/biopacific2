@extends('layouts.base')

@section('title', ($facility['subdomain'] ?? '') ?: 'Quality Care For Your Loved Ones')

@push('head')
@php
$currentSection = $section ?? 'home';
$metaDescription = '';
if (
isset($facility['meta_description']) &&
is_array($facility['meta_description']) &&
isset($facility['meta_description'][$currentSection]['meta_description']) &&
is_string($facility['meta_description'][$currentSection]['meta_description'])
) {
$metaDescription = $facility['meta_description'][$currentSection]['meta_description'];
} elseif (
isset($facility['meta_description']) &&
is_string($facility['meta_description'])
) {
$metaDescription = $facility['meta_description'];
}
@endphp
<meta name="description" content="{{ $metaDescription }}">
<style>
    :root {
        --color-primary: {
                {
                $colors['primary'] ?? '#047857'
            }
        }

        ;

        --color-secondary: {
                {
                $colors['secondary'] ?? '#1f2937'
            }
        }

        ;

        --color-accent: {
                {
                $colors['accent'] ?? '#06b6d4'
            }
        }

        ;
    }

    .high-contrast * {
        color: #000 !important;
        background-image: none !important;
    }
</style>
@endpush

@section('body')
<div class="bg-white px-2 md:px-5 antialiased transition-colors mx-0" style="padding-top: 72px;">
    <!-- Header -->
    @include('partials.header')

    <!-- Accessibility / Language Toolbar -->
    {{-- @include('partials.accessibility') --}}
    <!-- Main Content -->
    <main id="top" class="px-4 lg:px-0">
        @yield('page')
    </main>

    <!-- Footer -->
    @include('partials.footer.default', ['facility' => $facility ?? []])

    <!-- Toast -->
    @include('partials.toast')

    <!-- Go to Top Button -->
    @include('partials.gototop')

    {{-- @include('partials.screen-size-indicator') --}}

    @include('partials.scripts')
</div>
@endsection
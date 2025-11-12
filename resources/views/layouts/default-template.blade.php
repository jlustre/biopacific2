@extends('layouts.base')

@section('title', ($facility['subdomain'] ?? '') ?: 'Quality Care For Your Loved Ones')

@push('head')
<meta name="description" content="{{ $facility['meta_description'] ?? '' }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="@secureAsset('images/bplogo.png')" type="image/png">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush

@section('body')
<div x-data="siteUI()" :class="{'high-contrast': highContrast, 'text-lg': largeText, 'dark': darkMode}"
  class="scroll-smooth">
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

  <div class="bg-white px-2 md:px-5 antialiased transition-colors mx-0" style="padding-top: 72px;">
    <!-- Header -->
    @include('partials.header')

    <!-- Accessibility / Language Toolbar -->
    {{-- @include('partials.accessibility') --}}
    <!-- Main Content -->
    <main id="top" class="px-4 lg:px-0">
      @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer.default', ['facility' => $facility ?? []])

    <!-- Toast -->
    @include('partials.toast')

    <!-- Go to Top Button -->
    @include('partials.gototop')

    @include('partials.screen-size-indicator')

    @include('partials.scripts')

  </div>
  @endsection
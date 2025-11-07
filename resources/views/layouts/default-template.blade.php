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
  </head>

  <body
    class="bg-white px-2 md:px-5 lg:mr-0 dark:bg-slate-900 text-slate-800 dark:text-slate-200 antialiased transition-colors">
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

    <script>
      function siteUI(){
      return {
        mobileOpen:false,
        openRates:false,
        openApply:false,
        applyRole:'',
        largeText: JSON.parse(localStorage.getItem('largeText') || 'false'),
        highContrast: JSON.parse(localStorage.getItem('highContrast') || 'false'),
        darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'),
        lang: localStorage.getItem('lang') || 'en',
        nav: [
          {label:'About', href:'#about'},
          {label:'Services', href:'#services'},
          {label:'Rooms & Rates', href:'#rooms'},
          {label:'Careers', href:'#careers'},
          {label:'News & Events', href:'#news'},
          {label:'Testimonials', href:'#testimonials'},
          {label:'Gallery', href:'#gallery'},
          {label:'Contact', href:'#contact'},
          {label:'FAQs', href:'#faqs'},
          {label:'Resources', href:'#resources'},
        ],
        init() {
          // siteUI initialization - go to top functionality moved to gototop.blade.php
        },
        toggleLargeText(){ this.largeText=!this.largeText; localStorage.setItem('largeText', this.largeText) },
        toggleHighContrast(){ this.highContrast=!this.highContrast; localStorage.setItem('highContrast', this.highContrast) },
        toggleDarkMode(){ this.darkMode=!this.darkMode; localStorage.setItem('darkMode', this.darkMode) },
        setLang(v){ this.lang=v; localStorage.setItem('lang', v) },
        toast(msg){ 
            // Dispatch event to the toast component
            window.dispatchEvent(new CustomEvent('show-toast', { 
                detail: { message: msg } 
            }));
        }
      }
    }

    </script>

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
</div>
@endsection
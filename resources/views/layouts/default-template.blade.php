<!doctype html>
<html lang="en" x-data="siteUI()" :class="{'high-contrast': highContrast, 'text-lg': largeText, 'dark': darkMode}"
  class="scroll-smooth">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $facility['name'] }} — {{ $facility['tagline'] ?? 'Quality Care For Your Loved Ones' }}</title>
  <meta name="description" content="{{ $facility['meta_description'] }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '{{ $colors['primary'] ?? '#047857' }}',
            secondary: '{{ $colors['secondary'] ?? '#1f2937' }}',
            accent: '{{ $colors['accent'] ?? '#06b6d4' }}',
          },
        }
      }
    }
  </script>
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

<body class="bg-white px-5 lg:mr-0 dark:bg-slate-900 text-slate-800 dark:text-slate-200 antialiased transition-colors">
  <!-- Header -->
  @include('partials.header')

  <!-- Accessibility / Language Toolbar -->
  {{-- @include('partials.accessibility') --}}

  <!-- Main Content -->
  <main id="top" class="px-4 lg:px-0">
    @yield('content')
  </main>

  <!-- Footer -->
  @include('partials.footer.default')

  <!-- Toast -->
  @include('partials.toast')

  <!-- Go to Top Button -->
  @include('partials.gototop')

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
        toastOpen:false, toastMsg:'',
        showGoToTop: false,
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
          // Show/hide go to top button based on scroll position
          window.addEventListener('scroll', () => {
            this.showGoToTop = window.scrollY > 400;
          });
        },
        toggleLargeText(){ this.largeText=!this.largeText; localStorage.setItem('largeText', this.largeText) },
        toggleHighContrast(){ this.highContrast=!this.highContrast; localStorage.setItem('highContrast', this.highContrast) },
        toggleDarkMode(){ this.darkMode=!this.darkMode; localStorage.setItem('darkMode', this.darkMode) },
        setLang(v){ this.lang=v; localStorage.setItem('lang', v) },
        toast(msg){ this.toastMsg=msg; this.toastOpen=true; setTimeout(()=>this.toastOpen=false, 1800) },
        scrollToTop() {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        }
      }
    }
  </script>
</body>

</html>
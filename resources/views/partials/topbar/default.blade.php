<nav x-data="{ mobileOpen: false }" class="w-full bg-transparent shadow-md lg:-ml-7 lg:-mr-16 mb-2"
  style="position: sticky; top: 0; z-index: 100;">
  <div class="w-full px-0">
    <div class="flex justify-between items-center h-16 w-full">
      <!-- Logo and site name -->
      <div class="flex items-center justify-start">
        <span class=" inline-flex h-10 w-10 items-center justify-center bg-primary/10 text-primary font-bold mr-3 ml-0
        lg:ml-0 xl:ml-0">
          <a href="#top" class="flex items-center gap-1">
            <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-14 lg:h-16 w-auto object-contain">
          </a>
        </span>
        <a href="#top" class="flex items-center gap-1">
          <div class="flex flex-col">
            <div class="lg:text-xl font-semibold">{{ $facility['name'] }}</div>
            @if(!empty($facility['tagline']))
            <div class="md:block text-xs text-slate-500 dark:text-slate-400">{{ $facility['tagline'] }}</div>
            @endif
          </div>
        </a>
      </div>
      <!-- Desktop Navigation -->
      <div class="hidden md:flex gap-2 items-center pl-4 py-2 ml-auto mr-0 lg:mr-0 xl:mr-0">
        <!-- About & Services Dropdown -->
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="hover:cursor-pointer hover:text-primary transition flex items-center gap-1 text-center">
            About & Services
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition style="display: none;"
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            <a href="#about" class="block px-4 py-2 hover:bg-teal-100">About</a>
            <a href="#services" class="block px-4 py-2 hover:bg-teal-100">Services</a>
            <a href="#testimonials" class="block px-4 py-2 hover:bg-teal-100">Testimonials</a>
            <a href="#careers" class="block px-4 py-2 hover:bg-teal-100">Careers</a>
          </div>
        </div>
        <!-- Rooms & Gallery Dropdown -->
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="hover:cursor-pointer hover:text-primary transition flex items-center gap-1 text-center">
            Rooms & Gallery
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition style="display: none;"
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            <a href="#rooms" class="block px-4 py-2 hover:bg-teal-100">Rooms & Rates</a>
            <a href="#gallery" class="block px-4 py-2 hover:bg-teal-100">Gallery</a>
            <a href="#news" class="block px-4 py-2 hover:bg-teal-100">News</a>
          </div>
        </div>
        <!-- Contact & More Dropdown -->
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="hover:cursor-pointer hover:text-primary transition flex items-center gap-1 text-center">
            Contact & More
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition style="display: none;"
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            <a href="#contact" class="block px-4 py-2 hover:bg-teal-100">Contact</a>
            <a href="#faqs" class="block px-4 py-2 hover:bg-teal-100">FAQs</a>
            <a href="#resources" class="block px-4 py-2 hover:bg-teal-100">Resources</a>
          </div>
        </div>
        <!-- Book a Tour Button -->
        <a href="#book"
          class="bg-teal-600 ml-4 px-3 mr-0 py-2 rounded text-white font-semibold hover:bg-teal-500 transition text-center">Book
          a
          Tour</a>
      </div>
      <!-- Book a Tour Icon (Mobile Only) & Hamburger Icon -->
      <div class="flex items-center gap-2 md:hidden">
        <a href="#book"
          class="flex items-center justify-center p-2 rounded text-teal-600 hover:bg-teal-100 focus:outline-none focus:ring-2 focus:ring-primary"
          title="Book a Tour">
          <svg class="h-7 w-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5V6.5A2.5 2.5 0 0 1 6.5 4H20v13m-16 2.5V6.5m0 13V19.5z" />
          </svg>
        </a>
        <button @click="mobileOpen = true"
          class="hover:cursor-pointer hover:bg-teal-100 p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-center">
          <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>
  <!-- Mobile Menu -->
  <div x-cloak x-show="mobileOpen" x-transition style="display: none;"
    class="fixed top-0 left-0 w-full h-screen z-[9999] bg-teal-100/50 flex flex-col md:hidden overflow-y-auto">
    <div class="w-full min-h-screen px-6 py-6 flex flex-col gap-2 bg-white/95 backdrop-blur mt-16">
      <div class="flex justify-between items-center mb-6">
        <span class="font-bold text-xl text-primary ml-2">Menu</span>
        <button @click="mobileOpen = false" class="p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary">
          <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <nav class="flex flex-col gap-2 mt-2">
        <a href="#about" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">About</a>
        <a href="#services" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Services</a>
        <a href="#rooms" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Rooms
          & Rates</a>
        <a href="#gallery" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Gallery</a>
        <a href="#news" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">News</a>
        <a href="#testimonials" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Testimonials</a>
        <a href="#careers" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Careers</a>
        <a href="#contact" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Contact</a>
        <a href="#faqs" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">FAQs</a>
        <a href="#resources" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">Resources</a>
        <a href="#book" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg bg-primary text-white font-semibold hover:bg-primary/90 transition">Book a
          Tour</a>
      </nav>
    </div>
  </div>
</nav>
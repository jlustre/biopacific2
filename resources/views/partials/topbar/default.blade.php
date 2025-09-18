<nav x-data="{ mobileOpen: false }" class="w-full bg-white shadow-md">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center h-16">
      <!-- Logo and site name -->
      <div class="flex items-center justify-start">
        <span class="inline-flex h-10 w-10 items-center justify-center bg-primary/10 text-primary font-bold mr-3">
          <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-14 lg:h-16 w-auto object-contain">
        </span>
        <a href="#top" class="flex items-center gap-1">
          <div class="flex flex-col">
            <div class="lg:text-xl font-semibold">{{ $facility['name'] }}</div>
            @if(!empty($facility['tagline']))
            <div class="hidden md:block text-xs text-slate-500 dark:text-slate-400">{{ $facility['tagline'] }}</div>
            @endif
          </div>
        </a>
      </div>
      <!-- Desktop Navigation -->
      <div class="hidden md:flex gap-2 items-center px-4 py-2">
        <!-- About & Services Dropdown -->
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" class="hover:text-primary transition flex items-center gap-1">
            About & Services
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            <a href="#about" class="block px-4 py-2 hover:bg-primary/10">About</a>
            <a href="#services" class="block px-4 py-2 hover:bg-primary/10">Services</a>
            <a href="#testimonials" class="block px-4 py-2 hover:bg-primary/10">Testimonials</a>
            <a href="#careers" class="block px-4 py-2 hover:bg-primary/10">Careers</a>
          </div>
        </div>
        <!-- Rooms & Gallery Dropdown -->
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" class="hover:text-primary transition flex items-center gap-1">
            Rooms & Gallery
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            <a href="#rooms" class="block px-4 py-2 hover:bg-primary/10">Rooms & Rates</a>
            <a href="#gallery" class="block px-4 py-2 hover:bg-primary/10">Gallery</a>
            <a href="#news" class="block px-4 py-2 hover:bg-primary/10">News</a>
          </div>
        </div>
        <!-- Contact & More Dropdown -->
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" class="hover:text-primary transition flex items-center gap-1">
            Contact & More
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            <a href="#contact" class="block px-4 py-2 hover:bg-primary/10">Contact</a>
            <a href="#faqs" class="block px-4 py-2 hover:bg-primary/10">FAQs</a>
            <a href="#resources" class="block px-4 py-2 hover:bg-primary/10">Resources</a>
          </div>
        </div>
        <!-- Book a Tour Button -->
        <a href="#book"
          class="ml-4 px-4 py-2 rounded bg-primary text-white font-semibold hover:bg-primary/90 transition">Book a
          Tour</a>
      </div>
      <!-- Hamburger Icon -->
      <button @click="mobileOpen = true"
        class="md:hidden p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-center">
        <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>
  </div>
  <!-- Mobile Menu -->
  <div x-show="mobileOpen" x-transition
    class="fixed top-18 left-0 w-full h-full z-[9999] bg-teal-100/50 flex flex-col md:hidden overflow-y-auto">
    <div class="w-full h-full px-6 pb-2 flex flex-col gap-2">
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
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">About</a>
        <a href="#services" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Services</a>
        <a href="#rooms" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Rooms
          & Rates</a>
        <a href="#gallery" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Gallery</a>
        <a href="#news" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">News</a>
        <a href="#testimonials" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Testimonials</a>
        <a href="#careers" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Careers</a>
        <a href="#contact" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Contact</a>
        <a href="#faqs" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">FAQs</a>
        <a href="#resources" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary hover:text-white transition">Resources</a>
        <a href="#book" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg bg-primary text-white font-semibold hover:bg-primary/90 transition">Book a
          Tour</a>
      </nav>
    </div>
  </div>
</nav>
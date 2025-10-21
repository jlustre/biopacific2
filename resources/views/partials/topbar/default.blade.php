<nav x-data="{ mobileOpen: false }" class="w-full bg-transparent shadow-md mb-2"
  style="position: sticky; top: 0; z-index: 1000;">
  <div class="w-full px-2 sm:px-4">
    <div class="flex justify-between items-center h-16 w-full min-w-0">
      <!-- Logo and site name -->
      <div class="flex items-center justify-start min-w-0">
        <span
          class="inline-flex items-center justify-center bg-primary/10 text-primary font-bold mr-2 ml-0 flex-shrink-0">
          <a href="#top" class="flex items-center gap-1">
            <img src="{{ asset('images/bplogo.png') }}" alt="Logo"
              class="h-8 w-8 sm:h-10 sm:w-10 md:h-12 md:w-12 lg:h-16 lg:w-16 object-contain">
          </a>
        </span>
        <a href="#top" class="flex items-center gap-1 min-w-0 max-w-[70vw]">
          <div class="flex flex-col min-w-0 max-w-full">
            <div
              class="font-semibold text-sm sm:text-md md:text-lg lg:text-xl leading-tight truncate max-w-[280px] md:max-w-[150px] lg:max-w-md">
              {{ $facility['name'] }}</div>
            @if(!empty($facility['tagline']))
            <div
              class="block text-xs lg:text-sm leading-tight text-slate-500 dark:text-slate-400 truncate max-w-[60vw] xs:max-w-[200px] sm:max-w-xs">
              {{ $facility['tagline'] }}</div>
            @endif
          </div>
        </a>
      </div>
      <!-- Desktop Navigation -->
      <div class="hidden md:flex gap-2 items-center pl-4 py-2 ml-auto mr-0 lg:mr-0 xl:mr-0">
        <!-- About & Services Dropdown -->
        @php
        $activeSections = $activeSections ?? [];
        if (is_string($activeSections)) {
        $activeSections = json_decode($activeSections, true) ?: [];
        } elseif ($activeSections instanceof \Illuminate\Support\Collection) {
        $activeSections = $activeSections->toArray();
        } elseif (!is_array($activeSections)) {
        $activeSections = (array) $activeSections;
        }
        $aboutMenuItems = collect(['about', 'services', 'testimonials', 'careers'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        @if($aboutMenuItems->count())
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="hover:cursor-pointer hover:text-primary transition flex items-center gap-1 text-center">
            About & Services
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            @foreach($aboutMenuItems as $section)
            <a href="#{{ $section }}" class="block px-4 py-2 hover:bg-teal-100">{{ ucfirst($section) }}</a>
            @endforeach
          </div>
        </div>
        @endif
        <!-- Rooms & Gallery Dropdown -->
        @php
        $roomsMenuItems = collect(['rooms', 'gallery', 'news'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        @if($roomsMenuItems->count())
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="hover:cursor-pointer hover:text-primary transition flex items-center gap-1 text-center">
            Rooms & Gallery
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            @foreach($roomsMenuItems as $section)
            <a href="#{{ $section }}" class="block px-4 py-2 hover:bg-teal-100">{{ $section == 'rooms' ? 'Rooms & Rates'
              : ucfirst($section) }}</a>
            @endforeach
          </div>
        </div>
        @endif
        <!-- Contact & More Dropdown -->
        @php
        $contactMenuItems = collect(['contact', 'faqs', 'resources'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        @if($contactMenuItems->count())
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="hover:cursor-pointer hover:text-primary transition flex items-center gap-1 text-center">
            Contact & More
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            @foreach($contactMenuItems as $section)
            <a href="#{{ $section }}" class="block px-4 py-2 hover:bg-teal-100">{{ ucfirst($section) }}</a>
            @endforeach
          </div>
        </div>
        @endif
        <!-- Book a Tour Button -->
        @if(!empty($activeSections) && in_array('book', $activeSections))
        <a href="#book"
          class="bg-teal-600 ml-4 px-3 mr-0 py-2 rounded text-white font-semibold hover:bg-teal-500 transition text-center">Book
          a
          Tour</a>
        @endif
      </div>
      <!-- Book a Tour Icon (Mobile Only) & Hamburger Icon -->
      <div class="flex items-center gap-2 md:hidden flex-shrink-0">
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
  <div x-cloak x-show="mobileOpen" x-transition
    class="fixed top-0 left-0 w-full h-screen z-[9999] bg-black/30 md:hidden overflow-y-auto">
    <div
      class="w-full min-h-screen px-4 py-6 flex flex-col gap-2 bg-white rounded-xl shadow-2xl mt-16 mx-auto max-w-sm">
      <div class="flex justify-between items-center mb-6">
        <span class="font-bold text-xl text-primary ml-2">Menu</span>
        <button @click="mobileOpen = false" class="p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary">
          <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      @php
      $activeSections = $activeSections ?? [];
      if (is_string($activeSections)) {
      $activeSections = json_decode($activeSections, true) ?: [];
      } elseif ($activeSections instanceof \Illuminate\Support\Collection) {
      $activeSections = $activeSections->toArray();
      } elseif (!is_array($activeSections)) {
      $activeSections = (array) $activeSections;
      }
      $aboutMenuItems = collect(['about', 'services', 'testimonials', 'careers'])
      ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
      $roomsMenuItems = collect(['rooms', 'gallery', 'news'])
      ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
      $contactMenuItems = collect(['contact', 'faqs', 'resources'])
      ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
      @endphp
      <nav class="flex flex-col gap-2 mt-2">
        @if(!empty($activeSections) && in_array('book', $activeSections))
        <a href="#book" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-white font-semibold transition"
          style="background: {{$primary}}; filter: brightness(1);" onmouseover="this.style.filter='brightness(0.85)'"
          onmouseout="this.style.filter='brightness(1)'">Book a Tour</a>
        @endif
        @foreach($aboutMenuItems as $section)
        <a href="#{{ $section }}" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">{{
          ucfirst($section) }}</a>
        @endforeach
        @if($roomsMenuItems->count())
        @foreach($roomsMenuItems as $section)
        <a href="#{{ $section }}" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">{{
          $section == 'rooms' ? 'Rooms & Rates' : ucfirst($section) }}</a>
        @endforeach
        @endif
        @foreach($contactMenuItems as $section)
        <a href="#{{ $section }}" @click="mobileOpen = false"
          class="py-3 px-4 rounded text-lg text-primary bg-slate-100 hover:bg-primary/80 hover:text-white transition">{{
          ucfirst($section) }}</a>
        @endforeach
      </nav>
    </div>
  </div>
</nav>
@php
// Check if we're on a legal page (privacy, terms, accessibility, etc.)
$currentPath = request()->path();
$isLegalPage = str_contains($currentPath, '/privacy-policy') ||
str_contains($currentPath, '/terms-of-service') ||
str_contains($currentPath, '/accessibility') ||
str_contains($currentPath, '/notice-of-privacy-practices') ||
str_contains($currentPath, '/webmaster/contact');

// Get facility slug for legal page redirects
$facilitySlug = '';
if (isset($facility) && is_array($facility) && isset($facility['slug'])) {
$facilitySlug = $facility['slug'];
} elseif (isset($facility) && is_object($facility) && isset($facility->slug)) {
$facilitySlug = $facility->slug;
} else {
$facilitySlug = 'facility'; // fallback
}

$linkPrefix = $isLegalPage ? "/{$facilitySlug}" : '';
@endphp
<div style="width: 100%;">
  <div class="mx-auto max-w-7xl pr-4 sm:pr-6 lg:pr-8 flex items-center justify-between w-full">
    <div class="flex items-center gap-3">
      @include('partials.logo-site-name')
    </div>
    <div class="flex-shrink-0">
      <!-- Desktop Navigation -->
      <div class="hidden md:flex gap-2 items-center pl-4 py-2 ml-auto mr-0 lg:mr-0 xl:mr-0 flex-1 min-w-0 justify-end">
        <!-- About Dropdown -->
        @php
        $activeSections = $activeSections ?? [];
        if (is_string($activeSections)) {
        $activeSections = json_decode($activeSections, true) ?: [];
        } elseif ($activeSections instanceof \Illuminate\Support\Collection) {
        $activeSections = $activeSections->toArray();
        } elseif (!is_array($activeSections)) {
        $activeSections = (array) $activeSections;
        }
        $aboutMenuItems = collect(['about', 'faqs', 'testimonials', 'contact'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        @if($aboutMenuItems->count())
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="cursor-pointer px-3 py-2 rounded-md transition-all duration-200 flex items-center gap-1 text-center"
            style="color: {{ $neutral_dark }}; background-color: transparent;"
            @mouseenter="$el.style.backgroundColor = '{{ $primary }}20'; $el.style.color = '{{ $primary }}';"
            @mouseleave="$el.style.backgroundColor = 'transparent'; $el.style.color = '#374151';">
            About
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            @foreach($aboutMenuItems as $section)
            <a href="{{ $linkPrefix }}#{{ $section }}"
              class="cursor-pointer block px-4 py-2 transition-all duration-200"
              style="color: #374151; background-color: white;"
              @mouseenter="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';"
              @mouseleave="$el.style.backgroundColor = 'white'; $el.style.color = '#374151';">{{
              $section == 'about' ? 'About Us' :
              ($section == 'contact' ? 'Contact Us' : ucfirst($section))
              }}</a>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Community Dropdown -->
        @php
        $communityMenuItems = collect(['news', 'gallery', 'blog'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        @if($communityMenuItems->count())
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="cursor-pointer px-3 py-2 rounded-md transition-all duration-200 flex items-center gap-1 text-center"
            style="color: #374151; background-color: transparent;"
            @mouseenter="$el.style.backgroundColor = '{{ $primary }}20'; $el.style.color = '{{ $primary }}';"
            @mouseleave="$el.style.backgroundColor = 'transparent'; $el.style.color = '#374151';">
            Experience
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 shadow-lg rounded z-10">
            @foreach($communityMenuItems as $section)
            <a href="{{ $linkPrefix }}#{{ $section }}"
              class="cursor-pointer block px-4 py-2 transition-all duration-200"
              style="color: #374151; background-color: white;"
              @mouseenter="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';"
              @mouseleave="$el.style.backgroundColor = 'white'; $el.style.color = '#374151';">{{
              $section == 'news' ? 'News & Events' :
              ($section == 'gallery' ? 'Galleries' :
              ($section == 'blog' ? 'Blogs' : ucfirst($section)))
              }}</a>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Careers & Resources Dropdown -->
        @php
        $careersResourcesMenuItems = collect(['careers', 'resources'])
        ->filter(fn($section) => !empty($activeSections) && in_array($section, $activeSections));
        @endphp
        @if($careersResourcesMenuItems->count())
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open"
            class="cursor-pointer px-3 py-2 rounded-md transition-all duration-200 flex items-center gap-1 text-center"
            style="color: #374151; background-color: transparent;"
            @mouseenter="$el.style.backgroundColor = '{{ $primary }}20'; $el.style.color = '{{ $primary }}';"
            @mouseleave="$el.style.backgroundColor = 'transparent'; $el.style.color = '#374151';">
            Careers
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-cloak x-show="open" @click.away="open = false" x-transition
            class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded z-10">
            @foreach($careersResourcesMenuItems as $section)
            <a href="{{ $linkPrefix }}#{{ $section }}"
              class="cursor-pointer block px-4 py-2 transition-all duration-200"
              style="color: #374151; background-color: white;"
              @mouseenter="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';"
              @mouseleave="$el.style.backgroundColor = 'white'; $el.style.color = '#374151';">{{
              ucfirst($section)
              }}</a>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Services Button (Standalone) -->
        <a href="#services"
          class="max-w-[220px] min-w-[140px] mx-auto cursor-pointer px-4 py-1 border-2 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center justify-center"
          style="border-color: {{ $secondary }}; color: {{ $secondary }}; background-color: transparent; focus:ring-color: {{ $secondary }};"
          @mouseenter="$el.style.backgroundColor = '{{ $secondary }}'; $el.style.color = 'white';"
          @mouseleave="$el.style.backgroundColor = 'transparent'; $el.style.color = '{{ $secondary }}';">
          <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          Care & Services
        </a>

        <!-- Book a Tour Button -->
        @if(!empty($activeSections) && in_array('book', $activeSections))
        <div class="hidden md:block">
          <x-primary-button href="{{ $linkPrefix }}#book" size="sm" :primary="$primary" class="ml-2">
            Book a Tour
          </x-primary-button>
        </div>
        @endif
      </div>
      <!-- Book a Tour Icon (Mobile Only) & Hamburger Icon -->
      <div class="flex items-center gap-2 md:hidden flex-shrink-0">
        <button @click="mobileOpen = true"
          class="hover:cursor-pointer mr-4 hover:bg-teal-100 p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-center">
          <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  @include('partials.mobile-menu')
</div>
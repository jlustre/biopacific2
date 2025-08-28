{{-- Nav          @if(isset($facility['logo_url']) && $facility['logo_url'])
            <img src="{{ $facility['logo_url'] }}"
                 alt="{{ $facility['name'] ?? 'Facility' }} Logo"
                 class="h-8 w-auto">
          @else
            <div class="h-8 w-8 rounded-lg flex items-center justify-center"
                 style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">
              <span class="text-white font-bold text-sm">
                {{ substr($facility['name'] ?? 'B', 0, 1) }}
              </span>
            </div>
          @endif
          <span class="ml-2 text-xl font-bold text-gray-900">
            {{ $facility['name'] ?? 'Bio-Pacific' }}
          </span>nt --}}
<nav class="bg-white shadow-lg sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">

      {{-- Logo/Brand --}}
      <div class="flex-shrink-0 flex items-center">
        <a href="{{ url('/') }}" class="flex items-center">
          @if($facility['logo_url'])
            <img src="{{ $facility['logo_url'] }}"
                 alt="{{ $facility['name'] }} Logo"
                 class="h-8 w-auto">
          @else
            <div class="h-8 w-8 rounded-lg flex items-center justify-center"
                 style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">
              <span class="text-white font-bold text-sm">
                {{ substr($facility['name'], 0, 1) }}
              </span>
            </div>
          @endif
          <span class="ml-3 text-xl font-semibold text-gray-900 hidden sm:block">
            {{ $facility['name'] }}
          </span>
        </a>
      </div>

      {{-- Desktop Navigation --}}
      <div class="hidden md:block">
        <div class="ml-10 flex items-baseline space-x-8">
          <a href="#hero"
             class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors"
             style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
            Home
          </a>
          <a href="#about"
             class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors"
             style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
            About Us
          </a>
          <a href="#services"
             class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors"
             style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
            Services
          </a>
          <a href="#contact"
             class="text-gray-700 hover:text-primary px-3 py-2 text-sm font-medium transition-colors"
             style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
            Contact
          </a>
          <a href="#contact"
             class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition-colors"
             style="background-color: {{ $facility['primary_color'] ?? '#047857' }}; hover:opacity: 0.9;">
            Schedule Tour
          </a>
        </div>
      </div>

      {{-- Mobile menu button --}}
      <div class="md:hidden">
        <button type="button"
                class="bg-gray-200 inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary"
                style="focus:ring-color: {{ $facility['primary_color'] ?? '#047857' }};"
                onclick="toggleMobileMenu()">
          <span class="sr-only">Open main menu</span>
          <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

    </div>
  </div>

  {{-- Mobile Navigation Menu --}}
  <div class="md:hidden hidden" id="mobile-menu">
    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-50 border-t">
      <a href="#hero"
         class="text-gray-700 hover:text-primary block px-3 py-2 text-base font-medium"
         style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
        Home
      </a>
      <a href="#about"
         class="text-gray-700 hover:text-primary block px-3 py-2 text-base font-medium"
         style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
        About Us
      </a>
      <a href="#services"
         class="text-gray-700 hover:text-primary block px-3 py-2 text-base font-medium"
         style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
        Services
      </a>
      <a href="#contact"
         class="text-gray-700 hover:text-primary block px-3 py-2 text-base font-medium"
         style="hover:color: {{ $facility['primary_color'] ?? '#047857' }};">
        Contact
      </a>
      <a href="#contact"
         class="block px-3 py-2 text-base font-medium text-white rounded-md mt-2"
         style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">
        Schedule Tour
      </a>
    </div>
  </div>
</nav>

{{-- Mobile Menu Toggle Script --}}
<script>
function toggleMobileMenu() {
  const mobileMenu = document.getElementById('mobile-menu');
  mobileMenu.classList.toggle('hidden');
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });

      // Close mobile menu if open
      const mobileMenu = document.getElementById('mobile-menu');
      if (!mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('hidden');
      }
    }
  });
});
</script>

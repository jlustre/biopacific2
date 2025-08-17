{{-- Hero Section - Default Variant --}}
<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden"
         style="background: linear-gradient(135deg, {{ $facility['primary_color'] ?? '#047857' }}ee, {{ $facility['accent_color'] ?? '#06b6d4' }}aa);">

  {{-- Background Image --}}
  @if(isset($facility['hero_image_url']) && $facility['hero_image_url'])
    <div class="absolute inset-0 z-0">
      <img src="{{ $facility['hero_image_url'] }}" alt="{{ $facility['name'] ?? 'Facility' }}"
           class="w-full h-full object-cover opacity-30">
    </div>
  @endif

  {{-- Content --}}
  <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
    <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
      {{ $facility['headline'] ?? 'Welcome to Our Care Center' }}
    </h1>

    <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
      {{ $facility['subheadline'] ?? 'Providing exceptional care with compassion and dignity' }}
    </p>

    @if($config['show_cta'] ?? true)
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="#contact"
           class="inline-flex items-center px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
          Schedule a Tour
        </a>
        <a href="#about"
           class="inline-flex items-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-colors">
          Learn More
        </a>
      </div>
    @endif
  </div>

  {{-- Scroll indicator --}}
  <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
    </svg>
  </div>
</section>

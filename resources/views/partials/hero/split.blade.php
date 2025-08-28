{{-- Hero Section - Split Variant --}}
<section id="hero" class="min-h-screen flex items-center py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-12 items-center">

      {{-- Text Content --}}
      <div class="@if(($config['text_alignment'] ?? 'left') === 'right') lg:order-2 @endif">
        <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight"
            style="color: {{ $facility['primary_color'] ?? '#047857' }};">
          {{ $facility['headline'] ?? 'Welcome to Our Care Center' }}
        </h1>

        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
          {{ $facility['subheadline'] ?? 'Providing exceptional care with compassion and dignity' }}
        </p>

        @if($config['show_cta'] ?? true)
          <div class="flex flex-col sm:flex-row gap-4">
            <a href="#contact"
               class="inline-flex items-center px-8 py-4 font-semibold rounded-lg text-white transition-colors"
               style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">
              Schedule a Tour
              <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
              </svg>
            </a>
            <a href="#about"
               class="inline-flex items-center px-8 py-4 border-2 font-semibold rounded-lg transition-colors"
               style="border-color: {{ $facility['primary_color'] ?? '#047857' }}; color: {{ $facility['primary_color'] ?? '#047857' }};">
              Learn More
            </a>
          </div>
        @endif
      </div>

      {{-- Image Content --}}
      <div class="@if(($config['text_alignment'] ?? 'left') === 'right') lg:order-1 @endif">
        @if(isset($facility['hero_image_url']) && $facility['hero_image_url'])
          <div class="relative">
            <img src="{{ $facility['hero_image_url'] }}"
                 alt="{{ $facility['name'] ?? 'Facility' }}"
                 class="w-full h-96 lg:h-[500px] object-cover rounded-2xl shadow-2xl">

            {{-- Decorative elements --}}
            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full opacity-20"
                 style="background-color: {{ $facility['accent_color'] ?? '#06b6d4' }};"></div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 rounded-full opacity-30"
                 style="background-color: {{ $facility['primary_color'] ?? '#047857' }};"></div>
          </div>
        @else
          <div class="w-full h-96 lg:h-[500px] bg-gray-200 rounded-2xl flex items-center justify-center">
            <span class="text-gray-400 text-lg">Hero Image Placeholder</span>
          </div>
        @endif
      </div>

    </div>
  </div>
</section>

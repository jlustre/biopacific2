{{-- About Section - Default Variant --}}
<section id="about" class="py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-12 items-center">

      {{-- Image --}}
      <div class="@if(($config['layout'] ?? 'image-left') === 'image-right') lg:order-2 @endif">
        @if(isset($facility['about_image_url']) && $facility['about_image_url'])
          <img src="{{ $facility['about_image_url'] }}"
               alt="About {{ $facility['name'] ?? 'Our Facility' }}"
               class="w-full h-96 object-cover rounded-2xl shadow-lg">
        @else
          <div class="w-full h-96 bg-gray-200 rounded-2xl flex items-center justify-center">
            <span class="text-gray-400">About Image Placeholder</span>
          </div>
        @endif
      </div>

      {{-- Content --}}
      <div class="@if(($config['layout'] ?? 'image-left') === 'image-right') lg:order-1 @endif">
        <h2 class="text-3xl md:text-4xl font-bold mb-6"
            style="color: {{ $facility['primary_color'] ?? '#047857' }};">
          About {{ $facility['name'] }}
        </h2>

        <div class="prose prose-lg text-gray-600 max-w-none">
          {!! nl2br(e($facility['about_text'] ?? 'We are dedicated to providing exceptional care and services to our residents and their families.')) !!}
        </div>

        {{-- Stats --}}
        @if($config['show_stats'] ?? false)
          <div class="grid grid-cols-2 gap-6 mt-8 pt-8 border-t border-gray-200">
            @if(isset($facility['beds']) && $facility['beds'])
              <div class="text-center">
                <div class="text-3xl font-bold" style="color: {{ $facility['primary_color'] ?? '#047857' }};">
                  {{ $facility['beds'] }}
                </div>
                <div class="text-sm text-gray-600 mt-1">Licensed Beds</div>
              </div>
            @endif

            <div class="text-center">
              <div class="text-3xl font-bold" style="color: {{ $facility['accent_color'] ?? '#06b6d4' }};">
                25+
              </div>
              <div class="text-sm text-gray-600 mt-1">Years of Service</div>
            </div>
          </div>
        @endif

        <div class="mt-8">
          <a href="#contact"
             class="inline-flex items-center px-6 py-3 font-semibold rounded-lg text-white transition-colors"
             style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">
            Learn More About Us
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

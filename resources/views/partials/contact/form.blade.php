{{-- Contact Section - Form Variant --}}
<section id="contact" class="py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="text-center mb-16">
      @php
      if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
      $scheme = \DB::table('color_schemes')->where('id', $facility['color_scheme_id'])->first();
      $primary = $scheme ? ($scheme->primary_color ?? '#047857') : '#047857';
      $secondary = $scheme ? ($scheme->secondary_color ?? '#1f2937') : '#1f2937';
      $accent = $scheme ? ($scheme->accent_color ?? '#F59E0B') : '#F59E0B';
      } else {
      $primary = '#047857';
      $secondary = '#1f2937';
      $accent = '#F59E0B';
      }
      @endphp
      <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: {{ $primary }};">
        Get in Touch
      </h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">
        We're here to answer your questions and help you learn more about our services.
      </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-12">

      {{-- Contact Information --}}
      <div>
        <h3 class="text-2xl font-semibold mb-6" style="color: {{ $secondary }};">
          Contact Information
        </h3>

        <div class="space-y-6">
          {{-- Address --}}
          @if(isset($facility['address']) && $facility['address'])
          <div class="flex items-start">
            <div class="w-6 h-6 mt-1 mr-4 flex-shrink-0">
              <svg fill="currentColor" style="color: {{ $primary }};" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div>
              <div class="font-semibold text-gray-900">Address</div>
              <div class="text-gray-600">
                {{ $facility['address'] }}
                @if(isset($facility['city']) && $facility['city'] && isset($facility['state']) && $facility['state'])
                <br>{{ $facility['city'] }}, {{ $facility['state'] }}
                @endif
              </div>
            </div>
          </div>
          @endif

          {{-- Phone --}}
          @if(isset($facility['phone']) && $facility['phone'])
          <div class="flex items-start">
            <div class="w-6 h-6 mt-1 mr-4 flex-shrink-0">
              <svg fill="currentColor" style="color: {{ $facility['primary_color'] ?? '#047857' }};"
                viewBox="0 0 20 20">
                <path
                  d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
            </div>
            <div>
              <div class="font-semibold text-gray-900">Phone</div>
              <a href="tel:{{ $facility['phone'] }}" class="text-gray-600 hover:underline">
                {{ $facility['phone'] }}
              </a>
            </div>
          </div>
          @endif

          {{-- Email --}}
          @if(isset($facility['email']) && $facility['email'])
          <div class="flex items-start">
            <div class="w-6 h-6 mt-1 mr-4 flex-shrink-0">
              <svg fill="currentColor" style="color: {{ $facility['primary_color'] ?? '#047857' }};"
                viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
            </div>
            <div>
              <div class="font-semibold text-gray-900">Email</div>
              <a href="mailto:{{ $facility['email'] }}" class="text-gray-600 hover:underline">
                {{ $facility['email'] }}
              </a>
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Contact Form --}}
      @if($config['show_form'] ?? true)
      <div>
        <h3 class="text-2xl font-semibold mb-6" style="color: {{ $secondary }};">
          Send us a Message
        </h3>

        <form class="space-y-6">
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                First Name
              </label>
              <input type="text" id="first_name" name="first_name"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                style="focus:ring-color: {{ $primary }};">
            </div>

            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                Last Name
              </label>
              <input type="text" id="last_name" name="last_name"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                style="focus:ring-color: {{ $facility['primary_color'] ?? '#047857' }};">
            </div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email
            </label>
            <input type="email" id="email" name="email"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
              style="focus:ring-color: {{ $facility['primary_color'] ?? '#047857' }};">
          </div>

          <div>
            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
              Message
            </label>
            <textarea id="message" name="message" rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
              style="focus:ring-color: {{ $facility['primary_color'] ?? '#047857' }};"
              placeholder="How can we help you?"></textarea>
          </div>

          <button type="submit" class="w-full px-6 py-3 font-semibold rounded-lg text-white transition-colors"
            style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">
            Send Message
          </button>
        </form>
      </div>
      @endif

    </div>
  </div>
</section>
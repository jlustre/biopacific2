{{-- Services Section - Grid Variant --}}
<section id="services" class="py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="text-center mb-16">
      <h2 class="text-3xl md:text-4xl font-bold mb-4"
          style="color: {{ $facility['primary_color'] ?? '#047857' }};">
        Our Services & Amenities
      </h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">
        We provide comprehensive care and services designed to enhance the quality of life for our residents.
      </p>
    </div>

    {{-- Services Grid --}}
    @php
      // Handle both array and object access for services
      $services = $facility['services'] ?? $facility->services ?? collect();
      $columns = $config['columns'] ?? 3;
      $gridClass = match($columns) {
        2 => 'grid-cols-1 md:grid-cols-2',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
      };
    @endphp

    <div class="grid {{ $gridClass }} gap-8">
      @forelse($services as $service)
        <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">

          {{-- Icon/Image --}}
          @if($config['show_icons'] ?? true)
            @if($service->image_url)
              <img src="{{ $service->image_url }}"
                   alt="{{ $service->name }}"
                   class="w-16 h-16 object-cover rounded-lg mb-4">
            @elseif($service->icon)
              <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-4"
                   style="background-color: {{ $facility['primary_color'] ?? '#047857' }}10;">
                <i class="{{ $service->icon }} text-2xl"
                   style="color: {{ $facility['primary_color'] ?? '#047857' }};"></i>
              </div>
            @else
              <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-4"
                   style="background-color: {{ $facility['primary_color'] ?? '#047857' }}10;">
                <svg class="w-8 h-8" style="color: {{ $facility['primary_color'] ?? '#047857' }};"
                     fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            @endif
          @endif

          {{-- Content --}}
          <h3 class="text-xl font-semibold mb-3"
              style="color: {{ $facility['secondary_color'] ?? '#1f2937' }};">
            {{ $service->name }}
          </h3>

          @if($service->description)
            <p class="text-gray-600 leading-relaxed">
              {{ $service->description }}
            </p>
          @endif
        </div>
      @empty
        {{-- Placeholder services for demo --}}
        @foreach(['Skilled Nursing Care', 'Physical Therapy', 'Recreational Activities', 'Nutritious Dining', 'Medical Services', 'Personal Care'] as $serviceName)
          <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-4"
                 style="background-color: {{ $facility['primary_color'] ?? '#047857' }}10;">
              <svg class="w-8 h-8" style="color: {{ $facility['primary_color'] ?? '#047857' }};"
                   fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>

            <h3 class="text-xl font-semibold mb-3"
                style="color: {{ $facility['secondary_color'] ?? '#1f2937' }};">
              {{ $serviceName }}
            </h3>

            <p class="text-gray-600 leading-relaxed">
              Professional {{ strtolower($serviceName) }} provided by our experienced team of caregivers.
            </p>
          </div>
        @endforeach
      @endforelse
    </div>

  </div>
</section>

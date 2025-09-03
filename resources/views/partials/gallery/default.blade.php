<section id="gallery" class="py-16 sm:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Our Gallery',
    'section_sub_header' => "Explore our collection of beautiful moments and inspiring visuals"
    ])

    <!-- Gallery Grid -->
    @php
    $galleryImages = [
    asset('images/gallery/nursinghome_image1.png'),
    asset('images/gallery/nursinghome_image2.png'),
    asset('images/gallery/nursinghome_image3.png'),
    asset('images/gallery/nursinghome_image4.png'),
    asset('images/gallery/nursinghome_image5.png'),
    asset('images/gallery/nursinghome_image6.png'),
    asset('images/gallery/nursinghome_image7.png'),
    asset('images/gallery/nursinghome_image8.png'),
    asset('images/gallery/nursinghome_image9.png'),
    ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
      @foreach($galleryImages as $index => $img)
      <div
        class="gallery-item group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 {{ $index === 0 ? 'sm:col-span-2 sm:row-span-2' : '' }}">
        <!-- Image -->
        <div class="relative overflow-hidden {{ $index === 0 ? 'h-64 sm:h-full' : 'h-48 sm:h-56 lg:h-64' }}">
          <img src="{{ $img }}?q=80&w={{ $index === 0 ? '1200' : '600' }}&auto=format&fit=crop"
            alt="Gallery image {{ $index + 1 }}"
            class="w-full h-auto max-w-full object-cover block transition-transform duration-700 group-hover:scale-110"
            loading="lazy">

          <!-- Overlay -->
          <div
            class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
          </div>

          <!-- Hover Content -->
          <div
            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0">
            <div class="text-center text-white">
              <div class="bg-white/20 backdrop-blur-sm rounded-full p-4 mb-3 inline-block">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                </svg>
              </div>
              <p class="text-sm font-medium">View Full Size</p>
            </div>
          </div>
        </div>

        <!-- Click Link -->
        <a href="{{ $img }}?q=80&w=1600&auto=format&fit=crop" target="_blank" class="absolute inset-0 z-10"
          aria-label="View gallery image {{ $index + 1 }} in full size"></a>
      </div>
      @endforeach
    </div>

    <!-- View More Button -->
    <div class="text-center mt-12">
      <button
        class="inline-flex items-center px-8 py-3 bg-primary text-white font-semibold rounded-full hover:bg-primary-dark transition-colors duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
        <span>View More Photos</span>
        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
        </svg>
      </button>
    </div>
  </div>
</section>

<style>
  .gallery-item {
    animation: fadeInUp 0.6s ease-out forwards;
  }

  .gallery-item:nth-child(even) {
    animation-delay: 0.1s;
  }

  .gallery-item:nth-child(3n) {
    animation-delay: 0.2s;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 640px) {
    .gallery-item:first-child {
      grid-column: span 1;
      grid-row: span 1;
    }
  }
</style>
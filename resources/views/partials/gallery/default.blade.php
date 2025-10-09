<section id="gallery" class="py-16 sm:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Our Gallery',
    'section_sub_header' => "Explore our collection of beautiful moments and inspiring visuals"
    ])

    <!-- Gallery Grid -->
    @php
    if (!isset($galleryImages)) {
    $galleryImages = isset($facility) ? \App\Helpers\FacilityDataHelper::getGalleryImages($facility->id) : collect();
    }
    // Paginate images by 10 per page
    $paginatedImages = $galleryImages->sortBy('order')->values()->forPage(request('page', 1), 10);
    $totalPages = ceil($galleryImages->count() / 10);
    $currentPage = request('page', 1);
    @endphp
    @if($galleryImages->isEmpty())
    <div class="text-center text-gray-500 py-8">No gallery images found for this facility.</div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
      @foreach($paginatedImages as $index => $img)
      @php
      // Assign card size based on index for a dynamic layout
      $cardClass = '';
      if ($index % 7 === 0) {
      $cardClass = 'col-span-2 row-span-2 h-80';
      } elseif ($index % 5 === 0) {
      $cardClass = 'col-span-2 h-64';
      } elseif ($index % 3 === 0) {
      $cardClass = 'h-72';
      } else {
      $cardClass = 'h-48';
      }
      @endphp
      <div
        class="gallery-item group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 {{ $cardClass }}">
        <div class="relative overflow-hidden w-full h-full">
          <img src="{{ asset('storage/' . $img->image_url) }}" alt="{{ $img->title }}"
            class="w-full h-full object-cover block transition-transform duration-700 group-hover:scale-110"
            style="object-fit:cover;" loading="lazy">

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
        <a href="{{ asset('storage/' . $img->image_url) }}" target="_blank" class="absolute inset-0 z-10"
          aria-label="View gallery image {{ $index + 1 }} in full size"></a>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  <!-- Pagination Controls -->
  @if($totalPages > 1)
  <div class="flex justify-center mt-8 space-x-2">
    @for($page = 1; $page <= $totalPages; $page++) <a href="?page={{ $page }}"
      class="px-4 py-2 rounded-full font-semibold transition-colors duration-200 {{ $page == $currentPage ? 'bg-primary text-white shadow-lg' : 'bg-white text-primary hover:bg-primary hover:text-white' }}">
      {{ $page }}
      </a>
      @endfor
  </div>
  @endif
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
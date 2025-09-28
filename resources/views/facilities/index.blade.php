@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-4">
      <div>
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Our Facilities</h1>
        <p class="text-gray-600 text-lg">Discover our world-class healthcare facilities</p>
      </div>

      @can('create facilities')
      <a href="{{ route('admin.facilities.create') }}"
        style="background-color: var(--primary-color, #3B82F6); border-color: var(--primary-color, #3B82F6);"
        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white hover:opacity-90 transform hover:scale-105 transition duration-200 shadow-lg">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add New Facility
      </a>
      @endcan
    </div>

    <!-- Facilities Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      @foreach($facilities as $index => $facility)
      @php
      // Array of high-quality healthcare facility images from Unsplash
      $placeholderImages = [
      'https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=600&fit=crop&crop=center&auto=format&q=80', //
      Modern hospital exterior
      'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Hospital corridor
      'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Medical facility interior
      'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop&crop=center&auto=format&q=80', //
      Modern hospital room
      'https://images.unsplash.com/photo-1666214280557-f1b5022eb634?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Hospital building
      'https://images.unsplash.com/photo-1564015709027-d5c8f6bd7cb5?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Medical equipment room
      'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Hospital entrance
      'https://images.unsplash.com/photo-1586773860418-d37222d8eeb4?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Medical center lobby
      'https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=600&fit=crop&crop=faces&auto=format&q=80', //
      Healthcare facility
      'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Emergency department
      'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // Operating room
      'https://images.unsplash.com/photo-1631815589968-fdb09a223b1e?w=800&h=600&fit=crop&crop=center&auto=format&q=80',
      // ICU room
      ];

      // Get placeholder image (cycle through images if more facilities than images)
      $placeholderImage = $placeholderImages[$index % count($placeholderImages)];

      // Use facility image if available, otherwise use placeholder
      $imageUrl = $facility->hero_image_url ?? $placeholderImage;
      @endphp

      <div
        class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100">
        <!-- Image Container -->
        <div class="relative overflow-hidden">
          <a href="{{ route('facility.show', $facility->slug) }}" class="block">
            <img src="{{ $imageUrl }}" alt="{{ $facility->name }}"
              class="h-48 sm:h-52 w-full object-cover group-hover:scale-110 transition-transform duration-500"
              loading="lazy" onerror="this.src='{{ $placeholderImage }}'">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>
          </a>

          <!-- Beds Badge -->
          <div class="absolute top-3 right-3">
            <span style="background-color: var(--accent-color, #F59E0B); color: var(--secondary-color, #FFFFFF);"
              class="px-3 py-1 rounded-full text-sm font-semibold shadow-lg backdrop-blur-sm">
              {{ $facility->beds }} Beds
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <a href="{{ route('facility.show', $facility->slug) }}"
            class="block group-hover:text-opacity-80 transition-colors">
            <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
              {{ $facility->name }}
            </h3>
          </a>

          <!-- Address -->
          <div class="flex items-start mb-3">
            <svg class="w-4 h-4 text-gray-400 mt-1 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <p class="text-gray-600 text-sm line-clamp-2">{{ $facility->address }}</p>
          </div>

          <!-- Phone -->
          <div class="flex items-center mb-4">
            <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
              </path>
            </svg>
            <a href="tel:{{ $facility->phone }}" class="text-gray-600 text-sm hover:text-blue-600 transition-colors">
              {{ $facility->phone }}
            </a>
          </div>

          <!-- View Details Button -->
          <a href="{{ route('facility.show', $facility->slug) }}"
            style="background-color: var(--secondary-color, #6B7280); border-color: var(--secondary-color, #6B7280);"
            class="w-full inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white hover:opacity-90 transition duration-200 group">
            View Details
            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>
        </div>

        <!-- Admin Actions -->
        @can('edit facilities')
        <div style="background-color: var(--primary-color, #3B82F6);"
          class="bg-opacity-5 border-t border-gray-100 px-6 py-3">
          <div class="flex items-center justify-center">
            <a href="{{ route('admin.facilities.edit', $facility->id) }}" style="color: var(--primary-color, #3B82F6);"
              class="inline-flex items-center text-sm font-medium hover:opacity-75 transition-opacity">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                </path>
              </svg>
              Edit
            </a>
          </div>
        </div>
        @endcan
      </div>
      @endforeach
    </div>

    <!-- Empty State -->
    @if($facilities->isEmpty())
    <div class="text-center py-16">
      <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
        </path>
      </svg>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No facilities found</h3>
      <p class="text-gray-600 mb-6">Get started by adding your first facility.</p>
      @can('create facilities')
      <a href="{{ route('admin.facilities.create') }}" style="background-color: var(--primary-color, #3B82F6);"
        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white hover:opacity-90 transition">
        Add First Facility
      </a>
      @endcan
    </div>
    @endif
  </div>
</div>

@endsection
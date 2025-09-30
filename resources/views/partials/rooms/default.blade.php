@php
if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->find($facility['color_scheme_id']);
$primary = $scheme->primary_color ?? '#0EA5E9';
$secondary = $scheme->secondary_color ?? '#1E293B';
$accent = $scheme->accent_color ?? '#F59E0B';
} else {
$primary = '#0EA5E9';
$secondary = '#1E293B';
$accent = '#F59E0B';
}
@endphp

<section id="rooms" class="py-20 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
  @include('partials.section_header', [
  'section_header' => 'Rooms & Rates',
  'section_sub_header' => 'Choose from our thoughtfully designed living spaces, each crafted to provide comfort,
  dignity, and personalized care in a warm, home-like environment.'
  ])

  <!-- Room Cards Grid -->
  <div class="space-y-8 mb-16">
    <!-- Private Room - Featured -->
    <div
      class="group relative bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden border border-slate-100">
      <div class="absolute top-6 left-6 z-20">
        <span
          class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-sm font-bold rounded-full shadow-lg">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          Most Popular
        </span>
      </div>

      <div class="lg:flex">
        <!-- Image Section -->
        <div class="lg:w-2/5 relative">
          <div class="relative overflow-hidden h-72 lg:h-full lg:min-h-96">
            <img src="{{ asset('images/private_room.png') }}"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
              alt="Private Room">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-black/30">
            </div>
          </div>
        </div>

        <!-- Content Section -->
        <div class="lg:w-3/5 p-8 lg:p-12 flex flex-col justify-between">
          <div>
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-6">
              <div class="mb-4 lg:mb-0">
                <h3 class="text-3xl lg:text-4xl font-bold mb-2" style="color: {{ $secondary }};">Private Room</h3>
                <p class="font-semibold text-lg" style="color: {{ $primary }};">Premium Care Experience</p>
              </div>
              <div class="flex items-center text-amber-500 bg-amber-50 px-3 py-2 rounded-xl w-fit">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="font-medium">Premium</span>
              </div>
            </div>

            <p class="text-slate-600 text-lg leading-relaxed mb-8">
              Experience the ultimate in comfort and privacy with our premium private rooms, featuring spacious
              layouts, dedicated bathrooms, and personalized care services.
            </p>

            <!-- Enhanced Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Spacious Private Layout</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Private Bathroom</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Premium Entertainment</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">24/7 Personalized Care</span>
              </div>
            </div>
          </div>

          <button @click="openRates=true"
            class="w-full bg-gradient-to-r from-primary to-secondary text-white px-8 py-4 rounded-2xl font-bold text-lg hover:from-primary-dark hover:to-secondary-dark transition-all duration-300 transform hover:scale-[1.02] shadow-xl hover:shadow-2xl">
            Request Pricing Information
          </button>
        </div>
      </div>
    </div>

    <!-- Semi-Private Room - Full Width -->
    <div
      class="group relative bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden border border-slate-100">
      <div class="absolute top-6 left-6 z-20">
        <span
          class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-full shadow-lg">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zM14 2a2 2 0 012 2v7a1 1 0 11-2 0V4a2 2 0 00-2-2h-2a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2v-1a1 1 0 112 0v1a4 4 0 01-4 4h-2a4 4 0 01-4-4V4a4 4 0 014-4h2z"
              clip-rule="evenodd" />
          </svg>
          Great Value
        </span>
      </div>

      <div class="lg:flex">
        <!-- Image Section -->
        <div class="lg:w-2/5 relative">
          <div class="relative overflow-hidden h-72 lg:h-full lg:min-h-96">
            <img src="{{ asset('images/semi-private.png') }}"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
              alt="Semi-Private Room">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-black/30">
            </div>
          </div>
        </div>

        <!-- Content Section -->
        <div class="lg:w-3/5 p-8 lg:p-12 flex flex-col justify-between">
          <div>
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-6">
              <div class="mb-4 lg:mb-0">
                <h3 class="text-3xl lg:text-4xl font-bold text-secondary mb-2">Semi-Private Room</h3>
                <p class="text-emerald-600 font-semibold text-lg">Shared Care Experience</p>
              </div>
              <div class="flex items-center text-emerald-600 bg-emerald-50 px-3 py-2 rounded-xl w-fit">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Value</span>
              </div>
            </div>

            <p class="text-slate-600 text-lg leading-relaxed mb-8">
              Shared accommodations that foster companionship while maintaining personal space and privacy. Perfect
              for those who enjoy social interaction within a caring community environment.
            </p>

            <!-- Enhanced Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div
                  class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Shared Comfortable Layout</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div
                  class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Privacy Curtains & Personal Space</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div
                  class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Social Interaction Opportunities</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div
                  class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Professional Care & Support</span>
              </div>
            </div>
          </div>

          <button @click="openRates=true"
            class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-[1.02] shadow-xl hover:shadow-2xl">
            Request Pricing Information
          </button>
        </div>
      </div>
    </div>

    <!-- Regular Room - Full Width -->
    <div
      class="group relative bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden border border-slate-100">
      <div class="absolute top-6 left-6 z-20">
        <span
          class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold rounded-full shadow-lg">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
              clip-rule="evenodd" />
          </svg>
          Essential
        </span>
      </div>

      <div class="lg:flex">
        <!-- Image Section -->
        <div class="lg:w-2/5 relative">
          <div class="relative overflow-hidden h-72 lg:h-full lg:min-h-96">
            <img src="{{ asset('images/regular_room.png') }}"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
              alt="Regular Room">
            <div
              class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-black/30">
            </div>
          </div>
        </div>

        <!-- Content Section -->
        <div class="lg:w-3/5 p-8 lg:p-12 flex flex-col justify-between">
          <div>
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-6">
              <div class="mb-4 lg:mb-0">
                <h3 class="text-3xl lg:text-4xl font-bold text-secondary mb-2">Regular Room</h3>
                <p class="text-blue-600 font-semibold text-lg">Essential Care Experience</p>
              </div>
              <div class="flex items-center text-blue-600 bg-blue-50 px-3 py-2 rounded-xl w-fit">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Essential</span>
              </div>
            </div>

            <p class="text-slate-600 text-lg leading-relaxed mb-8">
              Comfortable, well-appointed rooms with all essential amenities for quality care and comfortable living.
              Designed to provide a warm, home-like atmosphere with professional support.
            </p>

            <!-- Enhanced Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Comfortable Furnishing</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Shared Amenities Access</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Professional Care Support</span>
              </div>
              <div class="flex items-center p-3 bg-slate-50 rounded-xl">
                <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                  </svg>
                </div>
                <span class="font-medium text-slate-700 text-sm">Quality Living Space</span>
              </div>
            </div>
          </div>

          <button @click="openRates=true"
            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-[1.02] shadow-xl hover:shadow-2xl">
            Request Pricing Information
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Why Choose Us Section -->
  <div class="bg-gradient-to-r from-white to-slate-50 rounded-3xl shadow-xl p-12 border border-slate-100">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Why Choose <span class="text-accent">' . e($facility['name']) . '?</span>',
    'section_sub_header' => 'Experience the difference that genuine care, modern amenities, and a warm community
    atmosphere can make.'
    ])


    <div class="grid md:grid-cols-3 gap-8">
      <div class="text-center group">
        <div
          class="w-20 h-20 bg-gradient-to-br from-teal-300 to-teal-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h4 class="font-bold text-xl mb-4 text-secondary">24/7 Professional Care</h4>
        <p class="text-slate-600 leading-relaxed">Round-the-clock professional nursing staff and personalized care
          plans tailored to each resident's needs.</p>
      </div>

      <div class="text-center group">
        <div
          class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m2 0h2a2 2 0 002-2V7a2 2 0 00-2-2h-2m-2 4h2" />
          </svg>
        </div>
        <h4 class="font-bold text-xl mb-4 text-secondary">Licensed & Accredited</h4>
        <p class="text-slate-600 leading-relaxed">Fully licensed and regulated facility meeting the highest standards
          of safety, quality, and regulatory compliance.</p>
      </div>
      <div class="text-center group">
        <div
          class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 1.343-3 3v4a3 3 0 006 0v-4c0-1.657-1.343-3-3-3zm0 0V6m0 0a2 2 0 114 0v2" />
          </svg>
        </div>
        <h4 class="font-bold text-xl mb-4 text-secondary">Modern Amenities</h4>
        <p class="text-slate-600 leading-relaxed">Enjoy updated facilities, comfortable living spaces, and convenient
          amenities designed for safety and ease of daily living.</p>
      </div>
      <div class="text-center group">
        <div
          class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <h4 class="font-bold text-xl mb-4 text-secondary">Vibrant Community</h4>
        <p class="text-slate-600 leading-relaxed">Engaging social activities, wellness programs, and community events
          that promote active, fulfilling lifestyles.</p>
      </div>
    </div>
  </div>
  </div>

  <!-- Enhanced Rates Modal -->
  <div x-cloak x-show="openRates" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto flex min-h-full items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div @click.away="openRates=false"
      class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg max-h-[90vh] overflow-y-auto p-8">
      <div class="text-center mb-8">
        <div
          class="w-20 h-20 bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <h3 class="text-3xl font-bold text-secondary mb-2">Request Room Information</h3>
        <p class="text-slate-600 text-lg">Get personalized pricing and availability information within 24 hours.</p>
      </div>

      <form class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-3">First Name *</label>
            <input
              class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
              placeholder="John" required>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-3">Last Name *</label>
            <input
              class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
              placeholder="Doe" required>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-3">Email Address *</label>
          <input type="email"
            class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
            placeholder="john.doe@email.com" required>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-3">Phone Number *</label>
          <input type="tel"
            class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
            placeholder="(555) 123-4567" required>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-3">Room Type Preference</label>
          <select
            class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary transition-all">
            <option value="">Select room type</option>
            <option value="private">Private Room</option>
            <option value="semi-private">Semi-Private Room</option>
            <option value="regular">Regular Room</option>
            <option value="all">All Options</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-3">Additional Questions</label>
          <textarea
            class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
            rows="3" placeholder="Any specific questions about care services, amenities, or availability?"></textarea>
        </div>

        <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-amber-800 mb-1">Privacy & Security Notice</p>
              <p class="text-xs text-amber-700">Please do not include sensitive medical information or personal health
                details. Our care team will discuss specific needs during a private consultation.</p>
            </div>
          </div>
        </div>

        <label class="flex items-start gap-3 text-sm text-slate-600 cursor-pointer">
          <input type="checkbox" class="mt-1 rounded border-slate-300 text-primary focus:ring-primary" required>
          <span>I understand that I should not submit medical information or personal health details in this form, and
            consent to being contacted about room availability and pricing.</span>
        </label>

        <div class="flex gap-4 pt-6">
          <button type="button" @click="openRates=false"
            class="flex-1 px-6 py-4 rounded-xl border-2 border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button type="button"
            @click="toast('Request sent successfully! We\'ll contact you within 24 hours.'); openRates=false"
            class="flex-1 px-6 py-4 rounded-xl bg-gradient-to-r from-primary to-secondary text-white font-bold hover:from-primary-dark hover:to-secondary-dark transition-all shadow-lg hover:shadow-xl">
            Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</section>
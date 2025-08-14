<section id="about" class="py-16 sm:py-24 bg-gradient-to-br from-blue-50 to-green-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl md:text-5xl font-bold text-secondary mb-4">
        About <span class="text-accent">{{ $facility['name'] }}</span>
      </h2>
      <p class="text-xl text-slate-600 max-w-3xl mx-auto">
        Dedicated to providing compassionate care and creating a warm, supportive environment where residents thrive.
      </p>
    </div>

    <!-- Main Content -->
    <div class="grid lg:grid-cols-2 gap-16 items-center mb-16">
      <!-- Image Side -->
      <div class="relative">
        <div class="relative overflow-hidden rounded-3xl shadow-2xl">
          <img
            src="{{ asset('images/nursehuggingpatient.jpg') }}"
            alt="Caring nursing staff helping elderly residents"
            class="w-full h-96 object-cover"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
        </div>
        <!-- Floating Card -->
        <div class="absolute -bottom-6 -right-6 bg-white p-6 rounded-2xl shadow-xl border-l-4 border-accent">
          <div class="text-3xl font-bold text-accent">25+</div>
          <div class="text-sm text-slate-600">Years of Excellence</div>
        </div>
      </div>

      <!-- Content Side -->
      <div class="space-y-6">
        <div class="space-y-4">
          <h3 class="text-2xl font-bold text-secondary">Our Mission</h3>
          <p class="text-slate-700 leading-relaxed">
            Founded on the principles of dignity, respect, and clinical excellence, {{ $facility['name'] }} provides comprehensive skilled nursing, rehabilitation, and long-term care services. We believe every resident deserves personalized attention and compassionate care in a home-like environment.
          </p>
        </div>

        <!-- Key Values -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6">
          <div class="text-center p-4 bg-white rounded-xl shadow-sm border">
            <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
            </div>
            <div class="font-semibold text-secondary">Compassion</div>
            <p class="text-sm text-slate-600 mt-1">Caring with heart</p>
          </div>
          <div class="text-center p-4 bg-white rounded-xl shadow-sm border">
            <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="font-semibold text-secondary">Excellence</div>
            <p class="text-sm text-slate-600 mt-1">Quality in everything</p>
          </div>
          <div class="text-center p-4 bg-white rounded-xl shadow-sm border">
            <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div class="font-semibold text-secondary">Community</div>
            <p class="text-sm text-slate-600 mt-1">Building connections</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Achievements Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-lg border-t-4 border-accent hover:shadow-xl transition-shadow">
        <div class="flex items-center space-x-3 mb-3">
          <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary">Accreditations</h4>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
          State-licensed facility with Medicare and Medicaid certification, ensuring compliance with highest care standards.
        </p>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-lg border-t-4 border-yellow-400 hover:shadow-xl transition-shadow">
        <div class="flex items-center space-x-3 mb-3">
          <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary">Awards</h4>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
          Recognized for outstanding quality care and exceptional resident satisfaction scores by state health authorities.
        </p>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-lg border-t-4 border-blue-400 hover:shadow-xl transition-shadow">
        <div class="flex items-center space-x-3 mb-3">
          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary">Facility</h4>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
          Modern, comfortable facilities designed with accessibility and safety in mind for optimal resident well-being.
        </p>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-lg border-t-4 border-purple-400 hover:shadow-xl transition-shadow">
        <div class="flex items-center space-x-3 mb-3">
          <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary">Staff</h4>
        </div>
        <p class="text-sm text-slate-600 leading-relaxed">
          Highly trained, compassionate healthcare professionals dedicated to providing personalized, 24/7 care.
        </p>
      </div>
    </div>
  </div>
</section>

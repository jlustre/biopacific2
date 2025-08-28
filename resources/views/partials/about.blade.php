<section id="about" class="py-16 sm:py-24 relative overflow-hidden">
  <!-- Animated Background Elements -->
  <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-green-50"></div>
  <div class="absolute top-0 left-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
  <div class="absolute top-0 right-0 w-96 h-96 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
  <div class="absolute -bottom-8 left-20 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Enhanced Header -->
    <div class="text-center mb-20">
      <div class="inline-block mb-6">
        <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent text-sm font-bold tracking-wider uppercase">About Us</span>
      </div>
      <h2 class="text-5xl md:text-7xl font-extrabold mb-8 leading-tight">
        <span class="text-primary">About</span>
        <span class="text-accent relative block md:inline">
          {{ $facility['name'] }}
          <svg class="absolute -bottom-3 left-0 w-full h-4 text-accent/30" viewBox="0 0 100 12" fill="currentColor">
            <path d="M0 8c30-6 70-6 100 0v4H0z"/>
          </svg>
        </span>
      </h2>
      <p class="text-xl md:text-2xl text-slate-600 max-w-4xl mx-auto font-light leading-relaxed">
        Dedicated to providing <span class="text-accent font-semibold">compassionate care</span> and creating a warm, supportive environment where residents thrive.
      </p>
    </div>

    <!-- Enhanced Main Content -->
    <div class="grid lg:grid-cols-2 gap-16 items-center mb-24">
      <!-- Enhanced Image Side -->
      <div class="relative group">
        <!-- Main Image Container -->
        <div class="relative overflow-hidden rounded-3xl shadow-2xl transform group-hover:scale-105 transition duration-700">
          <div class="absolute -inset-4 bg-gradient-to-r from-primary to-accent rounded-3xl blur opacity-25 group-hover:opacity-40 transition duration-1000"></div>
          <div class="relative">
            <img
              src="{{ asset('images/nursehuggingpatient.jpg') }}"
              alt="Caring nursing staff helping elderly residents"
              class="w-full h-[500px] object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

            <!-- Enhanced Overlay Content -->
            <div class="absolute bottom-6 left-6 text-white">
              <p class="text-xl font-bold mb-2">Compassionate Care</p>
              <p class="text-sm opacity-90">Every day, every moment</p>
            </div>
          </div>
        </div>

        <!-- Enhanced Floating Cards -->
        <div class="absolute -top-8 -right-8 bg-white/95 backdrop-blur p-8 rounded-3xl shadow-2xl border-l-4 border-accent transform hover:scale-110 transition duration-300 z-10">
          <div class="text-4xl font-bold text-accent">{{ $facility['years'] }}+</div>
          <div class="text-sm text-slate-600 font-medium">Years of Excellence</div>
        </div>

        <div class="absolute -bottom-8 -left-8 bg-white/95 backdrop-blur p-6 rounded-3xl shadow-2xl border-l-4 border-primary transform hover:scale-110 transition duration-300 z-10">
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-primary">100%</div>
              <div class="text-sm text-slate-600 font-medium">Satisfaction</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Content Side -->
      <div class="space-y-10">
        <div class="space-y-8">
          <div class="inline-flex items-center space-x-3 bg-gradient-to-r from-accent/10 to-primary/10 rounded-full px-6 py-3">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span class="text-accent font-bold text-lg">Our Mission</span>
          </div>

          <h3 class="text-4xl md:text-5xl font-bold text-primary leading-tight">
            Creating a <span class="text-accent">Home</span> Where Care Meets <span class="text-accent">Compassion</span>
          </h3>

          <div class="space-y-6">
            <p class="text-xl text-slate-700 leading-relaxed">
              Founded on the principles of <strong class="text-primary">dignity</strong>, <strong class="text-primary">respect</strong>, and <strong class="text-primary">clinical excellence</strong>, {{ $facility['name'] }} provides comprehensive skilled nursing, rehabilitation, and long-term care services.
            </p>

            <p class="text-xl text-slate-700 leading-relaxed">
              We believe every resident deserves personalized attention and compassionate care in a home-like environment where they can truly thrive.
            </p>
          </div>
        </div>

        <!-- Enhanced Key Values -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8">
          <div class="group text-center p-8 bg-white/90 backdrop-blur rounded-3xl shadow-lg border border-white/50 hover:shadow-2xl hover:bg-white transition-all duration-500 transform hover:-translate-y-2">
            <div class="w-20 h-20 bg-gradient-to-br from-red-400 to-pink-500 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition duration-300">
              <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
            </div>
            <div class="font-bold text-primary text-xl mb-2">Compassion</div>
            <p class="text-slate-600 text-lg">Caring with heart and soul</p>
          </div>

          <div class="group text-center p-8 bg-white/90 backdrop-blur rounded-3xl shadow-lg border border-white/50 hover:shadow-2xl hover:bg-white transition-all duration-500 transform hover:-translate-y-2">
            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-blue-500 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition duration-300">
              <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="font-bold text-primary text-xl mb-2">Excellence</div>
            <p class="text-slate-600 text-lg">Quality in everything we do</p>
          </div>

          <div class="group text-center p-8 bg-white/90 backdrop-blur rounded-3xl shadow-lg border border-white/50 hover:shadow-2xl hover:bg-white transition-all duration-500 transform hover:-translate-y-2">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition duration-300">
              <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div class="font-bold text-primary text-xl mb-2">Community</div>
            <p class="text-slate-600 text-lg">Building lasting connections</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Achievements Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
      <div class="group bg-white/95 backdrop-blur p-10 rounded-3xl shadow-xl border-t-4 border-green-400 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3">
        <div class="flex items-center space-x-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-500 rounded-3xl flex items-center justify-center group-hover:scale-110 transition duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary text-2xl">Accredited</h4>
        </div>
        <p class="text-slate-600 leading-relaxed text-lg">
          State-licensed facility with Medicare and Medicaid certification, ensuring compliance with highest care standards.
        </p>
      </div>

      <div class="group bg-white/95 backdrop-blur p-10 rounded-3xl shadow-xl border-t-4 border-yellow-400 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3">
        <div class="flex items-center space-x-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-3xl flex items-center justify-center group-hover:scale-110 transition duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary text-2xl">Award-Winning</h4>
        </div>
        <p class="text-slate-600 leading-relaxed text-lg">
          Recognized for outstanding quality care and exceptional resident satisfaction scores by state health authorities.
        </p>
      </div>

      <div class="group bg-white/95 backdrop-blur p-10 rounded-3xl shadow-xl border-t-4 border-blue-400 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3">
        <div class="flex items-center space-x-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-3xl flex items-center justify-center group-hover:scale-110 transition duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary text-2xl">Modern Facility</h4>
        </div>
        <p class="text-slate-600 leading-relaxed text-lg">
          State-of-the-art facilities designed with accessibility and safety in mind for optimal resident well-being.
        </p>
      </div>

      <div class="group bg-white/95 backdrop-blur p-10 rounded-3xl shadow-xl border-t-4 border-purple-400 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3">
        <div class="flex items-center space-x-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 rounded-3xl flex items-center justify-center group-hover:scale-110 transition duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
          </div>
          <h4 class="font-bold text-secondary text-2xl">Expert Staff</h4>
        </div>
        <p class="text-slate-600 leading-relaxed text-lg">
          Highly trained, compassionate healthcare professionals dedicated to providing personalized, 24/7 care.
        </p>
      </div>
    </div>
  </div>
</section>

<style>
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .lg\:grid-cols-2 {
    grid-template-columns: 1fr;
  }

  .absolute.-top-8.-right-8,
  .absolute.-bottom-8.-left-8 {
    position: relative;
    top: auto;
    right: auto;
    bottom: auto;
    left: auto;
    margin: 1rem 0;
  }
}

@media (max-width: 768px) {
  .text-5xl {
    font-size: 2.5rem;
  }

  .md\:text-7xl {
    font-size: 3rem;
  }
}
</style>

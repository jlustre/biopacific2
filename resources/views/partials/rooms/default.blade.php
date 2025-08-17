<section id="rooms" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl sm:text-5xl font-bold text-primary mb-4">
        Rooms & Rates
      </h2>
      <p class="text-lg text-slate-600 max-w-2xl mx-auto">
        Comfortable, secure living spaces designed for your comfort and well-being
      </p>
    </div>

    <!-- Room Cards -->
    <div class="grid lg:grid-cols-2 gap-8 mb-12">
      <!-- Private Room -->
      <div class="group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden">
        <div class="relative overflow-hidden">
          <img
            src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop"
            class="h-64 sm:h-72 w-full object-cover group-hover:scale-105 transition-transform duration-300"
            alt="Private Room"
          >
          <div class="absolute top-4 left-4">
            <span class="bg-primary text-white px-3 py-1 rounded-full text-sm font-medium">
              Most Popular
            </span>
          </div>
        </div>
        <div class="p-8">
          <div class="flex items-start justify-between mb-4">
            <h3 class="text-2xl font-bold text-secondary">Private Room</h3>
            <div class="flex items-center text-amber-500">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              <span class="text-sm ml-1">Premium</span>
            </div>
          </div>

          <!-- Features Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7z"/>
              </svg>
              <span class="text-sm">Spacious layout</span>
            </div>
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
              </svg>
              <span class="text-sm">Private bathroom</span>
            </div>
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
              </svg>
              <span class="text-sm">Wi-Fi & cable TV</span>
            </div>
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
              </svg>
              <span class="text-sm">Housekeeping</span>
            </div>
          </div>

          <button
            @click="openRates=true"
            class="w-full bg-gradient-to-r from-secondary to-accent text-white px-6 py-3 rounded-xl font-semibold hover:from-primary-dark hover:to-accent transition-all duration-300 transform hover:scale-105 shadow-lg"
          >
            Request Pricing Information
          </button>
        </div>
      </div>

      <!-- Semi-Private Room -->
      <div class="group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden">
        <div class="relative overflow-hidden">
          <img
            src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop"
            class="h-64 sm:h-72 w-full object-cover group-hover:scale-105 transition-transform duration-300"
            alt="Semi-Private Room"
          >
          <div class="absolute top-4 left-4">
            <span class="bg-accent text-white px-3 py-1 rounded-full text-sm font-medium">
              Great Value
            </span>
          </div>
        </div>
        <div class="p-8">
          <div class="flex items-start justify-between mb-4">
            <h3 class="text-2xl font-bold text-secondary">Semi-Private Room</h3>
            <div class="flex items-center text-emerald-500">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <span class="text-sm ml-1">Value</span>
            </div>
          </div>

          <!-- Features Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
              <span class="text-sm">Shared layout</span>
            </div>
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <span class="text-sm">Privacy curtains</span>
            </div>
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span class="text-sm">Daily activities</span>
            </div>
            <div class="flex items-center text-slate-600">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              <span class="text-sm">Care support</span>
            </div>
          </div>

          <button
            @click="openRates=true"
            class="w-full bg-gradient-to-r from-secondary to-accent text-white px-6 py-3 rounded-xl font-semibold hover:from-primary-dark hover:to-accent transition-all duration-300 transform hover:scale-105 shadow-lg"
          >
            Request Pricing Information
          </button>
        </div>
      </div>
    </div>

    <!-- Additional Info Section -->
    <div class="bg-white rounded-3xl shadow-lg p-8 text-center">
      <h3 class="text-2xl font-bold text-secondary mb-4">Why Choose {{ $facility['name'] }}?</h3>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="flex flex-col items-center">
          <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <h4 class="font-semibold text-lg mb-2">24/7 Care</h4>
          <p class="text-slate-600 text-sm">Round-the-clock professional care and support</p>
        </div>
        <div class="flex flex-col items-center">
          <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
            </svg>
          </div>
          <h4 class="font-semibold text-lg mb-2">Licensed Facility</h4>
          <p class="text-slate-600 text-sm">Fully licensed and regulated care facility</p>
        </div>
        <div class="flex flex-col items-center">
          <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h4 class="font-semibold text-lg mb-2">Community</h4>
          <p class="text-slate-600 text-sm">Vibrant community with social activities</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Rates Modal -->
  <div x-cloak x-show="openRates" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 z-50 overflow-y-auto flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0 bg-black/50 backdrop-blur-sm">
    <div @click.away="openRates=false" class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-8">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-secondary">Request Room Rates</h3>
        <p class="text-slate-600 mt-2">We'll respond within 24 hours with current availability and pricing information.</p>
      </div>

      <form class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
          <input class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all" placeholder="Enter your full name" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
          <input type="email" class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all" placeholder="your@email.com" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number *</label>
          <input type="tel" class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all" placeholder="(555) 123-4567" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Additional Notes</label>
          <textarea class="w-full border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all" rows="3" placeholder="Any specific questions or requirements?"></textarea>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
              <p class="text-sm font-medium text-amber-800">Privacy Notice</p>
              <p class="text-xs text-amber-700 mt-1">Please do not include medical information or PHI in your message. Our team will discuss care needs during a private consultation.</p>
            </div>
          </div>
        </div>

        <label class="flex items-start gap-3 text-sm text-slate-600">
          <input type="checkbox" class="mt-1 rounded border-slate-300 text-primary focus:ring-primary" required>
          <span>I understand that I should not submit medical information or personal health details in this form.</span>
        </label>

        <div class="flex gap-3 pt-4">
          <button type="button" @click="openRates=false" class="flex-1 px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button type="button" @click="toast('Request sent successfully! We\'ll contact you within 24 hours.'); openRates=false" class="flex-1 px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-accent text-white font-semibold hover:from-primary-dark hover:to-accent transition-all shadow-lg">
            Send Request
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

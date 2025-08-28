<section id="contact" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
<<<<<<< HEAD
    'section_header' => 'Get in Touch',
    'section_sub_header' => "Have questions or want to schedule a tour? We're here to help you every step of the way."
=======
      'section_header' => 'Get in Touch',
      'section_sub_header' => "Have questions or want to schedule a tour? We're here to help you every step of the way."
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
    ])

    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
      <!-- Contact Information -->
      <div class="space-y-8">
        <div class="bg-white rounded-2xl p-6 lg:p-8 shadow-lg border border-slate-100">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
=======
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-secondary">Contact Information</h3>
          </div>

          <div class="space-y-4">
            <div class="flex items-start">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
=======
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                </svg>
              </div>
              <div>
                <p class="font-medium text-slate-700">Phone</p>
                <a href="tel:{{ $facility['phone'] }}" class="text-primary hover:text-primary/80 transition-colors">
                  {{ $facility['phone'] }}
                </a>
              </div>
            </div>

            <div class="flex items-start">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
=======
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                </svg>
              </div>
              <div>
                <p class="font-medium text-slate-700">Email</p>
                <a href="mailto:{{ $facility['email'] }}" class="text-primary hover:text-primary/80 transition-colors">
                  {{ $facility['email'] }}
                </a>
              </div>
            </div>

            <div class="flex items-start">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
=======
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                </svg>
              </div>
              <div>
                <p class="font-medium text-slate-700">Address</p>
                <p class="text-slate-600">{{ $facility['address'] }}</p>
              </div>
            </div>

            <div class="flex items-start">
              <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
=======
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                </svg>
              </div>
              <div>
                <p class="font-medium text-slate-700">Visiting Hours</p>
<<<<<<< HEAD
                <p class="text-slate-600">{{ $facility['hours'] ?? 'N/A' }}</p>
=======
                <p class="text-slate-600">{{ $facility['hours'] }}</p>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
              </div>
            </div>
          </div>
        </div>

        <!-- Map -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-lg border border-slate-100">
          <div class="p-4 bg-slate-50 border-b">
            <h4 class="font-semibold text-secondary flex items-center">
              <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3" />
=======
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
              </svg>
              Our Location
            </h4>
          </div>
<<<<<<< HEAD
          <iframe class="w-full h-64 sm:h-72" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            src="{{ $facility['maps'] ?? '' }}" allowfullscreen>
=======
          <iframe
            class="w-full h-64 sm:h-72"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="{{ $facility['maps'] }}"
            allowfullscreen>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
          </iframe>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="lg:sticky lg:top-8">
        <form class="bg-white rounded-2xl p-6 lg:p-8 shadow-lg border border-slate-100">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center mr-4">
              <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<<<<<<< HEAD
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
=======
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-secondary">Send us a Message</h3>
              <p class="text-sm text-slate-500">We'll get back to you within 24 hours</p>
            </div>
          </div>

          <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-6">
            <div class="flex items-start">
<<<<<<< HEAD
              <svg class="w-4 h-4 text-amber-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
=======
              <svg class="w-4 h-4 text-amber-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
              </svg>
              <p class="text-xs text-amber-700">Please don't include personal medical information in your message.</p>
            </div>
          </div>

          <div class="space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
<<<<<<< HEAD
                <input type="text" required
=======
                <input
                  type="text"
                  required
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                  class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                  placeholder="Enter your full name">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
<<<<<<< HEAD
                <input type="tel"
=======
                <input
                  type="tel"
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                  class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                  placeholder="(555) 123-4567">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
<<<<<<< HEAD
              <input type="email" required
=======
              <input
                type="email"
                required
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
                class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                placeholder="your@email.com">
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Message *</label>
<<<<<<< HEAD
              <textarea required
                class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                rows="5" placeholder="How can we help you today?"></textarea>
=======
              <textarea
                required
                class="w-full border border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                rows="5"
                placeholder="How can we help you today?"></textarea>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
            </div>
          </div>

          <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
<<<<<<< HEAD
            <button type="reset"
              class="px-6 py-2.5 rounded-lg border border-slate-200 text-primary hover:bg-slate-50 transition-colors">
              Clear Form
            </button>
            <button type="button" @click="toast('Message sent successfully!')"
              class="px-6 py-2.5 rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors shadow-sm flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
=======
            <button
              type="reset"
              class="px-6 py-2.5 rounded-lg border border-slate-200 text-primary hover:bg-slate-50 transition-colors">
              Clear Form
            </button>
            <button
              type="button"
              @click="toast('Message sent successfully!')"
              class="px-6 py-2.5 rounded-lg bg-primary text-white hover:bg-primary/90 transition-colors shadow-sm flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
              </svg>
              Send Message
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
<<<<<<< HEAD
</section>
=======
</section>
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

<section id="testimonials" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-3xl sm:text-4xl font-bold text-primary mb-4">What Our Families Say</h2>
      <p class="text-lg text-slate-600 max-w-2xl mx-auto">Real stories from families who have experienced our compassionate care</p>
    </div>

    <!-- Testimonials Carousel -->
    <div class="relative" x-data="{
      currentIndex: 0,
      testimonials: [
        {
          name: 'Maria G.',
          role: 'Daughter of Patient',
          text: 'The staff treated our family like their own. From the moment we walked in, we felt the warmth and genuine care. The communication was excellent, and they kept us informed every step of the way.',
          rating: 5,
          avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face'
        },
        {
          name: 'Dr. Chen',
          role: 'Son of Patient',
          text: 'The therapy team worked miracles with my father. After his surgery, we thought he might never walk again. Thanks to their dedication and expertise, he is now mobile and independent.',
          rating: 5,
          avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face'
        },
        {
          name: 'Sarah Patel',
          role: 'Family Member',
          text: 'The facility is immaculate and the activities keep Mom engaged and happy. She has made wonderful friends here and truly feels at home. We could not be more grateful.',
          rating: 5,
          avatar: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face'
        }
      ]
    }">

      <!-- Main Testimonial Card -->
      <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="relative">
          <!-- Background Pattern -->
          <div class="absolute inset-0 bg-gradient-to-r from-primary/5 to-accent/5"></div>

          <!-- Quote Icon -->
          <div class="absolute top-6 left-6 text-primary/20">
            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
            </svg>
          </div>

          <div class="relative p-8 sm:p-12">
            <!-- Rating Stars -->
            <div class="flex items-center gap-1 mb-6">
              <template x-for="star in 5" :key="star">
                <svg class="w-5 h-5" :class="star <= testimonials[currentIndex].rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </template>
            </div>

            <!-- Testimonial Text -->
            <blockquote class="text-xl sm:text-2xl leading-relaxed text-slate-700 mb-8" x-text="testimonials[currentIndex].text">
            </blockquote>

            <!-- Author Info -->
            <div class="flex items-center gap-4">
              <img :src="testimonials[currentIndex].avatar" :alt="testimonials[currentIndex].name"
                   class="w-16 h-16 rounded-full object-cover ring-4 ring-white shadow-lg">
              <div>
                <div class="font-semibold text-lg text-secondary" x-text="testimonials[currentIndex].name"></div>
                <div class="text-slate-500" x-text="testimonials[currentIndex].role"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center justify-between mt-8">
        <!-- Previous Button -->
        <button @click="currentIndex = (currentIndex - 1 + testimonials.length) % testimonials.length"
                class="flex items-center gap-2 px-6 py-3 bg-white rounded-full shadow-md hover:shadow-lg transition-all duration-300 hover:bg-primary hover:text-white group">
          <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          <span class="font-medium text-secondary hover:text-white">Previous</span>
        </button>

        <!-- Dots Indicator -->
        <div class="flex gap-2">
          <template x-for="(testimonial, index) in testimonials" :key="index">
            <button @click="currentIndex = index"
                    class="w-3 h-3 rounded-full transition-all duration-300"
                    :class="index === currentIndex ? 'bg-primary scale-125' : 'bg-slate-300 hover:bg-slate-400'">
            </button>
          </template>
        </div>

        <!-- Next Button -->
        <button @click="currentIndex = (currentIndex + 1) % testimonials.length"
                class="flex items-center gap-2 px-6 py-3 bg-white rounded-full shadow-md hover:shadow-lg transition-all duration-300 hover:bg-primary hover:text-white group">
          <span class="font-medium text-secondary hover:text-white">Next</span>
          <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>

      <!-- Trust Indicators -->
      <div class="mt-16 text-center">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-secondary mb-2">Compassionate Care</h4>
            <p class="text-slate-600 text-sm">Every patient receives personalized attention</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-accent/10 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-secondary mb-2">Expert Team</h4>
            <p class="text-slate-600 text-sm">Licensed professionals with years of experience</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-secondary mb-2">Proven Results</h4>
            <p class="text-slate-600 text-sm">Consistently high satisfaction ratings</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

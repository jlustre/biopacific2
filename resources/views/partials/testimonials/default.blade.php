@if(isset($testimonials) && $testimonials && $testimonials->count() > 0)
@php
$scheme = isset($facility['color_scheme_id']) ? \DB::table('color_schemes')->find($facility['color_scheme_id']) : null;
$primary = $primary ?? ($scheme->primary_color ?? '#0EA5E9');
$secondary = $secondary ?? ($scheme->secondary_color ?? '#1E293B');
$accent = $accent ?? ($scheme->accent_color ?? '#F59E0B');

@include('components.color-scheme-vars')
@endphp
<section id="testimonials" class="py-16 sm:py-24"
  style="background: linear-gradient(90deg, {{ $primary }}11 0%, {{ $accent }}11 100%);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'What Our Families Say',
    'section_sub_header' => "Real stories from families who have experienced our compassionate care"
    ])

    <!-- Testimonials Carousel -->
    <div class="relative" x-data="{
      currentIndex: 0,
      testimonials: @js(isset($testimonials) ? $testimonials->map(function($testimonial) {
        return [
          'name' => $testimonial->name,
          'title' => $testimonial->title,
          'relationship' => $testimonial->relationship,
          'title_header' => $testimonial->title_header,
          'quote' => $testimonial->quote,
          'story' => $testimonial->story,
          'rating' => $testimonial->rating ?? 5,
          'is_active' => $testimonial->is_active,
          'is_featured' => $testimonial->is_featured,
          'created_at' => $testimonial->created_at,
          'updated_at' => $testimonial->updated_at,
          'avatar' => $testimonial->photo_url ?? 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face'
        ];
      })->values() : [])
    }">

      <!-- Redesigned Testimonial Card -->
      <div class="bg-white rounded-2xl shadow p-8 flex flex-col md:flex-row gap-6 items-start">
        <img :src="testimonials[currentIndex].avatar" :alt="testimonials[currentIndex].name"
          class="w-20 h-20 rounded-full object-cover border mr-4">
        <div class="flex-1">
          <div class="flex flex-wrap items-center gap-2 mb-1">
            <span class="font-semibold text-xl text-gray-900" x-text="testimonials[currentIndex].name"></span>
            <template x-if="testimonials[currentIndex].title_header">
              <span class="ml-2 text-primary font-bold text-lg" x-text="testimonials[currentIndex].title_header"></span>
            </template>
            <template x-if="testimonials[currentIndex].is_active">
              <span class="ml-2 px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Active</span>
            </template>
          </div>
          <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="text-gray-500" x-text="testimonials[currentIndex].title"></span>
            <span class="mx-1 text-gray-400">•</span>
            <span class="text-gray-500" x-text="testimonials[currentIndex].relationship"></span>
          </div>
          <div class="flex items-center gap-2 mb-2">
            <template x-for="star in 5" :key="star">
              <svg class="w-5 h-5"
                :class="star <= testimonials[currentIndex].rating ? 'text-yellow-400' : 'text-gray-300'"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </template>
            <span class="text-gray-500 text-sm" x-text="'(' + testimonials[currentIndex].rating + '/5)'"></span>
          </div>
          <blockquote class="text-gray-700 italic border-l-4 border-primary pl-4 py-2 mb-2">
            <span
              x-html="testimonials[currentIndex].quote ? `&ldquo;${testimonials[currentIndex].quote}&rdquo;` : '&mdash;' "></span>
          </blockquote>
          <template x-if=" testimonials[currentIndex].story">
            <div class="mb-2 text-gray-700 text-base" x-text="testimonials[currentIndex].story"></div>
          </template>
          <div class="mt-3 text-xs text-gray-500">
            Created: <span x-text="(new Date(testimonials[currentIndex].created_at)).toLocaleDateString()"></span>
            <template
              x-if="testimonials[currentIndex].updated_at && testimonials[currentIndex].updated_at !== testimonials[currentIndex].created_at">
              <span>• Updated: <span
                  x-text="(new Date(testimonials[currentIndex].updated_at)).toLocaleDateString()"></span></span>
            </template>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center justify-between mt-8">
        <!-- Previous Button -->
        <button @click="currentIndex = (currentIndex - 1 + testimonials.length) % testimonials.length"
          class="bg-teal-600 hover:bg-teal-500 flex items-center gap-2 px-6 py-3 rounded-full shadow-md hover:shadow-lg transition-all duration-300 hover:bg-primary hover:text-white group">
          <svg class="text-teal-300 w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span class="font-medium text-teal-300 hover:text-white">Previous</span>
        </button>

        <!-- Dots Indicator -->
        <div class="flex gap-2">
          <template x-for="(testimonial, index) in testimonials" :key="index">
            <button @click="currentIndex = index" class="w-3 h-3 rounded-full transition-all duration-300"
              :class="index === currentIndex ? 'bg-primary scale-125' : 'bg-slate-300 hover:bg-slate-400'">
            </button>
          </template>
        </div>

        <!-- Next Button -->
        <button @click="currentIndex = (currentIndex + 1) % testimonials.length"
          class="bg-teal-600 hover:bg-teal-500 flex items-center gap-2 px-6 py-3 rounded-full shadow-md hover:shadow-lg transition-all duration-300 hover:bg-primary hover:text-white group">
          <span class="text-teal-300 font-medium hover:text-white">Next</span>
          <svg class="text-teal-300 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Trust Indicators -->
      <div class="mt-16 text-center">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-8">
          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 17.75V17m0-10v.75m-7.07 7.07l.53-.53m12.02 0l-.53-.53M4.22 10.22l.53.53m14.02 0l-.53.53M12 7a5 5 0 100 10 5 5 0 000-10z" />
              </svg>
            </div>
            <h4 class="font-semibold text-secondary mb-2">Safe Environment</h4>
            <p class="text-slate-600 text-sm">Strict safety protocols for peace of mind</p>
          </div>
          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </div>
            <h4 class="font-semibold text-secondary mb-2">Compassionate Care</h4>
            <p class="text-slate-600 text-sm">Every patient receives personalized attention</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-accent/10 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h4 class="font-semibold text-secondary mb-2">Expert Team</h4>
            <p class="text-slate-600 text-sm">Licensed professionals with years of experience</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
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
@endif
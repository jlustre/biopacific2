<section id="news" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="text-center mb-12">
      <h2 class="text-4xl md:text-5xl font-bold text-primary mb-4">
        News & Events
      </h2>
      <p class="text-lg text-slate-600 max-w-2xl mx-auto">
        Stay updated with the latest happenings at Bio Pacific
      </p>
      <div class="mt-6">
        <a href="#" class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-full hover:bg-primary/90 transition-all duration-300 shadow-lg hover:shadow-xl">
          View All News
          <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </a>
      </div>
    </div>

    <!-- News Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
      @foreach([
        ['Fall Prevention Workshop','Sept 18','2024','Learn safety techniques for residents and families with expert guidance.','workshop','bg-emerald-500'],
        ['Flu Shot Clinic','Oct 3','2024','On-site vaccinations for residents & staff by certified healthcare professionals.','medical','bg-blue-500'],
        ['Family BBQ Day','Oct 20','2024','Join us for food, music, and facility tours in a festive atmosphere.','event','bg-orange-500'],
      ] as [$title,$date,$year,$desc,$type,$color])
      <article class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-slate-200/50">
        <!-- Date Badge -->
        <div class="absolute top-4 right-4 z-10">
          <div class="{{ $color }} text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
            {{ $date }}
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 pb-8">
          <!-- Event Type -->
          <div class="flex items-center mb-3">
            <div class="w-2 h-2 {{ $color }} rounded-full mr-2"></div>
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $type }}</span>
          </div>

          <!-- Title -->
          <h3 class="text-xl font-bold text-secondary mb-3 group-hover:text-primary transition-colors duration-300">
            {{ $title }}
          </h3>

          <!-- Description -->
          <p class="text-slate-600 text-sm leading-relaxed mb-4">
            {{ $desc }}
          </p>

          <!-- Read More Link -->
          <div class="flex items-center justify-between">
            <a href="#" class="inline-flex items-center text-primary font-semibold text-sm hover:text-primary/80 transition-colors duration-300 group/link">
              Learn More
              <svg class="ml-1 w-4 h-4 transform group-hover/link:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </a>
            <div class="text-xs text-slate-400">{{ $year }}</div>
          </div>
        </div>

        <!-- Hover Effect Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
      </article>
      @endforeach
    </div>

    <!-- Call to Action -->
    <div class="mt-16 text-center">
      <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
        <h3 class="text-2xl font-bold text-secondary mb-4">Stay Connected</h3>
        <p class="text-slate-600 mb-6">
          Subscribe to our newsletter to receive updates about upcoming events and important announcements.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
          <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
          <button class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-colors duration-300">
            Subscribe
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

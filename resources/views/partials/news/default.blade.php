<section id="news" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50" x-data="{ 
  openModal: false, 
  openAllNewsModal: false,
  selectedNews: {},
  phoneNumber: '{{ $facility['phone'] ?? '(555) 123-4567' }}',
  allNews: @js($newsItems),
  showModal(newsItem) {
    this.selectedNews = newsItem;
    this.openModal = true;
  },
  showAllNews() {
    this.openAllNewsModal = true;
  },
  callPhone() {
    // Try to initiate phone call
    try {
      window.location.href = 'tel:' + this.phoneNumber;
    } catch (error) {
      // Fallback: copy phone number to clipboard
      this.copyPhoneNumber();
    }
  },
  copyPhoneNumber() {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(this.phoneNumber).then(() => {
        alert('Phone number copied to clipboard: ' + this.phoneNumber);
      }).catch(() => {
        this.showPhoneAlert();
      });
    } else {
      this.showPhoneAlert();
    }
  },
  showPhoneAlert() {
    alert('Please call us at: ' + this.phoneNumber);
  }
}">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'News & Events',
    'section_sub_header' => "Stay updated with the latest happenings at ". e($facility['name']) ."."
    ])

    <!-- News Grid -->
    <template x-if="allNews.length === 0">
      <div class="text-center text-red-500 text-lg py-12">
        No news or events are available at this time. Please check back later.
      </div>
    </template>

    <template x-if="allNews.length > 6">
      <div class="text-center mb-12 mt-6">
        <button @click="showAllNews()"
          class="inline-flex items-center px-6 py-3 text-white font-semibold rounded-full transition-all duration-300 shadow-lg hover:shadow-xl"
          style="background: {{ $primary }};">
          View All News
          <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8" x-show="allNews.length > 0">
      <template x-for="(news, index) in allNews" :key="index">
        <article
          class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-slate-200/50">
          <!-- Date Badge -->
          <div class="absolute top-4 right-4 z-10">
            <div :class="news.color + ' text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide'">
              <span x-text="news.date"></span>
            </div>
          </div>
          <!-- Content -->
          <div class="p-6 pb-8">
            <div class="flex items-center mb-3">
              <div class="w-2 h-2 rounded-full mr-2" :class="news.color"></div>
              <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide" x-text="news.type"></span>
            </div>
            <h3 class="text-xl font-bold text-secondary mb-3 group-hover:text-primary transition-colors duration-300"
              x-text="news.title"></h3>
            <p class="text-slate-600 text-sm leading-relaxed mb-4" x-text="news.desc"></p>
            <div class="flex items-center justify-between">
              <button @click="showModal(news)"
                class="inline-flex items-center text-primary font-semibold text-sm hover:text-primary/80 transition-colors duration-300 group/link">
                Learn More
                <svg class="ml-1 w-4 h-4 transform group-hover/link:translate-x-1 transition-transform duration-300"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>
              <div class="text-xs text-slate-400" x-text="news.year"></div>
            </div>
          </div>
          <div
            class="absolute inset-0 bg-gradient-to-t from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
          </div>
        </article>
      </template>
    </div>

    <!-- Call to Action -->
    <div class="mt-16 text-center">
      <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl mx-auto">
        <h3 class="text-2xl font-bold text-secondary mb-4">Stay Connected</h3>
        <p class="text-slate-600 mb-6">
          Subscribe to our newsletter to receive updates about upcoming events and important announcements.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
          <input type="email" placeholder="Enter your email"
            class="flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
          <button
            class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-colors duration-300">
            Subscribe
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- View All News Modal -->
  <div x-cloak x-show="openAllNewsModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
    @click.self="openAllNewsModal=false">
    <div class="max-w-4xl w-full max-h-[90vh] overflow-y-auto rounded-3xl bg-white shadow-2xl">
      <div class="p-6 sm:p-8">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h2 class="text-3xl font-bold text-slate-900">All News & Events</h2>
            <p class="text-slate-600 mt-2">Stay updated with all our upcoming events and announcements</p>
          </div>
          <button @click="openAllNewsModal=false" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- News List -->
        <div class="space-y-6">
          <template x-for="(news, index) in allNews" :key="index">
            <article
              class="border border-slate-200 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 group cursor-pointer"
              @click="openAllNewsModal=false; showModal(news)">
              <div class="flex items-start gap-6">
                <!-- Date Badge -->
                <div class="flex-shrink-0">
                  <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white font-bold"
                      :class="news.color">
                      <div class="text-center">
                        <div class="text-xs uppercase font-bold" x-text="news.date.split(' ')[0]"></div>
                        <div class="text-lg font-black" x-text="news.date.split(' ')[1]"></div>
                      </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1" x-text="news.year"></div>
                  </div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3 mb-3">
                    <div class="w-2 h-2 rounded-full" :class="news.color"></div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                      x-text="news.type"></span>
                  </div>

                  <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-primary transition-colors"
                    x-text="news.title"></h3>
                  <p class="text-slate-600 leading-relaxed" x-text="news.desc"></p>

                  <div class="mt-4 flex items-center justify-between">
                    <span
                      class="inline-flex items-center text-primary font-semibold text-sm group-hover:text-primary/80 transition-colors">
                      Learn More
                      <svg class="ml-1 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                    </span>

                    <!-- Event Type Icon -->
                    <div class="text-slate-400">
                      <template x-if="news.type === 'workshop'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                          </path>
                        </svg>
                      </template>
                      <template x-if="news.type === 'seminar'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                          </path>
                        </svg>
                      </template>
                      <template x-if="news.type === 'medical'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                          </path>
                        </svg>
                      </template>
                      <template x-if="news.type === 'event'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                          </path>
                        </svg>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </template>
        </div>

        <!-- Modal Footer -->
        <div class="mt-8 pt-6 border-t border-slate-200">
          <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
            <div class="text-sm text-slate-600">
              <span class="font-semibold" x-text="allNews.length"></span> events and announcements
            </div>
            <div class="flex gap-3">
              <button @click="openAllNewsModal=false"
                class="px-6 py-3 border-2 border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition-colors">
                Close
              </button>
              <a href="#contact" @click="openAllNewsModal=false"
                class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-colors">
                Contact for More Info
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- News Detail Modal -->
  <div x-cloak x-show="openModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
    @click.self="openModal=false">
    <div class="max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-3xl bg-white shadow-2xl">
      <div class="p-6 sm:p-8">
        <!-- Modal Header -->
        <div class="flex items-start justify-between mb-6">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <div class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide text-white"
                :class="selectedNews.color">
                <span x-text="selectedNews.date"></span>
              </div>
              <span class="text-sm text-slate-500" x-text="selectedNews.year"></span>
            </div>
            <h3 class="text-2xl font-bold text-slate-900" x-text="selectedNews.title"></h3>
          </div>
          <button @click="openModal=false" class="text-slate-400 hover:text-slate-600 transition-colors ml-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="space-y-6">
          <!-- Description -->
          <div>
            <p class="text-slate-700 leading-relaxed text-lg" x-text="selectedNews.desc"></p>
          </div>

          <!-- Event Details -->
          <div class="bg-slate-50 rounded-xl p-6">
            <h4 class="font-semibold text-slate-900 mb-4">Event Details</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-slate-600">Date:</span>
                <span class="ml-2 text-slate-900" x-text="selectedNews.date + ' ' + selectedNews.year"></span>
              </div>
              <div>
                <span class="font-medium text-slate-600">Type:</span>
                <span class="ml-2 text-slate-900 capitalize" x-text="selectedNews.type"></span>
              </div>
              <div>
                <span class="font-medium text-slate-600">Location:</span>
                <span class="ml-2 text-slate-900">{{ $facility['name'] ?? 'Our Facility' }}</span>
              </div>
              <div>
                <span class="font-medium text-slate-600">Duration:</span>
                <span class="ml-2 text-slate-900">2-3 hours</span>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div>
            <h4 class="font-semibold text-slate-900 mb-3">Additional Information</h4>
            <div class="space-y-3 text-slate-700">
              <template x-if="selectedNews.type === 'workshop'">
                <p>• Interactive sessions with hands-on activities<br>• Take-home materials and resources<br>• Q&A
                  session with healthcare professionals</p>
              </template>
              <template x-if="selectedNews.type === 'seminar'">
                <p>• Expert guest speakers<br>• Educational resources provided<br>• Open discussion and networking
                  opportunity</p>
              </template>
              <template x-if="selectedNews.type === 'medical'">
                <p>• Licensed healthcare professionals<br>• No appointment necessary<br>• Insurance accepted for
                  eligible participants</p>
              </template>
              <template x-if="selectedNews.type === 'event'">
                <p>• Family-friendly activities<br>• Food and refreshments provided<br>• Facility tours available</p>
              </template>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="bg-primary/5 border border-primary/20 rounded-xl p-6">
            <h4 class="font-semibold text-slate-900 mb-3">How to Participate</h4>
            <p class="text-slate-700 mb-4">
              Interested in attending this event? Contact our activities coordinator for more information and to reserve
              your spot.
            </p>
            <div class="space-y-4">
              <!-- Phone Number Display -->
              <div class="flex items-center justify-center gap-2 text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span class="font-semibold" x-text="phoneNumber"></span>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row gap-3">
                <button @click="callPhone()"
                  class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-colors group">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="group-hover:hidden">Call Us</span>
                  <span class="hidden group-hover:inline">Tap to Call</span>
                </button>
                <button @click="copyPhoneNumber()"
                  class="hidden sm:flex flex-1 items-center justify-center px-4 py-3 bg-slate-100 text-slate-700 font-semibold rounded-lg hover:bg-slate-200 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                  </svg>
                  Copy Number
                </button>
                <a href="#contact" @click="openModal=false"
                  class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-white text-primary font-semibold rounded-lg border-2 border-primary hover:bg-primary/5 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  Contact Form
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  /* Hide elements with x-cloak until Alpine.js is loaded */
  [x-cloak] {
    display: none !important;
  }
</style>
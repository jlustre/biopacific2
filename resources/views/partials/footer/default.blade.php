<footer class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white relative overflow-hidden">

  <!-- Background decoration -->
  {{-- <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=" 60" height="60" viewBox="0 0 60 60"
    xmlns="http://www.w3.org/2000/svg" %3E%3Cg fill="none" fill-rule="evenodd" %3E%3Cg fill="%23ffffff"
    fill-opacity="0.03" %3E%3Ccircle cx="30" cy="30" r="2" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-50">
  </div> --}}

  <!--3-column layout -->
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Main footer content -->
    <div class="py-12 lg:py-16">
      <div class="flex justify-center">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12 max-w-6xl">

          <!-- Company Info -->
          <div class="space-y-6">
            <div class="flex items-center gap-3">
              <div class="relative">
                @if(!empty($facility['logo_url']))
                <img src="{{ asset('images/' . $facility['logo_url']) }}"
                  alt="{{ $facility['name'] ?? 'Bio-Pacific' }} Logo"
                  class="h-12 w-12 rounded-2xl object-contain bg-white/10 shadow-lg">
                @else
                <span
                  class="inline-flex h-12 w-12 rounded-2xl items-center justify-center bg-gradient-to-br from-primary to-primary/80 text-white font-bold text-lg shadow-lg">
                  V
                </span>
                @endif
              </div>
              <div>
                <div class="font-bold text-lg text-white">{{ $facility['name'] ?? 'Bio-Pacific' }}</div>
                <div class="text-xs text-slate-300 font-medium">{{ $facility['tagline'] ?? 'Healthcare Excellence' }}
                </div>
              </div>
            </div>
            <p class="text-slate-200 leading-relaxed text-lg text-center">{{ $facility['headline'] ?? 'Quality care for
              your loved
              ones' }}
            </p>
            <p class="text-slate-400 leading-relaxed text-md -m-2 text-center">{{ $facility['subheadline'] ?? 'Quality
              care for your
              loved
              ones' }}
            </p>

            <!-- Social Links -->
            <div class="space-y-3">
              @if(
              (!empty($facility['social']['facebook']) && $facility['social']['facebook']) ||
              (!empty($facility['social']['linkedin']) && $facility['social']['linkedin']) ||
              (!empty($facility['social']['youtube']) && $facility['social']['youtube'])
              )
              <div class="font-semibold text-white text-center mt-2">Connect With Us</div>
              @endif
              <div class="flex gap-3 justify-center">
                @if(isset($facility['social']['facebook']) && $facility['social']['facebook'])
                <a href="{{ $facility['social']['facebook'] }}"
                  class="group inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800/50 border border-slate-700 hover:bg-blue-600 hover:border-blue-500 transition-all duration-300 hover:scale-110">
                  <svg class="w-4 h-4 text-slate-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path
                      d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                  </svg>
                </a>
                @endif
                @if(isset($facility['social']['linkedin']) && $facility['social']['linkedin'])
                <a href="{{ $facility['social']['linkedin'] }}"
                  class="group inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800/50 border border-slate-700 hover:bg-blue-700 hover:border-blue-600 transition-all duration-300 hover:scale-110">
                  <svg class="w-4 h-4 text-slate-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path
                      d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                  </svg>
                </a>
                @endif
                @if(isset($facility['social']['youtube']) && $facility['social']['youtube'])
                <a href="{{ $facility['social']['youtube'] }}"
                  class="group inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800/50 border border-slate-700 hover:bg-red-600 hover:border-red-500 transition-all duration-300 hover:scale-110">
                  <svg class="w-4 h-4 text-slate-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path
                      d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                  </svg>
                </a>
                @endif
              </div>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="space-y-6">
            <div class="font-bold text-lg text-white flex items-center gap-2">
              <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Contact Information
            </div>
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <div class="text-slate-300">
                  <div>{{ $facility['address'] ?? 'Contact us for address' }}</div>
                  @if(!empty($facility['city']) || !empty($facility['state']) || !empty($facility['zip']))
                  <div>
                    {{ $facility['city'] ?? '' }}@if(!empty($facility['city']) && (!empty($facility['state']) ||
                    !empty($facility['zip']))), @endif
                    {{ $facility['state'] ?? '' }}@if(!empty($facility['state']) && !empty($facility['zip'])) @endif
                    {{ $facility['zip'] ?? '' }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <a href="tel:{{ $facility['phone'] ?? '' }}"
                  class="text-slate-300 hover:text-primary transition-colors">
                  @if(!empty($facility['phone']))
                  @php
                  $phone = preg_replace('/\D/', '', $facility['phone']);
                  if(strlen($phone) == 10) {
                  $formatted = '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
                  } else {
                  $formatted = $facility['phone'];
                  }
                  @endphp
                  {{ $formatted }}
                  @else
                  Contact us
                  @endif
                </a>
              </div>
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <a href="mailto:{{ $facility['email'] ?? '' }}"
                  class="text-slate-300 hover:text-primary transition-colors">{{ $facility['email'] ?? 'Contact us'
                  }}</a>
              </div>
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <div class="text-slate-400 text-sm">Operating Hours</div>
                  <div class="text-slate-300 font-medium">{{ $facility['hours'] ?? '24/7 Care' }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="space-y-6">
            <div class="font-bold text-lg text-white flex items-center gap-2">
              <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
              Quick Links
            </div>
            <ul class="space-y-3">

              @if(!empty($activeSections) && in_array('about', $activeSections))
              <li>
                <a href="{{ url('/' . $facility['slug'] . '#about') }}"
                  class="group flex items-center gap-2 text-slate-300 hover:text-primary transition-all duration-200">
                  <span class="w-1 h-1 bg-slate-500 rounded-full group-hover:bg-primary transition-colors"></span>
                  About Us
                </a>
              </li>
              @endif
              @if(!empty($activeSections) && in_array('book', $activeSections))
              <li>
                <a href="{{ url('/' . $facility['slug'] . '#book') }}"
                  class="group flex items-center gap-2 text-slate-300 hover:text-primary transition-all duration-200">
                  <span class="w-1 h-1 bg-slate-500 rounded-full group-hover:bg-primary transition-colors"></span>
                  Book A Tour
                </a>
              </li>
              @endif
              @if(!empty($activeSections) && in_array('services', $activeSections))
              <li>
                <a href="{{ url('/'.$facility['slug'] . '#services') }}"
                  class="group flex items-center gap-2 text-slate-300 hover:text-primary transition-all duration-200">
                  <span class="w-1 h-1 bg-slate-500 rounded-full group-hover:bg-primary transition-colors"></span>
                  Care & Services
                </a>
              </li>
              @endif
              @if(!empty($activeSections) && in_array('careers', $activeSections))
              <li>
                <a href="{{ url('/' . $facility['slug'] . '#careers') }}"
                  class="group flex items-center gap-2 text-slate-300 hover:text-primary transition-all duration-200">
                  <span class="w-1 h-1 bg-slate-500 rounded-full group-hover:bg-primary transition-colors"></span>
                  Career Opportunities
                </a>
              </li>
              @endif
              @if(!empty($activeSections) && in_array('contact', $activeSections))
              <li>
                <a href="{{ url('/' . $facility['slug'] . '#contact') }}"
                  class="group flex items-center gap-2 text-slate-300 hover:text-primary transition-all duration-200">
                  <span class="w-1 h-1 bg-slate-500 rounded-full group-hover:bg-primary transition-colors"></span>
                  Contact & Location
                </a>
              </li>
              @endif
              <li>
                <a href="{{ url($facility['slug'] . '/webmaster/contact') }}"
                  class="group flex items-center gap-2 text-slate-300 hover:text-primary transition-all duration-200">
                  <span class="w-1 h-1 bg-slate-500 rounded-full group-hover:bg-primary transition-colors"></span>
                  Contact Webmaster
                </a>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  </div>


  <!-- Bottom bar -->
  <div class="border-t border-slate-700 py-6">
    <div class="flex flex-col gap-4">
      <!-- Copyright -->
      <div class="text-slate-400 text-sm text-center">
        © <span x-text="new Date().getFullYear()"></span> {{ $facility['name'] ?? 'Bio-Pacific' }}. All rights
        reserved.
      </div>
      <!-- Footer Links -->
      <div class="flex flex-wrap justify-center items-center gap-x-4 gap-y-2 text-sm px-4">
        <a href="{{ url($facility['slug'] .'/privacy-policy') }}"
          class="text-slate-400 hover:text-primary transition-colors">Privacy Policy</a>
        <a href="{{ url($facility['slug'] .'/notice-of-privacy-practices') }}"
          class="text-slate-400 hover:text-primary transition-colors">Notice of Privacy Practices</a>
        <a href="{{ url($facility['slug'] .'/terms-of-service') }}"
          class="text-slate-400 hover:text-primary transition-colors">Terms of Service</a>
        <a href="{{ url($facility['slug'] .'/accessibility') }}"
          class="text-slate-400 hover:text-primary transition-colors">Accessibility</a>
      </div>
    </div>
  </div>

</footer>
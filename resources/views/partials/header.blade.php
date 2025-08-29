<header
    class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo and site name -->
            <div class="flex items-center justify-start">
                <span
                    class="inline-flex h-10 w-10 items-center justify-center bg-primary/10 text-primary font-bold mr-3">
                    <img src="{{ asset('images/bplogo.png') }}" alt="Logo" class="h-5 w-5 object-contain">
                </span>
                <a href="#top" class="flex items-center gap-1">
                    <div class="flex flex-col">
                        <div class="lg:text-xl font-semibold">{{ $facility['name'] }}</div>
                        @if(!empty($facility['tagline']))
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $facility['tagline'] }}</div>
                        @endif
                    </div>
                </a>
            </div>
            @include('partials.navbar')
            <div class="hidden md:flex gap-2">
                <a href="#book"
                    class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-white font-medium hover:bg-primary/90 focus:ring-2 focus:ring-primary/40">Book
                    a Tour</a>
            </div>
            <button @click="mobileOpen=true"
                class="md:hidden inline-flex items-center rounded-xl px-3 py-2 border border-slate-300 dark:border-slate-600">Menu</button>
        </div>
    </div>
    <!-- Mobile menu -->
    <div x-show="mobileOpen" x-transition
        class="md:hidden border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 max-h-[calc(100vh-4rem)] overflow-hidden">
        <div class="flex flex-col h-full max-h-[calc(100vh-4rem)]">
            <!-- Fixed header -->
            <div
                class="flex justify-between items-center px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex-shrink-0">
                <span class="font-medium text-slate-800 dark:text-slate-200">Menu</span>
                <button @click="mobileOpen=false"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Scrollable content -->
            <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-3">
                <div class="space-y-3">
                    <!-- About & Services -->
                    <div class="space-y-1">
                        <div class="font-medium text-slate-600 dark:text-slate-400 px-3 py-1">About</div>
                        <a href="#about" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">About Us</a>
                        <a href="#services" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Our Services</a>
                    </div>

                    <!-- Facility -->
                    <div class="space-y-1">
                        <div class="font-medium text-slate-600 dark:text-slate-400 px-3 py-1">Facility</div>
                        <a href="#rooms" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Rooms & Rates</a>
                        <a href="#gallery" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Photo Gallery</a>
                    </div>

                    <!-- Community -->
                    <div class="space-y-1">
                        <div class="font-medium text-slate-600 dark:text-slate-400 px-3 py-1">Community</div>
                        <a href="#news" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">News & Events</a>
                        <a href="#testimonials"
                            class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Testimonials</a>
                        <a href="#careers" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Careers</a>
                    </div>

                    <!-- Support -->
                    <div class="space-y-1">
                        <div class="font-medium text-slate-600 dark:text-slate-400 px-3 py-1">Support</div>
                        <a href="#contact" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Contact Us</a>
                        <a href="#faqs" class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">FAQs</a>
                        <a href="#resources"
                            class="block px-6 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                            @click="mobileOpen=false">Resources</a>
                    </div>

                    <!-- Book button with extra padding at bottom -->
                    <div class="pt-2 pb-4">
                        <a href="#book" class="block text-center px-3 py-3 rounded-lg bg-primary text-white font-medium"
                            @click="mobileOpen=false">Book a Tour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
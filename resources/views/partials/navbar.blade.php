<nav class="hidden md:flex items-center gap-6 text-sm" x-data="{ activeSection: '' }" x-init="
  // Function to update active section based on scroll position
  const updateActiveSection = () => {
    const sections = ['about', 'services', 'rooms', 'gallery', 'news', 'testimonials', 'careers', 'contact', 'faqs', 'resources'];
    let current = '';

    sections.forEach(section => {
      const element = document.getElementById(section);
      if (element) {
        const rect = element.getBoundingClientRect();
        if (rect.top <= 100 && rect.bottom >= 100) {
          current = section;
        }
      }
    });

    activeSection = current;
  };

  // Update on scroll
  window.addEventListener('scroll', updateActiveSection);

  // Update on hash change
  window.addEventListener('hashchange', () => {
    activeSection = window.location.hash.replace('#', '');
  });

  // Update on click
  document.addEventListener('click', (e) => {
    if (e.target.matches('a[href^=\'#\']')) {
      const hash = e.target.getAttribute('href').replace('#', '');
      activeSection = hash;
    }
  });

  // Initial check
  updateActiveSection();
  if (window.location.hash) {
    activeSection = window.location.hash.replace('#', '');
  }
">
  <!-- About & Services -->
  <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <button class="lg:text-xl flex items-center gap-1 transition-colors duration-200 hover:text-primary dark:hover:text-blue-400"
            :class="{'text-primary dark:text-blue-200 font-semibold': activeSection === 'about' || activeSection === 'services'}">
      About & Services
      <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-2 z-[9999] max-h-[60vh] overflow-y-auto overscroll-contain">
      <a href="#about" @click="activeSection = 'about'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'about'}">
        About Us
      </a>
      <a href="#services" @click="activeSection = 'services'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'services'}">
        Our Services
      </a>
    </div>
  </div>

  <!-- Facilities & Rates -->
  <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <button class="lg:text-xl flex items-center gap-1 transition-colors duration-200 hover:text-primary dark:hover:text-blue-400"
            :class="{' text-xl text-primary dark:text-blue-200 font-semibold': activeSection === 'rooms' || activeSection === 'gallery'}">
      Facilities
      <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-2 z-[9999] max-h-[60vh] overflow-y-auto overscroll-contain">
      <a href="#rooms" @click="activeSection = 'rooms'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'rooms'}">
        Rooms & Rates
      </a>
      <a href="#gallery" @click="activeSection = 'gallery'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'gallery'}">
        Photo Gallery
      </a>
    </div>
  </div>

  <!-- Community -->
  <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <button class="lg:text-xl flex items-center gap-1 transition-colors duration-200 hover:text-primary dark:hover:text-blue-400"
            :class="{'text-primary dark:text-blue-200 font-semibold': activeSection === 'news' || activeSection === 'testimonials' || activeSection === 'careers'}">
      Community
      <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-2 z-[9999] max-h-[60vh] overflow-y-auto overscroll-contain">
      <a href="#news" @click="activeSection = 'news'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'news'}">
        News & Events
      </a>
      <a href="#testimonials" @click="activeSection = 'testimonials'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'testimonials'}">
        Testimonials
      </a>
      <a href="#careers" @click="activeSection = 'careers'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'careers'}">
        Careers
      </a>
    </div>
  </div>

  <!-- Support -->
  <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <button class="lg:text-xl flex items-center gap-1 transition-colors duration-200 hover:text-primary dark:hover:text-blue-400"
            :class="{'text-primary dark:text-blue-200 font-semibold': activeSection === 'contact' || activeSection === 'faqs' || activeSection === 'resources'}">
      Support
      <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg py-2 z-[9999] max-h-[60vh] overflow-y-auto overscroll-contain">
      <a href="#contact" @click="activeSection = 'contact'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'contact'}">
        Contact Us
      </a>
      <a href="#faqs" @click="activeSection = 'faqs'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'faqs'}">
        FAQs
      </a>
      <a href="#resources" @click="activeSection = 'resources'"
         class="lg:text-xl block px-4 py-2 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-primary dark:hover:text-blue-400"
         :class="{'bg-blue-50 dark:bg-blue-900/20 text-primary dark:text-blue-200 font-medium': activeSection === 'resources'}">
        Resources
      </a>
    </div>
  </div>
</nav>

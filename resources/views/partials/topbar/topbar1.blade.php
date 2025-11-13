@php
use Illuminate\Support\Str;

$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
$phone = isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/','($1) $2-$3',$facility['phone']) : null;
@endphp

{{-- ===============================
ACCESSIBLE STICKY TOPBAR
=============================== --}}
<a href="#main"
    class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] bg-white text-slate-900 px-3 py-2 rounded-md shadow ring-1 ring-slate-200">
    Skip to main content
</a>

<header x-data="topbar()" x-init="init()" @keydown.escape="closeAll()" :class="{'shadow-md': scrolled}"
    class="sticky top-0 z-50 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/60"
    style="--primary: {{ $primary }}; --secondary: {{ $secondary }}; --accent: {{ $accent }};">
    {{-- Thin utility strip --}}
    <div class="hidden md:flex items-center justify-between text-xs px-4 lg:px-8 py-2 border-b border-slate-200/70">
        <div class="flex items-center gap-4 text-slate-600">
            <span class="inline-flex items-center gap-2">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M12 2v20m10-10H2" />
                </svg>
                {{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}
            </span>
            @if($phone)
            <a href="tel:{{ $facility['phone'] }}" class="inline-flex items-center gap-2 hover:underline">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M3 5l4 2 3 7 4 2 3-3-2-4-7-3-2-4z" />
                </svg>
                {{ $phone }}
            </a>
            @endif
            <span class="hidden lg:inline text-slate-400">•</span>
            <span class="hidden lg:inline text-slate-600">Tours {{ $facility['hours'] ?? '9AM–7PM' }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if(!empty($activeSections) && in_array('book', $activeSections))
            <a href="#book"
                class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold text-white"
                style="background: var(--primary);">
                Book a Tour
            </a>
            @endif
            <a href="#contact"
                class="hidden sm:inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1"
                style="color: var(--primary); border-color: var(--primary);">
                Contact
            </a>
        </div>
    </div>

    {{-- Main bar --}}
    <div class="px-4 lg:px-8 py-3">
        <div class="flex items-center justify-between">
            {{-- Left: Logo / Name --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl"
                    style="background: linear-gradient(135deg, var(--primary), var(--accent));"></div>
                <div class="hidden sm:block">
                    <div class="font-black tracking-tight text-slate-900 leading-none">
                        {{ Str::limit($facility['name'] ?? 'Our Facility', 26) }}
                    </div>
                    <div class="text-[11px] text-slate-500 leading-none">
                        {{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}
                    </div>
                </div>
            </a>

            {{-- Center: Desktop nav --}}
            <nav class="hidden md:flex items-center gap-2">
                {{-- Mega: About --}}
                <div class="relative" @mouseenter="open('about')" @mouseleave="close('about')">
                    <button @click="toggle('about')" class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100"
                        :class="isActive(['about','services']) ? 'text-[color:var(--primary)]' : 'text-slate-700'">
                        About
                    </button>
                    <div x-show="menus.about" x-transition
                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-[560px] bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 p-4 grid grid-cols-2 gap-3">
                        <a href="#about" class="group rounded-xl p-3 hover:bg-slate-50 flex items-start gap-3"
                            @click="setActive('about')">
                            <span class="h-9 w-9 rounded-lg shrink-0"
                                style="background: linear-gradient(135deg, var(--primary), var(--accent));"></span>
                            <div>
                                <div class="font-semibold text-slate-900">About Us</div>
                                <p class="text-sm text-slate-600">Our mission, values, and care philosophy.</p>
                            </div>
                        </a>
                        <a href="#services" class="group rounded-xl p-3 hover:bg-slate-50 flex items-start gap-3"
                            @click="setActive('services')">
                            <span class="h-9 w-9 rounded-lg shrink-0 bg-slate-100"></span>
                            <div>
                                <div class="font-semibold text-slate-900">Services</div>
                                <p class="text-sm text-slate-600">Skilled nursing, rehab, memory care, and more.</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Mega: Facilities --}}
                <div class="relative" @mouseenter="open('facilities')" @mouseleave="close('facilities')">
                    <button @click="toggle('facilities')"
                        class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100"
                        :class="isActive(['rooms','gallery']) ? 'text-[color:var(--primary)]' : 'text-slate-700'">
                        Facilities
                    </button>
                    <div x-show="menus.facilities" x-transition
                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-[640px] bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 p-5 grid grid-cols-3 gap-4">
                        <a href="#rooms" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('rooms')">
                            <div class="font-semibold text-slate-900">Rooms & Rates</div>
                            <p class="text-sm text-slate-600">Compare accommodations & pricing.</p>
                        </a>
                        <a href="#gallery" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('gallery')">
                            <div class="font-semibold text-slate-900">Photo Gallery</div>
                            <p class="text-sm text-slate-600">See our spaces and daily life.</p>
                        </a>
                        <a href="#book" class="rounded-xl p-3 hover:bg-slate-50">
                            @if(!empty($activeSections) && in_array('book', $activeSections))
                            <a href="#book" class="rounded-xl p-3 hover:bg-slate-50">
                                <div class="font-semibold text-slate-900">Book a Tour</div>
                                <p class="text-sm text-slate-600">Schedule a visit in minutes.</p>
                            </a>
                            @endif
                    </div>
                </div>

                {{-- Mega: Community --}}
                <div class="relative" @mouseenter="open('community')" @mouseleave="close('community')">
                    <button @click="toggle('community')"
                        class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100"
                        :class="isActive(['news','testimonials','careers']) ? 'text-[color:var(--primary)]' : 'text-slate-700'">
                        Community
                    </button>
                    <div x-show="menus.community" x-transition
                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-[640px] bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 p-5 grid grid-cols-3 gap-4">
                        <a href="#news" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('news')">
                            <div class="font-semibold text-slate-900">News & Events</div>
                            <p class="text-sm text-slate-600">Updates, activities, and happenings.</p>
                        </a>
                        <a href="#testimonials" class="rounded-xl p-3 hover:bg-slate-50"
                            @click="setActive('testimonials')">
                            <div class="font-semibold text-slate-900">Testimonials</div>
                            <p class="text-sm text-slate-600">Families share their experiences.</p>
                        </a>
                        <a href="#careers" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('careers')">
                            <div class="font-semibold text-slate-900">Careers</div>
                            <p class="text-sm text-slate-600">Join our compassionate team.</p>
                        </a>
                    </div>
                </div>

                {{-- Mega: Support --}}
                <div class="relative" @mouseenter="open('support')" @mouseleave="close('support')">
                    <button @click="toggle('support')"
                        class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100"
                        :class="isActive(['contact','faqs','resources']) ? 'text-[color:var(--primary)]' : 'text-slate-700'">
                        Support
                    </button>
                    <div x-show="menus.support" x-transition
                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-[560px] bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 p-4 grid grid-cols-2 gap-3">
                        <a href="#contact" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('contact')">
                            <div class="font-semibold text-slate-900">Contact Us</div>
                            <p class="text-sm text-slate-600">We’re here to help you decide.</p>
                        </a>
                        <a href="#faqs" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('faqs')">
                            <div class="font-semibold text-slate-900">FAQs</div>
                            <p class="text-sm text-slate-600">Common questions answered.</p>
                        </a>
                        <a href="#resources" class="rounded-xl p-3 hover:bg-slate-50" @click="setActive('resources')">
                            <div class="font-semibold text-slate-900">Resources</div>
                            <p class="text-sm text-slate-600">Guides for families & caregivers.</p>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Right: CTAs + Mobile burger --}}
            <div class="flex items-center gap-2">
                <a href="#book" @if(!empty($activeSections) && in_array('book', $activeSections)) <a href="#book"
                    class="hidden md:inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-white shadow"
                    style="background: var(--primary);">Book a Tour</a>
                @endif
                <button @click="drawer = true"
                    class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl ring-1 ring-slate-200">
                    <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Progress bar (scroll indicator) --}}
    <div class="h-0.5 bg-slate-100 relative">
        <div class="absolute left-0 top-0 h-full"
            :style="`width:${progress}% ; background: linear-gradient(90deg, ${getComputedStyle($el).getPropertyValue('--primary')}, ${getComputedStyle($el).getPropertyValue('--accent')})`">
        </div>
    </div>

    {{-- MOBILE DRAWER --}}
    <div x-show="drawer" x-transition.opacity class="fixed inset-0 z-50 bg-black/40" @click="drawer=false"
        aria-hidden="true"></div>
    <aside x-show="drawer" x-transition
        class="fixed right-0 top-0 bottom-0 z-[60] w-[88%] max-w-sm bg-white shadow-2xl ring-1 ring-slate-200 overflow-y-auto">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div class="font-bold text-slate-900">Menu</div>
            <button @click="drawer=false"
                class="h-9 w-9 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <nav class="p-4">
            @foreach([
            ['About Us','about'],
            ['Our Services','services'],
            ['Rooms & Rates','rooms'],
            ['Photo Gallery','gallery'],
            ['News & Events','news'],
            ['Testimonials','testimonials'],
            ['Careers','careers'],
            ['FAQs','faqs'],
            ['Resources','resources'],
            ['Contact Us','contact'],
            ] as [$label,$id])
            <a href="#{{ $id }}" @click="drawer=false; setActive('{{ $id }}')"
                class="block px-3 py-3 rounded-xl text-slate-800 hover:bg-slate-50 mb-1"
                :class="activeSection==='{{ $id }}' ? 'ring-1 ring-[color:var(--primary)]' : ''">
                {{ $label }}
            </a>
            @endforeach
            <div class="mt-3 grid grid-cols-2 gap-2">
                <a href="#book" @click="drawer=false"
                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold text-white"
                    style="background: var(--primary);">Book a Tour</a>
                @if($phone)
                <a href="tel:{{ $facility['phone'] }}"
                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold ring-1"
                    style="color: var(--primary); border-color: var(--primary);">Call {{ $phone }}</a>
                @endif
            </div>
        </nav>
    </aside>
</header>

{{-- Minimal script (Alpine store) --}}
<script>
    function topbar(){
    return {
      scrolled: false,
      progress: 0,
      drawer: false,
      menus: { about:false, facilities:false, community:false, support:false },
      activeSection: '',
      sections: ['about','services','rooms','gallery','news','testimonials','careers','contact','faqs','resources','book'],

      init(){
        // Shadow + progress on scroll
        const onScroll = () => {
          const y = window.scrollY || document.documentElement.scrollTop;
          this.scrolled = y > 6;
          const doc = document.documentElement;
          const total = doc.scrollHeight - doc.clientHeight;
          this.progress = Math.min(100, Math.max(0, (y / (total || 1)) * 100));
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        // Scrollspy via IntersectionObserver
        const opts = { root: null, rootMargin: '-50% 0px -45% 0px', threshold: [0, 1.0] };
        const io = new IntersectionObserver((entries)=>{
          entries.forEach(e=>{
            if(e.isIntersecting){
              const id = e.target.getAttribute('id');
              if (this.sections.includes(id)) { this.activeSection = id; }
            }
          });
        }, opts);

        this.sections.forEach(id=>{
          const el = document.getElementById(id);
          if (el) io.observe(el);
        });

        // hash on load
        if (window.location.hash) {
          this.activeSection = window.location.hash.replace('#','');
        }
      },

      isActive(ids){
        return ids.includes(this.activeSection);
      },
      setActive(id){ this.activeSection = id; },
      open(key){ this.closeAll(); this.menus[key] = true; },
      close(key){ this.menus[key] = false; },
      toggle(key){ this.menus[key] = !this.menus[key]; },
      closeAll(){ Object.keys(this.menus).forEach(k => this.menus[k] = false); }
    }
  }
</script>
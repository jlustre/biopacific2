{{-- @php
if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->find($facility['color_scheme_id']);
$primary = $scheme->primary_color ?? '#0EA5E9';
$secondary = $scheme->secondary_color ?? '#1E293B';
$accent = $scheme->accent_color ?? '#F59E0B';
} else {
$primary = '#0EA5E9';
$secondary = '#1E293B';
$accent = '#F59E0B';
}
@endphp --}}

@if(isset($testimonials) && $testimonials && $testimonials->count() > 0)
<section id="testimonials" class="relative isolate overflow-hidden py-16 sm:py-24">
    {{-- Ambient brand backdrop --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/70"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data="testimonialsWall()" x-init="init()"
        style="--primary: {{ $primary }}; --accent: {{ $accent }}">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                class="text-primary border-primary">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $primary }}"></span>
                Voices from Our Community
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold" style="color: {{ $primary }}">What Families & Residents
                Say</h2>
            <p class="mt-2 md:text-lg" style="color: {{ $neutral_dark }}">Real experiences about our care,
                communication, and daily life.
            </p>
        </div>

        {{-- Top Summary + Filters --}}
        <div class="mt-8 grid gap-6 md:grid-cols-[0.9fr,1.1fr] items-center">
            {{-- Rating Summary --}}
            <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm" style="color: {{ $neutral_dark }}">Average Rating</div>
                        <div class="mt-1 flex items-end gap-3">
                            <div class="text-3xl font-extrabold text-slate-900" x-text="avgRating.toFixed(1)"></div>
                            <div class="flex items-center">
                                <template x-for="i in 5"><svg class="h-5 w-5"
                                        :class="i <= Math.round(avgRating) ? 'text-yellow-400' : 'text-slate-300'"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg></template>
                            </div>
                        </div>
                    </div>
                    <div class="w-32">
                        <div class="text-xs mb-1" style="color: {{ $neutral_dark }}">5-star share</div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full"
                                :style="`width:${fiveStarPct}%; background: linear-gradient(90deg, var(--primary), var(--accent));`">
                            </div>
                        </div>
                        <div class="mt-1 text-right text-[11px] text-slate-500" x-text="fiveStarPct.toFixed(0) + '%'">
                        </div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-xs" style="color: {{ $neutral_dark }}">
                    <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
                        <div class="uppercase" style="color: {{ $neutral_dark }}">Total</div>
                        <div class="mt-1 font-semibold text-slate-900" x-text="all.length"></div>
                    </div>
                    <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
                        <div class="uppercase" style="color: {{ $neutral_dark }}">Family</div>
                        <div class="mt-1 font-semibold text-slate-900" x-text="countBy('Family')"></div>
                    </div>
                    <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
                        <div class="uppercase" style="color: {{ $neutral_dark }}">Residents</div>
                        <div class="mt-1 font-semibold text-slate-900" x-text="countBy('Resident')"></div>
                    </div>
                </div>
            </div>

            {{-- Filters + Search --}}
            <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="setFilter('All')" class="px-3 py-1.5 rounded-full text-sm ring-1"
                        :class="filter==='All' ? 'text-white' : 'text-slate-700'"
                        :style="filter==='All' ? 'background: var(--primary); border-color: transparent;' : 'border-color:#e5e7eb;'">All</button>
                    <template x-for="cat in uniqueTags" :key="cat">
                        <button @click="setFilter(cat)" class="px-3 py-1.5 rounded-full text-sm ring-1"
                            :class="filter===cat ? 'text-white' : 'text-slate-700'"
                            :style="filter===cat ? 'background: var(--primary); border-color: transparent;' : 'border-color:#e5e7eb;'"
                            x-text="cat"></button>
                    </template>
                    <div class="ml-auto relative w-full sm:w-56">
                        <input x-model="query" type="search" placeholder="Search testimonials…"
                            class="w-full rounded-full border-slate-300 pl-10 pr-3 py-2 focus:border-slate-400 focus:ring-0 text-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="7" stroke-width="2" />
                            <path d="M21 21l-4.3-4.3" stroke-width="2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Featured Testimonial --}}
        <div class="mt-10 overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm">
            <div class="grid md:grid-cols-2 items-stretch">
                <div class="relative p-8 sm:p-12">
                    <div class="absolute top-6 left-6 text-slate-200">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z" />
                        </svg>
                    </div>
                    <div class="relative">
                        <template x-if="featured.title_header">
                            <div class="text-primary text-lg font-bold mb-2" x-text="featured.title_header"></div>
                        </template>
                        <div class="flex items-center gap-1 mb-4">
                            <template x-for="i in 5"><svg class="h-5 w-5"
                                    :class="i <= featured.rating ? 'text-yellow-400' : 'text-slate-300'"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </template>
                        </div>
                        <blockquote class="text-xl sm:text-2xl leading-relaxed text-slate-700" x-text="featured.text">
                        </blockquote>
                        <template x-if="featured.story">
                            <div class="mt-2 text-slate-700 text-base" x-text="featured.story"></div>
                        </template>
                        <div class="mt-6 flex items-center gap-4">
                            <img :src="featured.avatar" :alt="featured.name"
                                class="w-14 h-14 rounded-full object-cover ring-4 ring-white shadow">
                            <div>
                                <div class="font-semibold text-slate-900" x-text="featured.name"></div>
                                <div class="text-sm text-slate-500" x-text="featured.role"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="{{ asset('images/testimonials.png') }}" alt="Testimonials Photo"
                        class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/10 to-transparent"></div>
                </div>
            </div>
        </div>

        {{-- Wall of Love (Masonry-like grid) --}}
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <template x-for="(t, i) in filtered" :key="i">
                <article class="rounded-3xl ring-1 ring-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                    <template x-if="t.title_header">
                        <div class="text-primary text-base font-bold mb-1" x-text="t.title_header"></div>
                    </template>
                    <div class="flex items-start gap-3">
                        <img :src="t.avatar" :alt="t.name"
                            class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-slate-900" x-text="t.name"></div>
                                    <div class="text-xs text-slate-500" x-text="t.role"></div>
                                </div>
                                <div class="flex items-center">
                                    <template x-for="i in 5"><svg class="h-4 w-4"
                                            :class="i <= t.rating ? 'text-yellow-400' : 'text-slate-300'"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </template>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-slate-700 line-clamp-5" x-text="t.text"></p>
                            <template x-if="t.story">
                                <div class="mt-2 text-slate-700 text-xs" x-text="t.story"></div>
                            </template>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] ring-1"
                                    :style="`border-color:#e5e7eb; color:${t.tag==='Family' ? 'var(--primary)' : (t.tag==='Resident' ? '#0f766e' : '#6b21a8')}`"
                                    x-text="t.tag"></span>
                            </div>
                        </div>
                    </div>
                </article>
            </template>
        </div>

        {{-- CTA strip --}}
        <div
            class="mt-12 rounded-3xl bg-white ring-1 ring-slate-200 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="text-lg font-bold text-slate-900">Ready to see for yourself?</div>
                <p class="text-sm text-slate-600">Schedule a tour—meet the team and explore our community.</p>
            </div>
            <div class="flex gap-3">
                @if(!empty($activeSections) && in_array('book', $activeSections))
                <a href="#book"
                    class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow"
                    style="background: {{ $primary }}">Book a Tour</a>
                @endif
                <a href="#contact" class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold ring-2"
                    class="text-primary border-primary">Contact Us</a>
            </div>
        </div>
    </div>
</section>
@endif

<script>
    function testimonialsWall(){
    return {
      // Database testimonials
      all: @js(isset($testimonials) ? $testimonials->map(function($testimonial) {
                return [
                    'name' => $testimonial->name,
                    'role' => $testimonial->relationship,
                    'tag' => $testimonial->relationship,
                    'rating' => $testimonial->rating ?? 5,
                    'avatar' => $testimonial->photo_url ?? 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
                    'photo' => $testimonial->photo_url ?? 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=1200&fit=crop',
                    'text' => $testimonial->quote,
                    'title_header' => $testimonial->title_header,
                    'story' => $testimonial->story
                ];
      })->values() : []),
    filter: 'All',
    query: '',
    modal: { open:false, item:{} },
    featured: {},
    avgRating: 0,
    fiveStarPct: 0,
    uniqueTags: [],

      init(){
          // Pick a featured testimonial (highest rating or first)
          this.featured = this.all.find(t => t.rating === 5) || this.all[0];
          // Compute stats
          this.computeStats();
          // Get unique tags for filter buttons
          this.uniqueTags = [...new Set(this.all.map(t => t.tag))];
      },

      computeStats(){
        if (!this.all.length) { this.avgRating = 0; this.fiveStarPct = 0; return; }
        const sum = this.all.reduce((s,t) => s + (t.rating||0), 0);
        this.avgRating = sum / this.all.length;
        const five = this.all.filter(t => t.rating === 5).length;
        this.fiveStarPct = (five / this.all.length) * 100;
      },

      setFilter(tag){ this.filter = tag; },

      countBy(tag){ return this.all.filter(t => t.tag === tag).length; },

      get filtered(){
        const q = this.query.trim().toLowerCase();
        return this.all.filter(t => {
          const byTag = this.filter === 'All' ? true : t.tag === this.filter;
          const matches = !q || (t.name+' '+t.role+' '+t.text).toLowerCase().includes(q);
          return byTag && matches;
        });
      },

      openModal(item){ 
        this.modal.item = item; 
        this.modal.open = true; 
      }
    }
  }
</script>

<style>
    /* Hide elements with x-cloak until Alpine.js is loaded */
    [x-cloak] {
        display: none !important;
    }

    /* optional line clamp if Tailwind plugin not enabled */
    .line-clamp-5 {
        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
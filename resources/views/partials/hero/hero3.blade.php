{{-- HERO — Refined: Calm focus, glass card, accessible & responsive --}}
<section class="relative isolate overflow-hidden min-h-[78vh] md:min-h-screen">
    {{-- Background media --}}
    <div class="absolute inset-0 -z-10">
        <video id="heroBgVideo" class="absolute inset-0 h-full w-full object-cover" playsinline autoplay muted loop
            preload="auto" aria-hidden="true"
            poster="{{ asset($facility['hero_poster'] ?? 'images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}">
            @if(!empty($facility['hero_video_webm']))
            <source src="{{ asset($facility['hero_video_webm']) }}" type="video/webm">
            @endif
            <source src="{{ asset($facility['hero_video_mp4'] ?? 'videos/hero.mp4') }}" type="video/mp4">
        </video>

        {{-- Soft readable overlays --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/30 to-black/70"></div>
        <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-25"
            style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}"></div>
        <div class="absolute -bottom-28 -right-28 h-96 w-96 rounded-full blur-3xl opacity-20"
            style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></div>

        {{-- No-JS image fallback --}}
        <noscript>
            <img src="{{ asset($facility['hero_poster'] ?? 'images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}"
                alt="Residents and caregiver at {{ $facility['name'] ?? 'our facility' }}"
                class="absolute inset-0 h-full w-full object-cover">
        </noscript>
    </div>

    {{-- Content --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 md:py-28">
        <div class="max-w-4xl">
            {{-- Pill --}}
            <div
                class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/30">
                <span class="inline-block h-2.5 w-2.5 rounded-full"
                    style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></span>
                Family-centered • Evidence-based • Compassion
            </div>

            {{-- Glass card for better legibility --}}
            <div class="mt-5 rounded-3xl bg-white/10 backdrop-blur-md ring-1 ring-white/20 p-6 sm:p-8">
                {{-- Name + tagline row --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <h1 class="text-white font-extrabold leading-tight text-4xl md:text-6xl">
                        {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
                    </h1>
                </div>

                <p class="mt-3 md:mt-4 text-slate-100/95 md:text-xl max-w-2xl">
                    {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a
                    warm, dignified setting.' !!}
                </p>

                {{-- Quick stats chips --}}
                <div class="mt-5 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                        Beds Available: Limited
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                        Rehab • Memory Care • Hospice
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                        Tours Daily {{ $facility['hours'] ?? '9AM–7PM' }}
                    </span>
                </div>

                {{-- CTAs --}}
                <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <a href="#contact"
                        class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                        style="background-color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
                        Quick Contact
                    </a>

                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold bg-white/15 backdrop-blur text-white ring-1 ring-white/40 hover:bg-white/25 transition">
                        Book a Tour
                    </a>

                    <button id="playVideoBtn"
                        class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-slate-900 transition hover:brightness-110"
                        style="background-color: {{ $facility['accent_color'] ?? '#F59E0B' }}">
                        <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                </div>
            </div>

            {{-- Trust bar (optional) --}}
            <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-white/80">
                <span>Licensed & Accredited</span>
                <span class="h-1 w-1 rounded-full bg-white/40"></span>
                <span>24/7 Skilled Nursing</span>
                <span class="h-1 w-1 rounded-full bg-white/40"></span>
                <span>On-site Rehabilitation</span>
            </div>
        </div>
    </div>

    {{-- Modal for intro video --}}
    <div id="videoModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div class="relative w-full max-w-3xl">
            <button id="closeVideoBtn" class="absolute -top-12 right-0 text-white hover:text-red-400"
                aria-label="Close video">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="relative overflow-hidden rounded-2xl bg-black" style="padding-bottom:56.25%;height:0;">
                <iframe id="youtubeIframe" class="absolute top-0 left-0 h-full w-full" src="" title="Facility intro"
                    allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>

{{-- Reduced motion handling & small fixes --}}
<style>
    @media (prefers-reduced-motion: reduce) {
        #heroBgVideo {
            display: none;
        }

        /* Use poster as a background on the section itself for reduced motion users */
        .is-reduced-motion {
            background-image: url('{{ asset($facility[' hero_poster'] ?? ' images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}');
            background-size: cover;
            background-position: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const section = document.currentScript.closest('section') || document.querySelector('section[aria-label="hero"]') || document.querySelector('section');
    const bgVideo = document.getElementById('heroBgVideo');
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Respect reduced motion & autoplay blocking
    if (prefersReduced && section) section.classList.add('is-reduced-motion');
    if (!prefersReduced && bgVideo) {
      const p = bgVideo.play?.();
      if (p && typeof p.catch === 'function') p.catch(() => {/* leave poster */});
    }

    // Modal video controls
    const playBtn = document.getElementById('playVideoBtn');
    const modal = document.getElementById('videoModal');
    const closeBtn = document.getElementById('closeVideoBtn');
    const iframe = document.getElementById('youtubeIframe');
    const YT = @json($facility['hero_video_id'] ?? 'YOUR_YOUTUBE_VIDEO_ID');

    function openModal(){
      iframe.src = `https://www.youtube.com/embed/${YT}?autoplay=1&rel=0`;
      modal.classList.remove('hidden'); modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }
    function closeModal(){
      modal.classList.add('hidden'); modal.classList.remove('flex');
      document.body.style.overflow = '';
      iframe.src = '';
    }

    playBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) closeModal(); });
  });
</script>
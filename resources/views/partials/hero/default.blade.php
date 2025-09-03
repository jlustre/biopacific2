{{-- HERO — Version C: Full-width background video with image fallback --}}
<section class="relative min-h-[80vh] md:min-h-screen overflow-hidden isolate">
  {{-- Background media --}}
  <div class="absolute inset-0 -z-10">
    {{-- Video (autoplays silently; pauses for reduced motion) --}}
    <video id="heroBgVideo" class="absolute inset-0 h-full w-full object-cover" playsinline autoplay muted loop
      preload="auto"
      poster="{{ asset($facility['hero_poster'] ?? 'images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}"
      aria-hidden="true">
      @if(!empty($facility['hero_video_webm']))
      <source src="{{ asset($facility['hero_video_webm']) }}" type="video/webm">
      @endif
      <source src="{{ asset($facility['hero_video_mp4'] ?? 'videos/hero.mp4') }}" type="video/mp4">
      {{-- If the browser can't play the video, it will show the poster automatically --}}
    </video>

    {{-- Fallback image (for <noscript> or if video fails completely) --}}
      <noscript>
        <img
          src="{{ asset($facility['hero_poster'] ?? 'images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}"
          alt="Residents and caregiver at {{ $facility['name'] ?? 'our facility' }}"
          class="absolute inset-0 w-full h-auto max-w-full object-cover block" />
      </noscript>

      {{-- Readability overlays --}}
      <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/50"></div>
      <div class="pointer-events-none absolute -top-32 -left-24 h-80 w-80 rounded-full blur-3xl opacity-30"
        style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}"></div>
      <div class="pointer-events-none absolute -bottom-32 -right-24 h-96 w-96 rounded-full blur-3xl opacity-25"
        style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></div>
  </div>

  {{-- Content --}}
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 md:py-28">
    <div class="max-w-3xl">
      <span
        class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/30">
        <span class="inline-block h-2.5 w-2.5 rounded-full"
          style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></span>
        Family-centered • Evidence-based • Compassion
      </span>

      <h1 class="mt-4 text-4xl md:text-6xl font-black leading-tight"
        style="color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,.35), 0 8px 24px rgba(0,0,0,.25);">
        {{ $facility['headline'] ?? 'Where Comfort Meets Compassion' }}
      </h1>

      <p class="mt-4 md:text-xl text-slate-100/95 max-w-2xl">
        {{ $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a warm, dignified
        setting.' }}
      </p>

      <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <a href="#contact"
          class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
          style="background-color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
          Quick Contact
        </a>
        <a href="#book"
          class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold bg-white/15 backdrop-blur text-white ring-1 ring-white/40 hover:bg-white/25 transition"
          style="--btn: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
          Book a Tour
        </a>
        <button id="playVideoBtn"
          class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-slate-900 transition hover:brightness-110"
          style="background-color: {{ $facility['accent_color'] ?? '#F59E0B' }}">
          <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M8 5v10l8-5-8-5z" />
          </svg>
          Watch Intro
        </button>
      </div>

      {{-- Chips --}}
      <div class="mt-6 flex flex-wrap gap-2">
        <span
          class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-3 py-1 text-xs font-medium text-white ring-1 ring-white/30">
          Beds Available: Limited
        </span>
        <span
          class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-3 py-1 text-xs font-medium text-white ring-1 ring-white/30">
          Rehab • Memory Care • Hospice
        </span>
        <span
          class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-3 py-1 text-xs font-medium text-white ring-1 ring-white/30">
          Tours Daily {{ $facility['hours'] ?? '9AM–7PM' }}
        </span>
      </div>
    </div>
  </div>

  {{-- Modal for intro video (reusing your previous pattern) --}}
  <div id="videoModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="relative w-full max-w-3xl">
      <button id="closeVideoBtn" class="absolute -top-12 right-0 text-white hover:text-red-400">
        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <div class="relative overflow-hidden rounded-2xl bg-black" style="padding-bottom:56.25%;height:0;">
        <iframe id="youtubeIframe" class="absolute top-0 left-0 h-full w-full" src="" frameborder="0"
          allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
      </div>
    </div>
  </div>
</section>

{{-- Styles to handle prefers-reduced-motion (pause video, keep poster) --}}
<style>
  @media (prefers-reduced-motion: reduce) {
    #heroBgVideo {
      display: none;
    }

    /* Poster still shows as background via poster attr? Not if hidden—so: */
  }

  /* Alternative: use an overlay image when reduced motion is on */
  @media (prefers-reduced-motion: reduce) {
    .reduced-motion-fallback {
      background-image: url('{{ asset($facility[' hero_poster'] ?? ' images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}');
      background-size: cover;
      background-position: center;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const bgVideo = document.getElementById('heroBgVideo');

    // Respect reduced motion and autoplay blocking
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReduced && bgVideo) {
      try { bgVideo.pause(); } catch(e) {}
      // Optional: add a class to section to ensure image fallback if needed
      bgVideo.closest('section')?.classList.add('reduced-motion-fallback');
    } else if (bgVideo) {
      // Some browsers block autoplay; try play() and swallow the promise rejection
      const tryPlay = bgVideo.play();
      if (tryPlay && typeof tryPlay.catch === 'function') {
        tryPlay.catch(() => { /* leave poster visible; nothing else to do */ });
      }
    }

    // Modal video controls
    const playBtn = document.getElementById('playVideoBtn');
    const modal = document.getElementById('videoModal');
    const closeBtn = document.getElementById('closeVideoBtn');
    const iframe = document.getElementById('youtubeIframe');
    const YT = '{{ $facility['hero_video_id'] ?? 'YOUR_YOUTUBE_VIDEO_ID' }}';

    function openModal(){
      iframe.src = `https://www.youtube.com/embed/${YT}?autoplay=1&rel=0`;
      modal.classList.remove('hidden'); modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }
    function closeModal(){
      modal.classList.add('hidden'); modal.classList.remove('flex');
      document.body.style.overflow = ''; iframe.src = '';
    }

    playBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) closeModal(); });
  });
</script>
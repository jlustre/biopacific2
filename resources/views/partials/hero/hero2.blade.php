{{-- HERO — Curved Mask Image, Right-Rail Content, Vertical Facts --}}
@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
// Build poster image URL for background
$posterFilename = $facility['hero_image_url'] ?? null;
$poster = !empty($posterFilename) ? url('images/' . $posterFilename) : asset('images/hero1.jpg');
$hasVideo = !empty($facility['hero_video_id']);
@endphp

<section class="relative isolate overflow-hidden">
  {{-- Brand glows --}}
  <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-20"
    style="background: {{ $primary }}"></div>
  <div class="pointer-events-none absolute -bottom-28 -right-28 h-96 w-96 rounded-full blur-3xl opacity-15"
    style="background: {{ $accent }}"></div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-12 gap-8 md:gap-10 items-stretch pt-10 md:pt-14 pb-8 md:pb-12">

      {{-- Left: Curved masked image --}}
      <div class="lg:col-span-7 relative">
        <div class="relative h-[46vh] md:h-[60vh] lg:h-[72vh] overflow-hidden">
          {{-- Curved mask via clip-path --}}
          <div class="absolute inset-0 [clip-path:ellipse(120%_85%_at_0%_50%)]">
            <img src="{{ $poster }}" alt="Residents and caregivers at {{ $facility['name'] ?? 'our facility' }}"
              class="h-full w-full object-cover object-center">
            {{-- Gentle readable gradient on the right edge to meet content column --}}
            <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-transparent"></div>
          </div>

          {{-- Vertical facts rail (sticks within the image on larger screens) --}}
          <div class="absolute bottom-4 left-4 flex flex-col gap-2">
            <div
              class="rounded-xl bg-white/70 backdrop-blur ring-1 ring-white/60 px-3 py-2 text-[12px] font-semibold text-slate-900">
              Tours: {{ $facility['hours'] ?? '9AM–7PM' }}
            </div>
            <div
              class="rounded-xl bg-white/70 backdrop-blur ring-1 ring-white/60 px-3 py-2 text-[12px] font-semibold text-slate-900">
              {{ ($facility['city'] ?? '') }}@if(!empty($facility['state'])), {{ $facility['state'] }}@endif
            </div>
          </div>

        </div>
        {{-- Trust chips --}}
        <div class="mt-5 flex flex-wrap gap-2 justify-center">
          <span
            class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
            <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
              <path d="M9 16.2l-3.5-3.5 1.41-1.4L9 13.8l7.09-7.09 1.41 1.41z" />
            </svg>
            Evidence-based
          </span>
          <span
            class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
            <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5c0-3.08 2.43-5.5 5.5-5.5a6 6 0 014.5 2.09A6 6 0 0119 3c3.07 0 5.5 2.42 5.5 5.5 0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
            Compassion-first
          </span>
          <span
            class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
            <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z" />
            </svg>
            Family-centered
          </span>
        </div>
      </div>

      {{-- Right: Content column --}}
      <div class="lg:col-span-5 flex">
        <div
          class="relative flex-1 flex flex-col justify-center rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm p-6 sm:p-8">

          {{-- Tagline --}}
          <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1"
            style="color: {{ $primary }}; border-color: {{ $primary }};">
            <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
            {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
          </span>

          {{-- Headline --}}
          <h1 class="mt-4 text-3xl md:text-5xl font-black leading-tight text-slate-900">
            {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
          </h1>

          {{-- Subheadline --}}
          <p class="mt-3 md:mt-4 text-slate-600 md:text-lg">
            {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a warm,
            dignified setting.' !!}
          </p>

          {{-- Action tray --}}
          <div class="mt-7 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="#book"
              class="inline-flex items-center justify-center rounded-xl px-3 py-2 font-semibold text-white shadow-lg hover:shadow-xl transition"
              style="background: {{ $primary }}">Book a Tour</a>

            <a href="#contact"
              class="inline-flex items-center justify-center rounded-xl px-3 py-2 font-semibold ring-2 hover:bg-slate-50 transition"
              style="color: {{ $primary }}; border-color: {{ $primary }}">Contact Us</a>

            @if($hasVideo)
            <button id="playVideoBtn"
              class="inline-flex items-center justify-center rounded-xl px-3 py-2 font-semibold text-white hover:brightness-110 transition"
              style="background: {{ $accent }}">
              <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M8 5v10l8-5-8-5z" />
              </svg>
              Intro
            </button>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Reassurance bar - moved below the image --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
      <div class="text-xs text-slate-500 flex flex-wrap justify-center gap-x-3 gap-y-1">
        <span class="inline-flex items-center gap-1">
          <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
          </svg>
          Licensed & Accredited
        </span>
        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
        <span class="inline-flex items-center gap-1">
          <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M12 3a9 9 0 019 9c0 4.5-3.5 8.2-8 8.9-4.5-.7-8-4.4-8-8.9a9 9 0 019-9zm0 2a7 7 0 00-7 7c0 3.6 2.8 6.6 6.4 7.1.2 0 .4 0 .6 0 3.6-.5 6.4-3.5 6.4-7.1a7 7 0 00-7-7zm0 3a4 4 0 014 4c0 2.2-1.8 4-4 4s-4-1.8-4-4a4 4 0 014-4z" />
          </svg>
          24/7 Skilled Nursing
        </span>
        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
        <span class="inline-flex items-center gap-1">
          <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20.285 6.709l-8.285 8.285-4.285-4.285-1.415 1.415 5.7 5.7 9.7-9.7z" />
          </svg>
          On-site Rehabilitation
        </span>
      </div>

    </div>
  </div>

  {{-- Optional: Video Modal --}}
  @if($hasVideo)
  <div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center hidden">
    <div class="relative w-full max-w-3xl mx-4">
      <div class="relative overflow-hidden rounded-2xl bg-black" style="padding-bottom:56.25%;height:0;">
        <!-- Close button positioned inside video container for better visibility -->
        <button id="closeVideoBtn"
          class="absolute top-2 right-2 z-20 text-white hover:text-red-400 bg-black/70 rounded-full p-2 transition-colors duration-200"
          aria-label="Close video">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <iframe id="youtubeIframe" class="absolute top-0 left-0 h-full w-full" src="" title="Intro video"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen frameborder="0"></iframe>
      </div>
    </div>
  </div>
  @endif
</section>

@push('styles')
<style>
  /* Ensure modal is above everything */
  #videoModal {
    z-index: 9999;
  }

  /* Disable scrolling when modal is open */
  body.modal-open {
    overflow: hidden;
  }
</style>
@endpush

<script>
  document.addEventListener('DOMContentLoaded', function() {
    @if($hasVideo)
    // Video modal functionality
    console.log('Hero2: Initializing video modal...');
    const playVideoBtn = document.getElementById('playVideoBtn');
    const videoModal = document.getElementById('videoModal');
    const closeVideoBtn = document.getElementById('closeVideoBtn');
    const youtubeIframe = document.getElementById('youtubeIframe');

    // Get YouTube video ID from database
    const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
    
    console.log('Hero2: Elements found:', {
        playVideoBtn: !!playVideoBtn,
        videoModal: !!videoModal,
        closeVideoBtn: !!closeVideoBtn,
        youtubeIframe: !!youtubeIframe,
        youtubeVideoId: youtubeVideoId
    });

    if (playVideoBtn && youtubeVideoId) {
        console.log('Hero2: Setting up video functionality');
        playVideoBtn.addEventListener('click', function() {
            console.log('Hero2: Play button clicked');
            // Set the YouTube URL with autoplay
            youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
            videoModal.classList.remove('hidden');
            videoModal.classList.add('flex');
            document.body.classList.add('modal-open');
        });

        function closeModal() {
            console.log('Hero2: Closing modal');
            videoModal.classList.add('hidden');
            videoModal.classList.remove('flex');
            document.body.classList.remove('modal-open');
            // Stop the video by clearing the src
            youtubeIframe.src = '';
        }

        closeVideoBtn.addEventListener('click', closeModal);

        // Close modal when clicking outside the video
        videoModal.addEventListener('click', function(e) {
            if (e.target === videoModal) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !videoModal.classList.contains('hidden')) {
                closeModal();
            }
        });
    } else {
        console.log('Hero2: Video setup failed - missing elements or video ID');
    }
    @endif
});
</script>
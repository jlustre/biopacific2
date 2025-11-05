{{-- HERO — Version C: Full-width background video with image fallback --}}
@php
// Color variables ($primary, $secondary, $accent) are now passed from the controller.
// Build poster image URL for video background
$posterFilename = $facility['hero_image_url'] ?? null;
if (!empty($posterFilename)) {
$poster = url('images/' . $posterFilename);
} else {
$poster = asset('images/hero1.jpg');
}
$hasVideo = !empty($facility['hero_video_id']);
@endphp

<section class="relative min-h-[80vh] md:min-h-screen overflow-hidden isolate">
  {{-- Background media --}}
  <div class="absolute inset-0 -z-10">
    {{-- Video (autoplays silently; pauses for reduced motion) --}}
    <video id="heroBgVideo" class="absolute inset-0 h-full w-full object-cover" playsinline autoplay muted loop
      preload="auto" poster="{{ $poster }}" aria-hidden="true">
      @if(!empty($facility['hero_video_webm']))
      <source src="{{ asset($facility['hero_video_webm']) }}" type="video/webm">
      @endif
      {{--
      <source src="{{ asset($facility['hero_video_mp4'] ?? 'videos/hero.mp4') }}" type="video/mp4"> --}}
      {{-- If the browser can't play the video, it will show the poster automatically --}}
    </video>

    {{-- Fallback image (for <noscript> or if video fails completely) --}}
      <noscript>
        <img src="{{ $poster }}" alt="Residents and caregiver at {{ $facility['name'] ?? 'our facility' }}"
          class="absolute inset-0 w-full h-auto max-w-full object-cover block" />
      </noscript>

      {{-- Readability overlays --}}
      <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/50"></div>

      <div class="pointer-events-none absolute -top-32 -left-24 h-80 w-80 rounded-full blur-3xl opacity-30"
        style="background: {{ $primary }}"></div>
      <div class="pointer-events-none absolute -bottom-32 -right-24 h-96 w-96 rounded-full blur-3xl opacity-25"
        style="background: {{ $accent }}"></div>
  </div>

  {{-- Content --}}
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 md:mt-36">
    <div class="max-w-2xl">
      <span
        class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/30 justify-center md:justify-start">
        <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
        Family-centered • Evidence-based • Compassion
      </span>

      <h1 class="mt-4 text-4xl md:text-6xl font-black leading-tight"
        style="color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,.35), 0 8px 24px rgba(0,0,0,.25);">
        {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
      </h1>

      <p class="mt-4 md:text-xl text-slate-100/95 max-w-2xl">
        {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a warm, dignified
        setting.' !!}
      </p>

      <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <a href="#contact"
          class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition hover:brightness-120 bg-primary"
          style="background-color: {{ $primary }};">
          Quick Contact
        </a>
        @php
        $activeSections = $active_sections ?? ($facility['active_sections'] ?? []);
        if (is_string($activeSections)) {
        $activeSections = json_decode($activeSections, true) ?: [];
        } elseif ($activeSections instanceof \Illuminate\Support\Collection) {
        $activeSections = $activeSections->toArray();
        } elseif (!is_array($activeSections)) {
        $activeSections = (array) $activeSections;
        }
        @endphp
        <a href="#book"
          class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold border-2 shadow-lg transition-all duration-200"
          style="
            color: {{ $secondary }};
            border-color: {{ $secondary }};
            background: linear-gradient(135deg, white 0%, #fff8 100%);
            box-shadow: 0 2px 8px 0 {{ $secondary }}22;
          "
          onmouseover="this.style.background='linear-gradient(135deg, {{ $secondary }}33 0%, #fff 100%)'; this.style.color='#fff'; this.style.borderColor='{{ $secondary }}'; this.style.boxShadow='0 4px 16px 0 {{ $secondary }}44';"
          onmouseout="this.style.background='linear-gradient(135deg, white 0%, #fff8 100%)'; this.style.color='{{ $secondary }}'; this.style.borderColor='{{ $secondary }}'; this.style.boxShadow='0 2px 8px 0 {{ $secondary }}22';">
          Book a Tour
        </a>

        @if(!empty($facility['hero_video_id']))
        <button id="playVideoBtn"
          class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-white transition hover:brightness-120"
          style="background: {{ $neutral_dark }}; color: {{ $neutral_light }}; transition: background-color 0.3s, color 0.3s;">
          <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M8 5v10l8-5-8-5z" />
          </svg>
          Watch Intro
        </button>
        @endif
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
  @if(!empty($facility['hero_video_id']))
  <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#F59E0B'" />
  @endif
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
      background-image: url('{{ $poster }}');
      background-size: cover;
      background-position: center;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
  var bgVideo = document.getElementById('heroBgVideo');
  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReduced && bgVideo) {
    try { bgVideo.pause(); } catch(e) {}
    bgVideo.closest('section')?.classList.add('reduced-motion-fallback');
  } else if (bgVideo) {
    var tryPlay = bgVideo.play();
    if (tryPlay && typeof tryPlay.catch === 'function') {
      tryPlay.catch(function(){});
    }
  }

});
</script>
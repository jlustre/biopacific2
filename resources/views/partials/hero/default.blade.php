{{-- HERO — Version C: Full-width background video with image fallback --}}
@php
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
      <source src="{{ asset($facility['hero_video_mp4'] ?? 'videos/hero.mp4') }}" type="video/mp4">
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
        style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}"></div>
      <div class="pointer-events-none absolute -bottom-32 -right-24 h-96 w-96 rounded-full blur-3xl opacity-25"
        style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></div>
  </div>

  {{-- Content --}}
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 md:py-28">
    <div class="max-w-2xl">
      <span
        class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/30">
        <span class="inline-block h-2.5 w-2.5 rounded-full"
          style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></span>
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
          class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
          style="background-color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
          Quick Contact
        </a>
        <a href="#book"
          class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold bg-white/15 backdrop-blur text-white ring-1 ring-white/40 hover:bg-white/25 transition"
          style="--btn: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
          Book a Tour
        </a>
        @if(!empty($facility['hero_video_id']))
        <button id="playVideoBtn"
          class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-white transition hover:brightness-110"
          style="background-color: {{ $facility['accent_color'] ?? '#F59E0B' }}">
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
</section>

@if(!empty($facility['hero_video_id']))
<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center hidden">
  <div class="relative w-full max-w-4xl mx-4">
    <!-- Video container -->
    <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%; height: 0;">
      <!-- Prominent close button positioned inside video area -->
      <button id="closeVideoBtn"
        class="absolute top-4 right-4 text-white hover:text-red-400 transition-colors duration-200 z-10 bg-black/50 backdrop-blur rounded-full p-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      <iframe id="youtubeIframe" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen></iframe>
    </div>
  </div>
</div>
@endif

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

  /* Ensure modal is above everything */
  #videoModal {
    z-index: 9999;
  }

  /* Disable scrolling when modal is open */
  body.modal-open {
    overflow: hidden;
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

  @if(!empty($facility['hero_video_id']))
  // Video modal functionality
  console.log('Default: Video functionality initializing...');
  const playVideoBtn = document.getElementById('playVideoBtn');
  const videoModal = document.getElementById('videoModal');
  const closeVideoBtn = document.getElementById('closeVideoBtn');
  const youtubeIframe = document.getElementById('youtubeIframe');

  console.log('Default: Elements found:', {
    playVideoBtn: !!playVideoBtn,
    videoModal: !!videoModal,
    closeVideoBtn: !!closeVideoBtn,
    youtubeIframe: !!youtubeIframe
  });

  // Get YouTube video ID from database
  const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
  console.log('Default: Video ID:', youtubeVideoId);

  if (playVideoBtn && videoModal && closeVideoBtn && youtubeIframe && youtubeVideoId) {
      console.log('Default: Setting up video functionality');
      playVideoBtn.addEventListener('click', function() {
          console.log('Default: Button clicked!');
          // Set the YouTube URL with autoplay
          youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
          videoModal.classList.remove('hidden');
          videoModal.classList.add('flex');
          document.body.classList.add('modal-open');
          console.log('Default: Modal should be open now');
      });

      function closeModal() {
          console.log('Default: Closing modal');
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
      console.log('Default: Setup failed - missing elements or video ID');
      console.log('Missing elements:', {
          playVideoBtn: !playVideoBtn,
          videoModal: !videoModal,
          closeVideoBtn: !closeVideoBtn,
          youtubeIframe: !youtubeIframe,
          youtubeVideoId: !youtubeVideoId
      });
  }
  @endif
});
</script>
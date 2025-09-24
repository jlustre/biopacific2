{{-- HERO — Refined: Calm focus, glass card, accessible & responsive --}}
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

<section class="relative isolate overflow-hidden min-h-[78vh] md:min-h-screen">
    {{-- Background media --}}
    <div class="absolute inset-0 -z-10">
        <video id="heroBgVideo" class="absolute inset-0 h-full w-full object-cover" playsinline autoplay muted loop
            preload="auto" aria-hidden="true" poster="{{ $poster }}">
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
            <img src="{{ $poster }}" alt="Residents and caregiver at {{ $facility['name'] ?? 'our facility' }}"
                class="absolute inset-0 h-full w-full object-cover">
        </noscript>
    </div>

    {{-- Content --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-20 pb-16 md:py-28">
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

                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-slate-900 transition hover:brightness-110"
                        style="background-color: {{ $facility['accent_color'] ?? '#F59E0B' }}">
                        <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                    @endif
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

    @if(!empty($facility['hero_video_id']))
    <!-- Video Modal -->
    <div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center hidden">
        <div class="relative w-full max-w-4xl mx-4">
            <!-- Prominent close button -->
            <button id="closeVideoBtn"
                class="absolute -top-12 right-0 text-white hover:text-red-400 transition-colors duration-200 z-10">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <!-- Video container -->
            <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%; height: 0;">
                <iframe id="youtubeIframe" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </div>
        </div>
    </div>
    @endif
</section>

{{-- Reduced motion handling & small fixes --}}
<style>
    @media (prefers-reduced-motion: reduce) {
        #heroBgVideo {
            display: none;
        }

        /* Use poster as a background on the section itself for reduced motion users */
        .is-reduced-motion {
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
  console.log('Hero3: Video functionality initializing...');
  const playVideoBtn = document.getElementById('playVideoBtn');
  const videoModal = document.getElementById('videoModal');
  const closeVideoBtn = document.getElementById('closeVideoBtn');
  const youtubeIframe = document.getElementById('youtubeIframe');

  console.log('Hero3: Elements found:', {
    playVideoBtn: !!playVideoBtn,
    videoModal: !!videoModal,
    closeVideoBtn: !!closeVideoBtn,
    youtubeIframe: !!youtubeIframe
  });

  // Get YouTube video ID from database
  const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
  console.log('Hero3: Video ID:', youtubeVideoId);

  if (playVideoBtn && videoModal && closeVideoBtn && youtubeIframe && youtubeVideoId) {
      console.log('Hero3: Setting up video functionality');
      playVideoBtn.addEventListener('click', function() {
          console.log('Hero3: Button clicked!');
          // Set the YouTube URL with autoplay
          youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
          videoModal.classList.remove('hidden');
          videoModal.classList.add('flex');
          document.body.classList.add('modal-open');
          console.log('Hero3: Modal should be open now');
      });

      function closeModal() {
          console.log('Hero3: Closing modal');
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
      console.log('Hero3: Setup failed - missing elements or video ID');
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
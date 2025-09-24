{{-- HERO — Split with Angled Media Panel + Compact Actions --}}
@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';

// Build poster image URL - try multiple approaches for compatibility
$posterFilename = $facility['hero_image_url'] ?? null;
if (!empty($posterFilename)) {
// Method 1: Try direct public path construction
$poster = url('images/' . $posterFilename);
} else {
$poster = asset('images/hero1.jpg');
}
$hasVideo = !empty($facility['hero_video_id']);
@endphp

<section class="relative isolate overflow-hidden bg-gradient-to-b from-white via-slate-50 to-slate-100 py-10 md:py-16">
    {{-- Soft brand glows --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-20"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-15"
            style="background: {{ $accent }}"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-10 items-center">
            {{-- Left: Content --}}
            <div class="lg:col-span-6 xl:col-span-5">
                {{-- Tagline pill --}}
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1 bg-white"
                    style="color: {{ $primary }}; border-color: {{ $primary }};">
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                    {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
                </span>

                <h1 class="mt-4 text-4xl md:text-5xl xl:text-6xl font-black text-slate-900 leading-[1.05]">
                    {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
                </h1>

                <p class="mt-3 md:mt-4 text-slate-700 md:text-lg max-w-xl">
                    {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a
                    warm, dignified setting.' !!}
                </p>

                {{-- Quick trust chips --}}
                <div class="mt-5 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-medium ring-1 ring-slate-200">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z" />
                        </svg>
                        Family-centered
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-medium ring-1 ring-slate-200">
                        <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.2l-3.5-3.5 1.41-1.4L9 13.8l7.09-7.09 1.41 1.41z" />
                        </svg>
                        Evidence-based
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-medium ring-1 ring-slate-200">
                        <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.43 3 7.5 3c1.74 0 3.41.81 4.5 2.09A6.002 6.002 0 0119 3c3.07 0 5.5 2.42 5.5 5.5 0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        Compassion-first
                    </span>
                </div>

                {{-- CTAs --}}
                <div class="mt-7 flex flex-col sm:flex-row sm:items-center gap-3">
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                        style="background: {{ $primary }}">Book a Tour</a>

                    <a href="#contact"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold bg-white text-slate-900 ring-1 ring-slate-200 hover:bg-slate-50 transition">
                        Quick Contact
                    </a>

                    @if($hasVideo)
                    <button id="playVideoBtn"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-3 font-semibold bg-white text-slate-900 ring-1 ring-slate-200 hover:bg-slate-50 transition"
                        aria-label="Watch intro video">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                    @endif
                </div>

                {{-- Thin facts row --}}
                <dl class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-xl bg-white ring-1 ring-slate-200 p-3">
                        <dt class="text-slate-500">Beds</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">{{ $facility['beds'] ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl bg-white ring-1 ring-slate-200 p-3">
                        <dt class="text-slate-500">Tours</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</dd>
                    </div>
                    <div class="rounded-xl bg-white ring-1 ring-slate-200 p-3 col-span-2 sm:col-span-1">
                        <dt class="text-slate-500">Location</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">
                            {{ ($facility['city'] ?? '') }}@if(!empty($facility['state'])), {{ $facility['state']
                            }}@endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Right: Angled Media Panel + Mini Action Card --}}
            <div class="lg:col-span-6 xl:col-span-7">
                <div class="relative">
                    {{-- Angled container --}}
                    <div class="relative h-[360px] sm:h-[440px] md:h-[520px]">
                        <div
                            class="absolute inset-0 [clip-path:polygon(6%_0,100%_0,100%_100%,0%_100%)] overflow-hidden rounded-3xl ring-1 ring-slate-200 shadow-lg bg-slate-200">
                            <img src="{{ $poster }}" alt="Residents and caregiver" class="h-full w-full object-cover"
                                onerror="console.error('Image failed to load:', this.src); this.src='{{ asset('images/hero1.jpg') }}';">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/10 to-transparent">
                            </div>
                        </div>
                    </div>

                    {{-- Floating mini-card --}}
                    <div class="absolute -bottom-6 left-6 right-6 md:left-auto md:right-6 md:w-[340px]">
                        <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-xl p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl"
                                    style="background: linear-gradient(135deg, {{ $primary }}, {{ $accent }});"></div>
                                <div class="text-sm">
                                    <div class="font-semibold text-slate-900">Talk with our team</div>
                                    <div class="text-slate-500">We’ll guide you step-by-step</div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="#book"
                                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold text-white shadow hover:shadow-md"
                                    style="background: {{ $primary }}">Book a Tour</a>
                                <a href="#contact"
                                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold ring-1 hover:bg-slate-50"
                                    style="color: {{ $primary }}; border-color: {{ $primary }}">Contact</a>
                            </div>
                            @if(!empty($facility['phone']))
                            <a href="tel:{{ $facility['phone'] }}"
                                class="mt-3 block text-center text-xs text-slate-600 underline">Or call {{
                                $facility['phone'] }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Video Modal --}}
    @if($hasVideo)
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
  @if($hasVideo)
  // Video modal functionality
  console.log('Hero5: Video functionality initializing...');
  const playVideoBtn = document.getElementById('playVideoBtn');
  const videoModal = document.getElementById('videoModal');
  const closeVideoBtn = document.getElementById('closeVideoBtn');
  const youtubeIframe = document.getElementById('youtubeIframe');

  console.log('Hero5: Elements found:', {
    playVideoBtn: !!playVideoBtn,
    videoModal: !!videoModal,
    closeVideoBtn: !!closeVideoBtn,
    youtubeIframe: !!youtubeIframe
  });

  // Get YouTube video ID from database
  const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
  console.log('Hero5: Video ID:', youtubeVideoId);

  if (playVideoBtn && videoModal && closeVideoBtn && youtubeIframe && youtubeVideoId) {
      console.log('Hero5: Setting up video functionality');
      playVideoBtn.addEventListener('click', function() {
          console.log('Hero5: Button clicked!');
          // Set the YouTube URL with autoplay
          youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
          videoModal.classList.remove('hidden');
          videoModal.classList.add('flex');
          document.body.classList.add('modal-open');
          console.log('Hero5: Modal should be open now');
      });

      function closeModal() {
          console.log('Hero5: Closing modal');
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
      console.log('Hero5: Setup failed - missing elements or video ID');
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
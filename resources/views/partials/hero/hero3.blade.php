{{-- HERO — Center-Stacked, Parallax Image, Sticky Action Bar --}}
@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
// Build poster image URL for background
$posterFilename = $facility['hero_image_url'] ?? null;
if (!empty($posterFilename)) {
$poster = url('images/' . $posterFilename);
} else {
$poster = asset('images/hero1.jpg');
}
$hasVideo = !empty($facility['hero_video_id']);
@endphp

<section class="relative isolate overflow-hidden min-h-[78vh] md:min-h-screen">
    {{-- Background: image with subtle parallax / fixed on desktop --}}
    <div class="absolute inset-0 -z-10">
        <div class="hidden md:block absolute inset-0 bg-fixed bg-cover bg-top"
            style="background-image:url('{{ $poster }}')"></div>
        <img src="{{ $poster }}" alt="Residents and caregivers at {{ $facility['name'] ?? 'our facility' }}"
            class="md:hidden absolute inset-0 w-full h-full object-cover object-top">

        {{-- Framing gradients for contrast (top+bottom, not full overlay) --}}
        <div class="absolute inset-x-0 top-0 h-40 md:h-64 bg-gradient-to-b from-black/50 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 md:h-64 bg-gradient-to-t from-black/60 to-transparent"></div>

        {{-- Soft brand glows (very faint) --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-15"
            style="background: {{ $accent }}"></div>
    </div>

    {{-- Content (centered) --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="min-h-[78vh] md:min-h-screen flex items-center">
            <div class="w-full text-center">
                {{-- Tagline pill --}}
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1 bg-white/90 backdrop-blur-sm"
                    style="color: {{ $primary }}; border-color: {{ $primary }};">
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                    {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
                </span>

                {{-- Headline --}}
                <h1
                    class="mt-4 text-white drop-shadow-[0_2px_12px_rgba(0,0,0,.35)] font-black leading-[1.05] text-4xl md:text-6xl">
                    {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
                </h1>

                {{-- Subheadline --}}
                <p class="mt-3 md:mt-4 text-slate-50/95 md:text-xl max-w-3xl mx-auto">
                    {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a
                    warm, dignified setting.' !!}
                </p>

                {{-- Floating stat chips (auto-wrap) --}}
                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/45 backdrop-blur px-3 py-1 text-xs font-medium ring-1 ring-white/60 text-slate-900">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z" />
                        </svg>
                        Family-centered
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/45 backdrop-blur px-3 py-1 text-xs font-medium ring-1 ring-white/60 text-slate-900">
                        <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.2l-3.5-3.5 1.41-1.4L9 13.8l7.09-7.09 1.41 1.41z" />
                        </svg>
                        Evidence-based
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/45 backdrop-blur px-3 py-1 text-xs font-medium ring-1 ring-white/60 text-slate-900">
                        <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.43 3 7.5 3c1.74 0 3.41.81 4.5 2.09A6.002 6.002 0 0119 3c3.07 0 5.5 2.42 5.5 5.5 0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        Compassion-first
                    </span>
                </div>

                {{-- Primary CTAs (stack on mobile) --}}
                <div class="mt-7 mb-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-200 hover:brightness-110 hover:scale-[1.02]"
                        style="background: {{ $primary }}">Book a Tour</a>

                    <a href="#contact"
                        class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold bg-white/60 backdrop-blur text-slate-900 ring-2 ring-white/80 hover:bg-white/80 hover:ring-white transition-all duration-200">
                        Quick Contact
                    </a>

                    @if($hasVideo)
                    <button id="playVideoBtn"
                        class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-white backdrop-blur ring-1 ring-white/30 hover:brightness-110 hover:scale-[1.02] transition-all duration-200"
                        style="background: {{ $accent }}">
                        <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Optional lightweight video modal --}}
    @if($hasVideo)
    <div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center hidden">
        <div class="relative w-full max-w-3xl mx-4">
            <div class="relative overflow-hidden rounded-2xl bg-black" style="padding-bottom:56.25%;height:0;">
                <!-- Close button positioned inside video container for better visibility -->
                <button id="closeVideoBtn"
                    class="absolute top-2 right-2 z-20 text-white hover:text-red-400 bg-black/70 rounded-full p-2 transition-colors duration-200"
                    aria-label="Close video">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M6 18L18 6M6 6l12 12" />
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
    @media (prefers-reduced-motion: reduce) {
        /* Turn off bg-fixed on reduced motion (handled by <img> already) */
    }
</style>
@endpush

@push('styles')
<style>
    @media (prefers-reduced-motion: reduce) {
        /* Turn off bg-fixed on reduced motion (handled by <img> already) */
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
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
    @if($hasVideo)
    // Video modal functionality
    console.log('Hero6: Initializing video modal...');
    const playVideoBtn = document.getElementById('playVideoBtn');
    const videoModal = document.getElementById('videoModal');
    const closeVideoBtn = document.getElementById('closeVideoBtn');
    const youtubeIframe = document.getElementById('youtubeIframe');

    // Get YouTube video ID from database
    const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
    
    console.log('Hero6: Elements found:', {
        playVideoBtn: !!playVideoBtn,
        videoModal: !!videoModal,
        closeVideoBtn: !!closeVideoBtn,
        youtubeIframe: !!youtubeIframe,
        youtubeVideoId: youtubeVideoId
    });

    if (playVideoBtn && youtubeVideoId) {
        console.log('Hero6: Setting up video functionality');
        playVideoBtn.addEventListener('click', function() {
            console.log('Hero6: Play button clicked');
            // Set the YouTube URL with autoplay
            youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
            videoModal.classList.remove('hidden');
            videoModal.classList.add('flex');
            document.body.classList.add('modal-open');
        });

        function closeModal() {
            console.log('Hero6: Closing modal');
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
        console.log('Hero6: Video setup failed - missing elements or video ID');
    }
    @endif
});
</script>
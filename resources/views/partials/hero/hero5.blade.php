{{-- HERO — Minimal Left Rail, wide background --}}
<section class="relative isolate overflow-hidden">
    {{-- Background image --}}
    <div class="absolute inset-0 -z-10">
        <img src="{{ asset($facility['hero_poster'] ?? 'images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}"
            alt="Residents and caregiver at {{ $facility['name'] ?? 'our facility' }}"
            class="h-[74vh] md:h-[92vh] w-full object-cover">
        {{-- Edge gradient for legibility (left side only) --}}
        <div
            class="absolute inset-y-0 left-0 w-[70%] md:w-[55%] bg-gradient-to-r from-black/70 via-black/45 to-transparent">
        </div>
        {{-- Brand glows (very subtle so image stays visible) --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-20"
            style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full blur-3xl opacity-15"
            style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></div>
    </div>

    {{-- Content rail (left aligned, no big card) --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="min-h-[74vh] md:min-h-[92vh] flex items-center">
            <div class="max-w-2xl pr-6">
                {{-- Tagline pill --}}
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/30 bg-white/10 backdrop-blur">
                    <span class="inline-block h-2.5 w-2.5 rounded-full"
                        style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></span>
                    {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
                </span>

                <h1 class="mt-4 text-white font-extrabold leading-tight text-4xl md:text-6xl">
                    {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
                </h1>

                <p class="mt-4 md:text-xl text-white/90">
                    {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a
                    warm, dignified setting.' !!}
                </p>

                {{-- CTAs (compact) --}}
                <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <a href="#contact"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                        style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
                        Quick Contact
                    </a>
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold bg-white/15 backdrop-blur text-white ring-1 ring-white/40 hover:bg-white/25 transition">
                        Book a Tour
                    </a>
                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="inline-flex justify-center items-center rounded-xl px-5 py-3 font-semibold text-white transition hover:brightness-110"
                        style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}">
                        <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                    @endif
                </div>

                {{-- Chips (thin row) --}}
                <div class="mt-5 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                        Beds: {{ $facility['beds'] ?? '—' }}
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                        Tours {{ $facility['hours'] ?? '9AM–7PM' }}
                    </span>
                    @if(!empty($facility['hero_video_id']))
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20 cursor-pointer"
                        onclick="document.getElementById('playVideoBtn')?.click()">
                        <svg class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </span>
                    @endif
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white ring-1 ring-white/20">
                        {{ ($facility['city'] ?? '') }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Optional: video modal --}}
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    var YT = @json($facility['hero_video_id'] ?? null);
    var playBtn = document.getElementById('playVideoBtn');
    var modal = document.getElementById('videoModal');
    var closeBtn = document.getElementById('closeVideoBtn');
    var iframe = document.getElementById('youtubeIframe');

    function openModal() {
        if (!YT) return;
        iframe.src = `https://www.youtube.com/embed/${YT}?autoplay=1&rel=0`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        iframe.src = '';
    }

    if (playBtn) playBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
});
</script>
@endpush
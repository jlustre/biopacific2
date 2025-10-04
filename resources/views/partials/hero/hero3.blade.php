@php
$posterFilename = $facility['hero_image_url'] ?? null;
if (!empty($posterFilename)) {
$poster = url('images/' . $posterFilename);
} else {
$poster = asset('images/hero1.jpg');
}
@endphp

<section class="relative isolate overflow-hidden min-h-[78vh] md:min-h-screen">
    <!-- Background: image with subtle parallax / fixed on desktop -->
    <div class="absolute inset-0 -z-10">
        <div class="hidden md:block absolute inset-0 bg-fixed bg-cover bg-top"
            style="background-image:url('{{ $poster }}')"></div>
        <img src="{{ $poster }}" alt="Residents and caregivers at {{ $facility['name'] ?? 'our facility' }}"
            class="md:hidden absolute inset-0 w-full h-full object-cover object-top">

        <!-- Framing gradients for contrast (top+bottom, not full overlay) -->
        <div class="absolute inset-x-0 top-0 h-40 md:h-64 bg-gradient-to-b from-black/50 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 md:h-64 bg-gradient-to-t from-black/60 to-transparent"></div>

        <!-- Soft brand glows (very faint) -->
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-15"
            style="background: {{ $accent }}"></div>
    </div>

    <!-- Content (centered) -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="min-h-[78vh] md:min-h-screen flex items-center">
            <div class="w-full text-center">
                <!-- Tagline pill -->
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1 bg-white/90 backdrop-blur-sm text-primary border-primary">
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                    {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
                </span>

                <!-- Headline -->
                <h1
                    class="mt-4 text-white drop-shadow-[0_2px_12px_rgba(0,0,0,.35)] font-black leading-[1.05] text-4xl md:text-6xl">
                    {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
                </h1>

                <!-- Subheadline -->
                <p class="mt-3 md:mt-4 text-slate-50/95 md:text-xl max-w-3xl mx-auto">
                    {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a
                    warm, dignified setting.' !!}
                </p>

                <!-- Floating stat chips (auto-wrap) -->
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

                <!-- Primary CTAs (stack on mobile) -->
                <div class="mt-7 mb-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-200 hover:brightness-110 hover:scale-[1.02]"
                        style="background: {{ $primary }}">Book a Tour</a>

                    <a href="#contact"
                        class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold bg-white/60 backdrop-blur text-slate-900 ring-2 ring-white/80 hover:bg-white/80 hover:ring-white transition-all duration-200"
                        style="border-color: {{ $secondary }}; color: {{ $secondary }};">
                        Quick Contact
                    </a>

                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="inline-flex justify-center items-center rounded-2xl px-5 py-3 font-semibold text-white backdrop-blur ring-1 ring-white/30 hover:brightness-110 hover:scale-[1.02] transition-all duration-200"
                        style="background: {{ $accent }};">
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
    @if(!empty($facility['hero_video_id']))
    <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#F59E0B'"
        background="rgba(0,0,0,0.75)" />
    @endif
</section>
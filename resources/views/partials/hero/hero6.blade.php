{{-- HERO — Diagonal Split, Gradient Strip, KPIs Column --}}
@php
// Color variables ($primary, $secondary, $accent) are now passed from the controller.
// Background poster
$posterFilename = $facility['hero_image_url'] ?? null;
$poster = !empty($posterFilename) ? url('images/' . $posterFilename) : asset('images/hero1.jpg');
$hasVideo = !empty($facility['hero_video_id']);
@endphp

<section class="relative isolate overflow-hidden">

    {{-- Background image with diagonal mask --}}
    <div class="absolute inset-0 -z-10">
        <img src="{{ $poster }}" alt="Residents and caregivers at {{ $facility['name'] ?? 'our facility' }}"
            class="h-[82vh] md:h-[96vh] w-full object-cover object-center">
        {{-- Diagonal split overlay (reveals image on right) --}}
        <div class="absolute inset-0 bg-gradient-to-tr from-white via-white/20 to-white/10"></div>
        <div class="absolute inset-0 [clip-path:polygon(0%_0%,62%_0%,48%_100%,0%_100%)]"
            style="background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.75));"></div>

        {{-- Soft brand dots pattern on the strip --}}
        <div class="absolute left-0 top-0 bottom-0 w-[56%] pointer-events-none opacity-30" style="background-image: radial-gradient(circle at 1px 1px, {{ $primary }} 1px, transparent 1px);
                background-size: 14px 14px;">
        </div>

        {{-- Brand glows --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-20"
            style="background: {{ $primary }}"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-28 h-96 w-96 rounded-full blur-3xl opacity-20"
            style="background: {{ $accent }}"></div>

        {{-- Phone number positioned at top right of the image, behind modal overlay --}}
        <div class="absolute top-6 right-6 z-0">
            @if(!empty($facility['phone']))
            <a href="tel:{{ $facility['phone'] }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white/90 backdrop-blur px-4 py-3 text-slate-800 hover:bg-white shadow-lg hover:shadow-xl transition text-lg font-semibold">
                <svg class="h-5 w-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11.05 11.05 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2H18C9.72 21 3 14.28 3 6V5z" />
                </svg>
                Call Us: {{ preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $facility['phone']) }}
            </a>
            @endif
        </div>
    </div>

    {{-- Content container --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Phone number positioned at top right of the image --}}
        <div class="absolute top-6 right-6 z-10">
            @if(!empty($facility['phone']))
            <a href="tel:{{ $facility['phone'] }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white/90 backdrop-blur px-4 py-3 text-slate-800 hover:bg-white shadow-lg hover:shadow-xl transition text-lg font-semibold">
                <svg class="h-5 w-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11.05 11.05 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2H18C9.72 21 3 14.28 3 6V5z" />
                </svg>
                Call Us: {{ preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $facility['phone']) }}
            </a>
            @endif
        </div>

        <div class="grid lg:grid-cols-12 gap-8 md:gap-10 items-center min-h-[82vh] md:min-h-[96vh] py-10 md:py-0">

            {{-- Left: Copy on translucent gradient strip --}}
            <div class="lg:col-span-7">
                <div class="max-w-2xl">
                    {{-- Tagline pill --}}
                    <span
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1 bg-white"
                        class="text-primary border-primary">
                        <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                        {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
                    </span>

                    <h1 class="mt-4 text-4xl md:text-6xl font-black leading-[1.06]" style="color: {{ $primary }}">
                        {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
                    </h1>

                    <p class="mt-3 md:mt-4 text-slate-600 md:text-lg">
                        {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a
                        warm, dignified setting.' !!}
                    </p>

                    {{-- Micro trust chips --}}
                    <div class="mt-5 flex flex-wrap gap-2">
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
                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5c0-3.08 2.43-5.5 5.5-5.5 1.74 0 3.41.81 4.5 2.09A6 6 0 0119 3c3.07 0 5.5 2.42 5.5 5.5 0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                            Compassion-first
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
                            <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z" />
                            </svg>
                            Family-centered
                        </span>
                    </div>

                    {{-- CTAs --}}
                    <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
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
                        @if(!empty($activeSections) && in_array('book', $activeSections))
                        <a href="#book"
                            class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                            style="background: {{ $primary }}">Book a Tour</a>
                        @endif

                        <a href="#contact"
                            class="inline-flex justify-center items-center rounded-2xl px-6 py-3 font-semibold ring-2 hover:bg-slate-50 transition"
                            style="border-color: {{ $secondary }}; color: {{ $secondary }};">Quick Contact</a>
                        @if(!empty($facility['hero_video_id']))
                        <button id="playVideoBtn"
                            class="inline-flex items-center justify-center rounded-2xl px-5 py-3 font-semibold text-white hover:brightness-110 transition"
                            style="background: {{ $accent }}">
                            <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 5v10l8-5-8-5z" />
                            </svg>
                            Watch Intro
                        </button>
                        @endif
                    </div>
                    {{-- On small screens, show tours/location in one row below buttons, aligned left/right --}}
                    <div class="block md:hidden mt-2 mb-2">
                        <div class="flex justify-between gap-2 max-w-[350px] mx-auto">
                            <div
                                class="rounded-xl bg-white/30 backdrop-blur ring-1 ring-slate-200/30 shadow-sm p-1 md:p-3 w-[48%]">
                                <div class="text-[10px] text-slate-600 uppercase tracking-wide">Tours</div>
                                <div class="mt-0.5 text-sm font-bold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM'
                                    }}</div>
                            </div>
                            <div
                                class="rounded-xl bg-white/30 backdrop-blur ring-1 ring-slate-200/30 shadow-sm p-1 md:p-3 w-[48%] text-right">
                                <div class="text-[10px] text-slate-600 uppercase tracking-wide">Location</div>
                                <div class="mt-0.5 text-sm font-bold text-slate-900">
                                    {{ ($facility['city'] ?? '') }}@if(!empty($facility['state'])), {{
                                    $facility['state'] }}@endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tours and Location cards: responsive positioning --}}
        <div class="absolute bottom-6 right-6 z-10 hidden md:block mb-2">
            <div class="grid grid-cols-1 gap-2 max-w-[220px]">
                <div class="rounded-xl bg-white/30 backdrop-blur ring-1 ring-slate-200/30 shadow-sm p-3">
                    <div class="text-[10px] text-slate-600 uppercase tracking-wide">Tours</div>
                    <div class="mt-0.5 text-sm font-bold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</div>
                </div>
                <div class="rounded-xl bg-white/30 backdrop-blur ring-1 ring-slate-200/30 shadow-sm p-3">
                    <div class="text-[10px] text-slate-600 uppercase tracking-wide">Location</div>
                    <div class="mt-0.5 text-sm font-bold text-slate-900">
                        {{ ($facility['city'] ?? '') }}@if(!empty($facility['state'])), {{ $facility['state'] }}@endif
                    </div>
                </div>
            </div>
        </div>


    </div>

    @if(!empty($facility['hero_video_id']))
    <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#F59E0B'"
        background="rgba(0,0,0,0.75)" />
    @endif
</section>
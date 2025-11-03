{{-- HERO — Split with Angled Media Panel + Compact Actions --}}
@php
// Color variables ($primary, $secondary, $accent) are now passed from the controller.
// Build poster image URL - try multiple approaches for compatibility
$posterFilename = $facility['hero_image_url'] ?? null;
if (!empty($posterFilename)) {
// Method 1: Try direct public path construction
$poster = url('images/' . $posterFilename);
} else {
$poster = asset('images/hero1.jpg');
}
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
                    class="text-primary border-primary">
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                    {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
                </span>

                <h1 class="mt-4 text-3xl md:text-4xl xl:text-5xl font-black leading-[1.05]"
                    style="color: {{ $primary }}">
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

                    @if(!empty($activeSections) && in_array('book', $activeSections))
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg hover:shadow-xl transition-all duration-200 hover:brightness-110"
                        style="background: {{ $primary }}">Book a Tour</a>
                    @endif

                    <a href="#contact"
                        class="inline-flex justify-center items-center rounded-xl px-4 py-2 text-sm font-semibold bg-transparent ring-1 transition-all duration-200 hover:bg-white/10 hover:backdrop-blur"
                        style="border-color: {{ $secondary }}; color: {{ $secondary }};">
                        Quick Contact
                    </a>

                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20 transition-all duration-200 hover:brightness-110"
                        style="background: {{ $neutral_dark }}; color: {{ $neutral_light }}"
                        aria-label="Watch intro video">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
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
                        <div class="rounded-2xl bg-white/80 backdrop-blur ring-1 ring-slate-200 shadow-xl p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl"
                                    style="background: linear-gradient(135deg, {{ $primary }}, {{ $accent }});"></div>
                                <div class="text-sm">
                                    <div class="font-semibold text-slate-900">Talk with our team</div>
                                    <div class="text-slate-500">We’ll guide you step-by-step</div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                @if(!empty($activeSections) && in_array('book', $activeSections))
                                <a href="#book"
                                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold text-white shadow hover:shadow-md"
                                    style="background: {{ $primary }}">Book a Tour</a>
                                @endif
                                <a href="#contact"
                                    class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold ring-1 hover:bg-slate-50"
                                    style="border-color: {{ $secondary }}; color: {{ $secondary }};">Contact</a>
                            </div>
                            @if(!empty($facility['phone']))
                            @php
                            // Format phone number (assumes 10-digit US format)
                            $phone = preg_replace('/\D/', '', $facility['phone']);
                            $formatted_phone = strlen($phone) === 10 ?
                            '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4) :
                            $facility['phone'];
                            @endphp
                            <a href="tel:{{ $facility['phone'] }}"
                                class="mt-3 block text-center text-xs text-slate-600 underline">Or call {{
                                $formatted_phone }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(!empty($facility['hero_video_id']))
    {{-- Video Modal (Reusable Component) --}}
    <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#F59E0B'"
        background="rgba(0,0,0,0.75)" />
    @endif
</section>
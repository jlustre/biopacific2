{{-- HERO — Alt Design: Split layout, framed image, soft texture --}}
@php
$poster = !empty($facility['hero_image_url'])
? asset('images/'.$facility['hero_image_url'])
: ($facility['hero_config']['background_image'] ??
asset('images/hero1.jpg'));
@endphp

<section class="relative isolate overflow-hidden">
    {{-- Subtle texture / brand glow --}}
    <div
        class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,0.7),transparent_60%)]">
    </div>
    {{-- Color variables ($primary, $secondary, $accent) are now passed from the controller. --}}
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-20"
        style="background: {{ $primary }}"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full blur-3xl opacity-15"
        style="background: {{ $accent }}"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 bg-yellow-50">
        <div class="grid lg:grid-cols-[1.2fr_1fr] gap-y-10 gap-x-12 items-center justify-center py-4 pt-0 md:py-20">
            {{-- LEFT: Copy + CTAs --}}
            <div class="order-2 lg:order-1">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    class="text-primary border-current">
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                    {{ $facility['hero_config']['badge'] ?? 'Family-centered • Evidence-based • Compassion' }}
                </span>

                <h1 class="mt-4 font-extrabold leading-tight text-4xl md:text-6xl" style="color: {{ $primary }}">
                    {!! $facility['headline'] ?? $facility['hero_config']['title'] ?? 'Where Comfort Meets Compassion'
                    !!}
                </h1>

                <p class="mt-4 md:text-xl text-slate-600 max-w-2xl">
                    {!! $facility['subheadline'] ?? $facility['hero_config']['subtitle'] ?? 'Skilled nursing,
                    rehabilitation, memory care, and hospice in a warm, dignified setting.' !!}
                </p>

                {{-- CTA Row --}}
                <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @if(!empty($facility['hero_config']['button_text']) &&
                    !empty($facility['hero_config']['button_link']))
                    <a href="{{ $facility['hero_config']['button_link'] }}"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                        style="background-color: {{ $primary }}">
                        {{ $facility['hero_config']['button_text'] }}
                    </a>
                    @endif

                    @if(!empty($activeSections) && in_array('book', $activeSections))
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold ring-1 transition"
                        style="border-color: {{ $secondary }}; color: {{ $secondary }};">
                        Book a Tour
                    </a>
                    @endif
                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="cursor-pointer inline-flex justify-center items-center rounded-xl px-5 py-3 font-semibold text-slate-900 transition hover:brightness-110"
                        style="background: {{ $neutral_dark }}; color: {{ $neutral_light }}">
                        <svg class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                    @endif
                </div>

                {{-- Info bar --}}
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-lg border bg-white px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Beds</div>
                        <div class="text-lg font-semibold text-slate-900">{{ $facility['beds'] ?? '—' }}</div>
                    </div>
                    <div class="rounded-lg border bg-white px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Tours</div>
                        <div class="text-lg font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</div>
                    </div>
                    <div class="rounded-lg border bg-white px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Location</div>
                        <div class="text-lg font-semibold text-slate-900">
                            {{ ($facility['city'] ?? '') }}{{ isset($facility['state']) ? ', '.$facility['state'] : ''
                            }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Framed image card --}}
            <div class="order-1 lg:order-2">
                <div class="relative">
                    {{-- Decorative corner stripe --}}
                    <div class="absolute -top-6 -left-6 h-16 w-16 rotate-12 rounded-lg opacity-20"
                        style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}"></div>

                    <figure class="relative rounded-[28px] overflow-hidden ring-1 ring-slate-200 shadow-xl bg-white">
                        <img src="{{ $poster }}"
                            alt="Residents and caregiver at {{ $facility['name'] ?? 'our facility' }}"
                            class="h-80 w-full object-cover md:h-[28rem]" loading="lazy"
                            srcset="{{ $poster }} 1200w, {{ $poster }} 800w" sizes="(max-width: 768px) 100vw, 1200px">

                        <figcaption class="absolute inset-x-0 bottom-0">
                            <div
                                class="m-3 rounded-2xl bg-white/60 backdrop-blur ring-1 ring-slate-200 px-4 py-3 flex items-center justify-between">
                                <div class="text-sm">
                                    <div class="font-semibold text-slate-900">{{ $facility['name'] ?? 'Our Facility' }}
                                    </div>
                                    <div class="text-slate-600">
                                        {{ ($facility['address'] ?? '') }}{{ isset($facility['city']) ? ',
                                        '.$facility['city'] : '' }}
                                    </div>
                                </div>
                                <a href="#contact"
                                    class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-white"
                                    style="background: {{ $primary }}">
                                    Contact
                                </a>
                            </div>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($facility['hero_video_id']))
    <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#F59E0B'"
        background="rgba(0,0,0,0.75)" />
    @endif
</section>
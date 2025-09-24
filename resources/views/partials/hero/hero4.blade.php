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
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-20"
        style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full blur-3xl opacity-15"
        style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-[1.2fr_1fr] gap-y-10 gap-x-12 items-center justify-center py-14 md:py-20">
            {{-- LEFT: Copy + CTAs --}}
            <div class="order-2 lg:order-1">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    style="color: {{ $facility['primary_color'] ?? '#0EA5E9' }}; border-color: currentColor;">
                    <span class="inline-block h-2.5 w-2.5 rounded-full"
                        style="background: {{ $facility['accent_color'] ?? '#F59E0B' }}"></span>
                    {{ $facility['hero_config']['badge'] ?? 'Family-centered • Evidence-based • Compassion' }}
                </span>

                <h1 class="mt-4 text-slate-900 font-extrabold leading-tight text-4xl md:text-6xl">
                    {!! $facility['hero_config']['title'] ?? 'Where Comfort Meets Compassion' !!}
                </h1>

                <p class="mt-4 md:text-xl text-slate-600 max-w-2xl">
                    {!! $facility['hero_config']['subtitle'] ?? 'Skilled nursing, rehabilitation, memory care, and
                    hospice in a warm, dignified setting.' !!}
                </p>

                {{-- CTA Row --}}
                <div class="mt-7 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @if(!empty($facility['hero_config']['button_text']) &&
                    !empty($facility['hero_config']['button_link']))
                    <a href="{{ $facility['hero_config']['button_link'] }}"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                        style="background-color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
                        {{ $facility['hero_config']['button_text'] }}
                    </a>
                    @endif
                    <a href="#book"
                        class="inline-flex justify-center items-center rounded-xl px-6 py-3 font-semibold ring-1 transition"
                        style="color: {{ $facility['primary_color'] ?? '#0EA5E9' }}; border-color: currentColor;">
                        Book a Tour
                    </a>
                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="inline-flex justify-center items-center rounded-xl px-5 py-3 font-semibold text-slate-900 transition hover:brightness-110"
                        style="background-color: {{ $facility['accent_color'] ?? '#F59E0B' }}">
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
                            class="h-80 w-full object-cover md:h-[28rem]">

                        <figcaption class="absolute inset-x-0 bottom-0">
                            <div
                                class="m-3 rounded-2xl bg-white/90 backdrop-blur ring-1 ring-slate-200 px-4 py-3 flex items-center justify-between">
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
                                    style="background: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
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
    {{-- Optional: video modal (if you provide hero_video_id) --}}
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
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        @if(!empty($facility['hero_video_id']))
        const playBtn = document.getElementById('playVideoBtn');
        const modal = document.getElementById('videoModal');
        const closeBtn = document.getElementById('closeVideoBtn');
        const iframe = document.getElementById('youtubeIframe');
        const YT = @json($facility['hero_video_id'] ?? null);
    
        if (playBtn && modal && YT) {
            function openModal(){
              iframe.src = `https://www.youtube.com/embed/${YT}?autoplay=1&rel=0`;
              modal.classList.remove('hidden'); modal.classList.add('flex');
              document.body.style.overflow = 'hidden';
            }
            function closeModal(){
              modal.classList.add('hidden'); modal.classList.remove('flex');
              document.body.style.overflow = '';
              iframe.src = '';
            }
        
            playBtn?.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
            document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) closeModal(); });
        }
        @endif
      });
    </script>
</section>
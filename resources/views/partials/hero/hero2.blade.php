{{-- HERO — Curved Mask Image, Right-Rail Content, Vertical Facts --}}
@php
// Color variables ($primary, $secondary, $accent) are now passed from the controller.
// Build poster image URL for background
$posterFilename = $facility['hero_image_url'] ?? null;
$poster = !empty($posterFilename) ? url('images/' . $posterFilename) : asset('images/hero1.jpg');
$hasVideo = !empty($facility['hero_video_id']);
@endphp

<section class="relative isolate overflow-hidden">
  {{-- Brand glows --}}
  <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-20"
    style="background: {{ $primary }}"></div>
  {{-- Color variables are now passed from the controller. --}}

  <div class="grid lg:grid-cols-12 gap-8 md:gap-10 items-stretch pt-10 md:pt-14 pb-8 md:pb-12">

    {{-- Left: Curved masked image --}}
    <div class="lg:col-span-7 relative">
      <div class="relative h-[46vh] md:h-[60vh] lg:h-[72vh] overflow-hidden">
        {{-- Curved mask via clip-path --}}
        <div class="absolute inset-0 [clip-path:ellipse(120%_85%_at_0%_50%)]">
          <img src="{{ $poster }}" alt="Residents and caregivers at {{ $facility['name'] ?? 'our facility' }}"
            class="h-full w-full object-cover object-center">
          {{-- Gentle readable gradient on the right edge to meet content column --}}
          <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-transparent"></div>
        </div>

        {{-- Vertical facts rail (sticks within the image on larger screens) --}}
        <div class="absolute bottom-4 left-4 flex flex-col gap-2">
          <div
            class="rounded-xl bg-white/70 backdrop-blur ring-1 ring-white/60 px-3 py-2 text-[12px] font-semibold text-slate-900">
            Tours: {{ $facility['hours'] ?? '9AM–7PM' }}
          </div>
          <div
            class="rounded-xl bg-white/70 backdrop-blur ring-1 ring-white/60 px-3 py-2 text-[12px] font-semibold text-slate-900">
            {{ ($facility['city'] ?? '') }}@if(!empty($facility['state'])), {{ $facility['state'] }}@endif
          </div>
        </div>

      </div>
      {{-- Trust chips --}}
      <div class="mt-5 flex flex-wrap gap-2 justify-center">
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
              d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5c0-3.08 2.43-5.5 5.5-5.5a6 6 0 014.5 2.09A6 6 0 0119 3c3.07 0 5.5 2.42 5.5 5.5 0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
          </svg>
          Compassion-first
        </span>
        <span
          class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
          <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.25l-7.19-.61L12 2 9.19 8.64 2 9.25l5.46 4.72L5.82 21z" />
          </svg>
          Family-centered
        </span>
      </div>
    </div>

    {{-- Right: Content column --}}
    <div class="lg:col-span-5 flex">
      <div
        class="relative flex-1 flex flex-col justify-center rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm p-6 sm:p-8">

        {{-- Tagline --}}
        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1"
          class="text-primary border-primary">
          <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
          {{ $facility['tagline'] ?? 'Guided by Compassion. Focused on You.' }}
        </span>

        {{-- Headline --}}
        <h1 class="mt-4 text-3xl md:text-5xl font-black leading-tight text-slate-900">
          {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
        </h1>

        {{-- Subheadline --}}
        <p class="mt-3 md:mt-4 text-slate-600 md:text-lg">
          {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a warm,
          dignified setting.' !!}
        </p>

        {{-- Action tray --}}
        <div class="mt-7 grid grid-cols-1 sm:grid-cols-3 gap-3">
          <a href="#book"
            class="inline-flex items-center justify-center rounded-xl px-3 py-2 font-semibold text-white shadow-lg hover:shadow-xl transition"
            style="background: {{ $primary }}">Book a Tour</a>

          <a href="#contact"
            class="inline-flex items-center justify-center rounded-xl px-3 py-2 font-semibold ring-2 hover:bg-slate-50 transition"
            style="border-color: {{ $secondary }}; color: {{ $secondary }};">Contact Us</a>

          @if(!empty($facility['hero_video_id']))
          <button id="playVideoBtn" class="inline-flex items-center rounded-xl px-5 py-3 text-white font-medium"
            style="background-color: {{ $accent }};">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8 5v10l8-5-8-5z" />
            </svg>
            Watch Intro
          </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Reassurance bar - moved below the image --}}
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
    <div class="text-xs text-slate-500 flex flex-wrap justify-center gap-x-3 gap-y-1">
      <span class="inline-flex items-center gap-1">
        <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
        </svg>
        Licensed & Accredited
      </span>
      <span class="h-1 w-1 rounded-full bg-slate-300"></span>
      <span class="inline-flex items-center gap-1">
        <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="currentColor">
          <path
            d="M12 3a9 9 0 019 9c0 4.5-3.5 8.2-8 8.9-4.5-.7-8-4.4-8-8.9a9 9 0 019-9zm0 2a7 7 0 00-7 7c0 3.6 2.8 6.6 6.4 7.1.2 0 .4 0 .6 0 3.6-.5 6.4-3.5 6.4-7.1a7 7 0 00-7-7zm0 3a4 4 0 014 4c0 2.2-1.8 4-4 4s-4-1.8-4-4a4 4 0 014-4z" />
        </svg>
        24/7 Skilled Nursing
      </span>
      <span class="h-1 w-1 rounded-full bg-slate-300"></span>
      <span class="inline-flex items-center gap-1">
        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
          <path d="M20.285 6.709l-8.285 8.285-4.285-4.285-1.415 1.415 5.7 5.7 9.7-9.7z" />
        </svg>
        On-site Rehabilitation
      </span>
    </div>

  </div>
  </div>

  {{-- Video Modal (Reusable Component) --}}
  <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#F59E0B'"
    background="rgba(0,0,0,0.75)" />
</section>
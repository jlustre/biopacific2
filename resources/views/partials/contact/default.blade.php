@php
use Illuminate\Support\Str;

// Color scheme logic
$primary = $primary ?? '#0EA5E9';
$secondary = $secondary ?? '#1E293B';
$accent = $accent ?? '#F59E0B';

// Social links
$facility['social'] = [
'facebook' => 'https://facebook.com/yourpage',
'x' => 'https://x.com/yourhandle',
'linkedin' => 'https://linkedin.com/company/yourcompany',
'instagram' => 'https://instagram.com/yourhandle',
'youtube' => 'https://youtube.com/@yourhandle',
];
@endphp
{{-- CONTACT — Side-by-side on md+, with inline SVG social icons --}}
<section id="contact" class="relative overflow-hidden py-20 md:py-28">
  {{-- Background / brand blobs --}}
  <div class="pointer-events-none absolute inset-0 -z-10">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
    <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-25"
      style="background: {{ $primary }}"></div>
    <div class="absolute -bottom-28 -right-24 h-96 w-96 rounded-full blur-3xl opacity-20"
      style="background: {{ $accent }}"></div>
  </div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    @include('partials.section_header', [
    'section_header' => 'Get in Touch',
    'section_sub_header' => "Have questions other than booking a tour, use this section instead? We're here to help you
    every step of the way."
    ])

    @php
    // Phone formatting
    $p_raw = preg_replace('/\D/','', $facility['phone'] ?? '');
    $p_fmt = $p_raw ? sprintf('(%s) %s-%s', substr($p_raw,0,3), substr($p_raw,3,3), substr($p_raw,6,4)) : 'N/A';

    // Maps link fallback
    $mapsEmbed = $facility['location_map'] ?? null;
    $mapsHref = (isset($mapsEmbed) && Str::startsWith($mapsEmbed, ['http://','https://']))
    ? $mapsEmbed
    : 'https://www.google.com/maps?q=' . urlencode(trim(($facility['address']??'').' '.($facility['city']??'').'
    '.($facility['state']??'').' '.($facility['zip']??'')));
    @endphp

    {{-- 1 col on small, 3 cols on md+ --}}
    @php
    $isBioPacific = false;
    if (isset($facility)) {
    if ((is_array($facility) && isset($facility['id']) && $facility['id'] == 99) || (is_object($facility) &&
    isset($facility->id) && $facility->id == 99)) {
    $isBioPacific = true;
    }
    }
    @endphp
    <div class="mt-10 grid gap-8 {{ $isBioPacific ? 'md:grid-cols-2' : 'md:grid-cols-3' }} items-start">

      {{-- Contact Information (with social icons) --}}
      <aside class="rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-xl p-6 sm:p-8 h-full">
        <h3 class="text-2xl font-extrabold text-slate-900">Contact Information</h3>
        <p class="mt-1 text-sm text-slate-600">We usually respond within 1 business day.</p>

        <dl class="mt-6 space-y-6">
          {{-- Phone --}}
          <div class="flex gap-4">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl"
              style="background: {{ $primary }}1A; color: {{ $primary }};">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h2.6a1 1 0 01.95.69l1.3 3.9a1 1 0 01-.47 1.2l-1.8.9a10.5 10.5 0 005.3 5.3l.9-1.8a1 1 0 011.2-.47l3.9 1.3a1 1 0 01.69.95V19a2 2 0 01-2 2h-1C9.16 21 3 14.84 3 7V5z" />
              </svg>
            </span>
            <div>
              <dt class="text-sm font-semibold text-slate-700">Phone</dt>
              <dd class="text-lg font-semibold text-slate-900">
                @if($p_raw)
                <a href="tel:{{ $p_raw }}" class="hover:underline">{{ $p_fmt }}</a>
                @else N/A @endif
              </dd>
            </div>
          </div>

          {{-- Email --}}
          <div class="flex gap-4">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl"
              style="background: {{ $secondary }}1A; color: {{ $secondary }};">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.9 4.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5A2 2 0 003 7v10a2 2 0 002 2z" />
              </svg>
            </span>
            <div>
              <dt class="text-sm font-semibold text-slate-700">Email</dt>
              <dd class="text-lg font-medium">
                @if(!empty($facility['email']))
                <a href="mailto:{{ $facility['email'] }}" class="text-slate-900 hover:text-slate-700 hover:underline">
                  {{ $facility['email'] }}
                </a>
                @else N/A @endif
              </dd>
            </div>
          </div>

          {{-- Address --}}
          <div class="flex gap-4">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl"
              style="background: {{ $accent }}1A; color: {{ $accent }};">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.7 17.7L12 23l-5.7-5.3A8 8 0 1117.7 17.7z" />
                <circle cx="12" cy="11" r="3" />
              </svg>
            </span>
            <div>
              <dt class="text-sm font-semibold text-slate-700">Address</dt>
              <dd class="text-slate-900">
                {{ $facility['address'] ?? '' }}{{ !empty($facility['city']) ? ', ' . $facility['city'] : '' }}{{
                !empty($facility['state']) ? ', ' . $facility['state'] : '' }} {{ $facility['zip'] ?? '' }}
              </dd>
            </div>
          </div>

          {{-- Visiting Hours --}}
          <div class="flex gap-4">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
            <div>
              <dt class="text-sm font-semibold text-slate-700">Visiting Hours</dt>
              <dd class="text-slate-900">{{ $facility['hours'] ?? '10:30 AM – 8:30 PM' }}</dd>
            </div>
          </div>
        </dl>

        {{-- Social Icons (inline SVG, only shown if provided) --}}
        @php $social = $facility['social'] ?? []; @endphp
        @if(!empty($social))
        <div class="mt-8">
          <h4 class="text-sm font-semibold text-slate-700 mb-3">Connect With Us</h4>
          <div class="flex flex-wrap gap-3">
            @if(!empty($social['facebook']))
            <a href="{{ $social['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"
              class="h-10 w-10 flex items-center justify-center rounded-full bg-[#1877F2] text-white hover:opacity-90">
              {{-- Facebook --}}
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path
                  d="M22 12.06C22 6.48 17.52 2 11.94 2S2 6.48 2 12.06c0 4.99 3.66 9.13 8.44 9.94v-7.03H8.08v-2.9h2.36V9.41c0-2.33 1.39-3.62 3.52-3.62.71 0 1.8.12 2.19.18v2.41h-1.24c-1.22 0-1.6.76-1.6 1.54v1.86h2.72l-.43 2.9h-2.29v7.03C18.34 21.19 22 17.05 22 12.06z" />
              </svg>
            </a>
            @endif

            @if(!empty($social['linkedin']))
            <a href="{{ $social['linkedin'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"
              class="h-10 w-10 flex items-center justify-center rounded-full bg-[#0A66C2] text-white hover:opacity-90">
              {{-- LinkedIn --}}
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path
                  d="M6.94 6.5A2.44 2.44 0 1 1 4.5 4.06 2.44 2.44 0 0 1 6.94 6.5ZM4.75 8.5h4.39V20H4.75ZM13.1 8.5h-4.4V20h4.4v-5.8c0-3.31 4.24-3.58 4.24 0V20h4.37v-6.68c0-5.84-6.64-5.63-8.61-2.76V8.5Z" />
              </svg>
            </a>
            @endif

            @if(!empty($social['instagram']))
            <a href="{{ $social['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"
              class="h-10 w-10 flex items-center justify-center rounded-full bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] text-white hover:opacity-90">
              {{-- Instagram --}}
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path
                  d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.75-2.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z" />
              </svg>
            </a>
            @endif

            @if(!empty($social['youtube']))
            <a href="{{ $social['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"
              class="h-10 w-10 flex items-center justify-center rounded-full bg-[#FF0000] text-white hover:opacity-90">
              {{-- YouTube --}}
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                <path
                  d="M23.5 7.2a4.5 4.5 0 0 0-3.15-3.18C18.55 3.5 12 3.5 12 3.5s-6.55 0-8.35.52A4.5 4.5 0 0 0 .5 7.2 47.7 47.7 0 0 0 0 12c-.02 1.62.17 3.24.5 4.8a4.5 4.5 0 0 0 3.15 3.18c1.8.52 8.35.52 8.35.52s6.55 0 8.35-.52a4.5 4.5 0 0 0 3.15-3.18c.33-1.56.5-3.18.5-4.8s-.17-3.24-.5-4.8ZM9.75 15.02V8.98l6.06 3.02-6.06 3.02Z" />
              </svg>
            </a>
            @endif
          </div>
        </div>
        @endif
      </aside>

      {{-- Contact Form (stacked, full width) --}}
      {{-- Contact Form (reusable component) --}}
      @include('partials.contact.contact-form', [
      'facility' => $facility,
      'primary' => $primary,
      'secondary' => $secondary,
      'accent' => $accent,
      'neutral_dark' => $neutral_dark ?? '#1e293b'
      ])

      {{-- Map: Show all facilities for Bio-Pacific only --}}
      @php
      $isBioPacific = false;
      if (isset($facility)) {
      if ((is_array($facility) && isset($facility['id']) && $facility['id'] == 99) || (is_object($facility) &&
      isset($facility->id) && $facility->id == 99)) {
      $isBioPacific = true;
      }
      }
      @endphp
      @if($isBioPacific && Auth::check())
      {{-- @include('facility_sections.facilities-map', ['facilities' => $facilities ?? []]) --}}
      @else
      <div class="rounded-3xl border bg-white shadow-xl overflow-hidden h-full">
        <div class="flex items-center justify-between border-b px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl"
              style="background: {{ ($facility['secondary_color'] ?? '#155E75') }}1A; color: {{ $facility['secondary_color'] ?? '#155E75' }}">
              📍</div>
            <div>
              <h4 class="text-base font-semibold text-slate-900">Find Us</h4>
              <p class="text-xs text-slate-500">Get directions and plan your visit</p>
            </div>
          </div>
          <a href="{{ $mapsHref }}" target="_blank" rel="noopener" class="text-sm font-semibold hover:underline"
            style="color: {{ $facility['primary_color'] ?? '#0EA5E9' }}">
            Open in Maps →
          </a>
        </div>
        <div>
          @if(!empty($facility['location_map']))
          @if(Str::startsWith($facility['location_map'], ['http://','https://']))
          <iframe src="{{ $facility['location_map'] }}" class="block w-full" height="560" loading="lazy"
            style="border:0;" allowfullscreen></iframe>
          @else
          {!! $facility['location_map'] !!}
          @endif
          @else
          <iframe src="{{ $mapsHref }}" class="block w-full" height="560" loading="lazy" style="border:0;"
            allowfullscreen></iframe>
          @endif
        </div>
      </div>
      @endif

    </div>
  </div>
</section>
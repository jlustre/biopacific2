{{-- ABOUT — Version B: Collage + Tabs + Timeline + Accreditations --}}
<section class="relative overflow-hidden py-20 md:py-28" id="about">
  @php
  $scheme = isset($facility['color_scheme_id']) ? \DB::table('color_schemes')->find($facility['color_scheme_id']) :
  null;
  $primary = $primary ?? ($scheme->primary_color ?? '#0EA5E9');
  $secondary = $secondary ?? ($scheme->secondary_color ?? '#1E293B');
  $accent = $accent ?? ($scheme->accent_color ?? '#F59E0B');
  @endphp
  {{-- Background decoration --}}
  <div class="pointer-events-none absolute inset-0 -z-10">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
    <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-30"
      style="background: {{ $primary }}"></div>
    <div class="absolute -bottom-28 -right-24 h-96 w-96 rounded-full blur-3xl opacity-20"
      style="background: {{ $accent }}"></div>
  </div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <p class="mt-3 text-lg md:text-xl text-slate-700">
      {{-- Header --}}
    <div class="max-w-3xl">
      <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900">
        <span class="text-primary">About</span> <span style="color: {{ $primary }}">{{ $facility['name'] ??
          'Our Facility' }}</span>
      </h2>
      <p class="mt-3 text-lg md:text-xl text-slate-700">
        {{ $facility['subheadline'] ?? 'Dedicated to compassionate, evidence-based care in a warm, family-centered
        environment.' }}
      </p>
    </div>

    {{-- Main grid --}}
    <div class="mt-12 grid gap-10 lg:grid-cols-12 items-start">
      {{-- Left: Image collage + years badge --}}
      <div class="lg:col-span-5">
        <div class="grid grid-cols-2 gap-3 sm:gap-4">
          <div class="aspect-[3/2] overflow-hidden rounded-2xl shadow-lg col-span-2 relative">
            <img src="{{ asset('images/'.($facility['about_image_url'] ?? 'physical-therapy-session.png')) }}"
              alt="Physical therapy session" class="h-full w-full object-cover max-w-full h-auto block">
            <div class="absolute bottom-4 right-4 z-10">
              <div class="rounded-2xl border bg-white/90 backdrop-blur px-5 py-4 shadow-xl">
                <div class="text-2xl md:text-3xl font-black" style="color: {{ $accent }}">
                  {{ $facility['years'] ?? '20' }}+
                </div>
                <div class="text-xs md:text-sm text-slate-600">Years of Service</div>
              </div>
            </div>
          </div>
          <div class="aspect-[4/5] overflow-hidden rounded-2xl shadow-lg flex items-start">
            <img src="{{ asset('images/nursehuggingpatient.jpg') }}" alt="Nurse supporting a resident"
              style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
          </div>
          <div class="aspect-[4/5] overflow-hidden rounded-2xl shadow-lg flex items-start">
            <img src="{{ asset('images/recreation_activities-room.png') }}"
              alt="Residents enjoying recreation activities"
              style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
          </div>

        </div>
      </div>

      {{-- Right: Tabs, values, metrics --}}
      <div class="lg:col-span-7">
        @if (!empty($facility['about_text']))
        <p class="my-2 text-slate-700 leading-relaxed">
          {{ $facility['about_text'] }}
        </p>
        @endif
        {{-- Tabs --}}
        <div x-data="{tab:'mission'}" class="relative">
          <div class="flex flex-wrap gap-2">
            <button @click="tab='mission'" class="rounded-full px-4 py-2 text-sm font-semibold ring-1 transition"
              :class="tab==='mission' ? 'text-white' : 'text-slate-700'"
              :style="tab==='mission' ? 'background:{{ $primary }}' : 'background:white; box-shadow:inset 0 0 0 1px rgba(15,23,42,.08)'">
              Mission
            </button>
            <button @click="tab='vision'" class="rounded-full px-4 py-2 text-sm font-semibold ring-1 transition"
              :class="tab==='vision' ? 'text-white' : 'text-slate-700'"
              :style="tab==='vision' ? 'background:{{ $secondary }}' : 'background:white; box-shadow:inset 0 0 0 1px rgba(15,23,42,.08)'">
              Vision
            </button>
            <button @click="tab='values'" class="rounded-full px-4 py-2 text-sm font-semibold ring-1 transition"
              :class="tab==='values' ? 'text-white' : 'text-slate-700'"
              :style="tab==='values' ? 'background:{{ $accent }}' : 'background:white; box-shadow:inset 0 0 0 1px rgba(15,23,42,.08)'">
              Values
            </button>
          </div>

          {{-- Panels --}}
          <div class="mt-6 space-y-6">
            {{-- Mission --}}
            <div x-show="tab==='mission'" x-transition>
              <h3 class="text-xl font-bold text-slate-900">Our Mission</h3>
              <p class="mt-2 text-slate-700 leading-relaxed">
                {{ $facility['mission'] ?? "We deliver personalized, outcomes-focused care—skilled nursing,
                rehabilitation, memory care, and hospice—so every resident can live with dignity and purpose." }}
              </p>
            </div>

            {{-- Vision --}}
            <div x-show="tab==='vision'" x-transition>
              <h3 class="text-xl font-bold text-slate-900">Our Vision</h3>
              <p class="mt-2 text-slate-700 leading-relaxed">
                {{ $facility['vision'] ?? "To be California’s most trusted home for seniors—where families feel
                confident, residents feel at home, and clinical excellence meets genuine compassion." }}
              </p>
            </div>

            {{-- Values --}}
            <div x-show="tab==='values'" x-transition>
              <h3 class="text-xl font-bold text-slate-900">Core Values</h3>
              <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach([
                ['Integrity','Honesty & transparency'],
                ['Compassion','Caring with heart'],
                ['Excellence','Quality in everything'],
                ['Community','We belong together'],
                ] as [$title,$desc])
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                  <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg"
                    style="background: {{ ($accent) }}1A; color: {{ $accent }}">
                    ★
                  </div>
                  <div class="mt-2 font-semibold text-primary">{{ $title }}</div>
                  <p class="text-sm text-slate-600">{{ $desc }}</p>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        {{-- Accreditations block --}}
        <div class="mt-10 rounded-2xl border bg-white p-5 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-lg flex items-center justify-center text-white"
                style="background: {{ $facility['secondary_color'] ?? '#155E75' }}">✓</div>
              <div>
                <div class="font-semibold text-slate-900">Accreditations & Certifications</div>
                <p class="text-xs text-slate-600">Medicare/Medicaid certified • State licensed • Regular quality audits
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
              <span class="rounded-full px-3 py-1 ring-1 ring-slate-200">HIPAA-aware processes</span>
              <span class="rounded-full px-3 py-1 ring-1 ring-slate-200">Care quality awards</span>
              <span class="rounded-full px-3 py-1 ring-1 ring-slate-200">Family satisfaction</span>
            </div>
          </div>
        </div>

        <p class="mt-4 text-sm text-slate-600">
          Serving families across California with <strong>dignity, respect, and clinical excellence</strong>.
        </p>

        {{-- Metrics row --}}
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
          @foreach([
          ['State-Licensed','green','Compliant & certified'],
          ['Awards','amber','Recognized for quality'],
          ['Modern Facility','blue','Safety & accessibility'],
          ['24/7 Staff','purple','Compassionate professionals'],
          ] as [$k,$tone,$txt])
          <div class="rounded-2xl border bg-white p-3 shadow-md hover:shadow-lg transition">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full flex items-center justify-center
                          @if($tone==='green') bg-green-100 text-green-700
                          @elseif($tone==='amber') bg-amber-100 text-amber-700
                          @elseif($tone==='blue') bg-blue-100 text-blue-700
                          @else bg-purple-100 text-purple-700 @endif">
                ✓
              </div>
              <div class="font-semibold text-slate-900">{{ $k }}</div>
            </div>
            <p class="mt-2 text-xs text-slate-600">{{ $txt }}</p>
          </div>
          @endforeach
        </div>
        {{-- Soft CTA --}}
        <div class="mt-10 rounded-3xl p-6 sm:p-8 bg-gradient-to-r from-white to-slate-50 ring-1 ring-slate-200">
          <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-2">
            <div>
              <h4 class="text-lg font-bold text-slate-900">Want to learn more?</h4>
              <p class="text-slate-600 text-sm">Schedule a tour or speak with our admissions team today.</p>
            </div>
            <div class="flex gap-3">
              <a href="#book"
                class="inline-flex items-center rounded-xl px-2 py-1 font-semibold text-white shadow transition"
                style="background: {{ $primary }}">Book a Tour</a>
              <a href="#contact" class="inline-flex items-center rounded-xl px-2 py-1 font-semibold border transition"
                style="border-color: {{ $secondary }}; color: {{ $secondary }}">Contact
                Us</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
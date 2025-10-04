@php
$cityState = trim(($facility['city'] ?? '').(isset($facility['state']) ? ', '.$facility['state'] : ''));
$beds = $facility['beds'] ?? null;

// Optional images (swap with your own)
$aboutHero = asset('images/about-hero.png');
$aboutPeople = asset('images/about-people.png');
@endphp

<section id="about" class="relative isolate overflow-hidden py-16 sm:py-24 scroll-mt-24">
  {{-- Ambient brand backdrop --}}
  <div class="pointer-events-none absolute inset-0 -z-10">
    <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
      style="background: {{ $primary }}"></div>
    <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
      style="background: {{ $accent }}"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/70"></div>
  </div>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    {{-- Header / Intro --}}
    <div class="grid lg:grid-cols-2 gap-10 items-center">
      <div>
        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
          class="text-primary border-primary">
          <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
          About {{ $facility['name'] ?? 'Our Community' }}
        </span>

        <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-primary">
          {!! $facility['headline'] ?? 'Where Comfort Meets Compassion' !!}
        </h2>
        <p class="mt-3 text-slate-600 md:text-lg">
          {!! $facility['subheadline'] ?? 'Skilled nursing, rehabilitation, memory care, and hospice in a respectful,
          resident-first environment.' !!}
        </p>

        <div class="mt-4 rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white font-bold"
            style="background: {{ $primary }}">❤</div>
          <div class="text-sm text-slate-700">
            <div class="font-semibold text-slate-900">{{ $facility['tagline'] ?? 'Compassion and Care You Can Trust.' }}
            </div>
            At our core, we believe that healing and wellness extend far beyond medical treatment. Our community is
            built on the principles of compassion, respect, and togetherness—where every resident is valued not only for
            who they are today but also for the life story they bring with them.
          </div>
        </div>
      </div>

      {{-- Visual cluster --}}
      <div class="relative">
        <div class="relative rounded-3xl overflow-hidden ring-1 ring-slate-200 shadow-lg">
          <img src="{{ $aboutHero }}" alt="Inside {{ $facility['name'] ?? 'our community' }}"
            class="w-full h-72 sm:h-96 object-cover">
        </div>
        <div
          class="hidden sm:block absolute -bottom-6 -left-6 w-48 sm:w-56 rounded-3xl overflow-hidden ring-1 ring-white shadow-xl">
          <img src="{{ $aboutPeople }}" alt="Our caregiving team" class="w-full h-40 object-cover">
        </div>
      </div>
    </div>


    {{-- Value pillars --}}
    <div class="mt-14">
      <h3 class="text-2xl font-bold text-secondary">What sets us apart</h3>
      <p class="mt-2 text-slate-600">Care that blends clinical excellence with genuine warmth.</p>

      <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach([
        ['title'=>'Resident-First', 'copy'=>'Personalized care plans reflect each resident’s goals, routines, and
        preferences.', 'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>'],
        ['title'=>'Clinical Excellence','copy'=>'RN/LVN coverage, physician oversight, and evidence-based protocols.',
        'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>'],
        ['title'=>'Connected Families','copy'=>'Transparent updates and open communication—on your terms.',
        'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 8h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10a2 2 0 012-2h2m2-4h6a2 2 0 012 2v4H7V6a2 2 0 012-2z" />
        </svg>'],
        ['title'=>'Whole-Person Life','copy'=>'Social, spiritual, and restorative activities every day.', 'icon'=>'<svg
          xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8c-1.657 0-3 1.343-3 3 0 1.657 1.343 3 3 3s3-1.343 3-3c0-1.657-1.343-3-3-3zm0 10c-4.418 0-8-1.79-8-4V7a2 2 0 012-2h12a2 2 0 012 2v7c0 2.21-3.582 4-8 4z" />
        </svg>'],
        ] as $p)
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col items-start">
          <div class="h-9 w-9 rounded-xl mb-3 flex items-center justify-center text-white"
            style="background: {{ $primary }}">{!! $p['icon'] !!}</div>
          <div class="font-semibold text-slate-900">{{ $p['title'] }}</div>
          <p class="mt-1 text-sm text-slate-600">{{ $p['copy'] }}</p>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Care model --}}
    <div class="mt-14 grid lg:grid-cols-2 gap-8 items-start">
      <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 sm:p-8">
        <h3 class="text-2xl font-bold text-secondary">Our Care Model</h3>
        <p class="mt-2 text-slate-600">Integrated teams deliver consistent, coordinated care.</p>

        <ol class="mt-4 space-y-4">
          <li class="flex gap-3">
            <span class="h-8 w-8 rounded-full flex items-center justify-center text-white font-bold"
              style="background: {{ $primary }}">1</span>
            <div>
              <div class="font-semibold text-slate-900">Assessment & Planning</div>
              <p class="text-sm text-slate-600">Intake review, goals of care, and a personalized plan built with the
                resident & family.</p>
            </div>
          </li>
          <li class="flex gap-3">
            <span class="h-8 w-8 rounded-full flex items-center justify-center text-white font-bold"
              style="background: {{ $primary }}">2</span>
            <div>
              <div class="font-semibold text-slate-900">Evidence-Based Care</div>
              <p class="text-sm text-slate-600">Skilled nursing, therapy, and symptom management informed by best
                practices.</p>
            </div>
          </li>
          <li class="flex gap-3">
            <span class="h-8 w-8 rounded-full flex items-center justify-center text-white font-bold"
              style="background: {{ $primary }}">3</span>
            <div>
              <div class="font-semibold text-slate-900">Meaningful Daily Life</div>
              <p class="text-sm text-slate-600">Activities, dining, and social connection curated to each person.</p>
            </div>
          </li>
          <li class="flex gap-3">
            <span class="h-8 w-8 rounded-full flex items-center justify-center text-white font-bold"
              style="background: {{ $primary }}">4</span>
            <div>
              <div class="font-semibold text-slate-900">Family Partnership</div>
              <p class="text-sm text-slate-600">Regular updates and care conferences—clear, kind, and collaborative.</p>
            </div>
          </li>
        </ol>
      </div>

      {{-- Leadership mini-bios --}}
      <div class="space-y-4">
        {{-- Accreditations / assurances --}}
        <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 sm:p-8">
          <h3 class="text-2xl font-bold text-secondary">Assurances</h3>
          <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-slate-600">
            <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
              <div class="h-6"></div>
              <div class="mt-1 font-medium text-slate-900">Licensed SNF</div>
            </div>
            <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
              <div class="h-6"></div>
              <div class="mt-1 font-medium text-slate-900">24/7 Nursing</div>
            </div>
            <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
              <div class="h-6"></div>
              <div class="mt-1 font-medium text-slate-900">Therapy On-site</div>
            </div>
            <div class="rounded-xl ring-1 ring-slate-200 p-3 text-center">
              <div class="h-6"></div>
              <div class="mt-1 font-medium text-slate-900">Emergency-Ready</div>
            </div>
          </div>
          <p class="mt-3 text-[12px] text-slate-500">Please avoid sharing sensitive medical information via web forms.
          </p>
        </div>
        {{-- CTA --}}
        <div
          class="mt-14 rounded-3xl bg-white ring-1 ring-slate-200 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <div class="text-xl font-bold text-secondary">See our care in action</div>
            <p class="text-sm text-slate-600">Schedule a private tour—meet our team, ask questions, and explore.</p>
          </div>
          <div class="flex gap-3">
            <a href="#book"
              class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow"
              style="background: {{ $primary }}">Book A Tour</a>
            @if(!empty($facility['phone']))
            <a href="tel:{{ $facility['phone'] }}"
              class="inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold ring-2"
              class="text-primary border-primary">Call Us</a>
            @endif
          </div>
        </div>
      </div>

    </div>

  </div>
</section>
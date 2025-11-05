@php
$poster = asset($facility['hero_poster'] ?? 'images/hero1.jpg');
@endphp

<section id="book" class="relative isolate overflow-hidden">
    {{-- Brand gradient band + soft shapes --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute inset-x-0 top-0 h-60" style="background:
           radial-gradient(70% 60% at 15% 10%, {{ $primary }}20 0%, transparent 60%),
           radial-gradient(60% 50% at 85% 30%, {{ $accent }}20 0%, transparent 60%),
           linear-gradient(180deg, {{ $secondary }}d0 0%, {{ $secondary }}a5 60%, transparent 100%);">
        </div>
        <svg class="absolute right-[-80px] top-[40px] w-[260px] h-[260px] opacity-15" viewBox="0 0 200 200" fill="none"
            aria-hidden="true">
            <path d="M50 20C90 -10 140 10 170 50C200 90 190 140 150 170C110 200 60 190 30 150C0 110 10 50 50 20Z"
                fill="{{ $accent }}" />
        </svg>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-20 pb-6">
        {{-- Header --}}
        <div class="text-center text-white">
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-white/30 bg-white/10">
                <span class="inline-block h-2.5 w-2.5 rounded-full"
                    style="background: {{ $accent }}; color: {{ $neutral_dark }};"></span>
                Come See Us
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold" style="color: {{ $primary }};">
                Book a Tour at <span style="color: {{ $primary }};">{{ $facility['name'] ?? 'Our Community' }}</span>
            </h2>
            <p class="mt-2 text-white/90 md:text-lg" style="color: {{ $neutral_dark }}">See our rooms, meet our care
                team, and learn how we support
                families.</p>
        </div>
    </div>

    {{-- Content grid --}}
    <div class="mx-auto max-w-7xl px-2 sm:px-4 md:px-6 lg:px-8 pb-14">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Left Grid: Image -->
            <div>
                <img src="{{ asset('images/book-a-tour1.png') }}" alt="Book a Tour" class="w-full h-auto">

                {{-- Photo collage card --}}
                <div class="rounded-md overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm my-4">
                    {{-- Quick stats strip --}}
                    <div class="grid grid-cols-3 divide-x divide-slate-600 border-slate-800 bg-teal-100/50">
                        <div class="p-4 text-center">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Location</div>
                            <div class="mt-1 font-semibold text-slate-900">
                                {{ $facility['city'] ?? '—' }}{{ isset($facility['state']) ? ', '.$facility['state']
                                :
                                '' }}
                            </div>
                        </div>
                        <div class="p-4 text-center">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Tours</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}
                            </div>
                        </div>
                        <div class="p-4 text-center">
                            <div class="text-xs uppercase tracking-wide text-slate-500">Phone</div>
                            <div class="mt-1 font-semibold text-slate-900">
                                {{ isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/','($1)
                                $2-$3',$facility['phone']) : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Map (optional) --}}
                @if(!empty($facility['location_map']))
                <div class="rounded-md overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm">
                    <iframe src="{{ $facility['location_map'] }}" class="w-full h-48 md:h-56" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                @endif
            </div>

            <!-- Right Grid: Content -->
            <div class="grid gap-8 lg:grid-cols-[1.1fr,0.9fr] items-start">
                {{-- LEFT: Form card --}}
                <div class="rounded-md bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden px-4">
                    <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-900">Schedule your in-person visit</h3>
                        <p class="text-sm text-slate-600">We’ll confirm within one business day.</p>
                    </div>
                    <div class="mx-auto max-w-2xl mt-6 px-2">
                        <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800">
                            ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
                            privately.
                        </div>
                    </div>

                    {{-- Form --}}
                    @livewire('book-a-tour', ['facility' => $facility])

                    {{-- Mini “What you’ll see” --}}
                    <div class="rounded-lg ring-1 ring-slate-600 bg-yellow-50 py-2 px-4 shadow-sm m-6">
                        <h4 class="text-lg font-semibold text-teal-700 text-center">What You’ll See</h4>
                        <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(['Resident rooms','Common areas','Therapy gym','Dining spaces','Activities
                            spaces','Outdoor areas'] as $it)
                            <li class="flex items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-white"
                                    style="background: {{ $primary }}; color: {{ $neutral_light }};">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <span class="text-sm text-slate-700">{{ $it }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div
                            class="mt-2 flex flex-wrap justify-center items-center gap-3 text-sm text-slate-600 text-center">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3" />
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                </svg>
                                Tours last 20–30 minutes
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                                Private tours available
                            </span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
</section>

{{-- Date guard: no past dates --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const d = document.getElementById('preferred_date');
    if (d) { d.min = new Date().toISOString().slice(0,10); }
  });
</script>
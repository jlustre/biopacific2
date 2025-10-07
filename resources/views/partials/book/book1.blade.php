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

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-12 pb-6">
        {{-- Header --}}
        <div class="text-center text-white">
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-white/30 bg-white/10">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                Come See Us
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold">
                Book a Tour at <span style="color: {{ $accent }};">{{ $facility['name'] ?? 'Our Community' }}</span>
            </h2>
            <p class="mt-2 text-white/90 md:text-lg">See our rooms, meet our care team, and learn how we support
                families.</p>
        </div>
        <div class="mx-auto max-w-2xl mt-6">
            <div class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800">
                ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics privately.
            </div>
        </div>
    </div>

    {{-- Content grid --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-14">
        <div class="grid gap-8 lg:grid-cols-[1.1fr,0.9fr] items-start">
            {{-- LEFT: Form card --}}
            <div class="rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Schedule your in-person visit</h3>
                    <p class="text-sm text-slate-600">We’ll confirm within one business day.</p>
                </div>

                <form method="POST" action="{{ route('tours.store') }}" class="p-6 md:p-8" novalidate>
                    @csrf
                    <input type="hidden" name="type" value="in_person">

                    {{-- Name / Relationship --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="text-sm font-medium text-slate-700">Your name *</label>
                            <input id="full_name" name="full_name" required placeholder="Jane Doe"
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0" />
                        </div>
                        <div>
                            <label for="relationship" class="text-sm font-medium text-slate-700">Relationship</label>
                            <select id="relationship" name="relationship"
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0">
                                <option value="">Select…</option>
                                <option>Self</option>
                                <option>Spouse</option>
                                <option>Parent</option>
                                <option>Adult child</option>
                                <option>Relative</option>
                                <option>Friend</option>
                                <option>Care manager</option>
                            </select>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="text-sm font-medium text-slate-700">Phone *</label>
                            <input id="phone" name="phone" type="tel" required placeholder="(555) 555-1234"
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0" />
                        </div>
                        <div>
                            <label for="email" class="text-sm font-medium text-slate-700">Email *</label>
                            <input id="email" name="email" type="email" required placeholder="you@example.com"
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0" />
                        </div>
                    </div>

                    {{-- Date/Time/Guests --}}
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="preferred_date" class="text-sm font-medium text-slate-700">Preferred date
                                *</label>
                            <input id="preferred_date" name="preferred_date" type="date" required
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0" />
                        </div>
                        <div>
                            <label for="preferred_time" class="text-sm font-medium text-slate-700">Preferred time
                                *</label>
                            <select id="preferred_time" name="preferred_time" required
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0">
                                <option value="">Select…</option>
                                <option>9:00 AM</option>
                                <option>10:30 AM</option>
                                <option>1:00 PM</option>
                                <option>2:30 PM</option>
                                <option>4:00 PM</option>
                            </select>
                        </div>
                        <div>
                            <label for="guests" class="text-sm font-medium text-slate-700">Guests</label>
                            <input id="guests" name="guests" type="number" min="1" max="6" value="1"
                                class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0" />
                        </div>
                    </div>

                    {{-- Interests --}}
                    <fieldset class="mt-4">
                        <legend class="text-sm font-medium text-slate-700">Areas of interest</legend>
                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($services ?? [] as $service)
                            <label
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                <input type="checkbox" name="interests[]" value="{{ $service->title }}"
                                    class="rounded text-sky-600 focus:ring-sky-500">
                                <span>{{ $service->title }}</span>
                            </label>
                            @endforeach
                        </div>
                    </fieldset {{-- Notes --}} <div class="mt-4">
                    <label for="message" class="text-sm font-medium text-slate-700">Anything else we should
                        know?</label>
                    <textarea id="message" name="message" rows="4"
                        class="mt-1 block w-full rounded-lg border border-slate-500 px-3 py-2 focus:border-primary focus:ring-0"
                        placeholder="Accessibility needs, questions, preferences…"></textarea>
            </div>

            {{-- Consent --}}
            <div class="mt-5 space-y-2 text-xs text-slate-600">
                <label class="inline-flex items-start gap-2">
                    <input type="checkbox" name="consent" required
                        class="mt-0.5 rounded text-sky-600 focus:ring-sky-500">
                    <span>I agree to be contacted about this tour request. Please do not include sensitive
                        medical information.</span>
                </label>
                <p>See our <a href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}"
                        class="underline text-primary" target="_blank" rel="noopener noreferrer">Notice of
                        Privacy Practices</a>.</p>
            </div>

            {{-- Honeypot --}}
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

            {{-- Actions --}}
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                    style="background: {{ $primary }}">
                    Request Tour
                </button>

                <a href="tel:{{ $facility['phone'] ?? '' }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-6 py-3 font-semibold ring-2 transition bg-white hover:bg-slate-50"
                    style="border-color: {{ $secondary }}; color: {{ $secondary }};">
                    Call Us: {{ isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1)
                    $2-$3',
                    preg_replace('/\D/', '', $facility['phone'])) : '' }}
                </a>
            </div>

            {{-- Reassurances --}}
            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                    </svg>
                    Tours last 20–30 minutes
                </span>
                <span class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Private tours available
                </span>
            </div>
            </form>
        </div>

        {{-- RIGHT: Visuals + Quick stats --}}
        <aside class="space-y-6">
            {{-- Photo collage card --}}
            <div class="rounded-3xl overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm">
                <div class="grid gap-2 p-2 sm:p-3 grid-cols-3">
                    <div class="col-span-2 rounded-2xl overflow-hidden">
                        <img src="{{ $poster }}" alt="Welcome to {{ $facility['name'] ?? 'our community' }}"
                            class="h-52 md:h-64 w-full object-cover">
                    </div>
                    <div class="space-y-2">
                        <img src="{{ $poster }}" alt="" class="rounded-2xl h-24 w-full object-cover">
                        <div class="rounded-2xl h-24 w-full overflow-hidden relative">
                            <svg viewBox="0 0 200 200" class="absolute inset-0 h-full w-full" aria-hidden="true">
                                <defs>
                                    <linearGradient id="grad" x1="0" x2="1" y1="0" y2="1">
                                        <stop offset="0%" stop-color="{{ $primary }}" />
                                        <stop offset="100%" stop-color="{{ $accent }}" />
                                    </linearGradient>
                                </defs>
                                <circle cx="100" cy="100" r="90" fill="url(#grad)" opacity=".18" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center text-center px-3">
                                <div class="text-[11px] leading-tight text-slate-700">
                                    Friendly staff • Bright spaces • Therapy gym • Outdoor patio
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick stats strip --}}
                <div class="grid grid-cols-3 divide-x divide-slate-200 border-t border-slate-200 bg-slate-50/60">
                    <div class="p-4 text-center">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Location</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ $facility['city'] ?? '—' }}{{ isset($facility['state']) ? ', '.$facility['state'] :
                            '' }}
                        </div>
                    </div>
                    <div class="p-4 text-center">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Tours</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</div>
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
            <div class="rounded-3xl overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm">
                <iframe src="{{ $facility['location_map'] }}" class="w-full h-56 md:h-64" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                <div class="p-4 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-sm">
                        <div class="font-semibold text-slate-900">Find us</div>
                        <div class="text-slate-600">{{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ',
                            '.$facility['state'] : '' }}</div>
                    </div>
                    <a href="#contact"
                        class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-white"
                        style="background: {{ $primary }}">Get Directions</a>
                </div>
            </div>
            @endif

            {{-- Mini “What you’ll see” --}}
            <div class="rounded-3xl ring-1 ring-slate-200 bg-white p-6 shadow-sm">
                <h4 class="text-base font-semibold text-slate-900">What you’ll see</h4>
                <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(['Resident rooms','Common areas','Therapy gym','Dining spaces','Activities
                    spaces','Outdoor areas'] as $it)
                    <li class="flex items-center gap-2">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-white"
                            style="background: {{ $accent }}">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="text-sm text-slate-700">{{ $it }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </aside>
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
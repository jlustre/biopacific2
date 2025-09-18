{{-- ================================
BOOK A TOUR — Glass Float Variant
- Full-bleed image background
- Floating glass form with brand rail
- Quick-pick time slots
================================ --}}
@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
$poster = asset($facility['hero_poster'] ?? 'images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg');
@endphp

<section id="book" class="relative isolate overflow-hidden">
    {{-- Background image --}}
    <div class="absolute inset-0 -z-10">
        <img src="{{ $poster }}" alt="Tour {{ $facility['name'] ?? 'our community' }}"
            class="h-[88vh] md:h-[92vh] w-full object-cover">
        {{-- Legibility gradient from left & bottom --}}
        <div class="absolute inset-0" style="background:
      linear-gradient(90deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.25) 40%, rgba(0,0,0,.0) 70%),
      linear-gradient(0deg, rgba(0,0,0,.35) 0%, rgba(0,0,0,0) 35%);"></div>
        {{-- Subtle brand glows --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-20"
            style="background: {{ $primary }}"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full blur-3xl opacity-15"
            style="background: {{ $accent }}"></div>
    </div>

    {{-- Left headline (keeps photo visible) --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-14">
        <div class="max-w-xl text-white">
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1 ring-white/30 bg-white/10">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                Visit & Experience
            </span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold leading-tight">
                Book a Tour at {{ $facility['name'] ?? 'Our Community' }}
            </h2>
            <p class="mt-3 md:text-lg text-white/90">
                Walk our spaces, meet our team, and learn how we support residents and families every day in
                {{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}.
            </p>

            {{-- Micro trust chips --}}
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                @foreach(['Wheelchair accessible','Private tours','Free on-site parking'] as $chip)
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 ring-1 ring-white/25">
                    <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-white/90">{{ $chip }}</span>
                </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Floating glass form --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-10">
        <div class="mt-8 md:-mt-16 flex justify-end">
            <div
                class="w-full md:w-[560px] overflow-hidden rounded-[28px] bg-white/80 backdrop-blur shadow-2xl ring-1 ring-black/10">
                {{-- Brand progress rail --}}
                <div class="h-1.5 w-full" style="background:
          linear-gradient(90deg, {{ $primary }} , {{ $accent }} );"></div>

                <div class="p-6 md:p-8">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 h-10 w-10 rounded-2xl flex items-center justify-center text-white font-bold"
                            style="background: {{ $primary }}">🏷️</div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Schedule your in-person visit</h3>
                            <p class="text-sm text-slate-600">We’ll confirm within one business day.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tours.store') }}" class="mt-5" novalidate>
                        @csrf
                        <input type="hidden" name="type" value="in_person">

                        {{-- Step 1: Contact --}}
                        <div class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="full_name" class="text-sm font-medium text-slate-700">Your name
                                        *</label>
                                    <input id="full_name" name="full_name" required placeholder="Jane Doe"
                                        class="mt-1 block w-full rounded-xl border-slate-300 focus:border-slate-400 focus:ring-0" />
                                </div>
                                <div>
                                    <label for="relationship"
                                        class="text-sm font-medium text-slate-700">Relationship</label>
                                    <select id="relationship" name="relationship"
                                        class="mt-1 block w-full rounded-xl border-slate-300 focus:border-slate-400 focus:ring-0">
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

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="phone" class="text-sm font-medium text-slate-700">Phone *</label>
                                    <input id="phone" name="phone" type="tel" required placeholder="(555) 555-1234"
                                        class="mt-1 block w-full rounded-xl border-slate-300 focus:border-slate-400 focus:ring-0" />
                                </div>
                                <div>
                                    <label for="email" class="text-sm font-medium text-slate-700">Email *</label>
                                    <input id="email" name="email" type="email" required placeholder="you@example.com"
                                        class="mt-1 block w-full rounded-xl border-slate-300 focus:border-slate-400 focus:ring-0" />
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Date & time --}}
                        <div class="mt-5">
                            <label for="preferred_date" class="text-sm font-medium text-slate-700">Preferred date
                                *</label>
                            <input id="preferred_date" name="preferred_date" type="date" required
                                class="mt-1 block w-full rounded-xl border-slate-300 focus:border-slate-400 focus:ring-0" />

                            <div class="mt-3">
                                <div class="text-sm font-medium text-slate-700">Pick a time</div>
                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach(['9:00 AM','10:30 AM','1:00 PM','2:30 PM','4:00 PM','Other'] as $slot)
                                    <label class="group relative">
                                        <input type="radio" name="preferred_time" value="{{ $slot }}"
                                            class="peer sr-only" required>
                                        <span class="block rounded-xl border bg-white px-3 py-2 text-sm text-slate-700
                                   peer-checked:text-white peer-checked:shadow
                                   transition select-none cursor-pointer"
                                            style="border-color:#e5e7eb; background:#fff; ">
                                            {{ $slot }}
                                        </span>
                                        <span
                                            class="pointer-events-none absolute inset-0 rounded-xl ring-2 ring-transparent peer-checked:ring-0"></span>
                                        <style>
                                            .peer:checked+span {
                                                background: {
                                                        {
                                                        $primary
                                                    }
                                                }

                                                ;
                                                border-color: transparent;
                                            }
                                        </style>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Interests & notes --}}
                        <div class="mt-5">
                            <fieldset>
                                <legend class="text-sm font-medium text-slate-700">Areas of interest</legend>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach(['Skilled Nursing','Rehabilitation','Memory Care','Long-term Care','Dining
                                    & Nutrition','Activities'] as $opt)
                                    <label
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                        <input type="checkbox" name="interests[]" value="{{ $opt }}"
                                            class="rounded text-sky-600 focus:ring-sky-500">
                                        <span>{{ $opt }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="mt-3">
                                <label for="message" class="text-sm font-medium text-slate-700">Notes (optional)</label>
                                <textarea id="message" name="message" rows="3"
                                    class="mt-1 block w-full rounded-xl border-slate-300 focus:border-slate-400 focus:ring-0"
                                    placeholder="Accessibility needs, questions, preferences…"></textarea>
                            </div>
                        </div>

                        {{-- Consent --}}
                        <div class="mt-5 space-y-2 text-xs text-slate-600">
                            <label class="inline-flex items-start gap-2">
                                <input type="checkbox" name="consent" required
                                    class="mt-0.5 rounded text-sky-600 focus:ring-sky-500">
                                <span>I agree to be contacted about this tour request. Please do not include sensitive
                                    medical information.</span>
                            </label>
                            <p>See our <a href="{{ $facility['npp_url'] ?? url('/privacy-practices') }}"
                                    class="underline" style="color: {{ $primary }}">Notice of Privacy Practices</a>.</p>
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
                                class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-6 py-3 font-semibold ring-2 transition bg-white text-slate-900 hover:bg-slate-50"
                                style="border-color: {{ $primary }}">Call Us</a>
                        </div>

                        {{-- Reassurance row --}}
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 text-xs text-slate-600">
                            <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Tours last 20–30 minutes
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Private tours available
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Optional: map strip under form on desktop --}}
        @if(!empty($facility['location_map']))
        <div class="mt-8 rounded-3xl overflow-hidden ring-1 ring-white/40 bg-white/80 backdrop-blur shadow-xl">
            <iframe src="{{ $facility['location_map'] }}" class="w-full h-56 md:h-72" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
            <div class="p-4 md:p-5 border-t border-slate-200/60 flex items-center justify-between">
                <div class="text-sm">
                    <div class="font-semibold text-slate-900">Find us</div>
                    <div class="text-slate-600">{{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ',
                        '.$facility['state'] : '' }}</div>
                </div>
                <a href="#contact"
                    class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-white"
                    style="background: {{ $primary }}">Get Directions</a>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- Helpers: no past dates --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const d = document.getElementById('preferred_date');
    if (d) d.min = new Date().toISOString().slice(0,10);
  });
</script>
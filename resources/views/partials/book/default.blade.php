{{-- BOOK A TOUR — beautiful, responsive, accessible --}}
<section id="book" class="relative isolate overflow-hidden py-16 sm:py-24">
    {{-- Brand tints / background --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-32 -left-24 h-80 w-80 rounded-full blur-3xl opacity-20"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-40 -right-24 h-96 w-96 rounded-full blur-3xl opacity-15"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-white/60"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mx-auto max-w-3xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                class="text-primary border-primary">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                Schedule Your Visit
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold tracking-tight text-primary">
                Book a Tour at <span style="color: {{ $primary }};">{{ $facility['name'] ?? 'Our Community' }}</span>
            </h2>
            <p class="mt-3 text-slate-600 md:text-lg">
                Use this section when you want to book a tour to see our rooms, meet our care team, and explore our
                services. We’ll tailor your visit to what matters
                most to you.
            </p>
        </div>

        {{-- Content grid --}}
        <div class="mt-10 grid gap-8 lg:grid-cols-[1.1fr,1fr]">
            {{-- Left: highlights / steps / info card --}}
            <div class="rounded-3xl ring-1 ring-slate-200 bg-white/80 backdrop-blur p-6 md:p-8 shadow-sm">
                <img src="{{ asset('images/book-a-tour.png') }}" alt="Book a Tour"
                    class="mb-6 w-full h-auto object-cover rounded-2xl" style="max-width:100%;" />
                {{-- 3-step rail --}}
                <ol class="grid gap-4 sm:grid-cols-3">
                    <li class="flex items-start gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-white font-semibold shadow"
                            style="background: {{ $primary }}">1</span>
                        <div>
                            <div class="font-semibold text-slate-900">Choose a date</div>
                            <p class="text-sm text-slate-600">Pick your preferred day & time.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-white font-semibold shadow"
                            style="background: {{ $secondary }}">2</span>
                        <div>
                            <div class="font-semibold text-slate-900">Tell us your needs</div>
                            <p class="text-sm text-slate-600">Interests, accessibility, preferences.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-900 font-semibold shadow"
                            style="background: {{ $accent }}">3</span>
                        <div>
                            <div class="font-semibold text-slate-900">Get confirmation</div>
                            <p class="text-sm text-slate-600">We’ll confirm and send directions.</p>
                        </div>
                    </li>
                </ol>

                {{-- facility snapshot --}}
                <div class="mt-7 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Location</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ $facility['city'] ?? '—' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Tours</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Phone</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/','($1)
                            $2-$3',$facility['phone']) : '—' }}
                        </div>
                    </div>
                </div>

                {{-- micro trust row --}}
                <div class="mt-6 flex flex-wrap gap-3 text-xs text-slate-600">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 ring-1 ring-slate-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Wheelchair accessible
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 ring-1 ring-slate-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Free parking on site
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 ring-1 ring-slate-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Private tours available
                    </span>
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-3xl ring-1 ring-slate-200 bg-white p-6 md:p-8 shadow-sm">
                {{-- If you use Livewire: replace form tag with wire:submit.prevent="submit" --}}
                <form method="POST" action="{{ route('tours.store') }}" novalidate>
                    @csrf
                    {{-- name + relationship --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="text-sm font-medium text-slate-700">Your name *</label>
                            <input id="full_name" name="full_name" type="text" required
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2"
                                placeholder="Jane Doe" />
                        </div>
                        <div>
                            <label for="relationship" class="text-sm font-medium text-slate-700">Relationship</label>
                            <select id="relationship" name="relationship"
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2">
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

                    {{-- contact --}}
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="text-sm font-medium text-slate-700">Phone *</label>
                            <input id="phone" name="phone" type="tel" required
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2"
                                placeholder="(555) 555-1234" />
                        </div>
                        <div>
                            <label for="email" class="text-sm font-medium text-slate-700">Email *</label>
                            <input id="email" name="email" type="email" required
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2"
                                placeholder="you@example.com" />
                        </div>
                    </div>

                    {{-- date/time/guests --}}
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="preferred_date" class="text-sm font-medium text-slate-700">Preferred date
                                *</label>
                            <input id="preferred_date" name="preferred_date" type="date" required
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2" />
                        </div>
                        <div>
                            <label for="preferred_time" class="text-sm font-medium text-slate-700">Preferred time
                                *</label>
                            <select id="preferred_time" name="preferred_time" required
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2">
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
                                class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2" />
                        </div>
                    </div>

                    {{-- interests --}}
                    <fieldset class="mt-4">
                        <legend class="text-sm font-medium text-slate-700">Areas of interest</legend>
                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($services as $service)
                            <label
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                <input type="checkbox" name="interests[]" value="{{ $service->title }}"
                                    class="rounded-md border border-gray-400 text-sky-600 focus:ring-sky-500 px-2 py-1">
                                <span>{{ $service->title }}</span>
                            </label>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- accessibility needs --}}
                    <div class="mt-4">
                        <label for="access" class="text-sm font-medium text-slate-700">Accessibility needs
                            (optional)</label>
                        <input id="access" name="access" type="text"
                            class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2"
                            placeholder="e.g., wheelchair access, interpreter, quiet space" />
                    </div>

                    {{-- message --}}
                    <div class="mt-4">
                        <label for="message" class="text-sm font-medium text-slate-700">Anything else you’d like us to
                            know?</label>
                        <textarea id="message" name="message" rows="4"
                            class="mt-1 block w-full rounded-md border border-gray-400 focus:border-slate-400 focus:ring-0 px-3 py-2"
                            placeholder="Tell us about your needs, timeline, or questions."></textarea>
                    </div>

                    {{-- consent & privacy note --}}
                    <div class="mt-5 space-y-2 text-xs text-slate-600">
                        <div class="mb-2 p-3 rounded-lg bg-amber-100 text-amber-800 border border-amber-200">
                            <strong>Warning:</strong> Do not include any Protected Health Information (PHI) in this
                            request form.
                        </div>
                        <label class="inline-flex items-start gap-2">
                            <input type="checkbox" name="consent" required
                                class="mt-0.5 rounded-md border border-gray-400 text-sky-600 focus:ring-sky-500 px-2 py-1">
                            <span>I agree to be contacted about this tour request. I understand this form should not
                                include sensitive medical information.</span>
                        </label>
                        <p>See our <a href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}"
                                class="underline text-primary" target="_blank" rel="noopener noreferrer">Notice of
                                Privacy Practices</a>.</p>
                    </div>

                    {{-- honeypot --}}
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                    {{-- actions --}}
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                            class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl transition"
                            style="background: {{ $primary }}">
                            Request Tour
                        </button>
                        <a href="tel:{{ $facility['phone'] ?? '' }}"
                            class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-6 py-3 font-semibold ring-2 transition bg-white text-slate-900 hover:bg-slate-50"
                            style="--ring: {{ $primary }}; border-color: {{ $primary }};">
                            Call Us Instead:
                            @if($facility['phone'])
                            <span class="text-primary">&nbsp;&nbsp;
                                {{ preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $facility['phone']) }}
                            </span>
                            @endif
                        </a>
                    </div>

                    {{-- micro reassurance --}}
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4s-3 1.567-3 3.5S10.343 11 12 11z M19 20a7 7 0 10-14 0h14z" />
                            </svg>
                            Tours typically last 20–30 minutes
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                            </svg>
                            Flexible scheduling available
                        </span>
                    </div>
                </form>
            </div>
        </div>

        {{-- optional map embed --}}
        @if(!empty($facility['location_map']))
        <div class="mt-10 rounded-3xl overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm">
            <iframe src="{{ $facility['location_map'] }}" class="w-full h-64 md:h-80" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        @endif
    </div>
</section>

{{-- Small helper: prevent booking in the past --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const d = document.getElementById('preferred_date');
    if (!d) return;
    const today = new Date(); today.setHours(0,0,0,0);
    const iso = today.toISOString().slice(0,10);
    d.min = iso;
  });
</script>
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
        <div class="mt-10 grid gap-8 lg:grid-cols-2">
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
                {{-- optional map embed --}}
                @if(!empty($facility['location_map']))
                <div class="mt-10 rounded-3xl overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm">
                    <iframe src="{{ $facility['location_map'] }}" class="w-full h-64 md:h-80" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                @endif
            </div>

            {{-- Right: form --}}
            @livewire('book-a-tour', ['facility' => $facility])
        </div>
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

{{-- Ensure modal form does not open when Request Tour button is clicked --}}
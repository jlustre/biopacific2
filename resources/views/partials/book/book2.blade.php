<section id="book" class="relative isolate overflow-hidden">
    {{-- Hero background with dark overlay and brand glows --}}
    <div class="absolute inset-0 -z-10">
        <img src="{{ asset('images/book-a-tour3.png') }}" alt="Tour {{ $facility['name'] ?? 'our community' }}"
            class="h-[90vh] w-full object-cover object-[right_top] md:object-top">
        <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/25 to-transparent"></div>
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-20"
            style="background: {{ $primary }}"></div>
        <div class="pointer-events-none absolute bottom-24 -right-24 h-80 w-80 rounded-full blur-3xl opacity-15"
            style="background: {{ $accent }}"></div>
    </div>

    {{-- Headline and trust chips --}}
    <div class="relative z-10 mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 pt-20 text-center -mb-40">
        <span
            class="inline-flex items-center gap-2 rounded-full px-5 py-1.5 text-sm font-semibold ring-1 ring-white/30 bg-white/10 text-white shadow">
            <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
            Visit & Experience
        </span>
        <h2 class="mt-5 text-4xl md:text-5xl font-extrabold leading-tight text-white drop-shadow-xl">
            Book a Tour at <span style="color: {{ $primary }};">{{ $facility['name'] ?? 'Our Community' }}</span>
        </h2>
        <p class="mt-4 md:text-lg text-white/90">
            Walk our spaces, meet our team, and learn how we support residents and families every day in
            {{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-2 text-xs">
            @foreach(['Wheelchair accessible','Private tours','Free on-site parking'] as $chip)
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 ring-1 ring-white/25 text-white/90">
                <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
                {{ $chip }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- Glassmorphism cards: map and form --}}
    <div class="z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-20 mt-1">
        <div class="mt-14 flex flex-col lg:grid lg:grid-cols-2 gap-10 items-stretch justify-center relative"
            style="margin-top: 16rem;">
            @if(!empty($facility['location_map']))
            <div class="w-full flex-shrink-0 flex-grow-0 overflow-hidden rounded-3xl bg-white/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/10"
                style="flex-basis:100%;max-width:100%;">
                <div class="h-2 w-full rounded-t-3xl"
                    style="background: linear-gradient(90deg, {{ $primary }} , {{ $accent }} );">
                </div>
                <div class="p-8 md:p-12">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="shrink-0 h-14 w-14 rounded-2xl flex items-center justify-center text-white text-3xl font-bold"
                            style="background: {{ $primary }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="h-8 w-8" style="color: {{ $accent }}">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 21c-4.418 0-8-5.373-8-10a8 8 0 1116 0c0 4.627-3.582 10-8 10zm0-7a3 3 0 100-6 3 3 0 000 6z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold" style="color: {{ $secondary }}">Our Location</h3>
                            <p class="text-base text-slate-600">Conveniently located for families and visitors.</p>
                        </div>
                    </div>
                    <iframe src="{{ $facility['location_map'] }}" class="w-full h-56 md:h-80 flex-shrink-0 rounded-2xl"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold mb-2" style="color: {{ $secondary }}">What to Expect During
                            Your Tour</h4>
                        <ul class="list-disc list-inside text-slate-700 space-y-1 text-base">
                            <li>Personalized walk-through of our facility and amenities</li>
                            <li>Meet and greet with our caring staff</li>
                            <li>Overview of our care programs and daily activities</li>
                            <li>Opportunity to ask questions about services and accommodations</li>
                            <li>See resident rooms, dining areas, and common spaces</li>
                        </ul>
                        <div
                            class="mt-4 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200 text-slate-700 text-base">
                            <strong>Tour duration:</strong> Most tours last 20–30 minutes.
                        </div>
                        @if(isset($facility['phone']))
                        <div
                            class="mt-6 flex flex-col sm:flex-row items-center gap-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <svg class="h-6 w-6 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z" />
                            </svg>
                            <div class="flex-1 text-amber-900 text-base">
                                <strong>Need a tour within 24 hours?</strong>
                                <span class="block sm:inline">Please call us directly to check same-day
                                    availability.</span>
                            </div>
                            <a href="tel:{{ $facility['phone'] }}"
                                class="inline-flex items-center gap-2 rounded-xl text-white font-semibold px-5 py-2 shadow cursor-pointer"
                                style="background: {{ $primary }}; transition: background 0.3s;"
                                onmouseover="this.style.background='{{ $secondary }}'"
                                onmouseout="this.style.background='{{ $primary }}'">
                                Call Us:
                                <span>
                                    {{
                                    preg_replace(
                                    '/(\d{3})(\d{3})(\d{4})/',
                                    '($1) $2-$3',
                                    preg_replace('/\D/', '', $facility['phone'])
                                    )
                                    }}
                                </span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="hidden md:flex md:flex-col md:items-center md:justify-center md:py-2">
                    <img src="{{ asset('images/bplogo.png') }}" alt="BioPacific Logo" class="h-16 md:h-20 w-auto mb-2">
                    @if(!empty($facility['name']))
                    <div class="text-2xl font-bold mb-1" style="color: {{ $secondary }}">{{ $facility['name'] }}</div>
                    @endif
                    @if(!empty($facility['tagline']))
                    <div class="text-base italic text-slate-600 text-center">{{ $facility['tagline'] }}</div>
                    @endif
                </div>
            </div>
            @endif
            <div class="w-full flex-shrink-0 flex-grow-0 overflow-hidden rounded-3xl bg-white/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/10"
                style="flex-basis:100%;max-width:100%;">
                <div class="h-2 w-full rounded-t-3xl"
                    style="background: linear-gradient(90deg, {{ $primary }} , {{ $accent }} );">
                </div>
                <div class="p-8 md:p-12">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="shrink-0 h-14 w-14 rounded-2xl flex items-center justify-center text-white text-3xl font-bold"
                            style="background: {{ $primary }}">🏷️</div>
                        <div>
                            <h3 class="text-2xl font-semibold" style="color: {{ $secondary }}">Schedule your in-person
                                visit</h3>
                            <p class="text-base text-slate-600">We’ll confirm within one business day.</p>
                        </div>
                    </div>
                    <div class="mx-auto max-w-2xl mb-7">
                        <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200 text-sm text-amber-800">
                            ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
                            privately.
                        </div>
                    </div>

                    @livewire('book-a-tour', ['facility' => $facility])

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 text-sm text-slate-600">
                        <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Tours last 20–30 minutes
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Private tours available
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
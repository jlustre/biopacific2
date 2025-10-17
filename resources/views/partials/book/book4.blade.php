<section id="book"
    class="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-white via-slate-50 to-blue-50 py-12 px-2 sm:px-6 overflow-hidden">
    <!-- Decorative Glows -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full blur-3xl opacity-20 pointer-events-none"
        style="background: {{ $primary }};"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full blur-3xl opacity-15 pointer-events-none"
        style="background: {{ $accent }};"></div>

    <div class="w-full max-w-5xl mx-auto z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Left Column: Image + Card -->
            <div class="flex flex-col gap-8">
                <div class="w-full h-64 md:h-full flex items-center justify-center">
                    <img src="{{ asset('images/book-a-tour.png') }}" alt="Book a Tour"
                        class="w-full h-full object-cover object-center drop-shadow-xl rounded-3xl bg-white/80" />
                </div>
                <div
                    class="flex flex-col justify-center items-start bg-white/90 rounded-3xl shadow-2xl ring-1 ring-black/10 p-8 md:p-8 backdrop-blur-xl">

                    <p class="text-lg text-slate-700 mb-6">Discover our community, meet our caring team, and see how we
                        make a difference every day. Schedule a personalized tour at your convenience.</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-3 text-base text-slate-700"><svg
                                class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>Flexible tour times</li>
                        <li class="flex items-center gap-3 text-base text-slate-700"><svg
                                class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>Private, guided walk-through</li>
                        <li class="flex items-center gap-3 text-base text-slate-700"><svg
                                class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>Free on-site parking</li>
                    </ul>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">What to Expect During Your Tour</h3>
                        <ul class="list-disc list-inside text-slate-700 space-y-1">
                            <li>Warm welcome and introduction to our team</li>
                            <li>Guided walk-through of our facilities and amenities</li>
                            <li>Overview of our programs and services</li>
                            <li>Time for your questions and personalized discussion</li>
                        </ul>
                    </div>
                    <div>
                        <span class="inline-block bg-blue-50 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                            Typical tour duration: 30–45 minutes
                        </span>
                    </div>
                </div>
            </div>
            <!-- Right Column: Booking Form -->
            <div
                class="flex flex-col justify-start bg-white/95 rounded-3xl shadow-2xl ring-1 ring-black/10 p-4 md:p-6 backdrop-blur-xl">
                <div class="rounded-xl bg-amber-50 p-2 ring-1 ring-amber-200 text-sm text-amber-800 mb-4">⚠ Please
                    avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics privately.
                </div>
                <form method="POST" action="{{ route('tours.store') }}" class="space-y-6">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-base font-medium text-slate-700 mb-1">Full Name
                                *</label>
                            <input type="text" id="name" name="name" required placeholder="Jane Doe"
                                class="mt-1 block w-full rounded-xl border-2 border-slate-500 focus:border-[{{ $primary }}] focus:ring-0 bg-white/90 px-3 py-2 text-base" />
                        </div>
                        <div>
                            <label for="email" class="block text-base font-medium text-slate-700 mb-1">Email *</label>
                            <input type="email" id="email" name="email" required placeholder="you@example.com"
                                class="mt-1 block w-full rounded-xl border-2 border-slate-500 focus:border-[{{ $primary }}] focus:ring-0 bg-white/90 px-3 py-2 text-base" />
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="block text-base font-medium text-slate-700 mb-1">Phone *</label>
                            <input type="text" id="phone" name="phone" required placeholder="(555) 555-1234"
                                class="mt-1 block w-full rounded-xl border-2 border-slate-500 focus:border-[{{ $primary }}] focus:ring-0 bg-white/90 px-3 py-2 text-base" />
                        </div>
                        <div>
                            <label for="date" class="block text-base font-medium text-slate-700 mb-1">Preferred Date
                                *</label>
                            <input type="date" id="date" name="date" required
                                class="mt-1 block w-full rounded-xl border-2 border-slate-500 focus:border-[{{ $primary }}] focus:ring-0 bg-white/90 px-3 py-2 text-base" />
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-base font-medium text-slate-700 mb-1">Message</label>
                        <textarea id="message" name="message" rows="3"
                            placeholder="Accessibility needs, questions, preferences…"
                            class="mt-1 block w-full rounded-xl border-2 border-slate-500 focus:border-[{{ $primary }}] focus:ring-0 bg-white/90 px-3 py-2 text-base"></textarea>
                    </div>
                    <div>
                        <label class="block text-base font-medium text-slate-700 mb-2">Services of Interest</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($services ?? [] as $service)
                            <div class="flex items-center">
                                <input type="checkbox" id="service-{{ Str::slug($service->name) }}" name="services[]"
                                    value="{{ $service->name }}" class="accent-blue-600 mr-2">
                                <label for="service-{{ Str::slug($service->name) }}" class="text-slate-700">{{
                                    $service->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-yellow-100 p-2">
                        <input type="checkbox" id="phi_ack" name="phi_ack" required class="accent-blue-600">
                        <label for="phi_ack" class="text-sm text-slate-700">I understand not to include
                            Protected Health
                            Information (PHI).</label>
                    </div>
                    <div class="text-sm text-slate-600 mb-1">
                        By submitting, you agree to our <a
                            href="{{ url($facility['slug'] . '/notice-of-privacy-practices') }}" target="_blank"
                            class="underline text-primary hover:text-blue-900">Notice of Privacy Practices</a>.
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 mt-6">
                        <button type="submit"
                            class="cursor-pointer w-full md:w-auto text-white font-bold py-3 px-8 rounded-2xl shadow-lg transition text-base min-w-[140px] md:min-w-[140px] md:flex-none"
                            style="background: {{ $primary }}; border: none;">Book Tour</button>
                        @if(isset($facility['phone']))
                        <a href="tel:{{ $facility['phone'] }}"
                            class="w-full md:w-auto inline-flex flex-col items-center justify-center font-bold py-3 px-8 rounded-2xl shadow-lg transition text-base flex-1 min-w-[160px]"
                            style="border: 2px solid {{ $secondary }}; color: {{ $secondary }};">
                            <span class="block leading-tight">Call Us: </span>
                            <span class="block text-slate-500 font-mono text-base mt-1 ml-1">{{
                                preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', preg_replace('/\D/', '',
                                $facility['phone'])) }}</span>
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
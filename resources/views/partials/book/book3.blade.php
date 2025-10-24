{{-- Book a Tour Variant 3 — Bold, Modern, Responsive Redesign --}}
<section id="book"
    class="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-100 py-10 overflow-hidden">
    <div class="absolute inset-0 pointer-events-none -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/70"></div>
    </div>
    <div class="w-full max-w-6xl mx-auto px-2 sm:px-6 z-10">
        <div class="flex flex-col md:flex-row gap-8 items-stretch">
            <!-- Left: Illustration -->
            <div class="hidden md:flex md:w-1/2 flex-col items-center">
                <img src="/images/tour-illustration.png" alt="Book a Tour"
                    class="w-full h-auto rounded-3xl shadow-2xl border-4 border-white/60 bg-white/30 backdrop-blur-lg object-cover">
                <div class="mt-6 w-full bg-white/80 rounded-2xl shadow p-4 border border-blue-100">
                    <h3 class="text-base font-semibold text-blue-900 mb-2 text-center">What to Expect on Your Tour</h3>
                    <ol class="list-decimal list-inside text-sm text-slate-700 space-y-1">
                        <li>Warm welcome and introduction to our team</li>
                        <li>Guided walk-through of our facility and amenities</li>
                        <li>Overview of our care programs and services</li>
                        <li>Opportunity to ask questions and discuss your needs</li>
                        <li>Review of next steps and resources</li>
                    </ol>
                    <div class="mt-3 text-xs text-slate-600 text-center">
                        <strong>Estimated duration:</strong> 30–45 minutes
                    </div>
                </div>
                <!-- Booking Only Statement -->
                <div class="mb-3 mt-8">
                    <div class="rounded-xl bg-blue-50 p-3 ring-1 ring-blue-200 text-xs text-blue-800 text-center">
                        <strong>Note:</strong> Please use the form in this section, only if you are booking a tour.<br>
                        For all other inquiries, <a href="#contact"
                            class="underline text-blue-700 hover:text-blue-900">contact us here</a>.
                    </div>
                </div>
                <div class="mt-5 flex flex-col sm:flex-row gap-3 justify-center items-center">
                    <div
                        class="flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-2 ring-2 ring-amber-200 text-sm font-semibold text-amber-900 shadow-sm">
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-amber-500' fill='none'
                            viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2'
                                d='M3 17v-2a4 4 0 014-4h10a4 4 0 014 4v2M6 17v2a2 2 0 002 2h8a2 2 0 002-2v-2' />
                        </svg>
                        Free on-site parking
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-2xl bg-blue-50 px-4 py-2 ring-2 ring-blue-200 text-sm font-semibold text-blue-900 shadow-sm">
                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-blue-500' fill='none'
                            viewBox='0 0 24 24' stroke='currentColor'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2'
                                d='M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2zm12-12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zm0 12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' />
                        </svg>
                        <span>Phone:</span>
                        <a href="tel:{{ $facility['phone'] ?? '' }}" class="underline hover:text-blue-700">
                            {{ isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/','($1)
                            $2-$3',$facility['phone']) : '—' }}
                        </a>
                    </div>
                </div>

                {{-- Mini “What you’ll see” --}}
                <div class="rounded-lg ring-1 ring-slate-600 bg-yellow-50 py-2 px-4 shadow-sm -m-2 mt-8 w-full">
                    <h4 class="text-lg font-semibold text-teal-700 text-center">What You’ll See</h4>
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

                    <div
                        class="mt-2 flex flex-wrap justify-center items-center gap-3 text-sm text-slate-600 text-center">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
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

                {{-- Map (optional) --}}
                @if(!empty($facility['location_map']))
                <div class="rounded-md overflow-hidden ring-1 ring-slate-200 bg-white shadow-sm mt-8 w-full">
                    <iframe src="{{ $facility['location_map'] }}" class="w-full h-48 md:h-80" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                @endif
            </div>
            <!-- Right: Form Card -->
            <div class="w-full md:w-1/2 flex flex-col justify-center">
                <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-blue-100 p-6 md:p-10">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-blue-900 mb-2 text-center">Book a Tour</h2>
                    <p class="text-blue-700 mb-5 text-center text-sm">Schedule a visit to experience our facility
                        firsthand. Fill out the form and our team will contact you soon.</p>
                    <!-- PHI Warning -->
                    <div class="mb-4">
                        <div
                            class="rounded-xl bg-amber-50 p-3 ring-1 ring-amber-200 text-xs text-amber-800 text-center">
                            ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
                            privately.
                        </div>
                    </div>
                    @livewire('book-a-tour', ['facility' => $facility])
                </div>
            </div>

        </div>
    </div>
    </div>
</section>
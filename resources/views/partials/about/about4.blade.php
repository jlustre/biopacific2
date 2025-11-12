{{-- ABOUT — Version G: Magazine Banner, Editorial Grid, Vertical Milestones, Stats Band, Leadership, Compliance --}}
@php
$years = (int)($facility['years'] ?? 20);
$poster = !empty($facility['about_image_url'])
? url('images/' . $facility['about_image_url'])
: asset('images/physical-therapy-session.png');
@endphp

<section class="relative overflow-hidden" id="about">
    {{-- Subtle backdrop glows --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
        <div class="absolute -top-20 -left-24 h-72 w-72 rounded-full blur-3xl opacity-25"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full blur-3xl opacity-20"
            style="background: {{ $accent }}"></div>
    </div>

    {{-- 1) Banner --}}
    <div class="relative">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-14">
            <div class="relative overflow-hidden rounded-3xl ring-1 ring-slate-200">

                <img src="{{ $poster }}" alt="Life at {{ $facility['name'] ?? 'our facility' }}"
                    class="w-full h-64 md:h-[30rem] object-cover object-[center_20%]">
                {{-- Soft top/bottom gradients for legibility --}}
                <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/30 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-black/35 to-transparent"></div>

                {{-- Floating badge --}}
                <div class="absolute bottom-4 left-4">
                    <div
                        class="inline-flex items-center gap-3 rounded-2xl bg-white/95 backdrop-blur px-4 py-2 shadow ring-1 ring-slate-200">
                        <span class="h-10 w-10 rounded-xl text-white font-black flex items-center justify-center"
                            style="background: {{ $primary }}; color: {{ $neutral_light }}">{{ $years }}+</span>
                        <div class="text-xs">
                            <div class="font-semibold text-slate-900">Years of Service</div>
                            <div class="text-slate-600">Care you can count on</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center pt-4">
        <h2 class="text-3xl md:text-4xl font-bold drop-shadow-[0_2px_10px_rgba(0,0,0,.35)]">
            About <span style="color: {{ $primary }}">{{ $facility['name'] ?? 'Our Facility'
                }}</span>
        </h2>
    </div>

    {{-- 2) Editorial intro (3-column at lg) --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 md:py-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <p class="text-slate-700 md:text-lg leading-relaxed">
                    {{ $facility['about_text'] ?? "We deliver outcomes-focused care—skilled nursing, rehabilitation,
                    memory care, and hospice—so every resident can live with dignity and purpose. Families stay
                    informed, residents feel known, and our team shows up with both expertise and heart." }}
                </p>

                {{-- Pill chips --}}
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach(['Family-centered','Evidence-based','Compassion-first'] as $chip)
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $primary }}"></span>{{ $chip }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Facts card --}}
            <aside>
                <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-5">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Location</dt>
                            <dd class="mt-1 font-semibold text-slate-900">
                                {{ ($facility['city'] ?? '') }}@if(!empty($facility['state'])), {{ $facility['state']
                                }}@endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Capacity</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $facility->beds ?? '—' }} beds</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tours</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Phone</dt>
                            <dd class="mt-1 font-semibold text-slate-900">
                                @if(!empty($facility['phone'])) <a href="tel:{{ $facility['phone'] }}"
                                    class="underline decoration-slate-300 hover:decoration-slate-500">{{
                                    $facility['phone'] }}</a> @else — @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 rounded-xl px-4 py-3 text-sm"
                        style="background: linear-gradient(135deg, {{ $primary }}12, {{ $accent }}14)">
                        Licensed & Accredited • HIPAA-aware processes
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- 3) Tabbed Mission, Vision, Values, Journey --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 md:p-8 shadow-sm">
            <div x-data="{ activeTab: 'mission' }">
                {{-- Tab navigation --}}
                <div class="border-b border-slate-200">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeTab = 'mission'"
                            :class="activeTab === 'mission' ? 'border-b-2 text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="py-3 px-1 border-transparent font-medium text-sm transition"
                            :style="activeTab === 'mission' ? 'border-color: {{ $primary }}; color: {{ $primary }}' : ''">
                            Our Mission
                        </button>
                        <button @click="activeTab = 'vision'"
                            :class="activeTab === 'vision' ? 'border-b-2 text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="py-3 px-1 border-transparent font-medium text-sm transition"
                            :style="activeTab === 'vision' ? 'border-color: {{ $primary }}; color: {{ $primary }}' : ''">
                            Vision
                        </button>
                        <button @click="activeTab = 'values'"
                            :class="activeTab === 'values' ? 'border-b-2 text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="py-3 px-1 border-transparent font-medium text-sm transition"
                            :style="activeTab === 'values' ? 'border-color: {{ $primary }}; color: {{ $primary }}' : ''">
                            Values
                        </button>
                    </nav>
                </div>

                {{-- Tab content --}}
                <div class="mt-6">
                    {{-- Mission Tab --}}
                    <div x-show="activeTab === 'mission'" class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-2xl font-bold mb-4 text-secondary">Our Mission</h3>
                            <p class="text-slate-600 leading-relaxed">
                                {{ $facility['mission'] ?? 'To provide compassionate, person-centered care that honors
                                the dignity of every individual while supporting families through life\'s most
                                challenging transitions.' }}
                            </p>
                            <div class="mt-6 flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
                                    <span class="h-2 w-2 rounded-full"
                                        style="background: {{ $primary }}"></span>Person-centered
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
                                    <span class="h-2 w-2 rounded-full"
                                        style="background: {{ $primary }}"></span>Compassionate
                                </span>
                            </div>
                        </div>
                        <div class="aspect-[4/3] overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-slate-100">
                            <img src="{{ asset('images/nursehuggingpatient.jpg') }}"
                                alt="Nurse providing compassionate care to patient" class="w-full h-full object-cover">
                        </div>
                    </div>

                    {{-- Vision Tab --}}
                    <div x-show="activeTab === 'vision'" class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-2xl font-bold mb-4 text-secondary">Our Vision</h3>
                            <p class="text-slate-600 leading-relaxed">
                                {{ $facility['vision'] ?? 'To be the leading provider of healthcare services, setting
                                the standard for quality care and innovation while creating an environment where
                                residents thrive and families find peace of mind.' }}
                            </p>
                            <div class="mt-6 flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
                                    <span class="h-2 w-2 rounded-full"
                                        style="background: {{ $primary }}"></span>Excellence
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1 text-xs font-medium ring-1 ring-slate-200 text-slate-700">
                                    <span class="h-2 w-2 rounded-full"
                                        style="background: {{ $primary }}"></span>Innovation
                                </span>
                            </div>
                        </div>
                        <div class="aspect-[4/3] overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-slate-100">
                            <img src="{{ asset('images/garden-outdoor-activities.png') }}"
                                alt="Happy residents enjoying life at our facility" class="w-full h-full object-cover">
                        </div>
                    </div>

                    {{-- Values Tab --}}
                    <div x-show="activeTab === 'values'" class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-2xl font-bold mb-4 text-secondary">Our Values</h3>
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <div class="h-6 w-6 rounded-full flex items-center justify-center text-white text-xs"
                                        style="background: {{ $primary }}">♥</div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">Compassion</h4>
                                        <p class="text-sm text-slate-600">Every interaction is guided by empathy and
                                            understanding.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="h-6 w-6 rounded-full flex items-center justify-center text-white text-xs"
                                        style="background: {{ $primary }}">★</div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">Excellence</h4>
                                        <p class="text-sm text-slate-600">We strive for the highest standards in
                                            everything we do.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="h-6 w-6 rounded-full flex items-center justify-center text-white text-xs"
                                        style="background: {{ $primary }}">♦</div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">Integrity</h4>
                                        <p class="text-sm text-slate-600">Honest, transparent, and ethical in all our
                                            practices.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="aspect-[4/3] overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-slate-100">
                            <img src="{{ asset('images/nursinghome_image1.png') }}"
                                alt="Our nursing home facility demonstrating our core values"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4) Stats band --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
            ['State-Licensed','Compliant & certified','green'],
            ['Care Awards','Quality recognized','amber'],
            ['Modern Facility','Safe & accessible','blue'],
            ['Family Trust','High satisfaction','purple'],
            ] as [$title,$desc,$tone])
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center {{
                        $tone === 'green' ? 'bg-green-100 text-green-700' :
                        ($tone === 'amber' ? 'bg-amber-100 text-amber-700' :
                        ($tone === 'blue' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'))
                    }}">✓</div>
                    <div class="font-semibold text-slate-900">{{ $title }}</div>
                </div>
                <p class="mt-2 text-xs text-slate-600">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 5) Leadership micro-cards --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-10">
        <h3 class="text-xl md:text-2xl font-bold mb-4 text-secondary">Leadership</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
            ['Administrator','Committed to resident experience'],
            ['Director of Nursing','Champions clinical excellence'],
            ['Therapy Manager','Restores strength & mobility'],
            ] as [$role,$blurb])
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white"
                        style="background: {{ $primary }}">★</div>
                    <div>
                        <div class="font-semibold text-slate-900">{{ $role }}</div>
                        <p class="text-xs text-slate-600">{{ $blurb }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 6) Compliance & CTA --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-16">
        <div class="rounded-3xl p-6 sm:p-8 ring-1 ring-slate-200 bg-gradient-to-r from-white to-slate-50">
            <div class="grid md:grid-cols-3 gap-6 items-center">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl text-white flex items-center justify-center shadow"
                        style="background: {{ $secondary }}">✓</div>
                    <div>
                        <div class="font-semibold text-slate-900">Accreditations & Certifications</div>
                        <p class="text-xs text-slate-600">Medicare/Medicaid certified • State licensed • Regular quality
                            audits</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 ring-1 ring-slate-200 text-xs">HIPAA-aware processes</span>
                    <span class="rounded-full px-3 py-1 ring-1 ring-slate-200 text-xs">Infection control</span>
                    <span class="rounded-full px-3 py-1 ring-1 ring-slate-200 text-xs">Staff credentialing</span>
                </div>
                <div class="flex gap-3 justify-start md:justify-end">
                    @if(!empty($activeSections) && in_array('book', $activeSections))
                    <a href="#book"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow transition"
                        style="background: {{ $primary }}">Book a Tour</a>
                    @endif
                    <a href="#contact"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold border transition"
                        style="border-color: {{ $primary }}; color: {{ $primary }}">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>
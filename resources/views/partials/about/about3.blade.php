{{-- ABOUT — Version E: Story-First, Alternating Blocks, Horizontal Timeline, Compliance Strip --}}
@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
@endphp

<section id="about" class="relative overflow-hidden">
    {{-- Subtle background --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
        <div class="absolute -top-20 -left-24 h-72 w-72 rounded-full blur-3xl opacity-25"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full blur-3xl opacity-20"
            style="background: {{ $accent }}"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-18 md:py-24">
        {{-- Intro band --}}
        <div class="rounded-3xl ring-1 ring-slate-200 bg-white/90 backdrop-blur p-6 md:p-10 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="max-w-3xl">
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900">
                        About <span style="color: {{ $primary }}">{{ $facility['name'] ?? 'Our Facility' }}</span>
                    </h2>
                    <p class="mt-3 text-slate-700 md:text-lg">
                        {{ $facility['subheadline'] ?? 'Dedicated to compassionate, evidence-based care in a warm,
                        family-centered environment.' }}
                    </p>
                </div>
                {{-- Highlight chips --}}
                <div class="flex flex-wrap gap-2">
                    @foreach([
                    ['Family-centered','M12 21.35l-1.45-1.32C5.4 15.36 2 12.28...'],
                    ['Evidence-based','M9 16.2l-3.5-3.5 1.41-1.4L9 13.8...'],
                    ['Licensed & Accredited','M9 12l2 2 4-4m5.618-4.016A11.955...']
                    ] as [$label,$path])
                    <span
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold bg-slate-50 ring-1 ring-slate-200 text-slate-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" style="color: {{ $accent }}">
                            <path d="{{ $path }}" />
                        </svg>
                        {{ $label }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Alternating story rows --}}
        <div class="mt-12 grid gap-10">
            {{-- Row 1 --}}
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6 order-2 lg:order-1">
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-900">Our Mission</h3>
                    <p class="mt-3 text-slate-700 leading-relaxed">
                        {{ $facility['mission'] ?? "We deliver personalized, outcomes-focused care—skilled nursing,
                        rehabilitation, memory care, and hospice—so every resident can live with dignity and purpose."
                        }}
                    </p>
                    <ul class="mt-4 space-y-2 text-slate-700 text-sm">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full"
                                style="background: {{ $primary }}"></span>
                            Person-centered plans and daily progress reviews
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full"
                                style="background: {{ $accent }}"></span>
                            Transparent family communication & involvement
                        </li>
                    </ul>
                    {{-- Optional long-form about text from CMS --}}
                    @if (!empty($facility['about_text']))
                    <div class="mt-10 prose prose-slate max-w-none">
                        <p>{{ $facility['about_text'] }}</p>
                    </div>
                    @endif
                </div>
                <div class="lg:col-span-6 order-1 lg:order-2">
                    <div class="aspect-[16/10] overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-slate-100">
                        <img src="{{ asset('images/physical-therapy-session.png') }}" alt="Therapy & compassionate care"
                            class="w-full h-full object-cover">
                    </div>
                </div>
            </div>

            {{-- Row 2 --}}
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6">
                    <div class="aspect-[16/10] overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-slate-100">
                        <img src="{{ asset('images/recreation_activities-room.png') }}"
                            alt="Activities & community life" class="w-full h-full object-cover">
                    </div>
                </div>
                <div class="lg:col-span-6">
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-900">Our Vision</h3>
                    <p class="mt-3 text-slate-700 leading-relaxed">
                        {{ $facility['vision'] ?? "To be California’s most trusted home for seniors—where families feel
                        confident, residents feel at home, and clinical excellence meets genuine compassion." }}
                    </p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl p-4 ring-1 ring-slate-200 bg-white">
                            <div class="text-xs text-slate-500">Years of Service</div>
                            <div class="mt-1 text-2xl font-bold" style="color: {{ $accent }}">{{ $facility['years'] ??
                                '20' }}+</div>
                        </div>
                        <div class="rounded-xl p-4 ring-1 ring-slate-200 bg-white">
                            <div class="text-xs text-slate-500">24/7 Skilled Staff</div>
                            <div class="mt-1 text-2xl font-bold" style="color: {{ $primary }}">Yes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Horizontal timeline scroller --}}
        <div class="mt-14">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Our Journey</h3>
            <div class="relative">
                <div class="overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none]"
                    style="-webkit-overflow-scrolling: touch;">
                    <ul class="flex items-stretch gap-4 min-w-[720px]">
                        @foreach([
                        ['Founded',now()->year - ($facility['years'] ?? 20),'Opened our doors with a promise
                        of dignity & respect'],
                        ['Expanded Services','+5 yrs','Added rehabilitation & memory care programs'],
                        ['Quality Milestones','+10 yrs','Recognized for care excellence & satisfaction'],
                        ['Today','Now','Serving families across California with heart']
                        ] as [$label,$when,$desc])
                        <li class="flex-1 min-w-[260px]">
                            <div class="h-full rounded-2xl ring-1 ring-slate-200 bg-white p-5 shadow-sm">
                                <div class="text-xs text-slate-500">{{ $label }}</div>
                                <div class="mt-1 text-lg font-bold text-slate-900">{{ $when }}</div>
                                <p class="mt-2 text-sm text-slate-600">{{ $desc }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                {{-- timeline line --}}
                <div
                    class="pointer-events-none absolute left-0 right-0 top-1/2 -translate-y-1/2 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent">
                </div>
            </div>
        </div>

        {{-- Metrics ribbon --}}
        <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
            ['State-Licensed','Compliant & certified','green'],
            ['Care Awards','Quality recognized','amber'],
            ['Modern Facility','Safe & accessible','blue'],
            ['Family Trust','High satisfaction','purple'],
            ] as [$title,$desc,$tone])
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 shadow hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center
             @if($tone==='green') bg-green-100 text-green-700
             @elseif($tone==='amber') bg-amber-100 text-amber-700
             @elseif($tone==='blue') bg-blue-100 text-blue-700
             @else bg-purple-100 text-purple-700 @endif">
                        ✓
                    </div>
                    <div class="font-semibold text-slate-900">{{ $title }}</div>
                </div>
                <p class="mt-2 text-xs text-slate-600">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

        {{-- Compliance & accreditations strip --}}
        <div class="mt-12 rounded-3xl p-6 sm:p-8 ring-1 ring-slate-200 bg-gradient-to-r from-white to-slate-50">
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
                    <span class="rounded-full px-3 py-1 ring-1 ring-slate-200 text-xs">Infection control programs</span>
                    <span class="rounded-full px-3 py-1 ring-1 ring-slate-200 text-xs">Family satisfaction goals</span>
                </div>
                <div class="flex gap-3 justify-start md:justify-end">
                    <a href="#book"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-white shadow transition"
                        style="background: {{ $primary }}">Book a Tour</a>
                    <a href="#contact"
                        class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold border transition"
                        style="border-color: {{ $primary }}; color: {{ $primary }}">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>
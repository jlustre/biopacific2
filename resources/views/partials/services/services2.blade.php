@php
// Your existing images map
$serviceImages = [
'Skilled Nursing' => asset('images/skilled_nursing.png'),
'Rehabilitation' => asset('images/rehab_care.png'),
'Long-term Care' => asset('images/long_term_care.png'),
'Memory Care' => asset('images/memory_care.png'),
'Hospice Care' => asset('images/hospice_care.png'),
'Dining & Nutrition' => asset('images/dining_and_nutrition_care.png'),
'Recreation & Activities' => asset('images/recreation_and_activities_care.png'),
'Transportation' => asset('images/transportation_care.png'),
];

// Palette from color scheme
if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->find($facility['color_scheme_id']);
$brandPrimary = $scheme->primary_color ?? '#0EA5E9';
$brandAccent = $scheme->accent_color ?? '#F59E0B';
} else {
$brandPrimary = '#0EA5E9';
$brandAccent = '#F59E0B';
}
@endphp

<section id="services" class="relative overflow-hidden py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-white">
    {{-- Decorative brand glows (very subtle) --}}
    <div class="pointer-events-none absolute -z-10 -top-24 -left-24 h-64 w-64 rounded-full blur-3xl opacity-15"
        style="background: {{ $brandPrimary }}"></div>
    <div class="pointer-events-none absolute -z-10 -bottom-28 -right-24 h-72 w-72 rounded-full blur-3xl opacity-10"
        style="background: {{ $brandAccent }}"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section header (kept) --}}
        @include('partials.section_header', [
        'section_header' => 'Our Services & Amenities',
        'section_sub_header' => 'Comprehensive care and enriching amenities designed to enhance quality of life for
        every resident.'
        ])

        {{-- Services Grid (refined) --}}
        @php
        $services = [
        [
        'title' => 'Skilled Nursing',
        'description' => '24/7 clinical care, wound care, IV therapy, and medication management by licensed
        professionals.',
        'color' => 'red',
        'detailed_description' => 'Our skilled nursing team provides comprehensive 24/7 clinical care with registered
        nurses and licensed practical nurses on-site at all times. We specialize in complex medical conditions including
        wound care management, IV therapy administration, medication management and monitoring, post-surgical care, and
        chronic disease management. Our nurses work closely with physicians to ensure optimal health outcomes and
        provide families with regular updates on their loved one\'s condition.',
        'features' => [
        '24/7 registered nurse supervision',
        'Advanced wound care and treatment',
        'IV therapy and medication administration',
        'Post-surgical rehabilitation support',
        'Chronic condition management',
        'Regular physician consultations',
        ],
        ],
        [
        'title' => 'Rehabilitation',
        'description' => 'Physical, occupational, and speech therapy with goal-driven recovery programs.',
        'color' => 'blue',
        'detailed_description' => 'Our comprehensive rehabilitation program features licensed physical, occupational,
        and speech therapists who create individualized treatment plans focused on restoring function and independence.
        We utilize state-of-the-art equipment and evidence-based techniques to help residents recover from surgery,
        injury, or illness while building strength, mobility, and confidence.',
        'features' => [
        'Physical therapy for mobility and strength',
        'Occupational therapy for daily living skills',
        'Speech therapy for communication and swallowing',
        'Modern rehabilitation equipment',
        'Individualized treatment plans',
        'Progress tracking and family updates',
        ],
        ],
        [
        'title' => 'Long-term Care',
        'description' => 'Personalized daily support, assistance with activities, and engaging social programs.',
        'color' => 'green',
        'detailed_description' => 'Our long-term care services provide comprehensive support for residents who need
        ongoing assistance with daily activities. We focus on maintaining dignity, independence, and quality of life
        through personalized care plans that address each resident\'s unique needs, preferences, and goals while
        fostering a warm, home-like environment.',
        'features' => [
        'Assistance with daily living activities',
        'Personalized care planning',
        'Social and recreational programs',
        'Nutritional support and monitoring',
        'Medication management',
        'Family involvement and communication',
        ],
        ],
        [
        'title' => 'Memory Care',
        'description' => 'Specialized secure environment and programs for Alzheimer\'s and dementia care.',
        'color' => 'purple',
        'detailed_description' => 'Our specialized memory care unit provides a secure, structured environment designed
        specifically for residents with Alzheimer\'s disease, dementia, and other memory-related conditions. Our trained
        staff use evidence-based approaches to create meaningful daily routines that promote cognitive function, reduce
        anxiety, and maintain quality of life.',
        'features' => [
        'Secure, specially designed environment',
        'Staff trained in dementia care',
        'Structured daily routines and activities',
        'Cognitive stimulation programs',
        'Behavior management support',
        'Family education and support groups',
        ],
        ],
        [
        'title' => 'Hospice Care',
        'description' => 'Compassionate comfort-focused end-of-life support for residents and families.',
        'color' => 'orange',
        'detailed_description' => 'Our hospice care program focuses on comfort, dignity, and quality of life for
        residents in their final stages of life. We work closely with hospice providers to ensure comprehensive pain
        management, emotional support, and spiritual care while providing families with guidance and comfort during this
        difficult time.',
        'features' => [
        'Comfort-focused care approach',
        'Pain and symptom management',
        'Emotional and spiritual support',
        'Family counseling and guidance',
        'Coordination with hospice providers',
        'Peaceful, dignified environment',
        ],
        ],
        [
        'title' => 'Dining & Nutrition',
        'description' => 'Chef-planned nutritious menus, special dietary accommodations, and dining experiences.',
        'color' => 'yellow',
        'detailed_description' => 'Our dining program features chef-prepared, nutritionally balanced meals designed to
        meet the dietary needs and preferences of our residents. We accommodate special diets, cultural preferences, and
        medical requirements while creating an enjoyable dining experience that promotes social interaction and
        maintains the pleasure of eating.',
        'features' => [
        'Chef-prepared nutritious meals',
        'Special dietary accommodations',
        'Cultural and personal preferences',
        'Pleasant dining environments',
        'Nutritional assessment and monitoring',
        'Flexible dining schedules',
        ],
        ],
        [
        'title' => 'Recreation & Activities',
        'description' => 'Social, spiritual, wellness activities, and entertainment programs for all interests.',
        'color' => 'pink',
        'detailed_description' => 'Our comprehensive activities program is designed to engage residents physically,
        mentally, socially, and spiritually. We offer a wide variety of programs tailored to different interests,
        abilities, and cognitive levels, ensuring every resident can participate in meaningful activities that bring joy
        and purpose to their daily lives.',
        'features' => [
        'Daily social and recreational activities',
        'Arts and crafts programs',
        'Music and entertainment events',
        'Exercise and wellness programs',
        'Spiritual and religious services',
        'Community outings and events',
        ],
        ],
        [
        'title' => 'Transportation',
        'description' => 'Coordinated medical appointments, shopping trips, and community outings.',
        'color' => 'indigo',
        'detailed_description' => 'Our transportation services ensure residents can maintain connections with their
        community and access essential services. We provide safe, comfortable transportation for medical appointments,
        shopping trips, family visits, and recreational outings, helping residents maintain their independence and
        quality of life.',
        'features' => [
        'Medical appointment transportation',
        'Shopping and errands assistance',
        'Community outing coordination',
        'Safe, accessible vehicles',
        'Trained transportation staff',
        'Flexible scheduling options',
        ],
        ],
        ];
        @endphp

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($services as $index => $service)
            <article
                class="group relative bg-white/70 backdrop-blur rounded-3xl ring-1 ring-slate-200 hover:ring-slate-300 shadow-sm hover:shadow-lg transition-all">
                {{-- Media --}}
                <div class="relative overflow-hidden rounded-t-3xl">
                    <img src="{{ $serviceImages[$service['title']] ?? 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=1000&q=80' }}"
                        alt="{{ $service['title'] }} at {{ $facility['name'] ?? 'our facility' }}"
                        class="h-44 w-full object-cover object-top sm:object-center md:object-[50%_20%] transition-transform duration-700 group-hover:scale-105"
                        loading="lazy" decoding="async">
                    {{-- Brand accent bar --}}
                    <div class="absolute bottom-0 left-0 right-0 h-1.5" style="background: {{ $brandPrimary }}"></div>
                </div>

                {{-- Body --}}
                <div class="p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ $service['title'] }}
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 line-clamp-3">
                        {{ $service['description'] }}
                    </p>

                    {{-- Actions --}}
                    <div class="mt-4 flex items-center justify-center">
                        <button onclick="openServiceModal('modal-{{ $index }}')"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2 text-sm font-semibold text-white transition-shadow shadow hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                            style="background: {{ $brandAccent }};" aria-controls="modal-{{ $index }}"
                            aria-expanded="false">
                            Details
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- CTA Panel --}}
        <div class="mt-16">
            <div class="mx-auto max-w-5xl rounded-3xl border border-slate-200 bg-white/80 backdrop-blur p-8 shadow">
                <div class="grid gap-6 md:grid-cols-[1.2fr,auto] md:items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">Need More Information?</h3>
                        <p class="mt-2 text-slate-600">
                            Our team is here to answer your questions and help you understand how our services can
                            benefit you or your loved one.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 justify-start md:justify-end">
                        <a href="#contact"
                            class="inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-white font-semibold shadow hover:shadow-md transition"
                            style="background: {{ $brandPrimary }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Contact Us
                        </a>
                        <a href="#about"
                            class="inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 font-semibold ring-2 transition bg-white text-slate-900 hover:bg-slate-50"
                            style="--ring: {{ $brandPrimary }}; border-color: {{ $brandPrimary }};">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Learn About Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals (accessible) --}}
    @foreach($services as $index => $service)
    <div id="modal-{{ $index }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="modal-title-{{ $index }}">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeServiceModal('modal-{{ $index }}')">
        </div>

        {{-- Dialog --}}
        <div id="modal-panel-{{ $index }}"
            class="relative max-w-2xl w-full max-h-[90vh] overflow-y-auto transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 transition"
            data-motion>
            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-{{ $service['color'] }}-100 ring-1 ring-{{ $service['color'] }}-200 overflow-hidden">
                        <img src="{{ $serviceImages[$service['title']] ?? '' }}" alt=""
                            class="w-full h-full object-cover">
                    </div>
                    <h3 id="modal-title-{{ $index }}" class="text-xl font-bold text-slate-900">
                        {{ $service['title'] }}
                    </h3>
                </div>
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                    aria-label="Close" onclick="closeServiceModal('modal-{{ $index }}')">
                    <svg class="h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6">
                <p class="text-slate-700 leading-relaxed">{{ $service['detailed_description'] }}</p>

                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-1">
                        <div
                            class="h-40 rounded-xl overflow-hidden ring-1 ring-slate-200 bg-{{ $service['color'] }}-50">
                            <img src="{{ $serviceImages[$service['title']] ?? '' }}" alt="{{ $service['title'] }}"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <h4 class="text-base font-semibold text-slate-900 mb-3">Key Features</h4>
                        <ul class="space-y-2">
                            @foreach($service['features'] as $feature)
                            <li class="flex items-start gap-2">
                                <span
                                    class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-{{ $service['color'] }}-100">
                                    <svg class="h-3.5 w-3.5 text-{{ $service['color'] }}-600" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                        </path>
                                    </svg>
                                </span>
                                <span class="text-slate-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a href="#contact"
                        class="flex-1 inline-flex items-center justify-center rounded-full px-5 py-3 font-semibold text-white transition shadow"
                        style="background: {{ $brandPrimary }};" onclick="closeServiceModal('modal-{{ $index }}')">
                        Contact Us About This Service
                    </a>
                    <button
                        class="flex-1 inline-flex items-center justify-center rounded-full px-5 py-3 font-semibold bg-slate-100 text-slate-800 hover:bg-slate-200 transition"
                        onclick="closeServiceModal('modal-{{ $index }}')">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</section>

{{-- Utilities --}}
<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Reduced motion: tone down modal animation */
    @media (prefers-reduced-motion: no-preference) {
        [data-motion] {
            transform: translateY(10px) scale(.98);
            opacity: 0;
        }

        .show [data-motion] {
            transform: translateY(0) scale(1);
            opacity: 1;
            transition: transform .25s ease, opacity .25s ease;
        }
    }
</style>

<script>
    // Accessible modal helpers
  function openServiceModal(id) {
    const modal = document.getElementById(id);
    const panel = document.getElementById('modal-panel-' + id.split('-')[1]);
    if (!modal) return;

    modal.classList.add('flex','show');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Focus trap basic
    setTimeout(() => panel?.querySelector('button[aria-label="Close"]')?.focus(), 10);

    // Close on ESC
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
  }

  function closeServiceModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    document.removeEventListener('keydown', modal._esc || (()=>{}));
    modal.classList.remove('show');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
    }, 200);
  }
</script>
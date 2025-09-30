@php
$serviceImages = [
'Skilled Nursing' => asset('images/skilled_nursing.png'),
'Rehabilitation' => asset('images/rehab_care.png'),
'Long-term Care' => asset('images/long_term_care.png'),
'Memory Care' => asset('images/memory_care.png'),
'Hospice Care' => asset('images/hospice_care.png'),
'Dining & Nutrition' => asset('images/dining_and_nutrition_care.png'),
'Recreation & Activities' => asset('images/recreation_and_activities_care.png'),
'Transportation' => asset('images/transportation_care.png')
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
<section id="services" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- SectionHeader -->
        @include('partials.section_header', [
        'section_header' => 'Our Services & Amenities',
        'section_sub_header' => 'Comprehensive care and enriching amenities designed to enhance quality of life for
        every
        resident.'
        ])

        <!-- Services Grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @php
            $services = [
            [
            'title' => 'Skilled Nursing',
            'description' => '24/7 clinical care, wound care, IV therapy, and medication management by licensed
            professionals.',
            'color' => 'red',
            'detailed_description' => 'Our skilled nursing team provides comprehensive 24/7 clinical care with
            registered
            nurses and licensed practical nurses on-site at all times. We specialize in complex medical conditions
            including
            wound care management, IV therapy administration, medication management and monitoring, post-surgical care,
            and
            chronic disease management. Our nurses work closely with physicians to ensure optimal health outcomes and
            provide
            families with regular updates on their loved one\'s condition.',
            'features' => [
            '24/7 registered nurse supervision',
            'Advanced wound care and treatment',
            'IV therapy and medication administration',
            'Post-surgical rehabilitation support',
            'Chronic condition management',
            'Regular physician consultations'
            ]
            ],
            [
            'title' => 'Rehabilitation',
            'description' => 'Physical, occupational, and speech therapy with goal-driven recovery programs.',
            'color' => 'blue',
            'detailed_description' => 'Our comprehensive rehabilitation program features licensed physical,
            occupational, and
            speech therapists who create individualized treatment plans focused on restoring function and independence.
            We
            utilize state-of-the-art equipment and evidence-based techniques to help residents recover from surgery,
            injury,
            or illness while building strength, mobility, and confidence.',
            'features' => [
            'Physical therapy for mobility and strength',
            'Occupational therapy for daily living skills',
            'Speech therapy for communication and swallowing',
            'Modern rehabilitation equipment',
            'Individualized treatment plans',
            'Progress tracking and family updates'
            ]
            ],
            [
            'title' => 'Long-term Care',
            'description' => 'Personalized daily support, assistance with activities, and engaging social programs.',
            'color' => 'green',
            'detailed_description' => 'Our long-term care services provide comprehensive support for residents who need
            ongoing assistance with daily activities. We focus on maintaining dignity, independence, and quality of life
            through personalized care plans that address each resident\'s unique needs, preferences, and goals while
            fostering
            a warm, home-like environment.',
            'features' => [
            'Assistance with daily living activities',
            'Personalized care planning',
            'Social and recreational programs',
            'Nutritional support and monitoring',
            'Medication management',
            'Family involvement and communication'
            ]
            ],
            [
            'title' => 'Memory Care',
            'description' => 'Specialized secure environment and programs for Alzheimer\'s and dementia care.',
            'color' => 'purple',
            'detailed_description' => 'Our specialized memory care unit provides a secure, structured environment
            designed
            specifically for residents with Alzheimer\'s disease, dementia, and other memory-related conditions. Our
            trained
            staff use evidence-based approaches to create meaningful daily routines that promote cognitive function,
            reduce
            anxiety, and maintain quality of life.',
            'features' => [
            'Secure, specially designed environment',
            'Staff trained in dementia care',
            'Structured daily routines and activities',
            'Cognitive stimulation programs',
            'Behavior management support',
            'Family education and support groups'
            ]
            ],
            [
            'title' => 'Hospice Care',
            'description' => 'Compassionate comfort-focused end-of-life support for residents and families.',
            'color' => 'orange',
            'detailed_description' => 'Our hospice care program focuses on comfort, dignity, and quality of life for
            residents
            in their final stages of life. We work closely with hospice providers to ensure comprehensive pain
            management,
            emotional support, and spiritual care while providing families with guidance and comfort during this
            difficult
            time.',
            'features' => [
            'Comfort-focused care approach',
            'Pain and symptom management',
            'Emotional and spiritual support',
            'Family counseling and guidance',
            'Coordination with hospice providers',
            'Peaceful, dignified environment'
            ]
            ],
            [
            'title' => 'Dining & Nutrition',
            'description' => 'Chef-planned nutritious menus, special dietary accommodations, and dining experiences.',
            'color' => 'yellow',
            'detailed_description' => 'Our dining program features chef-prepared, nutritionally balanced meals designed
            to
            meet the dietary needs and preferences of our residents. We accommodate special diets, cultural preferences,
            and
            medical requirements while creating an enjoyable dining experience that promotes social interaction and
            maintains
            the pleasure of eating.',
            'features' => [
            'Chef-prepared nutritious meals',
            'Special dietary accommodations',
            'Cultural and personal preferences',
            'Pleasant dining environments',
            'Nutritional assessment and monitoring',
            'Flexible dining schedules'
            ]
            ],
            [
            'title' => 'Recreation & Activities',
            'description' => 'Social, spiritual, wellness activities, and entertainment programs for all interests.',
            'color' => 'pink',
            'detailed_description' => 'Our comprehensive activities program is designed to engage residents physically,
            mentally, socially, and spiritually. We offer a wide variety of programs tailored to different interests,
            abilities, and cognitive levels, ensuring every resident can participate in meaningful activities that bring
            joy
            and purpose to their daily lives.',
            'features' => [
            'Daily social and recreational activities',
            'Arts and crafts programs',
            'Music and entertainment events',
            'Exercise and wellness programs',
            'Spiritual and religious services',
            'Community outings and events'
            ]
            ],
            [
            'title' => 'Transportation',
            'description' => 'Coordinated medical appointments, shopping trips, and community outings.',
            'color' => 'indigo',
            'detailed_description' => 'Our transportation services ensure residents can maintain connections with their
            community and access essential services. We provide safe, comfortable transportation for medical
            appointments,
            shopping trips, family visits, and recreational outings, helping residents maintain their independence and
            quality
            of life.',
            'features' => [
            'Medical appointment transportation',
            'Shopping and errands assistance',
            'Community outing coordination',
            'Safe, accessible vehicles',
            'Trained transportation staff',
            'Flexible scheduling options'
            ]
            ]
            ];
            @endphp

            @foreach($services as $index => $service)
            <div class="group relative overflow-hidden rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-105 cursor-pointer"
                style="border-color: {{ $facility['primary_color'] ?? '#1a7f37' }};">
                <!-- Background Image with Overlay -->
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-transform duration-700 group-hover:scale-110"
                    style="background-image: url('{{ $serviceImages[$service['title']] ?? 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' }}');">
                </div>

                <!-- Gradient Overlay for Text Contrast -->
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent group-hover:from-black/90 group-hover:via-black/50 transition-all duration-500">
                </div>

                <!-- Content Container -->
                <div class="relative z-10 p-6 h-80 flex flex-col justify-end text-white">
                    <!-- Title -->
                    <h3 class="text-xl font-bold mb-3" style="color: {{ $facility['accent_color'] ?? '#e3342f' }};">
                        {{ $service['title'] }}
                    </h3>

                    <!-- Description -->
                    <p class="text-white/90 leading-relaxed mb-4 text-sm line-clamp-3">
                        {{ $service['description'] }}
                    </p>

                    <!-- Learn More Button -->
                    <div class="inline-flex items-center gap-2 text-white font-medium text-sm">
                        <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full border border-white/30"
                            onclick="openServiceModal3('modal-{{ $index }}', event)"
                            style="cursor:pointer; background-color: {{ $facility['primary_color'] ?? '#000000' }}; color: #FFFFFF;">
                            Learn more
                        </span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Animated Border -->
                <div
                    class="absolute inset-0 border-2 border-transparent group-hover:border-white/30 rounded-3xl transition-all duration-500">
                </div>

                <!-- Shine Effect -->
                <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out">
                </div>
            </div>
            @endforeach
        </div>

        <!-- Call to Action -->
        <div class="mt-16 text-center">
            <div class="bg-white p-8 rounded-3xl shadow-lg border border-gray-100 max-w-4xl mx-auto">
                <h3 class="text-2xl font-bold text-secondary mb-4">
                    Need More Information?
                </h3>
                <p class="text-slate-600 mb-6 max-w-2xl mx-auto">
                    Our team is here to answer your questions and help you understand how our services can benefit you
                    or your
                    loved one.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#contact"
                        class="inline-flex items-center justify-center gap-2 bg-accent text-white px-8 py-3 rounded-full hover:bg-accent/90 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                            </path>
                        </svg>
                        <span>Contact Us</span>
                    </a>
                    <a href="#about"
                        class="inline-flex items-center justify-center gap-2 bg-white text-accent border-2 border-accent px-8 py-3 rounded-full hover:bg-accent hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Learn About Us</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach($services as $index => $service)
    <div id="modal-{{ $index }}"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 scale-95 opacity-0"
            id="modal-content-{{ $index }}">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-{{ $service['color'] }}-100 rounded-2xl flex items-center justify-center">
                        <div class="w-8 h-8 rounded-lg bg-cover bg-center"
                            style="background-image: url('{{ $serviceImages[$service['title']] ?? 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' }}');">
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-secondary">{{ $service['title'] }}</h3>
                </div>
                <button onclick="closeServiceModal3('modal-{{ $index }}')"
                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <p class="text-slate-700 leading-relaxed mb-6">
                    {{ $service['detailed_description'] }}
                </p>

                <div class="flex flex-col lg:flex-row gap-6 mb-6">
                    <!-- Service Image -->
                    <div class="lg:w-1/3">
                        <div
                            class="h-48 lg:h-full rounded-2xl overflow-hidden bg-{{ $service['color'] }}-50 border border-{{ $service['color'] }}-100">
                            <img src="{{ $serviceImages[$service['title']] ?? 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' }}"
                                alt="{{ $service['title'] }} at Bio Pacific"
                                class="w-full h-auto max-w-full object-cover block" loading="lazy">
                        </div>
                    </div>

                    <!-- Key Features -->
                    <div class="lg:w-2/3">
                        <h4 class="text-lg font-bold text-secondary mb-4">Key Features:</h4>
                        <ul class="space-y-3">
                            @foreach($service['features'] as $feature)
                            <li class="flex items-start gap-3">
                                <div
                                    class="w-5 h-5 rounded-full bg-{{ $service['color'] }}-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                    <svg class="w-3 h-3 text-{{ $service['color'] }}-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-slate-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                    <button onclick="closeServiceModal3AndNavigate('modal-{{ $index }}', '#contact')"
                        class="flex-1 bg-accent text-white px-6 py-3 rounded-full text-center hover:bg-accent/90 transition-colors">
                        Contact Us About This Service
                    </button>
                    <button onclick="closeServiceModal3('modal-{{ $index }}')"
                        class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-full hover:bg-gray-200 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</section>

<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 640px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }

    @media (min-width: 641px) and (max-width: 1023px) {
        .grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

<script>
    function openServiceModal3(modalId, event) {
  if (event) event.stopPropagation();
  const modal = document.getElementById(modalId);
  const modalContent = document.getElementById('modal-content-' + modalId.split('-')[1]);

  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.classList.add('overflow-hidden');

  // Animate modal appearance
  setTimeout(() => {
    modalContent.classList.remove('scale-95', 'opacity-0');
    modalContent.classList.add('scale-100', 'opacity-100');
  }, 10);
}

function closeServiceModal3(modalId) {
  const modal = document.getElementById(modalId);
  const modalContent = document.getElementById('modal-content-' + modalId.split('-')[1]);

  // Animate modal disappearance
  modalContent.classList.remove('scale-100', 'opacity-100');
  modalContent.classList.add('scale-95', 'opacity-0');

  setTimeout(() => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }, 300);
}

function closeServiceModal3AndNavigate(modalId, targetSection) {
  closeServiceModal3(modalId);

  setTimeout(() => {
    document.querySelector(targetSection).scrollIntoView({
      behavior: 'smooth'
    });
  }, 350);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('backdrop-blur-sm')) {
    const modals = document.querySelectorAll('[id^="modal-"]');
    modals.forEach(modal => {
      const modalId = modal.id;
      closeModal(modalId);
    });
  }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modals = document.querySelectorAll('[id^="modal-"]');
    modals.forEach(modal => {
      const modalId = modal.id;
      closeModal(modalId);
    });
  }
});
</script>
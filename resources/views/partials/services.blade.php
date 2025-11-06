<section id="services" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Our <span class="text-accent">Care & Services</span>',
    'section_sub_header' => 'Comprehensive care and enriching services designed to enhance quality of life for every
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
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
      </path>',
      'color' => 'red',
      'detailed_description' => 'Our skilled nursing team provides comprehensive 24/7 clinical care with registered
      nurses and licensed practical nurses on-site at all times. We specialize in complex medical conditions including
      wound care management, IV therapy administration, medication management and monitoring, post-surgical care, and
      chronic disease management. Our nurses work closely with physicians to ensure optimal health outcomes and provide
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
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z">
      </path>',
      'color' => 'blue',
      'detailed_description' => 'Our comprehensive rehabilitation program features licensed physical, occupational, and
      speech therapists who create individualized treatment plans focused on restoring function and independence. We
      utilize state-of-the-art equipment and evidence-based techniques to help residents recover from surgery, injury,
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
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21l4-4 4 4"></path>',
      'color' => 'green',
      'detailed_description' => 'Our long-term care services provide comprehensive support for residents who need
      ongoing assistance with daily activities. We focus on maintaining dignity, independence, and quality of life
      through personalized care plans that address each resident\'s unique needs, preferences, and goals while fostering
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
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
      </path>',
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
      'Family education and support groups'
      ]
      ],
      [
      'title' => 'Hospice Care',
      'description' => 'Compassionate comfort-focused end-of-life support for residents and families.',
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
      </path>',
      'color' => 'orange',
      'detailed_description' => 'Our hospice care program focuses on comfort, dignity, and quality of life for residents
      in their final stages of life. We work closely with hospice providers to ensure comprehensive pain management,
      emotional support, and spiritual care while providing families with guidance and comfort during this difficult
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
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
      </path>',
      'color' => 'yellow',
      'detailed_description' => 'Our dining program features chef-prepared, nutritionally balanced meals designed to
      meet the dietary needs and preferences of our residents. We accommodate special diets, cultural preferences, and
      medical requirements while creating an enjoyable dining experience that promotes social interaction and maintains
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
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M15 14h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
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
      'Community outings and events'
      ]
      ],
      [
      'title' => 'Transportation',
      'description' => 'Coordinated medical appointments, shopping trips, and community outings.',
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
      </path>',
      'color' => 'indigo',
      'detailed_description' => 'Our transportation services ensure residents can maintain connections with their
      community and access essential services. We provide safe, comfortable transportation for medical appointments,
      shopping trips, family visits, and recreational outings, helping residents maintain their independence and quality
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
      <div
        class="group bg-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-{{ $service['color'] }}-200 hover:-translate-y-1">
        <!-- Icon -->
        <div
          class="w-14 h-14 bg-{{ $service['color'] }}-100 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-{{ $service['color'] }}-200 transition-colors">
          <svg class="w-7 h-7 text-{{ $service['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $service['icon'] !!}
          </svg>
        </div>

        <!-- Content -->
        <h3
          class="text-xl font-bold text-secondary mb-3 group-hover:text-{{ $service['color'] }}-700 transition-colors">
          {{ $service['title'] }}
        </h3>
        <p class="text-slate-600 leading-relaxed mb-4 text-sm">
          {{ $service['description'] }}
        </p>

        <!-- CTA -->
        <button type="button" data-modal-target="modal-{{ $index }}" aria-controls="modal-{{ $index }}"
          aria-expanded="false"
          class="inline-flex items-center justify-center gap-2 bg-{{ $service['color'] }}-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow hover:bg-{{ $service['color'] }}-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $service['color'] }}-300">
          <span>Learn more</span>
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
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
          Our team is here to answer your questions and help you understand how our services can benefit you or your
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
  <div id="modal-{{ $index }}" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4"
    role="dialog" aria-modal="true" aria-hidden="true" tabindex="-1" data-modal>
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" role="document">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-{{ $service['color'] }}-100 rounded-2xl flex items-center justify-center">
            <svg class="w-6 h-6 text-{{ $service['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              {!! $service['icon'] !!}
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-secondary">{{ $service['title'] }}</h3>
        </div>
        <button onclick="closeModal('modal-{{ $index }}')"
          class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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
              class="aspect-video rounded-2xl overflow-hidden bg-{{ $service['color'] }}-50 border border-{{ $service['color'] }}-100">
              @php
              $serviceImages = [
              'Skilled Nursing' =>
              'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Rehabilitation' =>
              'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Long-term Care' =>
              'https://images.unsplash.com/photo-1559757175-0eb30cd8c063?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Memory Care' =>
              'https://images.unsplash.com/photo-1581833971358-2c8b550f87b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Hospice Care' =>
              'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Dining & Nutrition' =>
              'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Recreation & Activities' =>
              'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
              'Transportation' =>
              'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
              ];
              @endphp
              <img
                src="{{ $serviceImages[$service['title']] ?? 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' }}"
                alt="{{ $service['title'] }} at {{ $facility['name'] }}" class="w-full h-full object-cover"
                loading="lazy">
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
                  <svg class="w-3 h-3 text-{{ $service['color'] }}-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <span class="text-slate-700">{{ $feature }}</span>
              </li>
              @endforeach
            </ul>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
          <button onclick="closeModalAndNavigate('modal-{{ $index }}', '#contact')"
            class="flex-1 bg-accent text-white px-6 py-3 rounded-full text-center hover:bg-accent/90 transition-colors">
            Contact Us About This Service
          </button>
          <button onclick="closeModal('modal-{{ $index }}')"
            class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-full hover:bg-gray-200 transition-colors">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</section>

<script>
  function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  // Save previously focused element to restore later
  modal.__previousActive = document.activeElement;
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('overflow-hidden');
  // Focus the modal for accessibility
  modal.focus();
  // update triggering button aria-expanded if any
  const trigger = document.querySelector("[data-modal-target='" + modalId + "']");
  if (trigger) trigger.setAttribute('aria-expanded', 'true');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('overflow-hidden');
  // restore focus
  try {
    if (modal.__previousActive) modal.__previousActive.focus();
  } catch (e) {}
  // update triggering button aria-expanded if any
  const trigger = document.querySelector("[data-modal-target='" + modalId + "']");
  if (trigger) trigger.setAttribute('aria-expanded', 'false');
}

function closeModalAndNavigate(modalId, targetSection) {
  // Close the modal first
  closeModal(modalId);

  // Small delay to ensure modal closes before navigation
  setTimeout(() => {
    document.querySelector(targetSection).scrollIntoView({
      behavior: 'smooth'
    });
  }, 150);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  // If clicking on the backdrop (the element with [data-modal]) close that modal
  const modalBackdrop = e.target.closest('[data-modal]');
  if (modalBackdrop && e.target === modalBackdrop) {
    const id = modalBackdrop.getAttribute('id');
    if (id) closeModal(id);
  }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modals = document.querySelectorAll('[id^="modal-"]');
    modals.forEach(modal => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    });
    document.body.classList.remove('overflow-hidden');
  }
});
</script>
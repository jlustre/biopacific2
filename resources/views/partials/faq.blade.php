<section id="faqs" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center mb-12">
      <h2 class="text-3xl sm:text-4xl font-bold text-secondary mb-4">
        Frequently Asked Questions
      </h2>
      <p class="text-lg text-slate-600 max-w-2xl mx-auto">
        Find answers to common questions about our services and facilities
      </p>
    </div>

    <!-- FAQ Accordion -->
    <div class="space-y-4">
      @foreach([
        [
          'question' => 'What insurances do you accept?',
          'answer' => 'We accept Medicare, Medicaid, and many private insurance plans including Blue Cross Blue Shield, Aetna, Humana, and others. Contact our billing department for specific plan verification and coverage details.',
          'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 2.3-.72 4.396-1.888 6.168-3.38.522-.439 1.022-.9 1.5-1.38.145-.146.288-.294.43-.444A11.956 11.956 0 0021 9a12.02 12.02 0 00.382-3.016z'
        ],
        [
          'question' => 'Can families visit daily?',
          'answer' => 'Yes, families are welcome to visit daily during our visiting hours (9:00 AM - 7:00 PM). Please check in at the front desk and follow our visitor guidelines. We also offer extended hours for special circumstances.',
          'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
        ],
        [
          'question' => 'Do you offer specialized diets?',
          'answer' => 'Yes, our registered dietitians work closely with residents and families to create personalized meal plans accommodating dietary restrictions, allergies, cultural preferences, and medical requirements such as diabetic, low-sodium, or pureed diets.',
          'icon' => 'M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zM3 9a2 2 0 012-2h14a2 2 0 012 2v.01A2 2 0 0119 11H5a2 2 0 01-2-1.99V9z'
        ],
        [
          'question' => 'How do I schedule a tour?',
          'answer' => 'You can schedule a tour by clicking our "Book a Tour" button, calling our front desk or visiting us in person. We offer guided tours Monday through Saturday and can accommodate special scheduling needs.',
          'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
        ],
        [
          'question' => 'What activities and programs do you offer?',
          'answer' => 'We provide a comprehensive activities program including physical therapy, arts and crafts, music therapy, social events, religious services, educational programs, and outdoor activities. Our activity calendar is updated monthly.',
          'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
        ],
        [
          'question' => 'What safety measures are in place?',
          'answer' => 'We maintain 24/7 nursing staff, security systems, emergency call systems in every room, fire safety protocols, infection control measures, and regular safety drills. Our facility is fully licensed and regularly inspected.',
          'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 2.3-.72 4.396-1.888 6.168-3.38.522-.439 1.022-.9 1.5-1.38.145-.146.288-.294.43-.444A11.956 11.956 0 0021 9a12.02 12.02 0 00.382-3.016z'
        ]
      ] as $faq)

      <div class="bg-white rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all duration-200">
        <details class="group">
          <summary class="cursor-pointer list-none p-6 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-xl">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <!-- Icon -->
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-open:bg-blue-500 transition-colors duration-200">
                  <svg class="w-5 h-5 text-blue-600 group-open:text-white transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $faq['icon'] }}"></path>
                  </svg>
                </div>
                <!-- Question -->
                <h3 class="text-lg font-semibold text-slate-900 group-open:text-blue-700 transition-colors duration-200">
                  {{ $faq['question'] }}
                </h3>
              </div>
              <!-- Chevron -->
              <div class="flex-shrink-0 ml-4">
                <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 group-open:text-blue-500 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </summary>

          <!-- Answer -->
          <div class="px-6 pb-6">
            <div class="ml-14 pt-4 pl-4 border-l-2 border-blue-100">
              <p class="text-slate-600 leading-relaxed">{{ $faq['answer'] }}</p>
            </div>
          </div>
        </details>
      </div>

      @endforeach
    </div>

    <!-- Contact CTA -->
    <div class="mt-12 text-center">
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Still have questions?</h3>
        <p class="text-slate-600 mb-4">Our friendly staff is here to help you with any additional questions.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <a href="tel:(555)123-4567" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            Call Us
          </a>
          <a href="#contact" class="inline-flex items-center justify-center px-6 py-3 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Contact Form
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

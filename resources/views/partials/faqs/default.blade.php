<section id="faqs" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Frequently Asked Questions',
    'section_sub_header' => "Find answers to common questions about our services and facilities"
    ])

    <!-- Category Chips -->
    <div class="mb-6 flex flex-wrap gap-2 text-sm">
      <button type="button"
        class="faq-chip rounded-full border border-slate-500 bg-slate-100 px-3 py-1.5 text-slate-700 hover:bg-slate-300"
        data-category="all" onclick="filterFaqs('all')">All</button>
      @foreach($categories as $cat)
      <button type="button"
        class="faq-chip rounded-full border border-slate-500 bg-slate-100 px-3 py-1.5 text-slate-700 hover:bg-slate-300"
        data-category="{{ strtolower($cat) }}" onclick="filterFaqs('{{ strtolower($cat) }}')">{{ ucfirst($cat)
        }}</button>
      @endforeach
    </div>

    <!-- FAQ Accordion -->
    <div class="space-y-4" id="faqList">
      @foreach($faqs as $faq)
      <div
        class="bg-green-50 text-slate-900 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-all duration-200 faq-item"
        data-category="{{ strtolower($faq->category) }}">
        <details class="group">
          <summary
            class="cursor-pointer list-none p-6 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-xl">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <!-- Icon -->
                <div
                  class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-open:bg-blue-500 transition-colors duration-200">
                  <svg class="w-5 h-5 text-blue-600 group-open:text-white transition-colors duration-200" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $faq->icon }}"></path>
                  </svg>
                </div>
                <!-- Question -->
                <h3
                  class="text-lg font-semibold text-slate-900 group-open:text-blue-700 transition-colors duration-200">
                  {{ $faq->question }}
                </h3>
              </div>
              <!-- Chevron -->
              <div class="flex-shrink-0 ml-4">
                <svg
                  class="w-5 h-5 text-slate-400 group-open:rotate-180 group-open:text-blue-500 transition-all duration-200"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </summary>

          <!-- Answer -->
          <div class="px-6 pb-6">
            <div class="ml-14 pt-4 pl-4 border-l-2 border-blue-100">
              <p class="text-slate-600 leading-relaxed">{{ $faq->answer }}</p>
            </div>
          </div>
        </details>
      </div>
      @endforeach
    </div>

    <script>
      function filterFaqs(category) {
        document.querySelectorAll('.faq-item').forEach(function(item) {
          if (category === 'all' || item.getAttribute('data-category') === category) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });
      }
    </script>

    <!-- Contact CTA -->
    <div class="mt-12 text-center">
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Still have questions?</h3>
        <p class="text-slate-600 mb-4">Our friendly staff is here to help you with any additional questions.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <a href="tel:(555)123-4567"
            class="inline-flex items-center justify-center px-6 py-3 bg-secondary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
              </path>
            </svg>
            Call Us @ <span class="ml-1">
              @php
              function formatPhone($phone) {
              $digits = preg_replace('/\D/', '', $phone);
              if(strlen($digits) == 10) {
              return '(' . substr($digits,0,3) . ') ' . substr($digits,3,3) . '-' . substr($digits,6);
              }
              return $phone;
              }
              @endphp
              {{ isset($facility->phone) ? formatPhone($facility->phone) : '(555)123-4567' }}
            </span>
          </a>
          <a href="#contact"
            class="inline-flex items-center justify-center px-6 py-3 bg-slate-100 text-primary font-medium rounded-lg hover:bg-slate-200 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
              </path>
            </svg>
            Contact Form
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
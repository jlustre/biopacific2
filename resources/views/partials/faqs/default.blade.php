<section class="py-16 bg-gradient-to-br from-slate-50 to-blue-50/60" id="faqs"
  x-data="{ openFaq: null, selectedCategory: 'all' }">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-10 text-center">
      <h2 class="text-3xl font-extrabold text-blue-900 mb-2" style="color: {{ $primary }}">Frequently Asked Questions
      </h2>
      <p class="text-slate-600 text-lg">Find answers to common questions about our facility, services, and more.</p>
    </div>
    @php
    $categories = collect($faqs)->pluck('category')->filter()->unique()->values();
    @endphp
    <div class="flex flex-wrap justify-center gap-2 mb-8">
      <button @click="selectedCategory = 'all'"
        :class="selectedCategory === 'all' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-100 text-slate-700 border-slate-300'"
        class="faq-chip px-4 py-2 rounded-full border font-medium transition-colors focus:outline-none">
        All
      </button>
      @foreach($categories as $cat)
      <button @click="selectedCategory = '{{ addslashes($cat) }}'"
        :class="selectedCategory === '{{ addslashes($cat) }}' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-100 text-slate-700 border-slate-300'"
        class="faq-chip px-4 py-2 rounded-full border font-medium transition-colors focus:outline-none">
        {{ $cat }}
      </button>
      @endforeach
    </div>
    <div class="grid gap-6 md:grid-cols-2">
      @forelse($faqs as $faq)
      <div class="faq-item bg-white rounded-2xl shadow-md border border-blue-100 p-6 group transition hover:shadow-xl"
        data-category="{{ $faq->category ?? 'general' }}"
        x-show="selectedCategory === 'all' || selectedCategory === '{{ addslashes($faq->category ?? 'general') }}'"
        x-transition>
        <button type="button" class="w-full flex items-start justify-between text-left focus:outline-none"
          @click="openFaq === {{ $faq->id }} ? openFaq = null : openFaq = {{ $faq->id }}"
          :aria-expanded="openFaq === {{ $faq->id }}">
          <div class="flex-1">
            <h3
              class="text-lg font-semibold text-blue-900 group-hover:text-blue-700 transition-colors duration-200 flex items-center gap-2">
              {{ $faq->question }}
              @if($faq->is_featured)
              <span class="text-xs font-medium bg-yellow-100 px-2 py-1 rounded-full ml-2"
                style="color: {{ $accent }}">Featured</span>
              @endif
              @if($faq->is_default)
              <span class="text-xs font-medium bg-blue-100 px-2 py-1 rounded-full ml-2"
                style="color: {{ $primary }}">Default</span>
              @endif
            </h3>
            @if($faq->category)
            <span class="text-xs font-medium bg-blue-50 px-2 py-1 rounded-full mt-2 inline-block"
              style="color: {{ $secondary }}">{{ $faq->category }}</span>
            @endif
          </div>
          <span class="ml-4 flex-shrink-0">
            <svg class="w-6 h-6 text-blue-400 group-hover:text-blue-600 transition-transform duration-200"
              :class="{'rotate-180': openFaq === {{ $faq->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </span>
        </button>
        <div x-show="openFaq === {{ $faq->id }}" class="mt-4 border-t border-blue-100 pt-4"
          x-bind:aria-expanded="openFaq === {{ $faq->id }}">
          @php
          // Replace placeholders in FAQ answer
          $answerText = $faq->answer;
          if (isset($facility)) {
          $facilityPhone = null;
          $facilityHours = null;
          $facilityName = null;
          $facilityEmail = null;
          $facilityAddress = null;
          $facilityBeds = null;
          if (is_array($facility)) {
          $facilityPhone = $facility['phone'] ?? null;
          $facilityHours = $facility['hours'] ?? null;
          $facilityName = $facility['name'] ?? null;
          $facilityEmail = $facility['email'] ?? null;
          $facilityBeds = $facility['beds'] ?? null;
          $facilityAddress = trim(($facility['address'] ?? '') . ', ' . ($facility['city'] ?? '') . ', ' .
          ($facility['state'] ?? ''), ', ');
          } elseif (is_object($facility)) {
          $facilityPhone = $facility->phone ?? null;
          $facilityHours = $facility->hours ?? null;
          $facilityName = $facility->name ?? null;
          $facilityEmail = $facility->email ?? null;
          $facilityBeds = $facility->beds ?? null;
          $facilityAddress = trim(($facility->address ?? '') . ', ' . ($facility->city ?? '') . ', ' . ($facility->state
          ?? ''), ', ');
          }
          if ($facilityPhone) {
          $formattedPhone = \App\Helpers\PhoneHelper::format($facilityPhone);
          $answerText = str_replace('[phone number]', $formattedPhone, $answerText);
          }
          if ($facilityHours) {
          $answerText = str_replace('[visiting hours]', $facilityHours, $answerText);
          }
          if ($facilityName) {
          $answerText = str_replace('[facility name]', $facilityName, $answerText);
          $answerText = str_replace('[Facility name]', $facilityName, $answerText);
          }
          if ($facilityEmail) {
          $answerText = str_replace('[email]', $facilityEmail, $answerText);
          }
          if ($facilityAddress && $facilityAddress !== ', , ') {
          $answerText = str_replace('[facility address]', $facilityAddress, $answerText);
          }
          if ($facilityBeds) {
          $answerText = str_replace('[bed count]', $facilityBeds, $answerText);
          }
          }
          @endphp
          <p class="text-slate-700 leading-relaxed">{!! nl2br(e($answerText)) !!}</p>
        </div>
      </div>
      @empty
      <div class="text-center py-8 text-gray-500">
        <svg class="mx-auto text-gray-300 mb-4" width="48" height="48" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 10h.01M12 14h.01M16 10h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
        </svg>
        <p class="text-lg font-medium">No FAQs available</p>
        <p class="text-sm">Check back later for frequently asked questions.</p>
      </div>
      @endforelse
    </div>

    <script>
      function filterFaqs(category) {
        // Update chip styles
        document.querySelectorAll('.faq-chip').forEach(function(chip) {
          chip.classList.remove('active', 'bg-blue-500', 'text-white', 'border-blue-500');
          chip.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-500');
        });
        
        // Style active chip
        const activeChip = document.querySelector(`[data-category="${category}"]`);
        if (activeChip) {
          activeChip.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-500');
          activeChip.classList.add('active', 'bg-blue-500', 'text-white', 'border-blue-500');
        }
        
        // Filter FAQs
        document.querySelectorAll('.faq-item').forEach(function(item) {
          if (category === 'all' || item.getAttribute('data-category') === category) {
            item.style.display = '';
          } else {
            item.style.display = 'none';
          }
        });
      }
      
      // Initialize with "All" selected
      document.addEventListener('DOMContentLoaded', function() {
        filterFaqs('all');
      });
    </script>

    <!-- Contact CTA -->
    <div class="mt-12 text-center">
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-xl font-semibold mb-2" style="color: {{ $secondary }}">Still have questions?</h3>
        <p class="text-slate-600 mb-4">Our friendly staff is here to help you with any additional questions.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          @php
          // Handle both array and object facility data with better error handling
          $facilityPhone = null;

          try {
          if (isset($facility)) {
          if (is_array($facility) && !empty($facility['phone'])) {
          $facilityPhone = $facility['phone'];
          } elseif (is_object($facility) && !empty($facility->phone)) {
          $facilityPhone = $facility->phone;
          }
          }
          } catch (Exception $e) {
          // Fallback to default if there's any error
          $facilityPhone = null;
          }

          // Ensure we always have a phone number for the button
          if (!$facilityPhone) {
          $facilityPhone = '4083779275'; // Fallback phone number
          }
          @endphp
          <a href="tel:{{ \App\Helpers\PhoneHelper::forTel($facilityPhone) }}"
            class="inline-flex items-center justify-center px-6 py-3 text-white font-medium rounded-lg transition-colors duration-200"
            style="background: {{ $primary }}; hover:bg-opacity-80">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
              </path>
            </svg>
            Call Us @ {{ \App\Helpers\PhoneHelper::format($facilityPhone) }}
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
</section>
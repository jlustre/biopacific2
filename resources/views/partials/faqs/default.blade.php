@php
// Color scheme logic
$scheme = isset($facility['color_scheme_id']) ? \DB::table('color_schemes')->find($facility['color_scheme_id']) : null;
$primary = $primary ?? ($scheme->primary_color ?? '#0EA5E9');
$secondary = $secondary ?? ($scheme->secondary_color ?? '#1E293B');
$accent = $accent ?? ($scheme->accent_color ?? '#F59E0B');
@endphp
@forelse($faqs as $faq)
<div class="flex-1">
  <h3 class="text-lg font-semibold text-slate-900 group-open:text-blue-700 transition-colors duration-200">
    {{ $faq->question }}
  </h3>
  @if($faq->category)
  <span class="text-xs text-slate-500 font-medium bg-slate-200 px-2 py-1 rounded-full mt-1 inline-block">
    {{ $faq->category }}
  </span>
  @endif
  @if($faq->is_featured)
  <span class="text-xs text-yellow-800 font-medium bg-yellow-100 px-2 py-1 rounded-full mt-1 ml-2 inline-block">
    Featured
  </span>
  @endif
  @if($faq->is_default)
  <span class="text-xs text-blue-800 font-medium bg-blue-100 px-2 py-1 rounded-full mt-1 ml-2 inline-block">
    Default
  </span>
  @endif
</div>
<!-- Chevron -->
<div class="flex-shrink-0 ml-4">
  <svg class="w-5 h-5 text-slate-400 group-open:rotate-180 group-open:text-blue-500 transition-all duration-200"
    fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
  </svg>
</div>
<!-- Answer -->
<div class="px-6 pb-6">
  <div class="ml-14 pt-4 pl-4 border-l-2 border-blue-100">
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
    $facilityAddress = trim(($facility->address ?? '') . ', ' . ($facility->city ?? '') . ', ' .
    ($facility->state ?? ''), ', ');
    }

    // Replace all placeholders
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
    <p class="text-slate-600 leading-relaxed">{!! nl2br(e($answerText)) !!}</p>
  </div>
</div>
@empty
<div class="text-center py-8 text-gray-500">
  <i class="fas fa-question-circle text-4xl mb-4 text-gray-300"></i>
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
    <h3 class="text-xl font-semibold text-slate-900 mb-2">Still have questions?</h3>
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
        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
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
</div>
</section>
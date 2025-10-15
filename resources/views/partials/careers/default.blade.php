@php
if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->where('id', $facility['color_scheme_id'])->first();
$primary = $scheme ? ($scheme->primary_color ?? '#0EA5E9') : '#0EA5E9';
$secondary = $scheme ? ($scheme->secondary_color ?? '#1E293B') : '#1E293B';
$accent = $scheme ? ($scheme->accent_color ?? '#F59E0B') : '#F59E0B';
} else {
$primary = '#0EA5E9';
$secondary = '#1E293B';
$accent = '#F59E0B';
}
@endphp
<section id="careers" x-data="{ openApply: false, applyRole: '' }"
  class="py-16 sm:py-24 bg-gradient-to-br from-slate-50"
  style="background: linear-gradient(to bottom right, #f8fafc, {{ $primary }});">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Join Our Team',
    'section_sub_header' => "Build a rewarding career in healthcare with ". e($facility['name']) .". We're looking for
    passionate professionals to make a difference."
    ])


    <!-- Job Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 mb-12">
      @foreach([
      ['Registered Nurse (RN)','Full-time • San Pablo, CA', 'Provide exceptional patient care in our state-of-the-art
      facility.', 'stethoscope'],
      ['Medical Receptionist','Full-time • San Pablo, CA', 'Greet patients, manage appointments, and support front desk
      operations.', 'clock'],
      ['Certified Nursing Assistant (CNA)','Part-time • San Pablo, CA', 'Support our nursing team and help patients with
      daily activities.', 'heart'],
      ['Physical Therapist','Part-time • San Pablo, CA', 'Help patients recover and improve their mobility and quality
      of life.', 'activity'],
      ] as [$role,$meta,$description,$icon])
      <div
        class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
        <!-- Card gradient overlay -->
        <div class="absolute top-0 left-0 w-full h-1"
          style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }});"></div>

        <div class="p-6 sm:p-8">
          <!-- Icon -->
          <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
            style="background: linear-gradient(to bottom right, {{ $primary }}, {{ $accent }});">
            @if($icon === 'stethoscope')
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            @elseif($icon === 'heart')
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            @else
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            @endif
          </div>

          <!-- Job meta -->
          <div class="flex items-center text-sm text-slate-500 mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $meta }}
          </div>

          <!-- Job title -->
          <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors" class="text-secondary">
            {{ $role }}
          </h3>

          <!-- Description -->
          <p class="text-slate-600 text-sm mb-6 leading-relaxed">
            {{ $description }}
          </p>

          <!-- Apply button -->
          <button @click="openApply=true; applyRole='{{ $role }}'"
            class="w-full bg-gradient-to-r from-primary to-accent hover:from-primary-600 hover:to-accent-600 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-primary/20">
            Apply Now
          </button>
        </div>
      </div>
      @endforeach
    </div>

    <!-- Benefits Section -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-12">
      <h3 class="text-2xl font-bold text-center mb-8 text-secondary">Why Work With Us?</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
        ['Medical Benefits', 'Comprehensive health, dental, and vision coverage', 'shield-check'],
        ['Flexible Schedule', 'Work-life balance with flexible scheduling options', 'clock'],
        ['Career Growth', 'Professional development and advancement opportunities', 'academic-cap'],
        ['Competitive Pay', 'Above-market compensation and performance bonuses', 'currency-dollar']
        ] as [$title, $desc, $icon])
        <div class="text-center">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
            style="background: linear-gradient(to bottom right, {{ $primary }}1A, {{ $accent }}1A);">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              @if($icon === 'shield-check')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              @elseif($icon === 'clock')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              @elseif($icon === 'academic-cap')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
              @else
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              @endif
            </svg>
          </div>
          <h4 class="font-semibold mb-2 text-secondary">{{ $title }}</h4>
          <p class="text-sm" style="color: #64748b;">{{ $desc }}</p>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Enhanced Apply Modal -->
  <div x-cloak x-show="openApply" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div @click.away="openApply=false" x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
      x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
      x-transition:leave-end="opacity-0 transform scale-95"
      class="bg-white rounded-2xl max-w-2xl w-full p-8 shadow-2xl max-h-[90vh] overflow-y-auto">

      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-2xl font-bold text-secondary">Apply for Position</h3>
          <p class="font-semibold text-accent" x-text="applyRole"></p>
        </div>
        <button @click="openApply=false" class="text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Application Form -->
      <form class="space-y-6">
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">First Name *</label>
            <input type="text" required class="w-full border rounded-lg px-4 py-3 transition-colors"
              style="border-color: #cbd5e1; focus:ring: 2px {{ $primary }}20; focus:border-color: {{ $primary }};"
              placeholder="Enter first name">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Last Name *</label>
            <input type="text" required class="w-full border rounded-lg px-4 py-3 transition-colors"
              style="border-color: #cbd5e1; focus:ring: 2px {{ $primary }}20; focus:border-color: {{ $primary }};"
              placeholder="Enter last name">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
          <input type="email" required class="w-full border rounded-lg px-4 py-3 transition-colors"
            style="border-color: #cbd5e1; focus:ring: 2px {{ $primary }}20; focus:border-color: {{ $primary }};"
            placeholder="your.email@example.com">
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number *</label>
          <input type="tel" required class="w-full border rounded-lg px-4 py-3 transition-colors"
            style="border-color: #cbd5e1; focus:ring: 2px {{ $primary }}20; focus:border-color: {{ $primary }};"
            placeholder="(555) 123-4567">
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Resume/CV *</label>
          <div
            class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
            <input type="file" accept=".pdf,.doc,.docx" class="hidden" id="resume-upload">
            <label for="resume-upload" class="cursor-pointer">
              <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                style="color: #94a3b8;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <p style="color: #64748b;">Click to upload or drag and drop</p>
              <p class="text-sm" style="color: #94a3b8;">PDF, DOC, DOCX (max 10MB)</p>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Cover Letter (Optional)</label>
          <textarea rows="4" class="w-full border rounded-lg px-4 py-3 transition-colors"
            style="border-color: #cbd5e1; focus:ring: 2px {{ $primary }}20; focus:border-color: {{ $primary }};"
            placeholder="Tell us why you're interested in this position..."></textarea>
        </div>

        <div class="bg-slate-50 rounded-lg p-4">
          <label class="flex items-start gap-3 text-sm text-slate-600 cursor-pointer">
            <input type="checkbox" required class="mt-1 rounded" style="border-color: #cbd5e1; color: {{ $primary }};">
            <span>I consent to be contacted regarding this application. I understand that no protected health
              information (PHI) will be shared during the recruitment process. *</span>
          </label>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t">
          <button type="button" @click="openApply=false" class="px-6 py-3 rounded-lg border transition-colors"
            style="border-color: #cbd5e1; color: #334155; background: #f8fafc;">
            Cancel
          </button>
          <button type="submit" @click.prevent="toast('Application submitted successfully!'); openApply=false"
            class="px-6 py-3 rounded-lg text-white font-semibold transition-all duration-300 transform hover:scale-105"
            style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }});">
            Submit Application
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Messages Script -->
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('careersSection', () => ({
        openApply: false,
        applyRole: '',
        toastOpen: @js($success || $error),
        toastMsg: @js($success ? $success : ($error ? 'There was an error submitting your application.' : '')),
        closeToast() {
          this.toastOpen = false;
        },
        openApplyModal(role) {
          this.applyRole = role;
          this.openApply = true;
        }
      }));
    });
  </script>
</section>
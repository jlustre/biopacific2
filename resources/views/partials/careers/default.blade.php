@php
if (!isset($applications)) {
$applications = collect();
}

// Fetch job openings for this facility from the database
$jobOpenings = \App\Models\JobOpening::where('facility_id', $facility['id'] ?? null)
->where('active', true)
->orderByDesc('posted_at')
->get();
@endphp
@php
$success = session('success');
$error = $errors->any();
@endphp
<section id="careers" x-data="{ 
    openApply: false, 
    applyRole: '', 
    toastOpen: false, 
    toastMsg: '',
    infoModalOpen: false,
    infoModalJobId: null,
    selectedJobData: {}
  }" x-init="applyRole = '{{ old('job_opening_id') ?? '' }}'"
  @close-apply-modal.window="openApply=false; applyRole=''; applyRoleTitle=''; if (window.Livewire) { Livewire.dispatch('setJobOpening', { jobOpeningId: null }); }"
  class="py-16 sm:py-24 bg-gradient-to-br from-slate-50"
  style="background: linear-gradient(to bottom right, #f8fafc, {{ $primary }});">\
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Join Our Team',
    'section_sub_header' => "Build a rewarding career in healthcare with ". e($facility['name']) .". We're looking for
    passionate professionals to make a difference."
    ])

    <!-- Benefits Section -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-12">
      <h3 class="text-2xl font-bold text-center mb-8" style="color: {{ $primary }}">Why Work With Us?</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
        ['Medical Benefits', 'Comprehensive health, dental, and vision coverage', 'shield-check'],
        ['Flexible Schedule', 'Work-life balance with flexible scheduling options', 'clock'],
        ['Career Growth', 'Professional development and advancement opportunities', 'academic-cap'],
        ['Competitive Pay', 'Above-market compensation and performance bonuses', 'currency-dollar']
        ] as [$title, $desc, $icon])
        <div class="text-center">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
            style="background: {{ $secondary }};">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              @if($icon === 'shield-check')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 2c1.104 0 2 .896 2 2v2h2c1.104 0 2 .896 2 2v2c0 1.104-.896 2-2 2h-2v2h2c1.104 0 2 .896 2 2v2c0 1.104-.896 2-2 2h-2v2c0 1.104-.896 2-2 2s-2-.896-2-2v-2H8c-1.104 0-2-.896-2-2v-2c0-1.104.896-2 2-2h2v-2H8c-1.104 0-2-.896-2-2V8c0-1.104.896-2 2-2h2V4c0-1.104.896-2 2-2zm0 4v2h2V6h-2zm0 8v2h2v-2h-2z" />
              @endif
              @if($icon === 'clock')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              @endif
              @if($icon === 'academic-cap')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0c-3.866 0-7-1.343-7-3" />
              @endif
              @if($icon === 'currency-dollar')
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-4.418 0-8 1.79-8 4s3.582 4 8 4 8-1.79 8-4-3.582-4-8-4zm0 0V4m0 16v-4" />
              @endif
            </svg>
          </div>
          <h4 class="text-lg font-semibold mb-2" style="color: {{ $secondary }}">{{ $title }}</h4>
          <p class="text-slate-600 text-sm mb-4">{{ $desc }}</p>
        </div>
        @endforeach
      </div>
    </div>
    <!-- Job Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 mb-12">

      @forelse($jobOpenings as $job)
      <div
        class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
        <!-- Card gradient overlay -->
        <div class="absolute top-0 left-0 w-full h-1"
          style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }});"></div>

        <div class="p-6 sm:p-8">
          <!-- Icon -->
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4"
            style="background: linear-gradient(to bottom right, {{ $primary }}, {{ $accent }});">
            <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific logo" class="w-8 h-8 object-contain">
          </div>

          <!-- Job meta -->
          <div class="flex items-center text-sm text-slate-500 mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $job->employment_type }} | {{ $facility['city'].', '.$facility['state'] ?? '' }}
          </div>

          <!-- Job title -->
          <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors text-secondary">
            {{ $job->title }}
          </h3>

          <!-- Description -->
          <p class="text-slate-600 text-sm mb-6 leading-relaxed line-clamp-3">
            {{ substr(strip_tags($job->description), 0, 150) }}{{ strlen(strip_tags($job->description)) > 150 ? '...' :
            '' }}
          </p>

          <!-- Action Buttons -->
          <div class="flex gap-2 mt-4">
            <button @click="
                openApply=true; 
                applyRole='{{ $job->id }}'; 
                applyRoleTitle='{{ addslashes($job->title) }}';
                if (window.Livewire) { Livewire.dispatch('setJobOpening', { jobOpeningId: {{ $job->id }} }); }
              "
              class="flex-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm cursor-pointer transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/30 flex items-center justify-center gap-1"
              style="min-width: 0; background: linear-gradient(to right, {{ $primary }}, {{ $secondary }}); &:hover { background: linear-gradient(to right, {{ $secondary }}, {{ $primary }}); }"
              onmouseover="this.style.background='linear-gradient(to right, {{ $secondary }}, {{ $primary }})'"
              onmouseout="this.style.background='linear-gradient(to right, {{ $primary }}, {{ $secondary }})'">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17l4 4 4-4m-4-5v9" />
              </svg>
              Apply Now
            </button>
            <button @click="infoModalOpen=true; infoModalJobId={{ $job->id }}; selectedJobData={{ json_encode($job) }}"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/30 flex items-center justify-center gap-1 cursor-pointer"
              style="border-color: {{ $primary }}; color: {{ $primary }};"
              onmouseover="this.style.backgroundColor='{{ $primary }}'; this.style.color='white';"
              onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $primary }}';">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              View Details
            </button>
          </div>
        </div>
      </div>
      @empty
      <div class="col-span-1 sm:col-span-2 lg:col-span-4 text-center py-12 text-gray-500">
        <svg class="mx-auto mb-4 w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 12h20L12 2z" />
        </svg>
        <h4 class="text-2xl font-semibold mb-2" style="color: {{ $neutral_dark }}">No job openings available at this
          time.</h4>
        <p class="mb-6" style="color: {{ $neutral_light }}">You may still submit an application for future
          consideration. Please click the button below to
          fill out the form and
          indicate your desired position or title. We'll keep your application on file and reach out if a suitable
          opportunity arises.</p>
        <button
          @click="openApply=true; applyRole=''; applyRoleTitle=''; if (window.Livewire) { Livewire.dispatch('setJobOpening', { jobOpeningId: null }); }"
          class="px-6 py-2 rounded-lg text-white font-semibold transition-all duration-200"
          style="background: linear-gradient(to right, {{ $primary }}, {{ $secondary }});"
          onmouseover="this.style.background='linear-gradient(to right, {{ $secondary }}, {{ $primary }})'"
          onmouseout="this.style.background='linear-gradient(to right, {{ $primary }}, {{ $secondary }})'">
          Submit Application
        </button>
      </div>
      @endforelse
    </div>

    <!-- Job Details Info Modal -->
    <div x-cloak x-show="infoModalOpen" x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0" style="display: none;"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div @click.away="infoModalOpen=false" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="bg-white rounded-2xl max-w-2xl w-full p-8 shadow-2xl max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific logo" class="w-10 h-10 object-contain">
            <h3 class="text-2xl font-bold text-secondary">Job Details</h3>
          </div>
          <button @click="infoModalOpen=false"
            class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <h4 class="text-xl font-semibold mb-2" x-text="selectedJobData?.title || 'Job Details'"></h4>
        <div class="text-sm text-slate-500 mb-4">
          <span x-text="selectedJobData?.employment_type || ''"></span> • {{ $facility['location'] ?? '' }}
        </div>

        <div class="mb-6">
          <style>
            .job-detail-content ul {
              list-style-type: disc !important;
              margin-left: 1.5rem !important;
              padding-left: 1.5rem !important;
              margin-bottom: 1rem !important;
            }

            .job-detail-content ol {
              list-style-type: decimal !important;
              margin-left: 1.5rem !important;
              padding-left: 1.5rem !important;
              margin-bottom: 1rem !important;
            }

            .job-detail-content li {
              margin-bottom: 0.5rem !important;
              margin-left: 0 !important;
            }

            .job-detail-content h4 {
              font-weight: bold;
              margin-top: 1rem;
              margin-bottom: 0.5rem;
            }

            .job-detail-content p {
              margin-bottom: 1rem;
              line-height: 1.6;
            }
          </style>
          <div class="text-slate-700 prose prose-sm max-w-none job-detail-content"
            x-html="selectedJobData?.description || 'Loading...'"></div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
          <div><strong>Department:</strong> <span x-text="selectedJobData?.department || 'N/A'"></span></div>
          <div><strong>Employment Type:</strong> <span x-text="selectedJobData?.employment_type || 'N/A'"></span></div>
          <div><strong>Location:</strong> <span>{{ $facility['city'] ?? '' }}{{ isset($facility['city']) &&
              isset($facility['state']) ? ', ' : '' }}{{ $facility['state'] ?? '' }}</span></div>
          <div x-show="selectedJobData?.salary_range"><strong>Salary Range:</strong> <span
              x-text="selectedJobData?.salary_range || 'N/A'"></span><span x-show="selectedJobData?.salary_unit"
              x-text="' / ' + selectedJobData.salary_unit"></span></div>
          <div><strong>Posted:</strong> <span
              x-text="selectedJobData?.posted_at ? new Date(selectedJobData.posted_at).toLocaleDateString() : 'N/A'"></span>
          </div>
          <div x-show="selectedJobData?.expires_at"><strong>Expires:</strong> <span
              x-text="selectedJobData?.expires_at ? new Date(selectedJobData.expires_at).toLocaleDateString() : 'N/A'"></span>
          </div>
        </div>

        <div class="flex justify-between items-center mt-6 gap-3">
          <button @click="infoModalOpen=false"
            class="px-6 py-2 rounded-lg border text-slate-600 border-slate-300 bg-slate-50 hover:bg-slate-100 transition cursor-pointer">
            Close
          </button>
          <button
            @click="infoModalOpen=false; openApply=true; applyRole=selectedJobData.id; applyRoleTitle=selectedJobData.title; if (window.Livewire) { Livewire.dispatch('setJobOpening', { jobOpeningId: selectedJobData.id }); }"
            class="px-6 py-2 rounded-lg text-white font-semibold transition-all duration-200 cursor-pointer"
            style="background: linear-gradient(to right, {{ $primary }}, {{ $secondary }});"
            onmouseover="this.style.background='linear-gradient(to right, {{ $secondary }}, {{ $primary }})'"
            onmouseout="this.style.background='linear-gradient(to right, {{ $primary }}, {{ $secondary }})'">
            Apply Now
          </button>
        </div>
      </div>
    </div>

    <!-- Application Modal -->
    <div x-cloak x-show="openApply" x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0" style="display: none;"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div @click.away="openApply=false" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-900">Job Application</h3>
          <button @click="openApply=false" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
          @livewire('job-application', [
          'primary' => $primary,
          'secondary' => $secondary,
          'accent' => $accent,
          'neutral_dark' => $neutral_dark,
          'neutral_light' => $neutral_light,
          'facility_id' => $facility['id'] ?? null
          ], key('job-application-modal'))
        </div>
      </div>
    </div>
  </div>
</section>
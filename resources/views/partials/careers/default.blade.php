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
    toastMsg: '' 
  }" x-init="applyRole = '{{ old('job_opening_id') ?? '' }}'" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50"
  style="background: linear-gradient(to bottom right, #f8fafc, {{ $primary }});">
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
          <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
            style="background: linear-gradient(to bottom right, {{ $primary }}, {{ $accent }});">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 12h20L12 2z" />
            </svg>
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
          <p class="text-slate-600 text-sm mb-6 leading-relaxed">
            {!! $job->description !!}
          </p>

          <!-- Action Buttons -->
          <div class="flex gap-2 mt-4">
            <button @click="openApply=true; applyRole='{{ $job->id }}'; applyRoleTitle='{{ addslashes($job->title) }}'"
              class="flex-1 px-3 py-1.5 rounded-lg bg-gradient-to-r from-teal-300 to-teal-600 text-xs font-semibold text-white shadow-sm hover:from-teal-600 hover:to-teal-700 cursor-pointer transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/30 flex items-center justify-center gap-1"
              style="min-width: 0;">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17l4 4 4-4m-4-5v9" />
              </svg>
              Apply Now
            </button>
          </div>

          <!-- Info Modal -->
          <div x-data="{ infoModalOpen: false, infoModalJobId: null }">
            <template x-if="infoModalOpen && infoModalJobId === {{ $job->id }}">
              <div x-cloak x-show="infoModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div @click.away="infoModalOpen=false" x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 transform scale-95"
                  x-transition:enter-end="opacity-100 transform scale-100"
                  x-transition:leave="transition ease-in duration-200"
                  x-transition:leave-start="opacity-100 transform scale-100"
                  x-transition:leave-end="opacity-0 transform scale-95"
                  class="bg-white rounded-2xl max-w-xl w-full p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
                  <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-secondary">Job Details</h3>
                    <button @click="infoModalOpen=false" class="text-slate-400 hover:text-slate-600 transition-colors">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <h4 class="text-xl font-semibold mb-2">{{ $job->title }}</h4>
                  <div class="text-sm text-slate-500 mb-2">{{ $job->employment_type }} • {{ $facility['location'] ?? ''
                    }}
                  </div>
                  <div class="mb-4 text-slate-700">{!! $job->description !!}</div>
                  <div class="mb-2"><strong>Department:</strong> {{ $job->department }}</div>
                  <div class="mb-2"><strong>Posted:</strong> {{ $job->posted_at }}</div>
                  <div class="mb-2"><strong>Expires:</strong> {{ $job->expires_at }}</div>
                  <div class="mb-2"><strong>Status:</strong> {{ $job->active ? 'Active' : 'Inactive' }}</div>
                  <div class="flex justify-end mt-6">
                    <button @click="infoModalOpen=false"
                      class="px-6 py-2 rounded-lg border text-primary border-primary bg-slate-50 hover:bg-primary hover:text-white transition">Close</button>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
      @empty
      <div class="col-span-1 sm:col-span-2 lg:col-span-4 text-center py-12 text-gray-500">
        <svg class="mx-auto mb-4 w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 12h20L12 2z" />
        </svg>
        <h4 class="text-lg font-semibold mb-2">No job openings available at this time.</h4>
        <p>Please check back later or contact us for more information.</p>
      </div>
      @endforelse
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
        class="bg-white rounded-2xl max-w-xl w-full p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
        @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-300">
          {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-300">
          <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold" style="color: {{ $secondary }}">Apply for Position</h3>
          <button @click="openApply=false" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form method="POST" action="{{ route('careers.apply') }}" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <input type="hidden" name="job_opening_id" x-model="applyRole" required>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
            <input type="text" name="first_name" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
            <input type="text" name="last_name" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Resume (PDF, DOCX)</label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx" required
              class="w-full rounded-lg border border-slate-300 px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Cover Letter</label>
            <textarea name="cover_letter" rows="4"
              class="w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
          </div>
          <div class="flex items-center">
            <input type="checkbox" name="consent" value="1" required class="mr-2">
            <span class="text-sm text-slate-600">I consent to the processing of my personal data for recruitment
              purposes.</span>
          </div>
          <div class="flex justify-end mt-6">
            <button type="submit"
              class="px-6 py-2 rounded-lg bg-teal-500 text-white font-semibold hover:bg-teal-600 transition">
              Submit Application
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
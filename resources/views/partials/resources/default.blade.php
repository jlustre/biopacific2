@php
$primary = $primary ?? '#0EA5E9';
$secondary = $secondary ?? '#1E293B';
$accent = $accent ?? '#F59E0B';
@endphp
<section id="resources" class="py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- SectionHeader -->
    @include('partials.section_header', [
    'section_header' => 'Downloadable Resources',
    'section_sub_header' => "Access important documents and information to help you learn more about our community"
    ])

    <!-- Resources Grid -->
    <!-- Mobile Menu Button -->
    <div class="mb-6 block sm:hidden text-center">
      <button onclick="toggleResourceMenu()"
        class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg font-medium shadow transition-colors duration-200"
        style="background: {{ $primary }};">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        Resources Menu
      </button>
    </div>

    <!-- Resources Grid -->
    <div id="resourcesGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 lg:gap-8">
      @foreach([
      ['Menu','Weekly meal plan and dietary options','/placeholder.pdf', 'bg-red-500', 'M4 6h16M4 10h16M4 14h16M4
      18h16'],
      ['Brochure','Overview of services and amenities','/placeholder.pdf', 'bg-blue-500', 'M9 12h6m-6 4h6m2 5H7a2 2 0
      01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
      ['Resident Handbook','Guidelines and daily living information','/placeholder.pdf', 'bg-green-500', 'M12
      6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5
      1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746
      0-3.332.477-4.5 1.253'],
      ['Application Form','Start your admission process','/placeholder.pdf', 'bg-purple-500', 'M9 12h6m-6
      4h6m-6-8h6m-3-5v4m0 0l-2-2m2 2l2-2M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
      ] as [$title,$desc,$href,$color,$icon])
      <div
        class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
        <!-- Icon Header -->
        <div class="relative {{ $color }} p-6">
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white">{{ $title }}</h3>
        </div>

        <!-- Content -->
        <div class="p-6">
          <p class="text-slate-600 mb-6 leading-relaxed">{{ $desc }}</p>

          <!-- Download Button -->
          <a href="{{ $href }}"
            class="inline-flex items-center gap-2 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 group-hover:scale-105 transform"
            style="background: {{ $primary }};">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-4-4m4 4l4-4m-4-8H8a2 2 0 00-2 2v16a2 2 0 002 2h8a2 2 0 002-2V8a2 2 0 00-2-2z"></path>
            </svg>
            Download PDF
          </a>
        </div>

        <!-- File Info -->
        <div class="px-6 pb-6">
          <div class="flex items-center justify-between text-xs text-slate-500 bg-slate-50 rounded-lg p-3">
            <span class="flex items-center gap-1">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                  d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z">
                </path>
              </svg>
              PDF Document
            </span>
            <span>~ 2.5 MB</span>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <!-- Mobile Resource Menu -->
    <div id="resourceMenu" class="fixed inset-0 z-50 bg-black bg-opacity-60 flex items-center justify-center sm:hidden"
      style="display:none;">
      <div class="bg-white rounded-xl shadow-lg p-6 w-80 max-w-full">
        <h3 class="text-lg font-bold mb-4">Resources</h3>
        <ul class="space-y-4">
          @foreach([
          ['Menu','Weekly meal plan and dietary options','/placeholder.pdf'],
          ['Brochure','Overview of services and amenities','/placeholder.pdf'],
          ['Resident Handbook','Guidelines and daily living information','/placeholder.pdf'],
          ['Application Form','Start your admission process','/placeholder.pdf'],
          ] as [$title,$desc,$href])
          <li>
            <a href="{{ $href }}"
              class="block px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90 transition-colors duration-200">
              {{ $title }}
            </a>
            <p class="text-xs text-slate-500 mt-1">{{ $desc }}</p>
          </li>
          @endforeach
        </ul>
        <button onclick="toggleResourceMenu()"
          class="mt-6 w-full px-4 py-2 bg-slate-100 text-primary rounded-lg font-medium hover:bg-slate-200 transition-colors duration-200">Close</button>
      </div>
    </div>

    <script>
      function toggleResourceMenu() {
        var menu = document.getElementById('resourceMenu');
        var grid = document.getElementById('resourcesGrid');
        if (menu.style.display === 'none' || menu.style.display === '') {
          menu.style.display = 'flex';
          grid.style.display = 'none';
        } else {
          menu.style.display = 'none';
          grid.style.display = '';
        }
      }
    </script>

    <!-- Call to Action -->
    <div class="mt-12 text-center">
      <p class="text-slate-600 mb-4">Need assistance or have questions about our resources?</p>
      <a href="#contact"
        class="inline-flex items-center gap-2 text-primary font-medium hover:text-primary/80 transition-colors">
        Contact our team
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
        </svg>
      </a>
    </div>
  </div>
</section>
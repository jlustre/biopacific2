<section
  class="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-white via-slate-100 to-slate-300">
  <!-- Responsive slideshow background -->
  <div class="absolute inset-0 z-0">
    <div class="hero-slideshow h-full w-full">
      <div class="slide active">
        <img src="{{ asset('images/hero1.jpg') }}" alt="Warm nursing home common area with residents and staff"
          class="h-full w-full object-cover opacity-60">
      </div>
      <div class="slide">
        <img src="{{ asset('images/recreation_activities-room.png') }}"
          alt="Elegant dining room with residents enjoying meals" class="h-full w-full object-cover opacity-60">
      </div>
      <div class="slide">
        <img src="{{ asset('images/physical-therapy-session.png') }}" alt="Physical therapy session in modern facility"
          class="h-full w-full object-cover opacity-60">
      </div>
      <div class="slide">
        <img src="{{ asset('images/garden-outdoor-activities.png') }}"
          alt="Beautiful garden area for outdoor activities" class="h-full w-full object-cover opacity-60">
      </div>
      <div class="absolute inset-0 bg-gradient-to-t from-white/80 via-white/40 to-transparent"></div>
    </div>
  </div>

  <!-- Hero content -->
  <div class="relative z-10 w-full flex flex-col items-center justify-center px-4 py-16 sm:py-24">
    <div
      class="max-w-2xl w-full bg-gray/60 backdrop-blur-lg rounded-3xl shadow-2xl p-8 sm:p-12 text-center border border-slate-200">
      <h1 class="text-3xl sm:text-5xl font-black tracking-tight mb-4" style="color: {{ $facility['primary_color'] ?? '#e3342f' }};
                text-shadow: 0 2px 4px rgba(0,0,0,0.25),
                              0 4px 12px rgba(0,0,0,0.15);">
        {{ $facility['headline'] ?? 'Where Comfort Meets Compassion' }}
      </h1>
      <p class="text-lg sm:text-2xl text-slate-700 mb-6">{{ $facility['subheadline'] ?? 'Default Subheading' }}</p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-6">
        <a href="#contact"
          class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl px-6 py-3 text-white font-semibold shadow-lg transition-all duration-200 hover:scale-105"
          style="background-color: {{ $facility['primary_color'] ?? '#1a7f37' }};">
          Quick Contact
        </a>
        <a href="#book"
          class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border-2 px-6 py-3 font-semibold transition-all duration-200 hover:bg-slate-50 hover:scale-105"
          style="border-color: {{ $facility['primary_color'] ?? '#1a7f37' }}; color: {{ $facility['primary_color'] ?? '#1a7f37' }};">
          Book a Tour
        </a>
        @if(!empty($facility['hero_video_id']))
        <button id="playVideoBtn"
          class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl px-6 py-3 text-white font-semibold shadow-lg transition-all duration-200 hover:scale-105"
          style="background-color: {{ $facility['accent_color'] ?? '#e3342f' }};">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8 5v10l8-5-8-5z" />
          </svg>
          Watch Intro Video
        </button>
        @endif
      </div>
      <div class="flex flex-wrap justify-center gap-2 mt-2">
        <span
          class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">Facility
          ID: {{ $facility['id'] ?? 'N/A' }}</span>
        <span
          class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200"><a
            href="{{ route('admin.facilities.index') }}">Other Facilities</a></span>
        <span
          class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">Contact:
          {{ $facility['contact'] ?? 'N/A' }}</span>
      </div>
    </div>
  </div>
</section>

@if(!empty($facility['hero_video_id']))
<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black bg-opacity-80 z-50 items-center justify-center hidden">
  <div class="relative w-full max-w-2xl mx-4">
    <button id="closeVideoBtn"
      class="absolute -top-14 right-0 text-white hover:text-red-400 transition-colors duration-200 z-10">
      <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    <div class="relative bg-black rounded-xl overflow-hidden" style="padding-bottom: 56.25%; height: 0;">
      <iframe id="youtubeIframe" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen></iframe>
    </div>
  </div>
</div>
@endif


<style>
  .hero-slideshow {
    position: relative;
    min-height: 100vh;
  }

  .slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    min-height: 100vh;
    opacity: 0;
    transition: opacity 1s ease-in-out;
  }

  .slide.active {
    opacity: 1;
  }

  .slide img {
    width: 100%;
    height: 100%;
    min-height: 100vh;
    object-fit: cover;
  }

  #videoModal {
    z-index: 9999;
  }

  body.modal-open {
    overflow: hidden;
  }

  @media (max-width: 768px) {

    .hero-slideshow,
    .slide,
    .slide img {
      min-height: 60vh;
    }

    section.relative,
    section {
      min-height: 60vh;
      padding-top: 2rem;
      padding-bottom: 2rem;
    }

    .max-w-2xl {
      padding: 1.5rem;
    }

    h1 {
      font-size: 2rem;
    }

    .text-lg {
      font-size: 1rem;
    }

    .flex-row {
      flex-direction: column;
    }
  }
</style>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Slideshow functionality
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;
    function nextSlide() {
      slides[currentSlide].classList.remove('active');
      currentSlide = (currentSlide + 1) % slides.length;
      slides[currentSlide].classList.add('active');
    }
    setInterval(nextSlide, 5000);

    @if(!empty($facility['hero_video_id']))
    // Video modal functionality
    const playVideoBtn = document.getElementById('playVideoBtn');
    const videoModal = document.getElementById('videoModal');
    const closeVideoBtn = document.getElementById('closeVideoBtn');
    const youtubeIframe = document.getElementById('youtubeIframe');
    
    // Get YouTube video ID from database
    const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
    
    if (playVideoBtn && youtubeVideoId) {
        playVideoBtn.addEventListener('click', function() {
          youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
          videoModal.classList.remove('hidden');
          videoModal.classList.add('flex');
          document.body.classList.add('modal-open');
        });
        
        function closeModal() {
          videoModal.classList.add('hidden');
          videoModal.classList.remove('flex');
          document.body.classList.remove('modal-open');
          youtubeIframe.src = '';
        }
        
        closeVideoBtn.addEventListener('click', closeModal);
        videoModal.addEventListener('click', function(e) {
          if (e.target === videoModal) {
            closeModal();
          }
        });
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && !videoModal.classList.contains('hidden')) {
            closeModal();
          }
        });
    }
    @endif
  });
</script>
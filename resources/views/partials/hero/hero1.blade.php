@php
$posterFilename = $facility['hero_image_url'] ?? null;
if (!empty($posterFilename)) {
$poster = url('images/' . $posterFilename);
} else {
$poster = null;
}
@endphp

<section class="relative overflow-hidden min-h-screen bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500"
    style="@if($poster) background-image: url('{{ $poster }}'); background-size: cover; background-position: top; @endif">
    <!-- Hero Content -->
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center text-white px-6">
            <h1 class="text-4xl sm:text-6xl lg:text-8xl font-extrabold tracking-tight mb-4">
                {{ $facility['headline'] ?? 'Welcome to Our Facility' }}
            </h1>
            <p class="text-lg sm:text-xl lg:text-2xl mb-6">
                {{ $facility['subheadline'] ?? 'Experience unparalleled care and comfort.' }}
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#contact" class="px-6 py-3 font-semibold rounded-lg shadow hover:bg-gray-100"
                    style="color: {{ $primary }}; background-color: {{ $neutral_light }};">
                    Contact Us
                </a>
                @if(!empty($activeSections) && in_array('book', $activeSections))
                <a href="#book"
                    class="px-6 py-3 bg-transparent border border-white text-white font-semibold rounded-lg shadow hover:bg-white hover:text-blue-600">
                    Book a Tour
                </a>
                @endif
                @if(!empty($facility['hero_video_id']))
                <button id="playVideoBtn" class="px-6 py-3  font-semibold rounded-lg shadow"
                    style="background-color: {{ $neutral_dark }}; color: {{ $neutral_light }}">
                    <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 5v10l8-5-8-5z" />
                    </svg>
                    Watch Intro
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Background Animation -->
    <div class="absolute inset-0">
        <div class="hero-animation w-full h-full">
            <div class="circle bg-white opacity-10"></div>
            <div class="circle bg-white opacity-20"></div>
            <div class="circle bg-white opacity-30"></div>
        </div>
    </div>

    @if(!empty($facility['hero_video_id']))
    {{-- Video Modal --}}
    <div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center hidden">
        <div class="relative w-full max-w-4xl mx-4">
            <!-- Prominent close button -->
            <button id="closeVideoBtn"
                class="absolute -top-12 right-0 text-white hover:text-red-400 transition-colors duration-200 z-10">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <!-- Video container -->
            <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%; height: 0;">
                <iframe id="youtubeIframe" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </div>
        </div>
    </div>
    @endif
</section>

<style>
    .hero-animation {
        position: relative;
        overflow: hidden;
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        animation: float 6s infinite ease-in-out;
    }

    .circle:nth-child(1) {
        width: 200px;
        height: 200px;
        top: 10%;
        left: 20%;
        animation-delay: 0s;
    }

    .circle:nth-child(2) {
        width: 300px;
        height: 300px;
        top: 50%;
        left: 40%;
        animation-delay: 2s;
    }

    .circle:nth-child(3) {
        width: 150px;
        height: 150px;
        bottom: 20%;
        right: 30%;
        animation-delay: 4s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    /* Ensure modal is above everything */
    #videoModal {
        z-index: 9999;
    }

    /* Disable scrolling when modal is open */
    body.modal-open {
        overflow: hidden;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
  @if(!empty($facility['hero_video_id']))
  // Video modal functionality
  console.log('Hero1: Video functionality initializing...');
  const playVideoBtn = document.getElementById('playVideoBtn');
  const videoModal = document.getElementById('videoModal');
  const closeVideoBtn = document.getElementById('closeVideoBtn');
  const youtubeIframe = document.getElementById('youtubeIframe');

  console.log('Hero1: Elements found:', {
    playVideoBtn: !!playVideoBtn,
    videoModal: !!videoModal,
    closeVideoBtn: !!closeVideoBtn,
    youtubeIframe: !!youtubeIframe
  });

  // Get YouTube video ID from database
  const youtubeVideoId = @json($facility['hero_video_id'] ?? null);
  console.log('Hero1: Video ID:', youtubeVideoId);

  if (playVideoBtn && videoModal && closeVideoBtn && youtubeIframe && youtubeVideoId) {
      console.log('Hero1: Setting up video functionality');
      playVideoBtn.addEventListener('click', function() {
          console.log('Hero1: Button clicked!');
          // Set the YouTube URL with autoplay
          youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
          videoModal.classList.remove('hidden');
          videoModal.classList.add('flex');
          document.body.classList.add('modal-open');
          console.log('Hero1: Modal should be open now');
      });

      function closeModal() {
          console.log('Hero1: Closing modal');
          videoModal.classList.add('hidden');
          videoModal.classList.remove('flex');
          document.body.classList.remove('modal-open');
          // Stop the video by clearing the src
          youtubeIframe.src = '';
      }

      closeVideoBtn.addEventListener('click', closeModal);

      // Close modal when clicking outside the video
      videoModal.addEventListener('click', function(e) {
          if (e.target === videoModal) {
              closeModal();
          }
      });

      // Close modal with Escape key
      document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && !videoModal.classList.contains('hidden')) {
              closeModal();
          }
      });
  } else {
      console.log('Hero1: Setup failed - missing elements or video ID');
      console.log('Missing elements:', {
          playVideoBtn: !playVideoBtn,
          videoModal: !videoModal,
          closeVideoBtn: !closeVideoBtn,
          youtubeIframe: !youtubeIframe,
          youtubeVideoId: !youtubeVideoId
      });
  }
  @endif
});
</script>
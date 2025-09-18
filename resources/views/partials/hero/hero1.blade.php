<section class="relative overflow-hidden min-h-screen">
    <!-- Multiple background images that rotate -->
    <div class="absolute inset-0">
        <div class="hero-slideshow relative h-full w-full">
            <div class="slide active">
                <img src="{{ asset('images/a_cheerful_middleaged_caregiver_pushing_an_elderly.jpg') }}"
                    alt="Warm nursing home common area with residents and staff"
                    class="h-full w-full object-cover opacity-70">
            </div>
            <div class="slide">
                <img src="{{ asset('images/recreation_activities-room.png') }}"
                    alt="Elegant dining room with residents enjoying meals"
                    class="h-full w-full object-cover opacity-70">
            </div>
            <div class="slide">
                <img src="{{ asset('images/physical-therapy-session.png') }}"
                    alt="Physical therapy session in modern facility" class="h-full w-full object-cover opacity-70">
            </div>
            <div class="slide">
                <img src="{{ asset('images/garden-outdoor-activities.png') }}"
                    alt="Beautiful garden area for outdoor activities" class="h-full w-full object-cover opacity-70">
            </div>
        </div>
    </div>

    <!-- Positioned content at left bottom -->
    <div class="absolute left-0 bottom-24 z-10 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-2">
            <div class="max-w-xl bg-white/60 backdrop-blur rounded-2xl p-8 shadow-xl">
                <!-- Add a dark text stroke for better contrast -->
                <style>
                    .hero-headline-shadow {
                        /* Existing styles... */
                        -webkit-text-stroke: .5px rgba(40, 40, 40, 0.7);
                        text-stroke: .5px rgba(40, 40, 40, 0.7);
                        /* For future compatibility */
                        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.18), 0 0px 1px rgba(0, 0, 0, 0.12);
                        border: 2px solid rgba(73, 64, 64, 0.4);
                        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.10);
                        border-radius: 0.75rem;
                        padding: 0.5rem 1rem;
                        display: inline-block;
                        background: rgba(255, 255, 255, 0.18);
                    }
                </style>
                <h1 class="text-2xl sm:text-4xl font-extrabold hero-headline-shadow"
                    style="color: {{ $facility['primary_color'] ?? '#e3342f' }}">
                    {{ $facility['headline'] ?? 'Where Comfort Meets Compassion' }}
                </h1>
                <p class="mt-4 mx-4 text-slate-700">{{ $facility['subheadline'] ?? 'Default Subheading' }}</p>
                <div class="mt-6 mx-4 pb-4 flex flex-wrap gap-3 flex flex-row justify-between">
                    <a href="#contact" class="inline-flex items-center rounded-xl px-5 py-3 text-white font-medium"
                        style="background-color: {{ $facility['primary_color'] ?? '#1a7f37' }};">
                        Quick Contact
                    </a>
                    <a href="#book" class="inline-flex items-center rounded-xl border px-5 py-3 font-medium"
                        style="border-color: {{ $facility['primary_color'] ?? '#1a7f37' }}; color: {{ $facility['primary_color'] ?? '#1a7f37' }};">
                        Book a Tour
                    </a>
                    <button id="playVideoBtn"
                        class="inline-flex items-center rounded-xl px-5 py-3 text-white font-medium"
                        style="background-color: {{ $facility['accent_color'] ?? '#e3342f' }};">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center hidden">
    <div class="relative w-full max-w-4xl mx-4">
        <!-- Prominent close button -->
        <button id="closeVideoBtn"
            class="absolute -top-12 right-0 text-white hover:text-red-400 transition-colors duration-200 z-10">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
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

    /* Ensure modal is above everything */
    #videoModal {
        z-index: 9999;
    }

    /* Disable scrolling when modal is open */
    body.modal-open {
        overflow: hidden;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {

        .slide,
        .slide img,
        .hero-slideshow {
            min-height: 80vh;
        }

        section.relative {
            min-height: 80vh;
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

    // Change slide every 5 seconds
    setInterval(nextSlide, 5000);

    // Video modal functionality
    const playVideoBtn = document.getElementById('playVideoBtn');
    const videoModal = document.getElementById('videoModal');
    const closeVideoBtn = document.getElementById('closeVideoBtn');
    const youtubeIframe = document.getElementById('youtubeIframe');

    // Replace this with your actual YouTube video ID
    const youtubeVideoId = 'YOUR_YOUTUBE_VIDEO_ID'; // Replace with actual video ID

    playVideoBtn.addEventListener('click', function() {
        // Set the YouTube URL with autoplay
        youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
        videoModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
    });

    function closeModal() {
        videoModal.classList.add('hidden');
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
});
</script>
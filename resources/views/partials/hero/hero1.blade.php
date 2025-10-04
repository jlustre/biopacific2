<section class="relative overflow-hidden min-h-screen">
    <!-- Multiple background images that rotate -->
    <div class="absolute inset-0">
        <div class="hero-slideshow relative h-full w-full">
            <div class="slide active">
                <img src="{{ asset('images/garden-outdoor-activities.png') }}"
                    alt="Beautiful garden area for outdoor activities" class="h-full w-full object-cover opacity-70">
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
                {{-- Color variables ($primary, $secondary, $accent) are now passed from the controller. --}}
                <h1 class="text-2xl sm:text-4xl font-extrabold hero-headline-shadow text-primary">
                    {{ $facility['headline'] ?? 'Where Comfort Meets Compassion' }}
                </h1>
                <p class="mt-4 mx-4 text-slate-700">{{ $facility['subheadline'] ?? 'Default Subheading' }}</p>
                <div class="mt-6 mx-4 pb-4 flex flex-wrap gap-3 flex flex-row justify-between">
                    <a href="#contact" class="inline-flex items-center rounded-xl px-5 py-3 text-white font-medium"
                        style="background-color: {{ $primary }};">
                        Quick Contact
                    </a>
                    <a href="#book" class="inline-flex items-center rounded-xl border px-5 py-3 font-medium"
                        style="border-color: {{ $secondary }}; color: {{ $secondary }};">
                        Book a Tour
                    </a>
                    @if(!empty($facility['hero_video_id']))
                    <button id="playVideoBtn"
                        class="inline-flex items-center rounded-xl px-5 py-3 text-white font-medium"
                        style="background-color: {{ $accent }};">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 5v10l8-5-8-5z" />
                        </svg>
                        Watch Intro
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if(!empty($facility['hero_video_id']))
    <x-video-modal :videoId="$facility['hero_video_id']" :accentColor="$facility['accent_color'] ?? '#e3342f'"
        background="rgba(0,0,0,0.75)" />
    @endif
</section>


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

});
</script>
<section class="relative overflow-hidden min-h-screen bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500">
    <!-- Hero Content -->
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center text-white px-6">
            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-4">
                {{ $facility['headline'] ?? 'Welcome to Our Facility' }}
            </h1>
            <p class="text-lg sm:text-xl mb-6">
                {{ $facility['subheadline'] ?? 'Experience unparalleled care and comfort.' }}
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#contact"
                    class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg shadow hover:bg-gray-100">
                    Contact Us
                </a>
                @if(!empty($activeSections) && in_array('book', $activeSections))
                <a href="#book"
                    class="px-6 py-3 bg-transparent border border-white text-white font-semibold rounded-lg shadow hover:bg-white hover:text-blue-600">
                    Book a Tour
                </a>
                @endif
                @if(!empty($facility['hero_video_id']))
                <button id="playVideoBtn"
                    class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg shadow hover:bg-purple-700">
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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const playVideoBtn = document.getElementById('playVideoBtn');
        if (playVideoBtn) {
            playVideoBtn.addEventListener('click', function() {
                alert('Play video functionality goes here.');
            });
        }
    });
</script>
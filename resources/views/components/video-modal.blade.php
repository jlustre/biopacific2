@props([
'videoId' => null,
'accentColor' => '#F59E0B',
'zIndex' => 2001,
'background' => 'rgba(0,0,0,0.75)',
'modalId' => 'videoModal',
'playBtnId' => 'playVideoBtn',
'closeBtnId' => 'closeVideoBtn',
'iframeId' => 'youtubeIframe',
])
@if($videoId)
<!-- DEBUG: Video Modal Rendered (ID: {{ $modalId }}) -->
<div id="{{ $modalId }}" class="fixed inset-0 z-[{{ $zIndex }}] flex items-start justify-center hidden">
    <div class="absolute inset-0 bg-black/80"></div>
    <div class="relative w-full max-w-5xl mx-4 flex justify-center items-center mt-[80px] z-10">
        <div
            class="relative bg-white rounded-2xl shadow-2xl p-2 md:p-6 max-w-4xl w-[95vw] md:w-[70vw] max-h-[85vh] flex flex-col items-center">
            <button id="{{ $closeBtnId }}"
                class="absolute top-3 right-3 z-10 text-white bg-black/80 hover:bg-red-600 rounded-full p-2 md:p-3 shadow-lg focus:outline-none transition-all duration-150"
                aria-label="Close video">
                <svg class="h-7 w-7 md:h-8 md:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="w-full aspect-video rounded-xl overflow-hidden">
                <iframe id="{{ $iframeId }}" class="w-full h-full rounded-xl" src="" frameborder="0"
                    allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>
<style>
    #{{ $modalId }
    }

        {
        z-index: {
                {
                $zIndex
            }
        }

        ;
    }

    body.modal-open {
        overflow: hidden;
    }
</style>
<script>
    (() => {
        // Use unique IDs for multiple modals on the same page
        const playVideoBtn = document.getElementById(@json($playBtnId));
        const videoModal = document.getElementById(@json($modalId));
        const closeVideoBtn = document.getElementById(@json($closeBtnId));
        const youtubeIframe = document.getElementById(@json($iframeId));
        const youtubeVideoId = @json($videoId);
        function closeModal() {
            videoModal.classList.add('hidden');
            videoModal.classList.remove('flex');
            document.body.classList.remove('modal-open');
            youtubeIframe.src = '';
        }
        if (playVideoBtn && videoModal && closeVideoBtn && youtubeIframe && youtubeVideoId) {
            playVideoBtn.addEventListener('click', function() {
                youtubeIframe.src = `https://www.youtube.com/embed/${youtubeVideoId}?autoplay=1&rel=0`;
                videoModal.classList.remove('hidden');
                videoModal.classList.add('flex');
                document.body.classList.add('modal-open');
            });
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
            // Hide modal when a menu link is clicked
            document.querySelectorAll('nav a, .menu a, .navbar a, .mobile-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    if (!videoModal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            });
        }
    })();
</script>
@endif
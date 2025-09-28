@props([
'videoId' => null,
'accentColor' => '#F59E0B',
'zIndex' => 50,
'background' => 'rgba(0,0,0,0.75)',
'modalId' => 'videoModal',
'playBtnId' => 'playVideoBtn',
'closeBtnId' => 'closeVideoBtn',
'iframeId' => 'youtubeIframe',
])
@if($videoId)
<div id="{{ $modalId }}" class="fixed inset-0 z-{{ $zIndex }} items-center justify-center hidden"
    style="background: {{ $background }};">
    <div class="relative w-full max-w-4xl mx-4">
        <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%; height: 0;">
            <button id="{{ $closeBtnId }}"
                class="absolute top-4 right-4 text-white hover:text-red-400 transition-colors duration-200 z-10 bg-black/50 backdrop-blur rounded-full p-2"
                aria-label="Close video">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <iframe id="{{ $iframeId }}" class="absolute top-0 left-0 w-full h-full" src="" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
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
        if (playVideoBtn && videoModal && closeVideoBtn && youtubeIframe && youtubeVideoId) {
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
    })();
</script>
@endif
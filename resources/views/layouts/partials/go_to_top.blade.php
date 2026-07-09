<div
    x-data="{
        visible: false,
        init() {
            const onScroll = () => {
                this.visible = window.scrollY > 300;
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        },
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    }"
    class="pointer-events-none fixed inset-0 z-[60]"
    aria-hidden="true"
>
    <button
        type="button"
        x-show="visible"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        @click="scrollToTop()"
        x-cloak
        class="pointer-events-auto fixed bottom-20 right-4 flex h-11 w-11 items-center justify-center rounded-full bg-teal-600 text-white shadow-lg ring-1 ring-black/5 transition hover:bg-teal-700 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 sm:bottom-6 sm:right-6 lg:bottom-8 lg:right-8"
        aria-label="Go to top"
        title="Go to top"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>
</div>

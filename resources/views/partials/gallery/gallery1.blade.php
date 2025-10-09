<section id="gallery" class="relative isolate overflow-hidden py-16 sm:py-24">
    {{-- Subtle brand backdrop --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/60"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                class="text-primary border-primary">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                Photo Gallery
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold text-slate-900">Life at {{ $facility['name'] ?? 'Our
                Community' }}</h2>
            <p class="mt-2 text-slate-600 md:text-lg">Bright spaces, caring moments, and inviting amenities.</p>
        </div>

        {{-- Mobile: horizontal carousel --}}
        <div class="mt-10 lg:hidden">
            <div class="relative">
                <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4" style="scrollbar-width: none;"
                    onwheel="if(Math.abs(event.deltaY)>Math.abs(event.deltaX)) this.scrollLeft+=event.deltaY; event.preventDefault();">
                    @foreach($galleryImages as $i => $src)
                    <button
                        class="snap-center shrink-0 w-[82%] sm:w-[70%] rounded-3xl overflow-hidden ring-1 ring-slate-200 bg-white shadow"
                        onclick="openGalleryModal({{ $i }})" aria-label="Open image {{ $i+1 }}">
                        <img src="{{ $src }}" alt="Gallery image {{ $i+1 }} - {{ $facility['name'] ?? 'community' }}"
                            class="w-full h-64 object-cover transition-transform duration-500 hover:scale-105"
                            loading="lazy" decoding="async">
                    </button>
                    @endforeach
                </div>

                {{-- Mobile hint --}}
                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 text-[11px] text-slate-500">
                    Swipe to see more
                </div>
            </div>
        </div>

        {{-- Desktop: masonry columns (pure CSS) --}}
        <div class="mt-12 hidden lg:block">
            <div class="columns-2 xl:columns-3 gap-5 [column-fill:balance]">
                @foreach($galleryImages as $i => $src)
                <button
                    class="mb-5 w-full break-inside-avoid rounded-3xl overflow-hidden ring-1 ring-slate-200 bg-white shadow group"
                    onclick="openGalleryModal({{ $i }})" aria-label="Open image {{ $i+1 }}">
                    <div class="relative">
                        <img src="{{ $src }}" alt="Gallery image {{ $i+1 }} - {{ $facility['name'] ?? 'community' }}"
                            class="w-full h-auto max-h-[560px] object-cover transition-transform duration-700 group-hover:scale-105"
                            loading="lazy" decoding="async">
                        {{-- Soft overlay + icon on hover --}}
                        <div class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition
                          bg-gradient-to-t from-black/20 via-black/0 to-transparent"></div>
                        <div
                            class="pointer-events-none absolute right-3 bottom-3 opacity-0 group-hover:opacity-100 transition">
                            <span
                                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/30 bg-black/30">
                                View
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 5l7 5-7 5V5z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Optional: small reassurance strip --}}
        <div class="mt-10 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 text-center">
                <div class="text-xs uppercase tracking-wide text-slate-500">Common Areas</div>
                <div class="mt-1 font-semibold text-slate-900">Bright, welcoming lounges</div>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 text-center">
                <div class="text-xs uppercase tracking-wide text-slate-500">Therapy Spaces</div>
                <div class="mt-1 font-semibold text-slate-900">Modern rehab equipment</div>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 text-center">
                <div class="text-xs uppercase tracking-wide text-slate-500">Outdoor Areas</div>
                <div class="mt-1 font-semibold text-slate-900">Gardens & patios</div>
            </div>
        </div>
    </div>

    {{-- LIGHTBOX MODAL --}}
    <div id="galleryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/80" onclick="closeGalleryModal()" aria-hidden="true"></div>

        {{-- Dialog --}}
        <div class="relative w-full max-w-5xl">
            {{-- Close --}}
            <button class="absolute -top-12 right-0 text-white hover:text-red-300" onclick="closeGalleryModal()"
                aria-label="Close gallery">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Image stage --}}
            <div class="relative rounded-3xl overflow-hidden bg-black ring-1 ring-white/10">
                <img id="galleryModalImg" src="" alt="Expanded gallery image"
                    class="w-full max-h-[78vh] object-contain">
                {{-- Controls --}}
                <button id="galleryPrev"
                    class="absolute left-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center rounded-full h-10 w-10 bg-white/20 text-white ring-1 ring-white/30 hover:bg-white/30"
                    aria-label="Previous image">
                    ‹
                </button>
                <button id="galleryNext"
                    class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center rounded-full h-10 w-10 bg-white/20 text-white ring-1 ring-white/30 hover:bg-white/30"
                    aria-label="Next image">
                    ›
                </button>
            </div>

            {{-- Counter / caption --}}
            <div class="mt-3 flex items-center justify-between text-white/90 text-sm">
                <div id="galleryCounter"></div>
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-white/30"
                    style="background: {{ $primary }};">
                    {{ $facility['city'] ?? '' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Lightbox state
  const galleryImages = @json($galleryImages ?? []);
  let gIndex = 0;

  function openGalleryModal(i = 0) {
    gIndex = i;
    const m = document.getElementById('galleryModal');
    const img = document.getElementById('galleryModalImg');
    const counter = document.getElementById('galleryCounter');
    if (!m || !img) return;
    img.src = galleryImages[gIndex] || '';
    counter.textContent = (gIndex + 1) + ' / ' + galleryImages.length;

    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', onGalleryKey);
  }

  function closeGalleryModal() {
    const m = document.getElementById('galleryModal');
    if (!m) return;
    m.classList.add('hidden'); m.classList.remove('flex');
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onGalleryKey);
  }

  function galleryPrev() {
    gIndex = (gIndex - 1 + galleryImages.length) % galleryImages.length;
    updateGalleryStage();
  }
  function galleryNext() {
    gIndex = (gIndex + 1) % galleryImages.length;
    updateGalleryStage();
  }
  function updateGalleryStage() {
    const img = document.getElementById('galleryModalImg');
    const counter = document.getElementById('galleryCounter');
    if (img) img.src = galleryImages[gIndex] || '';
    if (counter) counter.textContent = (gIndex + 1) + ' / ' + galleryImages.length;
  }
  function onGalleryKey(e) {
    if (e.key === 'Escape') closeGalleryModal();
    if (e.key === 'ArrowLeft') galleryPrev();
    if (e.key === 'ArrowRight') galleryNext();
  }

  // Wire buttons
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('galleryPrev')?.addEventListener('click', galleryPrev);
    document.getElementById('galleryNext')?.addEventListener('click', galleryNext);
  });
</script>
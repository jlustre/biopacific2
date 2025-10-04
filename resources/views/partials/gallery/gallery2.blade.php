@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$accent = $facility['accent_color'] ?? '#F59E0B';
$galleryImages = [
asset('images/gallery/nursinghome_image1.png'),
asset('images/gallery/nursinghome_image2.png'),
asset('images/gallery/nursinghome_image3.png'),
asset('images/gallery/nursinghome_image4.png'),
asset('images/gallery/nursinghome_image5.png'),
asset('images/gallery/nursinghome_image6.png'),
asset('images/gallery/nursinghome_image7.png'),
asset('images/gallery/nursinghome_image8.png'),
asset('images/gallery/nursinghome_image9.png'),
];


// Optional captions (can be CMS-driven later)
$captions = [
'Welcome lobby', 'Resident room', 'Therapy gym', 'Dining room',
'Activities lounge', 'Garden patio', 'Reading nook', 'Nurse station', 'Sunlit corridor'
];
@endphp

<section id="gallery" class="relative isolate overflow-hidden py-16 sm:py-24">
    {{-- Ambient brand background --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-white via-slate-50 to-white"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                class="text-primary border-primary">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                Featured Moments
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold text-slate-900">Gallery</h2>
            <p class="mt-2 text-slate-600 md:text-lg">A cinematic look at {{ $facility['name'] ?? 'our community' }}.
            </p>
        </div>

        {{-- Slideshow Card --}}
        <div class="mt-10 rounded-[28px] overflow-hidden bg-white ring-1 ring-slate-200 shadow">
            {{-- Stage --}}
            <div class="relative">
                <div class="relative h-[48vh] sm:h-[56vh] lg:h-[64vh] overflow-hidden">
                    @foreach($galleryImages as $i => $src)
                    <img src="{{ $src }}" alt="{{ $captions[$i] ?? 'Community photo' }}"
                        class="kb-slide absolute inset-0 h-full w-full object-contain opacity-0 bg-slate-100"
                        style="object-position: center;" data-index="{{ $i }}"
                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}" decoding="async">
                    @endforeach
                    {{-- gradient legibility --}}
                    <div
                        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent">
                    </div>
                </div>

                {{-- Caption + controls --}}
                <div class="absolute inset-x-0 bottom-0 p-4 sm:p-6">
                    <div class="flex items-end justify-between gap-3">
                        <div class="text-white">
                            <div id="kbCaption" class="text-base sm:text-lg font-semibold">—</div>
                            <div id="kbCounter" class="text-xs text-white/85 mt-0.5">—</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="kbPrev"
                                class="h-10 w-10 inline-flex items-center justify-center rounded-full bg-white/20 text-white ring-1 ring-white/30 hover:bg-white/30"
                                aria-label="Previous">‹</button>
                            <button id="kbNext"
                                class="h-10 w-10 inline-flex items-center justify-center rounded-full bg-white/20 text-white ring-1 ring-white/30 hover:bg-white/30"
                                aria-label="Next">›</button>
                            <button id="kbPlayPause"
                                class="h-10 w-10 inline-flex items-center justify-center rounded-full bg-white/20 text-white ring-1 ring-white/30 hover:bg-white/30"
                                aria-label="Pause">⏸</button>
                        </div>
                    </div>

                    {{-- progress dots --}}
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($galleryImages as $i => $_)
                        <button class="kb-dot h-1.5 rounded-full transition"
                            style="width: 16px; background: {{ $i===0 ? $primary : '#ffffff80' }}" data-index="{{ $i }}"
                            aria-label="Go to slide {{ $i+1 }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Filmstrip thumbnails --}}
            <div class="border-t border-slate-200 bg-slate-50/50 p-2 sm:p-3">
                <div id="kbStrip" class="flex gap-2 overflow-x-auto pb-2" style="scrollbar-width:none;">
                    @foreach($galleryImages as $i => $src)
                    <button class="kb-thumb relative shrink-0 rounded-xl overflow-hidden ring-2 ring-transparent"
                        data-index="{{ $i }}" aria-label="Show slide {{ $i+1 }}">
                        <img src="{{ $src }}" alt="Thumbnail {{ $i+1 }}"
                            class="h-16 w-24 sm:h-20 sm:w-28 object-cover opacity-85 hover:opacity-100 transition">
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Ken-Burns animation (scale & pan) */
    .kb-anim-in {
        animation: kbIn 10s ease-in-out forwards;
    }

    @keyframes kbIn {
        0% {
            transform: scale(1.05) translate3d(0, 0, 0);
        }

        50% {
            transform: scale(1.1) translate3d(2%, -1%, 0);
        }

        100% {
            transform: scale(1.12) translate3d(-2%, 1%, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .kb-anim-in {
            animation: none !important;
        }
    }
</style>

<script>
    (function(){
    const slides   = Array.from(document.querySelectorAll('.kb-slide'));
    const dots     = Array.from(document.querySelectorAll('.kb-dot'));
    const thumbs   = Array.from(document.querySelectorAll('.kb-thumb'));
    const caption  = document.getElementById('kbCaption');
    const counter  = document.getElementById('kbCounter');
    const prevBtn  = document.getElementById('kbPrev');
    const nextBtn  = document.getElementById('kbNext');
    const playBtn  = document.getElementById('kbPlayPause');

    const CAPTIONS = @json($captions ?? []);
    const TOTAL    = slides.length;
    let index = 0;
    let autoplay = true;
    let timer = null;
    const DURATION = 6500; // ms per slide (Ken Burns anim is 10s; overlap is fine)

    function show(i) {
      index = (i + TOTAL) % TOTAL;

      slides.forEach((el, idx) => {
        el.style.opacity = idx === index ? '1' : '0';
        el.classList.remove('kb-anim-in');
        if (idx === index) {
          // trigger reflow to restart animation
          void el.offsetWidth;
          el.classList.add('kb-anim-in');
        }
      });

      dots.forEach((d, idx) => {
        d.style.background = idx === index ? '{{ $primary }}' : '#ffffff80';
      });

      thumbs.forEach((t, idx) => {
        t.style.boxShadow  = idx === index ? 'inset 0 0 0 2px #fff' : 'none';
        t.style.borderColor = idx === index ? '{{ $primary }}' : 'transparent';
        t.style.opacity    = idx === index ? '1' : '0.9';
      });

      if (caption) caption.textContent = CAPTIONS[index] || 'Community photo';
      if (counter) counter.textContent = (index + 1) + ' / ' + TOTAL;
    }

    function next(){ show(index + 1); }
    function prev(){ show(index - 1); }

    function start() {
      stop();
      if (!autoplay) return;
      timer = setInterval(next, DURATION);
      playBtn.textContent = '⏸'; playBtn.setAttribute('aria-label', 'Pause');
    }
    function stop() {
      if (timer) clearInterval(timer);
      timer = null;
      playBtn.textContent = '▶️'; playBtn.setAttribute('aria-label', 'Play');
    }
    function togglePlay(){
      autoplay = !autoplay;
      autoplay ? start() : stop();
    }

    // Wire UI
    prevBtn?.addEventListener('click', () => { prev(); start(); });
    nextBtn?.addEventListener('click', () => { next(); start(); });
    playBtn?.addEventListener('click', togglePlay);

    dots.forEach(d => d.addEventListener('click', () => { show(parseInt(d.dataset.index)); start(); }));
    thumbs.forEach(t => t.addEventListener('click', () => { show(parseInt(t.dataset.index)); start(); }));

    // Pause on hover (desktop)
    const stage = slides[0]?.parentElement;
    stage?.addEventListener('mouseenter', () => { if (autoplay) stop(); });
    stage?.addEventListener('mouseleave', () => { if (autoplay) start(); });

    // Keyboard
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft')  { prev();  start(); }
      if (e.key === 'ArrowRight') { next();  start(); }
      if (e.key.toLowerCase() === ' ') { e.preventDefault(); togglePlay(); }
    });

    // Init
    show(0);
    start();
  })();
</script>
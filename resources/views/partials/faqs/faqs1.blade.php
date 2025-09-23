@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
@endphp

<section id="faqs" class="relative isolate overflow-hidden py-16 sm:py-24">
    {{-- Ambient brand backdrop --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/70"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                style="color: {{ $primary }}; border-color: {{ $primary }};">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                We’re here to help
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold text-slate-900">Frequently Asked Questions</h2>
            <p class="mt-2 text-slate-600 md:text-lg">Answers to common questions about care, visits, dining, safety,
                and more.</p>
        </div>

        {{-- Layout: Left intro + search | Right accordion --}}
        <div class="mt-10 grid gap-8 lg:grid-cols-[0.9fr,1.1fr] items-start">
            {{-- LEFT: Intro, quick links, contact --}}
            <aside class="space-y-6">
                <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Find answers fast</h3>
                    <p class="mt-1 text-sm text-slate-600">Search FAQs or browse the most common topics.</p>

                    {{-- Search --}}
                    <div class="mt-4">
                        <label for="faqSearch" class="sr-only">Search FAQs</label>
                        <div class="relative">
                            <input id="faqSearch" type="search"
                                placeholder="Search by keyword (e.g., insurance, diet, visits)…"
                                class="w-full rounded-2xl border-slate-300 pl-10 pr-3 py-2.5 focus:border-slate-400 focus:ring-0">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="11" cy="11" r="7" stroke-width="2"></circle>
                                <path d="M21 21l-4.3-4.3" stroke-width="2"></path>
                            </svg>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 text-sm">
                            <button type="button" data-chip="all"
                                class="faq-chip rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-slate-700 hover:bg-slate-100">All</button>
                            @foreach($categories as $chip)
                            <button type="button" data-chip="{{ strtolower($chip) }}"
                                class="faq-chip rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-slate-700 hover:bg-slate-100">
                                {{ ucfirst($chip) }}
                            </button>
                            @endforeach
                        </div>
                        <ul id="faqList" class="divide-y divide-slate-200">
                            @foreach($faqs as $idx => $faq)
                            <li class="faq-item" data-text="{{ Str::of($faq->question.' '.$faq->answer)->lower() }}">
                                <button
                                    class="group w-full text-left px-5 sm:px-6 py-4 hover:bg-slate-50 focus:outline-none focus-visible:bg-slate-50"
                                    aria-expanded="false" aria-controls="faq-panel-{{ $idx }}">
                                    <div class="flex items-start gap-4">
                                        <span
                                            class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl text-white shrink-0"
                                            style="background: {{ $primary }}">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="{{ $faq->icon }}" stroke-width="1.8" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="font-semibold text-slate-900">{{ $faq->question }}</span>
                                                <span
                                                    class="ml-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-700 group-aria-expanded:rotate-45 transition"
                                                    style="color: {{ $primary }}">＋</span>
                                            </div>
                                            {{-- Panel --}}
                                            <div id="faq-panel-{{ $idx }}"
                                                class="max-h-0 overflow-hidden transition-[max-height] duration-300 ease-out">
                                                <p class="mt-3 pr-2 text-sm leading-6 text-slate-700">{{ $faq->answer }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
        </div>
</section>

<script>
    // FAQ accordion behavior, search, and chip filters (no external libs)
  (function(){
    const items   = Array.from(document.querySelectorAll('#faqList .faq-item'));
    const buttons = Array.from(document.querySelectorAll('#faqList .faq-item > button'));
    const search  = document.getElementById('faqSearch');
    const chips   = Array.from(document.querySelectorAll('.faq-chip'));

    // Accordion open/close
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        const panel = btn.querySelector('[id^="faq-panel-"]');
        // Close others
        buttons.forEach(b => {
          if (b !== btn) {
            b.setAttribute('aria-expanded','false');
            const p = b.querySelector('[id^="faq-panel-"]');
            if (p) p.style.maxHeight = '0px';
          }
        });
        // Toggle current
        btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        if (panel) panel.style.maxHeight = expanded ? '0px' : panel.scrollHeight + 'px';
      });
    });

    // Search filter
    function applyFilter(q){
      const query = (q || '').trim().toLowerCase();
      items.forEach(item => {
        const hay = item.getAttribute('data-text') || '';
        item.style.display = hay.includes(query) ? '' : 'none';
      });
    }
    search?.addEventListener('input', (e)=> applyFilter(e.target.value));

        // Chip quick filters
        chips.forEach(ch => ch.addEventListener('click', ()=>{
            const term = ch.getAttribute('data-chip') || '';
            if (search) search.value = term === 'all' ? '' : term;
            if (term === 'all') {
                items.forEach(item => { item.style.display = ''; });
            } else {
                applyFilter(term);
            }
        }));

    // Expand first item by default (optional)
    const firstBtn = buttons[0];
    if (firstBtn) firstBtn.click();

    // Accessibility: close on Escape when focus inside a panel
    document.addEventListener('keydown', (e)=>{
      if (e.key === 'Escape') {
        buttons.forEach(b => {
          b.setAttribute('aria-expanded','false');
          const p = b.querySelector('[id^="faq-panel-"]');
          if (p) p.style.maxHeight = '0px';
        });
      }
    });
  })();
</script>
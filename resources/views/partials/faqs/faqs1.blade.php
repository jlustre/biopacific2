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
                                                <span class="font-semibold text-slate-900">{{ $faq->question
                                                    }}</span>
                                                <span
                                                    class="ml-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-700 group-aria-expanded:rotate-45 transition"
                                                    style="color: {{ $primary }}">＋</span>
                                            </div>
                                            {{-- Panel --}}
                                            <div id="faq-panel-{{ $idx }}"
                                                class="max-h-0 overflow-hidden transition-[max-height] duration-300 ease-out">
                                                <p class="mt-3 pr-2 text-sm leading-6 text-slate-700">{{
                                                    $faq->answer }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </li>
                            @endforeach
                            'question' => 'Do you offer specialized diets?',
                            'answer' => 'Yes, our registered dietitians work closely with residents and families to
                            create
                            personalized meal plans accommodating dietary restrictions, allergies, cultural
                            preferences, and
                            medical requirements such as diabetic, low-sodium, or pureed diets.',
                            'icon' => 'M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3
                            0 2.704
                            2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9
                            6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zM3
                            9a2 2 0
                            012-2h14a2 2 0 012 2v.01A2 2 0 0119 11H5a2 2 0 01-2-1.99V9z'
                            ],
                            [
                            'question' => 'How do I schedule a tour?',
                            'answer' => 'You can schedule a tour by clicking our “Book a Tour” button, calling our
                            front desk or
                            visiting us in person. We offer guided tours Monday through Saturday and can accommodate
                            special
                            scheduling needs.',
                            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2
                            0 002 2z'
                            , 'category' => 'tours'
                            ],
                            [
                            'question' => 'Can I book a tour online?',
                            'answer' => 'Yes, you can book a tour directly on our website or by calling our front
                            desk.',
                            'icon' => 'M15 12h6m-6 0H3',
                            'category' => 'tours'
                            ],
                            [
                            'question' => 'Are virtual tours available?',
                            'answer' => 'We offer virtual tours for families who cannot visit in person. Contact us
                            to schedule
                            a virtual walkthrough.',
                            'icon' => 'M4 6v16h16V6',
                            'category' => 'tours'
                            ],
                            [
                            'question' => 'Is there on-site parking?',
                            'answer' => 'Yes, we offer free on-site parking for visitors and families. Accessible
                            parking spaces
                            are available near the main entrance.',
                            'icon' => 'M3 17v-2a4 4 0 014-4h10a4 4 0 014 4v2M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4
                            4v2M7 10V7a4 4 0
                            018 0v3'
                            ],
                            [
                            'question' => 'What safety measures are in place?',
                            'answer' => 'Our facility follows strict infection control protocols, regular staff
                            training, and
                            24/7 security monitoring to ensure resident safety and well-being.',
                            'icon' => 'M12 11c0-1.104.896-2 2-2s2 .896 2 2-.896 2-2 2-2-.896-2-2zm0 0V7m0 4v4m0
                            0h4m-4 0H8'
                            ],
                            [
                            'question' => 'Can residents personalize their rooms?',
                            'answer' => 'Absolutely! Residents are encouraged to bring personal items, photos, and
                            small
                            furnishings to make their space feel like home.',
                            'icon' => 'M4 6v16h16V6M4 6l8-4 8 4'
                            ],
                            [
                            'question' => 'Are pets allowed to visit?',
                            'answer' => 'Yes, pets are welcome to visit as long as they are supervised and comply
                            with our pet
                            policy. Please notify staff in advance for arrangements.',
                            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0
                            11-6 0 3 3 0
                            016 0z'
                            ],
                            [
                            'question' => 'What activities and programs do you offer?',
                            'answer' => 'We provide a comprehensive activities program including physical therapy,
                            arts and
                            crafts, music therapy, social events, religious services, educational programs, and
                            outdoor
                            activities. Our activity calendar is updated monthly.',
                            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0
                            00-9-5.197m13.5-9a2.5
                            2.5 0 11-5 0 2.5 2.5 0 015 0z'
                            ],
                            [
                            'question' => 'What safety measures are in place?',
                            'answer' => 'We maintain 24/7 nursing staff, security systems, emergency call systems in
                            every room,
                            fire safety protocols, infection control measures, and regular safety drills. Our
                            facility is fully
                            licensed and regularly inspected.',
                            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618
                            3.04A12.02
                            12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 2.3-.72 4.396-1.888 6.168-3.38.522-.439
                            1.022-.9
                            1.5-1.38.145-.146.288-.294.43-.444A11.956 11.956 0 0021 9a12.02 12.02 0 00.382-3.016z'
                            ],
                            ] as $idx => $faq)
                            <li class="faq-item"
                                data-text="{{ Str::of($faq['question'].' '.$faq['answer'])->lower() }}">
                                <button
                                    class="group w-full text-left px-5 sm:px-6 py-4 hover:bg-slate-50 focus:outline-none focus-visible:bg-slate-50"
                                    aria-expanded="false" aria-controls="faq-panel-{{ $idx }}">
                                    <div class="flex items-start gap-4">
                                        <span
                                            class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl text-white shrink-0"
                                            style="background: {{ $primary }}">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="{{ $faq['icon'] }}" stroke-width="1.8" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="font-semibold text-slate-900">{{ $faq['question']
                                                    }}</span>
                                                <span
                                                    class="ml-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-700 group-aria-expanded:rotate-45 transition"
                                                    style="color: {{ $primary }}">＋</span>
                                            </div>
                                            {{-- Panel --}}
                                            <div id="faq-panel-{{ $idx }}"
                                                class="max-h-0 overflow-hidden transition-[max-height] duration-300 ease-out">
                                                <p class="mt-3 pr-2 text-sm leading-6 text-slate-700">{{
                                                    $faq['answer'] }}</p>
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
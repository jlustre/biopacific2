@php

if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->where('id', $facility['color_scheme_id'])->first();
$primary = $scheme ? ($scheme->primary_color ?? '#0EA5E9') : '#0EA5E9';
$secondary = $scheme ? ($scheme->secondary_color ?? '#1E293B') : '#1E293B';
$accent = $scheme ? ($scheme->accent_color ?? '#F59E0B') : '#F59E0B';
} else {
$primary = '#0EA5E9';
$secondary = '#1E293B';
$accent = '#F59E0B';
}
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
                class="text-primary border-primary">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $primary }}"></span>
                We’re here to help
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold" style="color: {{ $primary }};">Frequently Asked
                Questions</h2>
            <p class="mt-2 md:text-lg" style="color: {{ $neutral_dark }}">Answers to common questions about care,
                visits, dining, safety,
                and more.</p>
        </div>

        {{-- Layout: Left intro + search | Right accordion --}}
        <div class="mt-10 grid gap-8 lg:grid-cols-[0.9fr,1.1fr] items-start">
            {{-- LEFT: Intro, quick links, contact --}}
            <aside class="space-y-6">
                <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold" style="color: {{ $primary }};">Find answers fast</h3>
                    <p class="mt-1 text-sm" style="color: {{ $neutral_dark }}">Search FAQs or browse the most common
                        topics.</p>

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
                            @if($chip) {{-- Only show non-empty categories --}}
                            <button type="button"
                                data-chip="{{ strtolower(str_replace([' ', '&'], ['-', 'and'], $chip)) }}"
                                class="faq-chip rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-slate-700 hover:bg-slate-100">
                                {{ $chip }}
                            </button>
                            @endif
                            @endforeach
                        </div>
                        <ul id="faqList" class="divide-y divide-slate-200">
                            @forelse($faqs as $idx => $faq)
                            @php
                            $processedAnswer = processFaqAnswer($faq->answer, $facility);
                            @endphp
                            <li class="faq-item" data-text="{{ Str::of($faq->question.' '.$processedAnswer)->lower() }}"
                                data-category="{{ strtolower(str_replace([' ', '&'], ['-', 'and'], $faq->category ?? '')) }}">
                                <button
                                    class="group w-full text-left px-5 sm:px-6 py-4 hover:bg-slate-50 focus:outline-none focus-visible:bg-slate-50"
                                    aria-expanded="false" aria-controls="faq-panel-{{ $idx }}">
                                    <div class="flex items-start gap-4">
                                        <span
                                            class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl text-white shrink-0"
                                            style="background: {{ $primary }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 14h.01M12 8a2 2 0 1 1 4 0c0 1.1-.9 2-2 2s-2 .9-2 2 .9 2 2 2" />
                                                <text x="12" y="16" text-anchor="middle" font-size="10"
                                                    fill="currentColor" font-family="Arial" dy="-2">?</text>
                                            </svg>
                                        </span>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <span class="font-semibold" style="color: {{ $neutral_dark }}">{{
                                                        $faq->question
                                                        }}</span>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        @if($faq->category)
                                                        <span
                                                            class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-1 rounded-full">
                                                            {{ $faq->category }}
                                                        </span>
                                                        @endif
                                                        @if($faq->is_featured)
                                                        <span
                                                            class="text-xs text-yellow-800 font-medium bg-yellow-100 px-2 py-1 rounded-full">
                                                            Featured
                                                        </span>
                                                        @endif
                                                        @if($faq->is_default)
                                                        <span
                                                            class="text-xs text-blue-800 font-medium bg-blue-100 px-2 py-1 rounded-full"
                                                            style="color: {{ $secondary }}; background: {{ $neutral_light }}20;">
                                                            Default
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span
                                                    class="ml-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-700 group-aria-expanded:rotate-45 transition"
                                                    class="text-primary">＋</span>
                                            </div>
                                            {{-- Panel --}}
                                            <div id="faq-panel-{{ $idx }}"
                                                class="max-h-0 overflow-hidden transition-[max-height] duration-300 ease-out">
                                                <p class="mt-3 pr-2 text-sm leading-6 text-slate-700">{!!
                                                    nl2br(e($processedAnswer)) !!}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </li>
                            @empty
                            <li class="text-center py-8 text-slate-500">
                                <i class="fas fa-question-circle text-4xl mb-4 text-slate-300"></i>
                                <p class="text-lg font-medium">No FAQs available</p>
                                <p class="text-sm">Check back later for frequently asked questions.</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Contact CTA --}}
                <div class="rounded-3xl bg-white ring-1 ring-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold" style="color: {{ $primary }};">Still have questions?</h3>
                    <p class="mt-1 text-sm" style="color: {{ $neutral_dark }};">Our caring team is here to help with any
                        additional questions
                        about our services.</p>

                    @php
                    // Process FAQ answers to replace placeholders
                    function processFaqAnswer($answer, $facility) {
                    if (!isset($facility)) {
                    return $answer;
                    }

                    $facilityPhone = null;
                    $facilityHours = null;
                    $facilityName = null;
                    $facilityEmail = null;
                    $facilityAddress = null;
                    $facilityBeds = null;

                    if (is_array($facility)) {
                    $facilityPhone = $facility['phone'] ?? null;
                    $facilityHours = $facility['hours'] ?? null;
                    $facilityName = $facility['name'] ?? null;
                    $facilityEmail = $facility['email'] ?? null;
                    $facilityBeds = $facility->beds ?? null;
                    $facilityAddress = trim(($facility['address'] ?? '') . ', ' . ($facility['city'] ?? '') . ', ' .
                    ($facility['state'] ?? ''), ', ');
                    } elseif (is_object($facility)) {
                    $facilityPhone = $facility->phone ?? null;
                    $facilityHours = $facility->hours ?? null;
                    $facilityName = $facility->name ?? null;
                    $facilityEmail = $facility->email ?? null;
                    $facilityBeds = $facility->beds ?? null;
                    $facilityAddress = trim(($facility->address ?? '') . ', ' . ($facility->city ?? '') . ', ' .
                    ($facility->state ?? ''), ', ');
                    }

                    // Replace all placeholders
                    if ($facilityPhone) {
                    $formattedPhone = \App\Helpers\PhoneHelper::format($facilityPhone);
                    $answer = str_replace('[phone number]', $formattedPhone, $answer);
                    }

                    if ($facilityHours) {
                    $answer = str_replace('[visiting hours]', $facilityHours, $answer);
                    }

                    if ($facilityName) {
                    $answer = str_replace('[facility name]', $facilityName, $answer);
                    $answer = str_replace('[Facility name]', $facilityName, $answer);
                    }

                    if ($facilityEmail) {
                    $answer = str_replace('[email]', $facilityEmail, $answer);
                    }

                    if ($facilityAddress && $facilityAddress !== ', , ') {
                    $answer = str_replace('[facility address]', $facilityAddress, $answer);
                    }

                    if ($facilityBeds) {
                    $answer = str_replace('[bed count]', $facilityBeds, $answer);
                    }

                    return $answer;
                    }

                    // Handle both array and object facility data with better error handling
                    $facilityPhone = null;

                    try {
                    if (isset($facility)) {
                    if (is_array($facility) && !empty($facility['phone'])) {
                    $facilityPhone = $facility['phone'];
                    } elseif (is_object($facility) && !empty($facility->phone)) {
                    $facilityPhone = $facility->phone;
                    }
                    }
                    } catch (Exception $e) {
                    // Fallback to default if there's any error
                    $facilityPhone = null;
                    }

                    // Ensure we always have a phone number for the button
                    if (!$facilityPhone) {
                    $facilityPhone = '4083779275'; // Fallback phone number
                    }
                    @endphp

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <a href="tel:{{ \App\Helpers\PhoneHelper::forTel($facilityPhone) }}"
                            class="inline-flex items-center justify-center w-full px-4 py-3 rounded-xl text-white font-medium shadow-sm transition duration-200 hover:shadow-lg hover:-translate-y-0.5 hover:brightness-110"
                            style="background: {{ $primary }};">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            Call {{ \App\Helpers\PhoneHelper::format($facilityPhone) }}
                        </a>
                        <a href="#contact"
                            class="inline-flex items-center justify-center w-full px-4 py-3 bg-slate-100 text-slate-700 font-medium rounded-xl transition duration-200 hover:bg-slate-200 hover:shadow-lg hover:-translate-y-0.5"
                            style="border: 1px solid {{ $neutral_dark }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Contact Form
                        </a>
                    </div>
                </div>
            </aside>
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
        const category = item.getAttribute('data-category') || '';
        const matches = hay.includes(query) || category.includes(query);
        item.style.display = matches ? '' : 'none';
      });
    }
    search?.addEventListener('input', (e)=> applyFilter(e.target.value));

    // Chip quick filters with improved category matching
    chips.forEach(ch => ch.addEventListener('click', ()=>{
        const term = ch.getAttribute('data-chip') || '';
        
        // Update chip styles
        chips.forEach(chip => {
            chip.classList.remove('bg-blue-100', 'text-blue-700', 'border-blue-200');
            chip.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200');
        });
        ch.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200');
        ch.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
        
        if (search) search.value = term === 'all' ? '' : term;
        if (term === 'all') {
            items.forEach(item => { item.style.display = ''; });
        } else {
            items.forEach(item => {
                const category = item.getAttribute('data-category') || '';
                item.style.display = category === term ? '' : 'none';
            });
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
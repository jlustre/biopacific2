@props([
    'title',
    'accordionKey',
    'sectionHeadingId',
])

<div class="mb-1 flex flex-wrap items-start justify-between gap-2">
    <div class="min-w-0 flex-1 font-bold text-lg text-gray-800">{{ $title }}</div>
    <button type="button"
        class="go-to-section-heading-btn shrink-0 rounded border border-slate-500 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm hover:bg-amber-50 hover:border-amber-500 hover:text-amber-900"
        aria-label="Go to top of section"
        x-on:click="
            $store.partGAccordion.openSection = '{{ $accordionKey }}';
            $nextTick(() => {
                const el = document.getElementById('{{ $sectionHeadingId }}');
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.focus({ preventScroll: true });
                el.classList.add('competency-summary-focus');
                setTimeout(() => el.classList.remove('competency-summary-focus'), 2000);
            });
        "
    >
        Go to Top <span class="inline-block font-black text-[13px] leading-none" aria-hidden="true">↑</span>
    </button>
</div>

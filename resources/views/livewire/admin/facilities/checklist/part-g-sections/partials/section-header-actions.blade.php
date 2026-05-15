@props([
    'accordionKey',
    'summaryFormId',
])

<div class="flex shrink-0 flex-wrap items-center gap-2">
    @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-exclude-checkbox')

    <button type="button"
        class="go-to-summary-btn rounded border border-slate-500 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm hover:bg-amber-50 hover:border-amber-500 hover:text-amber-900"
        x-on:click="
            $store.partGAccordion.openSection = '{{ $accordionKey }}';
            $nextTick(() => {
                const el = document.getElementById('{{ $summaryFormId }}');
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.focus({ preventScroll: true });
                el.classList.add('competency-summary-focus');
                setTimeout(() => el.classList.remove('competency-summary-focus'), 2000);
            });
        "
    >
        Evaluation Summary ↓
    </button>
</div>

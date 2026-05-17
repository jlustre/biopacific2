@props([
    'accordionKey',
    'store' => 'partGAccordion',
    'toggleClass' => 'section-toggle',
])

<div wire:ignore>
    <button
        type="button"
        class="{{ $toggleClass }} inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100"
        data-accordion-key="{{ $accordionKey }}"
        data-expanded="1"
        aria-label="Collapse section items"
        x-text="$store.{{ $store }}.openSection === '{{ $accordionKey }}' ? '▲' : '▼'"
        x-bind:aria-label="$store.{{ $store }}.openSection === '{{ $accordionKey }}' ? 'Collapse section items' : 'Expand section items'"
        x-on:click="$store.{{ $store }}.openSection = $store.{{ $store }}.openSection === '{{ $accordionKey }}' ? null : '{{ $accordionKey }}'"
        x-bind:data-expanded="$store.{{ $store }}.openSection === '{{ $accordionKey }}' ? '1' : '0'"
    >▲</button>
</div>

@props([
    'column',
    'label',
    'align' => 'left',
])

@php
    $isActive = $sortColumn === $column;
    $alignClass = match ($align) {
        'center' => 'justify-center text-center',
        'right' => 'justify-end text-right',
        default => 'justify-start text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => 'border border-slate-500 px-1 py-1 font-semibold leading-tight']) }}>
    <button
        type="button"
        wire:click="sortBy('{{ $column }}')"
        class="flex w-full items-center gap-0.5 {{ $alignClass }} hover:text-slate-700 focus:outline-none focus:ring-1 focus:ring-slate-500 rounded-sm"
        aria-label="Sort by {{ strip_tags($label) }}"
    >
        <span>{!! $label !!}</span>
        @if($isActive)
        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} shrink-0 text-[8px] text-slate-700" aria-hidden="true"></i>
        @else
        <i class="fas fa-sort shrink-0 text-[8px] text-slate-400 opacity-70" aria-hidden="true"></i>
        @endif
    </button>
</th>

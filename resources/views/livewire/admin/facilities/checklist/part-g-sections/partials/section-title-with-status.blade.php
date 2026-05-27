@props([
    'title',
    'sectionItems' => [],
    'variant' => 'header',
])

@php
    $statusLabel = $this->sectionProgressStatusLabel(is_array($sectionItems) ? $sectionItems : []);
    $statusClass = match ($statusLabel) {
        'In Progress' => 'text-amber-700',
        'Excluded' => 'text-slate-500 italic',
        default => 'text-slate-500',
    };
@endphp

<div class="flex min-w-0 flex-col leading-tight">
    <span @class(['truncate' => $variant === 'header'])>{{ $title }}</span>
    <span class="text-[10px] font-normal {{ $statusClass }}">{{ $statusLabel }}</span>
</div>

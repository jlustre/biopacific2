@php
    $avatarUrl = $avatarUrl ?? null;
    $initials = $initials ?? '?';
    $size = $size ?? 'md';
    $shape = $shape ?? 'rounded-2xl';
    $ring = $ring ?? '';
    $imgClass = $imgClass ?? '';

    $sizeClasses = [
        'xs' => 'h-8 w-8 text-[10px]',
        'sm' => 'h-9 w-9 text-xs',
        'md' => 'h-16 w-16 text-xl',
        'lg' => 'h-20 w-20 text-2xl',
        'hero' => 'h-16 w-16 text-xl sm:h-[4.5rem] sm:w-[4.5rem] sm:text-2xl',
    ];
    $classes = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

@if($avatarUrl)
<img src="{{ $avatarUrl }}" alt=""
     class="{{ $classes }} {{ $shape }} object-cover {{ $ring }} {{ $imgClass }}">
@else
<div class="flex shrink-0 items-center justify-center font-black {{ $classes }} {{ $shape }} {{ $ring }} {{ $imgClass }}
            {{ ($variant ?? 'light') === 'hero' ? 'bg-white/15 text-white ring-2 ring-white/25' : 'bg-brand-100 text-brand-800' }}">
    {{ $initials }}
</div>
@endif

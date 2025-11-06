@props([
'type' => 'button',
'size' => 'md',
'disabled' => false,
'loading' => false,
'loadingText' => 'Loading...',
'icon' => null,
'iconPosition' => 'left',
'primary' => '#3B82F6',
'secondary' => '#1E40AF',
'accent' => '#6366F1',
'neutral_dark' => '#374151',
'neutral_light' => '#F3F4F6',
'href' => null
])

@php
$sizeClasses = [
'xs' => 'px-2 py-1 text-xs',
'sm' => 'px-3 py-1.5 text-xs',
'md' => 'px-4 py-2 text-sm',
'lg' => 'px-6 py-3 text-base',
'xl' => 'px-8 py-4 text-lg'
];

$baseClasses = 'cursor-pointer inline-flex items-center justify-center font-medium rounded-lg shadow-sm transition-all
duration-200
focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
$classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);

$isLink = !empty($href);
$element = $isLink ? 'a' : 'button';
@endphp

<{{ $element }} @if($isLink) href="{{ $href }}" @else type="{{ $type }}" {{ $disabled || $loading ? 'disabled' : '' }}
    @endif {{ $attributes->merge(['class' => $classes]) }}
    style="
    --primary-color: {{ $primary }};
    --secondary-color: {{ $secondary }};
    --accent-color: {{ $accent }};
    --neutral-dark: {{ $neutral_dark }};
    --neutral-light: {{ $neutral_light }};
    background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    text-decoration: none;
    "
    onmouseover="this.style.background='linear-gradient(to right, var(--secondary-color), var(--primary-color))'"
    onmouseout="this.style.background='linear-gradient(to right, var(--primary-color), var(--secondary-color))'"
    onfocus="this.style.boxShadow='0 0 0 3px ' + this.style.getPropertyValue('--primary-color') + '40'"
    onblur="this.style.boxShadow=''"
    >
    @if($loading)
    <!-- Loading State -->
    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
    </svg>
    {{ $loadingText }}
    @else
    <!-- Normal State -->
    @if($icon && $iconPosition === 'left')
    <i class="{{ $icon }} mr-2"></i>
    @endif

    {{ $slot }}

    @if($icon && $iconPosition === 'right')
    <i class="{{ $icon }} ml-2"></i>
    @endif
    @endif
</{{ $element }}>
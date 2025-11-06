@props([
'type' => 'info',
'dismissible' => false,
'icon' => null,
'title' => null,
'primary' => '#3B82F6',
'secondary' => '#1E40AF',
'accent' => '#6366F1',
'neutral_dark' => '#374151',
'neutral_light' => '#F3F4F6'
])

@php
$typeStyles = [
'success' => [
'container' => 'bg-green-100 text-green-800 border-green-300',
'icon' => 'fas fa-check-circle',
'iconColor' => 'text-green-600'
],
'error' => [
'container' => 'bg-red-100 text-red-800 border-red-300',
'icon' => 'fas fa-exclamation-circle',
'iconColor' => 'text-red-600'
],
'warning' => [
'container' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
'icon' => 'fas fa-exclamation-triangle',
'iconColor' => 'text-yellow-600'
],
'info' => [
'container' => 'bg-blue-100 text-blue-800 border-blue-300',
'icon' => 'fas fa-info-circle',
'iconColor' => 'text-blue-600'
],
'primary' => [
'container' => 'border',
'icon' => 'fas fa-info-circle',
'iconColor' => ''
]
];

$currentStyle = $typeStyles[$type] ?? $typeStyles['info'];
$defaultIcon = $icon ?? $currentStyle['icon'];

// Build base classes
$baseClasses = 'mb-6 p-4 rounded-lg border transition-all duration-300';
$containerClasses = $type === 'primary' ? $baseClasses : $baseClasses . ' ' . $currentStyle['container'];
@endphp

<div {{ $attributes->merge(['class' => $containerClasses]) }}
    @if($type === 'primary')
    style="
    --primary-color: {{ $primary }};
    --secondary-color: {{ $secondary }};
    --accent-color: {{ $accent }};
    --neutral-dark: {{ $neutral_dark }};
    --neutral-light: {{ $neutral_light }};
    background-color: color-mix(in srgb, var(--primary-color) 10%, white);
    border-color: var(--primary-color);
    color: var(--primary-color);
    "
    @endif
    @if($dismissible)
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    @endif
    >
    <div class="flex items-start">
        @if($defaultIcon)
        <i class="{{ $defaultIcon }} mr-3 mt-0.5 {{ $type === 'primary' ? '' : $currentStyle['iconColor'] }}"
            @if($type==='primary' ) style="color: var(--primary-color);" @endif></i>
        @endif

        <div class="flex-1">
            @if($title)
            <h4 class="font-semibold mb-1">{{ $title }}</h4>
            @endif

            <div>{{ $slot }}</div>
        </div>

        @if($dismissible)
        <!-- DEBUG: Dismiss button should show here -->
        <button @click="show = false"
            class="ml-4 flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-red-500 text-white hover:bg-red-600 transition-all duration-200 font-bold text-xl cursor-pointer"
            style="min-width: 32px; min-height: 32px; z-index: 9999; line-height: 1;" aria-label="Dismiss"
            title="Close">
            ×
        </button>
        @else
        <!-- DEBUG: dismissible is false -->
        @endif
    </div>
</div>
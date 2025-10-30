@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$accent = $facility['accent_color'] ?? '#F59E0B';
@endphp

<!-- Wave Divider -->
<div aria-hidden="true" class="relative overflow-hidden">
    <svg viewBox="0 0 1440 120" class="w-full h-16 md:h-24">
        <defs>
            <linearGradient id="divider-grad" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="{{ $primary }}" stop-opacity="0.12" />
                <stop offset="50%" stop-color="{{ $primary }}" stop-opacity="0.08" />
                <stop offset="100%" stop-color="{{ $accent }}" stop-opacity="0.12" />
            </linearGradient>
        </defs>
        <path fill="url(#divider-grad)"
            d="M0,40 C160,80 320,0 480,20 C640,40 800,110 960,90 C1120,70 1280,10 1440,30 L1440,120 L0,120 Z">
        </path>
    </svg>
</div>
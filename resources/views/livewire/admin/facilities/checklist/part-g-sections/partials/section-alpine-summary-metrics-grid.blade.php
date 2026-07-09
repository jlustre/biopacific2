@php
    $totalItemsLabel = $totalItemsLabel ?? 'TOTAL ITEMS';
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">{{ $totalItemsLabel }}</div>
        <div class="text-2xl font-bold text-gray-700" x-text="summary.totalItems"></div>
        <div class="text-xs text-gray-500 mt-1" x-text="summary.checkedOfTotal"></div>
    </div>
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL POINTS</div>
        <div class="text-2xl font-bold text-gray-700" x-text="summary.totalPoints"></div>
        <div class="text-xs text-gray-500 mt-1" x-text="summary.pointsOfTotal"></div>
    </div>
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">AVERAGE</div>
        <div class="text-2xl font-bold text-gray-700" x-text="summary.average"></div>
    </div>
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">OVERALL RATING</div>
        <div class="text-2xl font-bold text-gray-700" x-text="summary.overallRating"></div>
    </div>
</div>

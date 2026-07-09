@php
    $sectionSummary = $sectionSummary ?? [
        'totalItems' => 0,
        'checkedOfTotal' => '0 of 0 rated',
        'totalPoints' => 0,
        'pointsOfTotal' => '0 of 0 points',
        'average' => '—',
        'overallRating' => '—',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3" wire:key="section-summary-metrics-{{ md5(json_encode($sectionSummary)) }}">
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL ITEMS</div>
        <div class="text-2xl font-bold text-gray-700">{{ $sectionSummary['totalItems'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $sectionSummary['checkedOfTotal'] }}</div>
    </div>
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL POINTS</div>
        <div class="text-2xl font-bold text-gray-700">{{ $sectionSummary['totalPoints'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $sectionSummary['pointsOfTotal'] ?? '' }}</div>
    </div>
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">AVERAGE</div>
        <div class="text-2xl font-bold text-gray-700">{{ $sectionSummary['average'] }}</div>
    </div>
    <div class="rounded border border-gray-300 bg-white p-4 flex flex-col items-center">
        <div class="text-xs font-semibold text-gray-500 mb-1">OVERALL RATING</div>
        <div class="text-2xl font-bold text-gray-700">{{ $sectionSummary['overallRating'] }}</div>
    </div>
</div>

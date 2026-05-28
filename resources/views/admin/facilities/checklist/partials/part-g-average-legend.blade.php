@php
    $legendVariant = $legendVariant ?? 'screen';
@endphp

@include('admin.facilities.checklist.partials.part-f-rating-legend', [
    'legendVariant' => $legendVariant,
    'showOverallFooter' => false,
])

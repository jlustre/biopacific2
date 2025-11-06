@php
// Use the activeSections passed from the controller
$legalActiveSections = $activeSections ?? [];
if (is_string($legalActiveSections)) {
$legalActiveSections = json_decode($legalActiveSections, true) ?: [];
} elseif ($legalActiveSections instanceof \Illuminate\Support\Collection) {
$legalActiveSections = $legalActiveSections->toArray();
} elseif (!is_array($legalActiveSections)) {
$legalActiveSections = (array) $legalActiveSections;
}
@endphp

@include('partials.topbar.default', [
'activeSections' => $legalActiveSections,
'facility' => $facility,
'primary' => $primary ?? '#000000',
'secondary' => $secondary ?? '#666666',
'accent' => $accent ?? '#cccccc',
'neutral_light' => $neutral_light ?? '#f8f9fa',
'neutral_dark' => $neutral_dark ?? '#343a40'
])
@php
$activeSections = $facility->sections ?? [];
$activeSections = $activeSections ?? [];
if (is_string($activeSections)) {
$activeSections = json_decode($activeSections, true) ?: [];
} elseif ($activeSections instanceof \Illuminate\Support\Collection) {
$activeSections = $activeSections->toArray();
} elseif (!is_array($activeSections)) {
$activeSections = (array) $activeSections;
}
@endphp
@include('partials.topbar.default', ['activeSections' => $activeSections, 'facility' => $facility])
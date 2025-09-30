{{-- Color Scheme Vars Component --}}
@php
if (isset($facility['color_scheme_id']) && $facility['color_scheme_id']) {
$scheme = \DB::table('color_schemes')->find($facility['color_scheme_id']);
$primary = $scheme->primary_color ?? '#047857';
$secondary = $scheme->secondary_color ?? '#000000';
$accent = $scheme->accent_color ?? '#F59E0B';
} else {
$primary = '#047857';
$secondary = '#000000';
$accent = '#F59E0B';
}
@endphp
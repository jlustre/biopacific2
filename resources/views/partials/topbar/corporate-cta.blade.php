@php
    $facilityModel = null;
    if (isset($facility) && is_object($facility) && $facility instanceof \App\Models\Facility) {
        $facilityModel = $facility;
    } elseif (isset($facility) && is_array($facility) && !empty($facility['slug'])) {
        $facilityModel = \App\Models\Facility::query()->where('slug', $facility['slug'])->first();
    }
    $cta = $facilityModel?->publicHeaderCta();
@endphp
@if($cta)
<a href="{{ $cta['url'] }}"
  class="max-w-[180px] min-w-[120px] mx-auto cursor-pointer px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center justify-center ml-2 shadow-md"
  style="background-color: {{ $primary }}; color: white; border: none;"
  @mouseenter="$el.style.backgroundColor = '{{ $secondary }}'; $el.style.color = 'white';"
  @mouseleave="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';">
  <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
  </svg>
  {{ $cta['label'] }}
</a>
@endif

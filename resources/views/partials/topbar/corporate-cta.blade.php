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
<a href="{{ $cta['url'] }}" target="_self"
  class="relative z-[60] max-w-[180px] min-w-[120px] mx-auto cursor-pointer px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center justify-center ml-2 shadow-md"
  style="background-color: {{ $primary }}; color: white; border: none;"
  @mouseenter="$el.style.backgroundColor = '{{ $secondary }}'; $el.style.color = 'white';"
  @mouseleave="$el.style.backgroundColor = '{{ $primary }}'; $el.style.color = 'white';">
  @if(($cta['label'] ?? '') === 'Dashboard')
  <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
  </svg>
  @else
  <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
  </svg>
  @endif
  {{ $cta['label'] }}
</a>
@endif

@props(['sections' => null, 'facility' => null])

@php
    $layoutService = app(\App\Services\DynamicLayoutService::class);
    $sections = $sections ?? $layoutService->getLayoutSections($facility);
@endphp

@foreach($sections as $sectionData)
    @if(view()->exists($sectionData['component_path']))
        @include($sectionData['component_path'], [
            'facility' => $facility,
            'config' => $sectionData['config'],
            'variant' => $sectionData['variant']
        ])
    @else
        {{-- Fallback to default variant --}}
        @php
            $fallbackPath = str_replace(['.video', '.split', '.stats', '.timeline', '.cards', '.tabs', '.form', '.info', '.map'], '.default', $sectionData['component_path']);
        @endphp

        @if(view()->exists($fallbackPath))
            @include($fallbackPath, [
                'facility' => $facility,
                'config' => $sectionData['config'],
                'variant' => 'default'
            ])
        @else
            {{-- Show section placeholder for development --}}
            @if(config('app.debug'))
                <div class="py-8 px-4 bg-gray-100 border-2 border-dashed border-gray-300 text-center">
                    <h3 class="text-lg font-semibold text-gray-600">Section: {{ $sectionData['section']->name }}</h3>
                    <p class="text-sm text-gray-500">Component not found: {{ $sectionData['component_path'] }}</p>
                    <p class="text-xs text-gray-400">Variant: {{ $sectionData['variant'] }}</p>
                </div>
            @endif
        @endif
    @endif
@endforeach

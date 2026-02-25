@props([
'section',
'icon',
'title',
])

<div x-show="activeSection === '{{ $section }}'" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
    class="bg-white rounded-lg shadow-md p-8">
    <h3 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-200">
        <i class="{{ $icon }} text-teal-600 mr-2"></i> {{ $title }}
    </h3>
    {{ $slot }}
</div>
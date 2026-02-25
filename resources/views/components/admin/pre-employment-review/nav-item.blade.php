@props([
'section',
'icon',
'label',
])

<button @click="activeSection = '{{ $section }}'"
    :class="{ 'bg-teal-50 border-l-4 border-teal-600 text-teal-700': activeSection === '{{ $section }}' }"
    class="w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition font-semibold text-gray-900">
    <i class="{{ $icon }} mr-2"></i> {{ $label }}
</button>
@props([
'label',
'value' => null,
])

<div>
    <p class="text-gray-600 text-sm font-semibold mb-1">{{ $label }}</p>
    <p class="text-gray-900 text-base">{{ filled($value) ? $value : 'N/A' }}</p>
</div>
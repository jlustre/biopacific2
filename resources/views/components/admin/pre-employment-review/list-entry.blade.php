@props([
'title' => null,
'line1' => null,
'line2' => null,
'line3' => null,
])

<div class="border-l-4 border-teal-500 pl-4 py-3 bg-gray-50 rounded">
    <p class="font-semibold text-gray-900">{{ filled($title) ? $title : 'N/A' }}</p>
    @if(filled($line1))
    <p class="text-gray-600 text-sm">{{ $line1 }}</p>
    @endif
    @if(filled($line2))
    <p class="text-gray-600 text-sm">{{ $line2 }}</p>
    @endif
    @if(filled($line3))
    <p class="text-gray-600 text-sm">{{ $line3 }}</p>
    @endif
</div>
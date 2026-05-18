@props([
    'itemId',
    'rating',
    'namePrefix',
    'wireKeyPrefix' => 'partg',
    'disabled' => false,
    'currentResponse' => null,
])

<td class="text-center border border-gray-300 py-0">
    <input
        type="radio"
        name="{{ $namePrefix }}-response-{{ $itemId }}"
        value="{{ $rating }}"
        wire:key="{{ $wireKeyPrefix }}-response-{{ $itemId }}-{{ $rating }}"
        wire:model.live="responses.{{ (int) $itemId }}"
        @click="setResponse({{ $itemId }}, '{{ $rating }}')"
        @disabled($disabled)
        class="h-4 w-4 border-slate-400 text-slate-700 focus:ring-slate-400"
    >
</td>

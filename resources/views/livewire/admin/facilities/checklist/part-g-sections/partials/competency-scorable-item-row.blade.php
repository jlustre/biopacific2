@props([
    'item',
    'index',
    'wireKeyPrefix',
    'rowClassPrefix',
    'disabled' => false,
])

<tr wire:key="{{ $wireKeyPrefix }}-row-{{ $item['id'] }}-{{ $this->itemReviewDisplayRating((int) $item['id']) ?: 'none' }}"
    class="{{ $rowClassPrefix }}-row-{{ $index % 2 === 0 ? 'even' : 'odd' }} {{ $rowClassPrefix }}-row-hover">
    <td class="border border-gray-300 py-0 text-sm" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
        {{ $item['item'] ?? '' }}
    </td>
    @include('livewire.admin.facilities.checklist.part-g-sections.partials.competency-item-review-cells', [
        'itemId' => $item['id'],
        'wireKeyPrefix' => $wireKeyPrefix,
        'disabled' => $disabled,
    ])
</tr>


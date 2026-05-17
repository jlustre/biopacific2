@props([
    'item',
    'wireKey',
    'rowClass' => 'bg-blue-50',
])

<tr
    wire:key="{{ $wireKey }}"
    class="{{ $rowClass }}"
    data-indent-level="{{ $item['indentLevel'] ?? 0 }}"
    data-has-child-items="1"
>
    <td class="border border-gray-300 font-bold text-gray-700 text-md" colspan="5" style="padding-left: calc(0.5rem + {{ ($item['indentLevel'] ?? 0) * 20 }}px);">
        <span class="inline-flex items-center gap-2">
            <span wire:ignore>
                <button
                    type="button"
                    class="hierarchy-toggle inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-400 bg-white text-[10px] font-bold text-slate-700 shadow-sm hover:bg-slate-100"
                    data-expanded="1"
                    aria-label="Collapse child items"
                >▲</button>
            </span>
            <span>{{ $item['item'] ?? '' }}</span>
        </span>
    </td>
</tr>

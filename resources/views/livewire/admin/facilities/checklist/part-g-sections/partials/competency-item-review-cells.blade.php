@props([
    'itemId',
    'wireKeyPrefix' => 'partg',
    'disabled' => false,
])

@php
    $itemId = (int) $itemId;
    $hasReview = $this->itemHasReview($itemId);
    $reviewDate = $this->itemReviewDisplayDate($itemId);
    $reviewer = $this->itemReviewDisplayReviewer($itemId);
    $rating = $this->itemReviewDisplayRating($itemId);
    $ratingClasses = match ($rating) {
        'E' => 'bg-emerald-100 text-emerald-800',
        'S' => 'bg-sky-100 text-sky-800',
        'U' => 'bg-red-100 text-red-800',
        'N' => 'bg-slate-200 text-slate-700',
        default => 'bg-slate-100 text-slate-500',
    };
@endphp

<td class="border border-gray-300 px-2 py-1 text-center text-xs text-slate-700 whitespace-nowrap">
    {{ $hasReview && $reviewDate ? $reviewDate : '—' }}
</td>
<td class="border border-gray-300 px-2 py-1 text-center text-xs text-slate-700 max-w-[8rem] truncate" title="{{ $reviewer }}">
    {{ $hasReview && $reviewer ? $reviewer : '—' }}
</td>
<td class="border border-gray-300 px-2 py-1 text-center">
    @if($hasReview && $rating)
    <span class="inline-flex min-w-[1.75rem] items-center justify-center rounded px-1.5 py-0.5 text-xs font-bold {{ $ratingClasses }}">{{ $rating }}</span>
    @else
    <span class="text-xs text-slate-400">—</span>
    @endif
</td>
<td class="border border-gray-300 px-2 py-1 text-center">
    <div class="flex flex-wrap items-center justify-center gap-1">
        @if($hasReview)
        <span class="inline-flex items-center gap-1 rounded bg-teal-700 px-2 py-0.5 text-[10px] font-semibold text-white" title="Reviewed">
            <i class="fas fa-check text-[9px]" aria-hidden="true"></i> Reviewed
        </span>
        @if(! $disabled)
        <button type="button"
            wire:click="openItemReview({{ $itemId }})"
            class="rounded border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700 hover:bg-slate-50"
            title="Edit review">Edit</button>
        <button type="button"
            wire:click="undoItemReview({{ $itemId }})"
            wire:confirm="Remove this item review?"
            class="rounded border border-amber-300 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-900 hover:bg-amber-100"
            title="Undo review">Undo</button>
        @endif
        @elseif(! $disabled)
        <button type="button"
            wire:click="openItemReview({{ $itemId }})"
            class="inline-flex items-center gap-1 rounded bg-slate-800 px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-slate-900"
            title="Review this item">
            <i class="fas fa-clipboard-check text-[9px]" aria-hidden="true"></i> Review
        </button>
        @else
        <span class="text-xs text-slate-400">—</span>
        @endif
    </div>
</td>


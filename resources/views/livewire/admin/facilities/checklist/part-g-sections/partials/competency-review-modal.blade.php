@php
    use App\Support\PartGCompetencyScoring;

    $itemRatingOptions = PartGCompetencyScoring::itemRatingOptions();
@endphp

@if($this->reviewModalOpen)
<div class="fixed inset-0 z-[130] flex items-center justify-center bg-black/40 p-4" wire:key="partg-review-modal">
    <div class="w-full max-w-md rounded-lg bg-white shadow-xl" role="dialog" aria-modal="true" aria-labelledby="partgReviewModalTitle">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h4 id="partgReviewModalTitle" class="text-sm font-bold text-slate-900">Review competency item</h4>
            <button type="button" wire:click="closeItemReview" class="text-slate-500 hover:text-slate-800 text-xl leading-none" aria-label="Close">&times;</button>
        </div>

        <div class="space-y-3 px-4 py-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Review date</label>
                    <input type="date" wire:model="reviewModalDate" readonly
                        class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm text-slate-700" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reviewer</label>
                    <input type="text" wire:model="reviewModalReviewerName" readonly
                        class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm text-slate-700" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Rating <span class="text-red-600">*</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($itemRatingOptions as $code => $label)
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs font-semibold transition-colors
                        {{ $this->reviewModalRating === $code ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                        <input type="radio" wire:model.live="reviewModalRating" value="{{ $code }}" class="sr-only">
                        <span>{{ $code }}</span>
                        <span class="font-normal opacity-80">({{ $label }})</span>
                    </label>
                    @endforeach
                </div>
                @error('reviewModalRating')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showComments: @js(PartGCompetencyScoring::isBelowExpectationsItemRating($this->reviewModalRating)) }" x-effect="showComments = $wire.reviewModalRating === 'B'">
                <div x-show="showComments" x-cloak>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Comments <span class="text-red-600">*</span>
                        <span class="font-normal text-slate-500">(required for Below Expectations)</span>
                    </label>
                    <textarea wire:model="reviewModalComments" rows="3"
                        class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm"
                        placeholder="Explain why this item was rated Below Expectations..."></textarea>
                    @error('reviewModalComments')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-200 px-4 py-3">
            <button type="button" wire:click="closeItemReview"
                class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </button>
            <button type="button" wire:click="saveItemReview"
                class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">
                Save review
            </button>
        </div>
    </div>
</div>
@endif

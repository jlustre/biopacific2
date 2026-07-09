<div class="mb-3">
    <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS</label>
    <textarea
        wire:model.blur="summaryComments"
        @disabled($this->reviewerSummaryCommentsLocked)
        class="w-full rounded border border-gray-300 bg-slate-100 p-3 text-gray-700 min-h-[100px] resize-y @if($this->reviewerSummaryCommentsLocked) opacity-70 cursor-not-allowed @endif"
        placeholder="Enter comments here..."
    ></textarea>
</div>

@if($this->showDraftSubmitActions)
    <div class="flex flex-col md:flex-row justify-end gap-2 mt-2">
        <button
            type="button"
            wire:click="saveDraft"
            wire:loading.attr="disabled"
            class="rounded border-gray-400 bg-white px-6 py-2 font-semibold inline-flex items-center text-amber-900 shadow hover:bg-amber-200 border"
        >
            <span wire:loading.remove wire:target="saveDraft">Save as Draft</span>
            <span wire:loading wire:target="saveDraft">Saving...</span>
        </button>
        <button
            type="button"
            wire:click="submitAssessment"
            wire:loading.attr="disabled"
            class="rounded border border-gray-800 bg-gray-900 px-6 py-2 font-semibold text-white hover:bg-gray-800"
        >
            <span wire:loading.remove wire:target="submitAssessment">{{ $submitLabel ?? 'Submit Assessment' }}</span>
            <span wire:loading wire:target="submitAssessment">Submitting...</span>
        </button>
    </div>
@endif

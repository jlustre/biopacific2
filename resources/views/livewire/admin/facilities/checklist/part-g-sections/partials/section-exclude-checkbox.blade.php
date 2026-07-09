<label class="inline-flex items-center gap-2 text-[11px] font-medium normal-case text-slate-700 {{ $this->sectionItemReviewsLocked() ? 'opacity-60' : '' }}">
    <input
        type="checkbox"
        wire:model.live="sectionExcluded"
        @disabled($this->sectionItemReviewsLocked())
        class="h-4 w-4 rounded border-slate-400 text-slate-700 focus:ring-slate-400"
    >
    <span>Exclude</span>
</label>

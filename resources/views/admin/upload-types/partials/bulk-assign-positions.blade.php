<div class="rounded-2xl border border-cyan-200 bg-cyan-50/50 p-4">
    <form id="bulkPositionForm" action="{{ route('admin.checklist-items.bulk-positions') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <h2 class="text-sm font-bold text-slate-900">Bulk assign to positions</h2>
            <p class="mt-1 text-xs text-slate-600">Select one or more positions, check the document rows below, then update.</p>
        </div>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end">
            <div class="xl:w-1/2">
                <label for="bulk_position_ids" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Positions</label>
                <select name="position_ids[]" id="bulk_position_ids" multiple size="6"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                    @foreach ($positions as $position)
                        <option value="{{ $position->position_id }}">{{ $position->title }} ({{ $position->position_code }})</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Hold Ctrl (Windows) or Cmd (Mac) to select multiple positions.</p>
            </div>
            <div class="flex-1 space-y-3">
                <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="apply_to_everyone" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Apply selected documents to everybody
                </label>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-xl bg-cyan-600 px-5 py-2 text-sm font-semibold text-white hover:bg-cyan-700">
                        <i class="fas fa-layer-group mr-2"></i> Update selected documents
                    </button>
                    <span class="text-sm text-slate-500">Use the checkboxes in the table's first column.</span>
                </div>
            </div>
        </div>
    </form>
</div>

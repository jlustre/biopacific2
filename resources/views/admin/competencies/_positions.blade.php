@php
    $rawPositionIds = old('position_ids', $sample->position_ids ?? ['global']);
    $appliesToEveryone = old('apply_to_everyone', null) !== null
        ? (bool) old('apply_to_everyone')
        : (in_array('global', (array) $rawPositionIds, true) || empty(array_filter((array) $rawPositionIds, fn ($id) => $id !== 'global' && $id !== null && $id !== '')));
    $selectedPositionIds = collect($rawPositionIds)
        ->filter(fn ($id) => $id !== 'global' && $id !== null && $id !== '')
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div>
    <div class="mb-2 flex items-center justify-between gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-900">Applicable positions</label>
            <p class="mt-1 text-sm text-gray-600">Applies to every item in this competency. Check “All positions” for global, or select specific positions.</p>
        </div>
        <label class="inline-flex items-center gap-2 text-sm font-semibold">
            <input type="checkbox" name="apply_to_everyone" value="1" id="apply_to_everyone" @checked($appliesToEveryone) class="rounded border-gray-300">
            All positions (global)
        </label>
    </div>
    <div id="position-checkboxes" class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-2 xl:grid-cols-3 {{ $appliesToEveryone ? 'opacity-50 pointer-events-none' : '' }}">
        @foreach($positions as $position)
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="position_ids[]" value="{{ $position->id }}"
                @checked(in_array($position->id, $selectedPositionIds, true))
                class="rounded border-gray-300">
            {{ $position->title }}
        </label>
        @endforeach
    </div>
</div>

<script>
(function () {
    var all = document.getElementById('apply_to_everyone');
    var box = document.getElementById('position-checkboxes');
    if (!all || !box) return;
    all.addEventListener('change', function () {
        box.classList.toggle('opacity-50', all.checked);
        box.classList.toggle('pointer-events-none', all.checked);
    });
})();
</script>

@php
    $seederCheckboxId = $seederCheckboxId ?? 'update_facility_seeder';
    $seederChecked = (bool) old('update_facility_seeder', $seederCheckedDefault ?? false);
@endphp
<label for="{{ $seederCheckboxId }}" class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50/80 px-3 py-2.5 text-left">
    <input type="checkbox"
           name="update_facility_seeder"
           id="{{ $seederCheckboxId }}"
           value="1"
           @checked($seederChecked)
           class="mt-0.5 rounded border-amber-300 text-teal-600 focus:ring-teal-500">
    <span>
        <span class="block text-sm font-semibold text-amber-950">Update FacilitySeeder on save</span>
        <span class="mt-0.5 block text-xs leading-relaxed text-amber-900/80">
            Exports this facility to <code class="rounded bg-white/70 px-1">database/seeders/data/facilities.json</code>
            so <code class="rounded bg-white/70 px-1">migrate:fresh --seed</code> restores its settings. Commit that file to git.
        </span>
    </span>
</label>

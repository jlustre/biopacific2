@php
    $seederCheckboxId = $seederCheckboxId ?? 'update_seeder';
    $seederChecked = (bool) old('update_seeder', $seederCheckedDefault ?? true);
@endphp
<label for="{{ $seederCheckboxId }}" class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50/80 px-3 py-2.5 text-left">
    <input type="checkbox"
           name="update_seeder"
           id="{{ $seederCheckboxId }}"
           value="1"
           @checked($seederChecked)
           class="mt-0.5 rounded border-amber-300 text-teal-600 focus:ring-teal-500">
    <span>
        <span class="block text-sm font-semibold text-amber-950">Update ImportMappingPresetsTableSeeder</span>
        <span class="mt-0.5 block text-xs leading-relaxed text-amber-900/80">
            Exports all presets to <code class="rounded bg-white/70 px-1">database/seeders/ImportMappingPresetsTableSeeder.php</code>
            so <code class="rounded bg-white/70 px-1">migrate:fresh --seed</code> restores them. Commit that file to git.
        </span>
    </span>
</label>

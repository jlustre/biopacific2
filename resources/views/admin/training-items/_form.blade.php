@php
    $rawPositionIds = old('position_ids', $trainingItem->position_ids ?? ['global']);
    $appliesToEveryone = old('apply_to_everyone', null) !== null
        ? (bool) old('apply_to_everyone')
        : (in_array('global', (array) $rawPositionIds, true) || empty(array_filter((array) $rawPositionIds, fn ($id) => $id !== 'global' && $id !== null && $id !== '')));
    $selectedPositionIds = collect($rawPositionIds)
        ->filter(fn ($id) => $id !== 'global' && $id !== null && $id !== '')
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div>
    <label for="name" class="mb-2 block text-sm font-semibold text-gray-900">Training name <span class="text-red-500">*</span></label>
    <input type="text" name="name" id="name" value="{{ old('name', $trainingItem->name) }}" required
        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500">
    @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
</div>

<div>
    <label for="description" class="mb-2 block text-sm font-semibold text-gray-900">Description</label>
    <textarea name="description" id="description" rows="3"
        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500">{{ old('description', $trainingItem->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="content_url" class="mb-2 block text-sm font-semibold text-gray-900">Training link (server or provider)</label>
        <input type="text" name="content_url" id="content_url" value="{{ old('content_url', $trainingItem->content_url) }}"
            placeholder="https://lms.provider.com/... or /trainings/module-name"
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500">
        <p class="mt-1 text-xs text-gray-500">Full URL to your LMS/provider, or a path on this server.</p>
        @error('content_url')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="provider_label" class="mb-2 block text-sm font-semibold text-gray-900">Provider / source label</label>
        <input type="text" name="provider_label" id="provider_label" value="{{ old('provider_label', $trainingItem->provider_label) }}"
            placeholder="e.g. Relias, HealthStream, Internal"
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500">
        @error('provider_label')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div>
        <label for="frequency" class="mb-2 block text-sm font-semibold text-gray-900">Frequency <span class="text-red-500">*</span></label>
        <select name="frequency" id="frequency" required class="w-full rounded-lg border border-gray-300 px-4 py-2">
            @foreach(\App\Models\EmployeeTrainingItem::FREQUENCIES as $value => $meta)
                <option value="{{ $value }}" @selected(old('frequency', $trainingItem->frequency) === $value)>{{ $meta['label'] }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Recurring trainings use assessment periods. Multi-year intervals stay current until the next due date after the last approval.</p>
        @error('frequency')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="order" class="mb-2 block text-sm font-semibold text-gray-900">Display order</label>
        <input type="number" min="0" name="order" id="order" value="{{ old('order', $trainingItem->order) }}"
            class="w-full rounded-lg border border-gray-300 px-4 py-2">
    </div>
    <div>
        <label for="is_active" class="mb-2 block text-sm font-semibold text-gray-900">Active</label>
        <select name="is_active" id="is_active" class="w-full rounded-lg border border-gray-300 px-4 py-2">
            <option value="1" @selected(old('is_active', $trainingItem->is_active ? '1' : '0') === '1')>Yes</option>
            <option value="0" @selected(old('is_active', $trainingItem->is_active ? '1' : '0') === '0')>No</option>
        </select>
    </div>
</div>

<div>
    <div class="mb-2 flex items-center justify-between gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-900">Applicable positions</label>
            <p class="mt-1 text-sm text-gray-600">Check “All positions” for global trainings, or select specific positions.</p>
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

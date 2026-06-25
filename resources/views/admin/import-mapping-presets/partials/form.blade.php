@php

    $preset = $preset ?? null;

@endphp



<div class="space-y-6">

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

        <h2 class="mb-4 text-lg font-semibold text-slate-900">Preset details</h2>



        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">

            <div>

                <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Preset name <span class="text-red-500">*</span></label>

                <input type="text" name="name" id="name" required maxlength="255"

                       value="{{ old('name', $preset?->name) }}"

                       class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-teal-500 focus:ring-2 focus:ring-teal-200">

                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            </div>



            <div>

                <label for="facility_id" class="mb-1 block text-sm font-semibold text-slate-700">Facility <span class="text-red-500">*</span></label>

                <select name="facility_id" id="facility_id" required

                        class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-teal-500 focus:ring-2 focus:ring-teal-200">

                    <option value="{{ $globalId }}" {{ (int) old('facility_id', $preset?->facility_id) === (int) $globalId ? 'selected' : '' }}>

                        Global (all facilities) — ID {{ $globalId }}

                    </option>

                    @foreach($facilities as $facility)

                        @if((int) $facility->id !== (int) $globalId)

                        <option value="{{ $facility->id }}" {{ (int) old('facility_id', $preset?->facility_id) === (int) $facility->id ? 'selected' : '' }}>

                            {{ $facility->name }}

                        </option>

                        @endif

                    @endforeach

                </select>

                @error('facility_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            </div>



            <div class="md:col-span-2 xl:col-span-1">

                <label for="user_id" class="mb-1 block text-sm font-semibold text-slate-700">Owner <span class="text-red-500">*</span></label>

                <select name="user_id" id="user_id" required

                        class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-teal-500 focus:ring-2 focus:ring-teal-200">

                    @foreach($users as $user)

                    <option value="{{ $user->id }}" {{ (int) old('user_id', $preset?->user_id ?? $defaultUserId) === (int) $user->id ? 'selected' : '' }}>

                        {{ $user->name }} ({{ $user->email }})

                    </option>

                    @endforeach

                </select>

                @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            </div>

        </div>

        <p class="mt-3 text-xs text-slate-500">Global presets (ID {{ $globalId }}) are shared across all facilities during import.</p>

        <div class="mt-4">
            @include('admin.import-mapping-presets.partials.seeder-sync-option')
        </div>
    </div>



    <div class="w-full">

        @error('mappings')<p class="mb-3 text-sm text-red-600">{{ $message }}</p>@enderror

        @include('admin.import-mapping-presets.partials.mappings-editor', [

            'mappings' => old('mappings', $preset?->mappings ?? []),

            'targetTables' => $targetTables,

            'parseWorkbookUrl' => $parseWorkbookUrl,

            'validateDraftMappingsUrl' => $validateDraftMappingsUrl ?? '',

            'tableColumnsUrl' => $tableColumnsUrl,

        ])

    </div>

</div>


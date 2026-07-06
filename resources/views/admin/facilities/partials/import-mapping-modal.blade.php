@php
    use App\Support\ImportMappingPresetAccess;

    $canCreateMappingPreset = ImportMappingPresetAccess::canCreate();
    $canUseMappingPreset = ImportMappingPresetAccess::canUse();
    $presetRestrictedRoleLabel = ImportMappingPresetAccess::restrictedRoleLabel();
    $globalPresetFacilityId = (int) config('import-mapping.global_facility_id', 99);
    $duplicateFacilities = \App\Models\Facility::orderBy('name')->get(['id', 'name']);
@endphp
@include('admin.partials.import-data-loader')

<div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gradient-to-br from-blue-100 via-indigo-100 to-purple-100 bg-opacity-90">
    <div class="flex min-h-full items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-0 relative border border-indigo-200 flex max-h-[92vh] flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-8 pt-8 pb-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal-600 text-white shadow-md" aria-hidden="true">
                    <i class="fas fa-download text-lg"></i>
                </span>
                <div>
                    <h2 class="text-2xl font-semibold text-indigo-800 mb-0">Import Facility Data</h2>
                    <div class="text-base font-medium text-indigo-500">{{ $facility->name }}</div>
                </div>
            </div>
            <button type="button" class="text-gray-400 hover:text-red-500 text-3xl font-semibold focus:outline-none" onclick="document.getElementById('importModal').classList.add('hidden')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Upload Section with Preset Select -->
        @if(!$canUseMappingPreset)
        <div class="mx-8 mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            You do not have permission to import facility data. Contact a Super Administrator if you need access.
        </div>
        @else
        <form id="excelUploadForm" method="POST" action="{{ route('admin.facility.files.import', ['facility' => $facility->id]) }}" enctype="multipart/form-data" onsubmit="showMappingStep(event)" data-no-loader class="px-8 pt-6 pb-2">
            <input type="hidden" name="facility_id" value="{{ $facility->id }}">
            @csrf
            <div id="importPresetMessage" class="mb-4 hidden rounded-lg border px-4 py-3 text-sm" role="alert"></div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2" for="presetSelect">
                    <span class="inline-flex items-center gap-1"><svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-indigo-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/></svg> Preset</span>
                </label>
                <div class="flex flex-wrap items-center gap-2">
                    <select id="presetSelect" name="preset_id" class="min-w-0 flex-1 border-2 border-indigo-800 bg-indigo-50 rounded-lg p-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none transition">
                        <option value="">-- Select Preset --</option>
                    </select>
                    @if($canCreateMappingPreset)
                    <button type="button" id="editTopPresetBtn" class="hidden shrink-0 rounded-lg border border-teal-300 bg-white px-3 py-2 text-sm font-semibold text-teal-700 shadow-sm transition hover:bg-teal-50" title="Edit selected preset">
                        Edit Preset
                    </button>
                    <button type="button" id="duplicateTopPresetBtn" class="hidden shrink-0 rounded-lg border border-indigo-300 bg-white px-3 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50" title="Duplicate selected preset">
                        Duplicate
                    </button>
                    @endif
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2" for="importFile">
                    <span class="inline-flex items-center gap-1"><svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-indigo-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3'/></svg> Excel File</span>
                </label>
                <input type="file" name="file" id="importFile" accept=".xlsx,.xls,.csv" required class="border-2 border-teal-800 bg-teal-100 rounded-lg w-full p-2 focus:ring-2 focus:ring-teal-400 focus:outline-none transition">
            </div>
            @if(!$canCreateMappingPreset)
            <div id="createPresetUnavailableNotice" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                <p class="font-semibold">Create Preset is not available yet</p>
                <p class="mt-1">
                    @if($presetRestrictedRoleLabel)
                        This function is temporarily restricted for <strong>{{ $presetRestrictedRoleLabel }}</strong>.
                    @else
                        This function is temporarily restricted for your account.
                    @endif
                    Please select an existing preset and use <strong>Import Using Preset</strong>, or contact a Super Administrator to create a new mapping preset.
                </p>
            </div>
            @endif
            <div class="flex flex-wrap items-center justify-end gap-2">
                <button type="button" id="importWithPresetBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-2 py-1 rounded-lg shadow transition border border-indigo-700 disabled:cursor-not-allowed disabled:opacity-50" disabled>Import Using Preset</button>
                @if($canCreateMappingPreset)
                <button type="button" id="createPresetBtn" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-2 py-1 rounded-lg shadow transition border border-teal-700">Create Preset</button>
                @else
                <button type="button" id="createPresetBtn" class="cursor-not-allowed rounded-lg border border-slate-300 bg-slate-200 px-2 py-1 font-semibold text-slate-500 shadow" disabled title="Not available for {{ $presetRestrictedRoleLabel ?? 'your role' }}">Create Preset</button>
                @endif
            </div>        </form>
        @endif

        <!-- Mapping UI (hidden by default, shown after upload) -->
        <div class="flex-1 overflow-y-auto min-h-0 px-8 pb-8">
            <!-- Success/Error message area -->
            <div id="mappingMessage" class="hidden mb-4"></div>
            @include('admin.facilities.partials.import-mapping')
        </div>
        @include('admin.facilities.partials.import-mapping-scripts', [
            'canCreateMappingPreset' => $canCreateMappingPreset,
            'canUseMappingPreset' => $canUseMappingPreset,
            'presetRestrictedRoleLabel' => $presetRestrictedRoleLabel,
            'currentFacilityId' => $facility->id,
            'currentFacilityName' => $facility->name,
            'globalPresetFacilityId' => $globalPresetFacilityId,
            'duplicateFacilities' => $duplicateFacilities,
        ])

        @if($canCreateMappingPreset)
        <div id="editPresetModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="editPresetModalTitle">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-teal-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 id="editPresetModalTitle" class="text-lg font-semibold text-teal-800">Edit Mapping Preset</h3>
                    <button type="button" class="text-2xl leading-none text-gray-400 hover:text-red-500" onclick="closeEditPresetModal()" aria-label="Close">&times;</button>
                </div>
                <form id="editPresetForm" class="space-y-4 px-6 py-5" onsubmit="submitEditPreset(event)">
                    <input type="hidden" id="editPresetId" value="">
                    <div>
                        <label for="editPresetName" class="mb-1 block text-sm font-semibold text-gray-700">Preset name</label>
                        <input type="text" id="editPresetName" required maxlength="255"
                               class="w-full rounded-lg border-2 border-teal-200 bg-teal-50 px-3 py-2 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-300"
                               placeholder="Enter preset name">
                    </div>
                    <div>
                        <label for="editPresetFacility" class="mb-1 block text-sm font-semibold text-gray-700">Facility</label>
                        <select id="editPresetFacility" required
                                class="w-full rounded-lg border-2 border-teal-200 bg-teal-50 px-3 py-2 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-300">
                            <option value="{{ $globalPresetFacilityId }}">Global (all facilities)</option>
                            <option value="{{ $facility->id }}">Current: {{ $facility->name }}</option>
                            @foreach($duplicateFacilities as $dupFacility)
                                @if($dupFacility->id != $facility->id && $dupFacility->id != $globalPresetFacilityId)
                                <option value="{{ $dupFacility->id }}">{{ $dupFacility->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Global presets use facility ID {{ $globalPresetFacilityId }} and are available to every facility.</p>
                    </div>
                    <div id="editPresetMessage" class="hidden rounded-lg border px-3 py-2 text-sm" role="alert"></div>
                    <div>
                        @include('admin.import-mapping-presets.partials.seeder-sync-option', [
                            'seederCheckboxId' => 'updateSeederOnEdit',
                        ])
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-2">
                        <button type="button" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300" onclick="closeEditPresetModal()">Cancel</button>
                        <button type="submit" id="editPresetSubmitBtn" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-teal-700">Save changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="duplicatePresetModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="duplicatePresetModalTitle">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-indigo-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 id="duplicatePresetModalTitle" class="text-lg font-semibold text-indigo-800">Duplicate Mapping Preset</h3>
                    <button type="button" class="text-2xl leading-none text-gray-400 hover:text-red-500" onclick="closeDuplicatePresetModal()" aria-label="Close">&times;</button>
                </div>
                <form id="duplicatePresetForm" class="space-y-4 px-6 py-5" onsubmit="submitDuplicatePreset(event)">
                    <input type="hidden" id="duplicateSourcePresetId" value="">
                    <div>
                        <label for="duplicatePresetName" class="mb-1 block text-sm font-semibold text-gray-700">New preset name</label>
                        <input type="text" id="duplicatePresetName" required maxlength="255"
                               class="w-full rounded-lg border-2 border-indigo-200 bg-indigo-50 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="Enter preset name">
                    </div>
                    <div>
                        <label for="duplicatePresetFacility" class="mb-1 block text-sm font-semibold text-gray-700">Facility</label>
                        <select id="duplicatePresetFacility" required
                                class="w-full rounded-lg border-2 border-indigo-200 bg-indigo-50 px-3 py-2 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="{{ $globalPresetFacilityId }}">Global (all facilities)</option>
                            <option value="{{ $facility->id }}" selected>Current: {{ $facility->name }}</option>
                            @foreach($duplicateFacilities as $dupFacility)
                                @if($dupFacility->id != $facility->id && $dupFacility->id != $globalPresetFacilityId)
                                <option value="{{ $dupFacility->id }}">{{ $dupFacility->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Global presets use facility ID {{ $globalPresetFacilityId }} and are available to every facility.</p>
                    </div>
                    <div id="duplicatePresetMessage" class="hidden rounded-lg border px-3 py-2 text-sm" role="alert"></div>
                    <div>
                        @include('admin.import-mapping-presets.partials.seeder-sync-option', [
                            'seederCheckboxId' => 'updateSeederOnDuplicate',
                        ])
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-2">
                        <button type="button" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300" onclick="closeDuplicatePresetModal()">Cancel</button>
                        <button type="submit" id="duplicatePresetSubmitBtn" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">Save duplicate</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    </div>
</div>

<!-- Duplicate / success overlays (outside importModal so scrolling and fixed positioning work correctly) -->
<div id="duplicateConfirmModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-black/40">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="flex w-full max-w-lg max-h-[90vh] flex-col rounded-xl bg-white shadow-xl">
            <div class="shrink-0 border-b border-gray-100 px-6 py-4">
                <h3 class="text-xl font-semibold text-red-700">Duplicate Employee IDs Detected</h3>
                <p class="mt-2 text-sm text-gray-700">The following Employee IDs already exist. Do you want to overwrite them?</p>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                <ul id="duplicateList" class="list-inside list-disc space-y-1 text-sm text-gray-800"></ul>
            </div>
            <div class="flex shrink-0 justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button type="button" class="rounded bg-gray-300 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-400" onclick="hideDuplicateModal()">Cancel</button>
                <button type="button" class="rounded bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700" onclick="confirmDuplicateOverwrite()">Overwrite</button>
            </div>
        </div>
    </div>
</div>

<div id="importSuccessModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-black/40">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h3 class="mb-2 text-xl font-semibold text-green-700">Import Successful</h3>
            <div class="mb-4 text-gray-700">Employee data was imported successfully.</div>
            <div class="flex justify-end">
                <button type="button" class="rounded bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700" onclick="hideImportSuccessModal()">OK</button>
            </div>
        </div>
    </div>
</div>
<div id="mappingStep" class="hidden mt-6">
    <div class="grid grid-cols-2 gap-6">
        <!-- Source Column -->
        <div>
            <h3 class="text-lg font-semibold mb-2">Source</h3>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Worksheet</label>
                <select id="worksheetSelect" class="border-2 border-teal-800 bg-teal-100 rounded w-full p-2">
                    <option value="">-- Select Worksheet --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Column</label>
                <select id="worksheetColumnSelect" class="border-2 border-teal-800 bg-teal-100 rounded w-full p-2">
                    <option value="">-- Select Column --</option>
                </select>
            </div>
        </div>
        <!-- Target Column -->
        <div>
            <h3 class="text-lg font-semibold mb-2">Target</h3>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Table</label>
                <select id="tableSelect" class="border-2 border-teal-800 bg-teal-100 rounded w-full p-2">
                    <option value="">-- Select Table --</option>
                    <option value="bp_emp_checklists">bp_emp_checklists</option>
                    <option value="bp_emp_job_data">bp_emp_job_data</option>
                    <option value="bp_employees">bp_employees</option>
                    <option value="bp_emp_phones">bp_emp_phones</option>
                    <option value="bp_emp_addresses">bp_emp_addresses</option>
                    <option value="bp_emp_compensation">bp_emp_compensation</option>
                    <option value="bp_emp_tax_data">bp_emp_tax_data</option>
                    <option value="bp_emp_health_screenings">bp_emp_health_screenings</option>
                    <option value="bp_emp_credentials">bp_emp_credentials</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Column</label>
                <select id="tableColumnSelect" class="border-2 border-teal-800 bg-teal-100 rounded w-full p-2">
                    <option value="">-- Select Column --</option>
                </select>
            </div>
        </div>
    </div>
    <div class="mt-4 space-y-4">
        @php
            $importFieldClass = 'min-h-[42px] border-2 border-teal-800 bg-teal-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none';
            $importBtnClass = 'inline-flex min-h-[42px] shrink-0 items-center justify-center gap-1.5 rounded-lg border-2 px-3 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-50';
        @endphp

        <div>
            <label for="mappingPresetSelect" class="mb-2 block text-gray-700 font-medium">Load Mapping Preset</label>
            <div class="flex flex-wrap items-stretch gap-2">
                <select id="mappingPresetSelect" class="{{ $importFieldClass }} min-w-0 flex-1 basis-[14rem]">
                    <option value="">-- Load Mapping Preset --</option>
                </select>
                @if($canCreateMappingPreset ?? false)
                <button type="button" id="editMappingPresetBtn"
                    class="{{ $importBtnClass }} hidden border-teal-800 bg-teal-50 text-teal-900 hover:bg-teal-100 focus:ring-teal-300"
                    title="Edit selected preset" onclick="openEditPresetModalFromMapping()">
                    <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                    <span>Edit Preset</span>
                </button>
                <button type="button" id="duplicateMappingPresetBtn"
                    class="{{ $importBtnClass }} hidden border-indigo-800 bg-indigo-50 text-indigo-900 hover:bg-indigo-100 focus:ring-indigo-300"
                    title="Duplicate selected preset" onclick="openDuplicatePresetModalFromMapping()">
                    <i class="fas fa-copy text-xs" aria-hidden="true"></i>
                    <span>Duplicate</span>
                </button>
                <button type="button" id="deletePresetBtn"
                    class="{{ $importBtnClass }} hidden border-red-700 bg-red-50 text-red-800 hover:bg-red-100 focus:ring-red-300"
                    title="Delete selected preset">
                    <i class="fas fa-times text-xs" aria-hidden="true"></i>
                    <span>Delete</span>
                </button>
                @endif
            </div>
        </div>

        @if($canCreateMappingPreset ?? false)
        <div>
            <label for="mappingPresetName" class="mb-2 block text-gray-700 font-medium">Save as New Preset</label>
            <div class="flex flex-wrap items-stretch gap-2">
                <input type="text" id="mappingPresetName"
                    class="{{ $importFieldClass }} min-w-0 flex-1 basis-[14rem]"
                    placeholder="Preset name">
                <button type="button" id="saveMappingPresetBtn"
                    class="{{ $importBtnClass }} border-amber-700 bg-amber-50 text-amber-900 hover:bg-amber-100 focus:ring-amber-300"
                    onclick="saveMappingPreset()">
                    <i class="fas fa-check text-xs" aria-hidden="true"></i>
                    <span>Save Preset</span>
                </button>
                <div id="importMappingFileNameWrap"
                    class="{{ $importFieldClass }} flex min-w-0 max-w-full basis-[14rem] flex-1 items-center gap-2 bg-white text-gray-700 lg:max-w-[18rem] lg:flex-none"
                    title="Excel file used for this preset">
                    <i class="fas fa-file-excel shrink-0 text-teal-700" aria-hidden="true"></i>
                    <span id="importMappingFileName" class="truncate font-semibold text-teal-900">No file selected</span>
                </div>
            </div>
            <div class="mt-3">
                @include('admin.import-mapping-presets.partials.seeder-sync-option', [
                    'seederCheckboxId' => 'updateSeederOnSave',
                ])
            </div>
        </div>
        @endif

        <div class="flex justify-end pt-1">
            <button type="button"
                class="{{ $importBtnClass }} border-blue-700 bg-blue-50 text-blue-900 hover:bg-blue-100 focus:ring-blue-300"
                onclick="addMappingRow()">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span>Add Mapping</span>
            </button>
        </div>
    </div>
    <!-- Mapping Table -->
    <div class="mt-6">
        <div class="flex items-center justify-between">
            <h4 class="font-semibold mb-2">Mappings</h4>
            <button type="button" class="button-sm px-2 py-1 mb-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-md shadow-sm text-sm" onclick="submitMapping()">
                <svg xmlns='http://www.w3.org/2000/svg' class='inline h-4 w-4 mr-1 -mt-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2l4-4'/></svg>Import Data
            </button>
        </div>
        <table class="min-w-full border text-sm" id="mappingTable">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Source Worksheet</th>
                    <th class="border px-2 py-1">Source Column</th>
                    <th class="border px-2 py-1">Target Table</th>
                    <th class="border px-2 py-1">Target Column</th>
                    <th class="border px-2 py-1">Action</th>
                    <th class="border px-2 py-1">Order</th>
                </tr>
            </thead>
            <tbody id="mappingTableBody">
            </tbody>
        </table>
    </div>
</div>
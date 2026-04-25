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
                    <option value="bp_emp_assignments">bp_emp_assignments</option>
                    <option value="bp_employees">bp_employees</option>
                    <option value="bp_emp_phones">bp_emp_phones</option>
                    <option value="bp_emp_addresses">bp_emp_addresses</option>
                    <option value="bp_emp_compensation">bp_emp_compensation</option>
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
    <div class="flex flex-wrap items-center gap-2 mt-4 mb-2">
        <select id="mappingPresetSelect" class="border-2 border-teal-800 bg-teal-100 rounded-lg px-3 py-2 w-56 focus:ring-2 focus:ring-teal-400 focus:outline-none text-sm">
            <option value="">-- Load Mapping Preset --</option>
        </select>
        <button type="button" id="deletePresetBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-2 rounded-lg shadow-sm text-sm hidden" title="Delete selected preset">
            <svg xmlns='http://www.w3.org/2000/svg' class='inline h-4 w-4 mr-1 -mt-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/></svg>Delete
        </button>
        <input type="text" id="mappingPresetName" class="border-2 border-teal-800 bg-teal-100 rounded-lg px-3 py-2 w-44 focus:ring-2 focus:ring-teal-400 focus:outline-none text-sm" placeholder="Preset Name">
        <button type="button" class="bg-orange-500 hover:bg-orange-700 text-white font-bold px-2 py-1 rounded-lg shadow-sm text-sm" onclick="saveMappingPreset()">
            <svg xmlns='http://www.w3.org/2000/svg' class='inline h-4 w-4 mr-1 -mt-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/></svg>Save Preset
        </button>

        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-2 py-1 rounded-lg shadow-sm text-sm" onclick="addMappingRow()">
            <svg xmlns='http://www.w3.org/2000/svg' class='inline h-4 w-4 mr-1 -mt-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/></svg>Add Mapping
        </button>
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
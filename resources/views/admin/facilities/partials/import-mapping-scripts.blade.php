<script>
    window.canCreateMappingPreset = @json($canCreateMappingPreset ?? false);
    window.canUseMappingPreset = @json($canUseMappingPreset ?? true);
    window.presetRestrictedRoleLabel = @json($presetRestrictedRoleLabel ?? null);
    window.importContextFacilityId = @json($currentFacilityId ?? null);
    window.importGlobalFacilityId = @json($globalPresetFacilityId ?? 99);

    function presetCreationUnavailableMessage() {
        const role = window.presetRestrictedRoleLabel || 'your role';
        return `Creating mapping presets is not available yet for ${role}. Please select an existing preset or contact a Super Administrator.`;
    }

    // --- Mapping Preset Logic ---
    function getCurrentMappings() {
        const tbody = document.getElementById('mappingTableBody');
        const mappings = [];
        for (let row of tbody.rows) {
            mappings.push({
                worksheet: row.cells[0].innerText,
                worksheet_column: row.cells[1].innerText,
                table: row.cells[2].innerText,
                table_column: row.cells[3].innerText
            });
        }
        return mappings;
    }
    
    function setMappings(mappings) {
        const tbody = document.getElementById('mappingTableBody');
        tbody.innerHTML = '';
        mappings.forEach((m, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="border px-2 py-1">${m.worksheet}<input type="hidden" name="mappings[][worksheet]" value="${m.worksheet}"></td>
                <td class="border px-2 py-1">${m.worksheet_column}<input type="hidden" name="mappings[][worksheet_column]" value="${m.worksheet_column}"></td>
                <td class="border px-2 py-1">${m.table}<input type="hidden" name="mappings[][table]" value="${m.table}"></td>
                <td class="border px-2 py-1">${m.table_column}<input type="hidden" name="mappings[][table_column]" value="${m.table_column}"></td>
                <td class="border px-2 py-1 text-center"><button type="button" class="text-red-600 hover:underline" onclick="this.closest('tr').remove()">Remove</button></td>
                <td class="border px-2 py-1 text-center">
                    <button type="button" class="text-gray-500 hover:text-indigo-600 px-1" title="Move Up" onclick="moveMappingRow(this, -1)">&#8593;</button>
                    <button type="button" class="text-gray-500 hover:text-indigo-600 px-1" title="Move Down" onclick="moveMappingRow(this, 1)">&#8595;</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        // Auto-select worksheet in dropdown if mappings exist
        if (mappings.length > 0) {
            const wsSelect = document.getElementById('worksheetSelect');
            if (wsSelect) {
                wsSelect.value = mappings[0].worksheet;
                updateWorksheetColumnSelect();
            }
        }
    }
    
    async function saveMappingPreset() {
        if (!window.canCreateMappingPreset) {
            showImportPresetMessage('error', presetCreationUnavailableMessage());
            return;
        }
        const name = document.getElementById('mappingPresetName')?.value.trim();
        if (!name) {
            alert('Please enter a preset name.');
            return;
        }
        const mappings = getCurrentMappings();
        if (!mappings.length) {
            alert('No mappings to save.');
            return;
        }
        // Check if a preset with this name exists
        let existingId = null;
        try {
            const res = await fetch('/admin/facility/files/mapping-presets');
            const data = await res.json();
            if (data.presets) {
                const found = data.presets.find(p => p.name === name);
                if (found) existingId = found.id;
            }
        } catch (e) {}

        const url = existingId
            ? `/admin/facility/files/mapping-presets/${existingId}`
            : '/admin/facility/files/mapping-presets';
        const method = existingId ? 'PUT' : 'POST';
        fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
            },
            body: JSON.stringify({ name, mappings })
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                alert('Mapping preset saved!');
                loadMappingPresets();
            } else {
                alert(data.error || 'Failed to save preset.');
            }
        });
    }
    
    function loadMappingPresets() {
        const facilityId = window.importContextFacilityId;
        const url = facilityId
            ? `/admin/facility/files/mapping-presets?facility_id=${facilityId}`
            : '/admin/facility/files/mapping-presets';
        return fetch(url)
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('mappingPresetSelect');
                if (!select) return data;
                select.innerHTML = '<option value="">-- Load Mapping Preset --</option>';
                if (data.presets) {
                    data.presets.forEach(preset => {
                        const opt = document.createElement('option');
                        opt.value = preset.id;
                        opt.textContent = preset.name + (preset.facility_id == window.importGlobalFacilityId ? ' (Global)' : '');
                        opt.dataset.presetName = preset.name;
                        opt.dataset.facilityId = preset.facility_id;
                        select.appendChild(opt);
                    });
                }
                return data;
            });
    }

    function getSelectedPresetMeta(select) {
        const opt = select?.selectedOptions?.[0];
        if (!select?.value || !opt) {
            return null;
        }
        return {
            id: select.value,
            name: opt.dataset?.presetName || opt.textContent?.replace(/\s*\(Global\)$/, '').trim() || '',
            facilityId: opt.dataset?.facilityId ?? '',
        };
    }

    function togglePresetActionButtons(presetId, presetName, facilityId) {
        const show = window.canCreateMappingPreset && presetId;
        ['duplicateMappingPresetBtn', 'duplicateTopPresetBtn', 'editMappingPresetBtn', 'editTopPresetBtn'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('hidden', !show);
        });

        window.activePresetMeta = show
            ? { id: presetId, name: presetName || '', facilityId: facilityId ?? '' }
            : null;
    }

    function showDuplicatePresetMessage(type, message) {
        const msgDiv = document.getElementById('duplicatePresetMessage');
        if (!msgDiv) return;
        msgDiv.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-800', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        if (type === 'success') {
            msgDiv.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        } else {
            msgDiv.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        }
        msgDiv.textContent = message;
    }

    function clearDuplicatePresetMessage() {
        const msgDiv = document.getElementById('duplicatePresetMessage');
        if (msgDiv) {
            msgDiv.classList.add('hidden');
            msgDiv.textContent = '';
        }
    }

    function openDuplicatePresetModal(presetId, presetName) {
        if (!window.canCreateMappingPreset) {
            showImportPresetMessage('error', presetCreationUnavailableMessage());
            return;
        }

        const modal = document.getElementById('duplicatePresetModal');
        const idInput = document.getElementById('duplicateSourcePresetId');
        const nameInput = document.getElementById('duplicatePresetName');
        const facilitySelect = document.getElementById('duplicatePresetFacility');

        if (!modal || !idInput || !nameInput) return;

        idInput.value = presetId;
        const baseName = (presetName || 'Preset').trim();
        nameInput.value = baseName.startsWith('Copy of ') ? baseName : `Copy of ${baseName}`;

        if (facilitySelect && window.importContextFacilityId) {
            facilitySelect.value = String(window.importContextFacilityId);
        }

        clearDuplicatePresetMessage();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        nameInput.focus();
    }

    function closeDuplicatePresetModal() {
        const modal = document.getElementById('duplicatePresetModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        clearDuplicatePresetMessage();
    }

    function openDuplicatePresetModalFromMapping() {
        const meta = getSelectedPresetMeta(document.getElementById('mappingPresetSelect'));
        if (!meta) return;
        openDuplicatePresetModal(meta.id, meta.name);
    }

    function showEditPresetMessage(type, message) {
        const msgDiv = document.getElementById('editPresetMessage');
        if (!msgDiv) return;
        msgDiv.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-800', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        if (type === 'success') {
            msgDiv.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        } else {
            msgDiv.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        }
        msgDiv.textContent = message;
    }

    function clearEditPresetMessage() {
        const msgDiv = document.getElementById('editPresetMessage');
        if (msgDiv) {
            msgDiv.classList.add('hidden');
            msgDiv.textContent = '';
        }
    }

    function openEditPresetModal(presetId, presetName, facilityId) {
        if (!window.canCreateMappingPreset) {
            showImportPresetMessage('error', presetCreationUnavailableMessage());
            return;
        }

        const modal = document.getElementById('editPresetModal');
        const idInput = document.getElementById('editPresetId');
        const nameInput = document.getElementById('editPresetName');
        const facilitySelect = document.getElementById('editPresetFacility');

        if (!modal || !idInput || !nameInput) return;

        idInput.value = presetId;
        nameInput.value = (presetName || '').trim();

        if (facilitySelect && facilityId !== '' && facilityId != null) {
            facilitySelect.value = String(facilityId);
        }

        clearEditPresetMessage();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        nameInput.focus();
    }

    function closeEditPresetModal() {
        const modal = document.getElementById('editPresetModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        clearEditPresetMessage();
    }

    function openEditPresetModalFromMapping() {
        const meta = getSelectedPresetMeta(document.getElementById('mappingPresetSelect'));
        if (!meta) return;
        openEditPresetModal(meta.id, meta.name, meta.facilityId);
    }

    async function submitEditPreset(event) {
        event.preventDefault();
        if (!window.canCreateMappingPreset) {
            showEditPresetMessage('error', presetCreationUnavailableMessage());
            return;
        }

        const idInput = document.getElementById('editPresetId');
        const nameInput = document.getElementById('editPresetName');
        const facilitySelect = document.getElementById('editPresetFacility');
        const submitBtn = document.getElementById('editPresetSubmitBtn');

        const presetId = idInput?.value;
        const name = nameInput?.value?.trim();
        const facilityId = facilitySelect?.value;

        if (!presetId || !name) {
            showEditPresetMessage('error', 'Please enter a preset name.');
            return;
        }

        const prevLabel = submitBtn?.textContent;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';
        }

        try {
            const res = await fetch(`/admin/facility/files/mapping-presets/${presetId}/details`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value || '',
                },
                body: JSON.stringify({
                    name,
                    facility_id: parseInt(facilityId, 10),
                    context_facility_id: window.importContextFacilityId,
                }),
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.success) {
                showEditPresetMessage('error', data.error || 'Failed to update preset.');
                return;
            }

            closeEditPresetModal();
            showImportPresetMessage('success', `Preset "${data.preset.name}" updated.`);

            const nameInputMapping = document.getElementById('mappingPresetName');
            if (nameInputMapping) nameInputMapping.value = data.preset.name;

            await reloadAllPresetDropdowns(data.preset.id);
        } catch (err) {
            showEditPresetMessage('error', 'Update failed: ' + (err?.message || 'Unknown error'));
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = prevLabel || 'Save changes';
            }
        }
    }

    async function submitDuplicatePreset(event) {
        event.preventDefault();
        if (!window.canCreateMappingPreset) {
            showDuplicatePresetMessage('error', presetCreationUnavailableMessage());
            return;
        }

        const idInput = document.getElementById('duplicateSourcePresetId');
        const nameInput = document.getElementById('duplicatePresetName');
        const facilitySelect = document.getElementById('duplicatePresetFacility');
        const submitBtn = document.getElementById('duplicatePresetSubmitBtn');

        const presetId = idInput?.value;
        const name = nameInput?.value?.trim();
        const facilityId = facilitySelect?.value;

        if (!presetId || !name) {
            showDuplicatePresetMessage('error', 'Please enter a preset name.');
            return;
        }

        const prevLabel = submitBtn?.textContent;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';
        }

        try {
            const res = await fetch(`/admin/facility/files/mapping-presets/${presetId}/duplicate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value || '',
                },
                body: JSON.stringify({
                    name,
                    facility_id: parseInt(facilityId, 10),
                    context_facility_id: window.importContextFacilityId,
                }),
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok || !data.success) {
                showDuplicatePresetMessage('error', data.error || 'Failed to duplicate preset.');
                return;
            }

            closeDuplicatePresetModal();
            showImportPresetMessage('success', `Preset "${data.preset.name}" created.`);

            await reloadAllPresetDropdowns(data.preset.id);
        } catch (err) {
            showDuplicatePresetMessage('error', 'Duplicate failed: ' + (err?.message || 'Unknown error'));
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = prevLabel || 'Save duplicate';
            }
        }
    }

    async function reloadAllPresetDropdowns(selectNewId = null) {
        const facilityId = window.importContextFacilityId;
        const presetSelect = document.getElementById('presetSelect');
        const mappingSelect = document.getElementById('mappingPresetSelect');

        const url = facilityId
            ? `/admin/facility/files/mapping-presets?facility_id=${facilityId}`
            : '/admin/facility/files/mapping-presets';

        const res = await fetch(url);
        const data = await res.json().catch(() => ({}));
        const presets = data.presets || [];

        const fillSelect = (select, placeholder) => {
            if (!select) return;
            select.innerHTML = `<option value="">${placeholder}</option>`;
            presets.forEach(preset => {
                const opt = document.createElement('option');
                opt.value = preset.id;
                opt.textContent = preset.name + (preset.facility_id == window.importGlobalFacilityId ? ' (Global)' : '');
                opt.dataset.presetName = preset.name;
                opt.dataset.facilityId = preset.facility_id;
                select.appendChild(opt);
            });
            if (selectNewId) {
                select.value = String(selectNewId);
            }
        };

        fillSelect(presetSelect, '-- Select Preset --');
        fillSelect(mappingSelect, '-- Load Mapping Preset --');

        if (selectNewId && mappingSelect?.value === String(selectNewId)) {
            mappingSelect.dispatchEvent(new Event('change'));
        }

        const activeMeta = getSelectedPresetMeta(mappingSelect?.value ? mappingSelect : presetSelect)
            || (selectNewId ? { id: String(selectNewId), name: '', facilityId: '' } : null);
        if (activeMeta) {
            togglePresetActionButtons(activeMeta.id, activeMeta.name, activeMeta.facilityId);
        } else {
            togglePresetActionButtons(null);
        }
        toggleImportPresetBtn();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('mappingPresetSelect');
        const deleteBtn = document.getElementById('deletePresetBtn');
        if (select) {
            select.addEventListener('change', function() {
                const id = this.value;
                const nameInput = document.getElementById('mappingPresetName');
                if (!id) {
                    if (deleteBtn) deleteBtn.classList.add('hidden');
                    if (nameInput) nameInput.value = '';
                    togglePresetActionButtons(null);
                    return;
                }

                const facilityId = window.importContextFacilityId;
                const url = facilityId
                    ? `/admin/facility/files/mapping-presets?facility_id=${facilityId}`
                    : '/admin/facility/files/mapping-presets';

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        const preset = data.presets.find(p => p.id == id);
                        if (preset) {
                            setMappings(preset.mappings);
                            if (nameInput) nameInput.value = preset.name;
                            togglePresetActionButtons(id, preset.name, preset.facility_id);
                        }
                        if (deleteBtn) deleteBtn.classList.remove('hidden');
                    });
            });
        }

        document.getElementById('duplicateTopPresetBtn')?.addEventListener('click', function() {
            const meta = getSelectedPresetMeta(document.getElementById('presetSelect'));
            if (!meta) return;
            openDuplicatePresetModal(meta.id, meta.name);
        });

        document.getElementById('editTopPresetBtn')?.addEventListener('click', function() {
            const meta = getSelectedPresetMeta(document.getElementById('presetSelect'));
            if (!meta) return;
            openEditPresetModal(meta.id, meta.name, meta.facilityId);
        });
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                const id = select.value;
                if (!id) return;
                if (!confirm('Are you sure you want to delete this mapping preset?')) return;
                fetch(`/admin/facility/files/mapping-presets/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Preset deleted!');
                        select.value = '';
                        setMappings([]);
                        deleteBtn.classList.add('hidden');
                        togglePresetActionButtons(null);
                        reloadAllPresetDropdowns();
                    } else {
                        alert('Failed to delete preset.');
                    }
                });
            });
        }
        loadMappingPresets();
    });

    // Add mapping row to table
    function addMappingRow() {
        const ws = document.getElementById('worksheetSelect').value;
        const wsCol = document.getElementById('worksheetColumnSelect').value;
        const table = document.getElementById('tableSelect').value;
        const tableCol = document.getElementById('tableColumnSelect').value;
        if (!ws || !wsCol || !table || !tableCol) {
            alert('Please select all fields before adding a mapping.');
            return;
        }
        const tbody = document.getElementById('mappingTableBody');
        // Prevent duplicate mapping
        for (let row of tbody.rows) {
            if (
                row.cells[0].innerText === ws &&
                row.cells[1].innerText === wsCol &&
                row.cells[2].innerText === table &&
                row.cells[3].innerText === tableCol
            ) {
                alert('A mapping for this worksheet, source column, target table, and target column already exists. If you want to change it, please remove the existing mapping first.');
                return;
            }
        }
        // Warn if the same source worksheet/column is mapped to multiple target columns
        let sourceUsed = false;
        for (let row of tbody.rows) {
            if (
                row.cells[0].innerText === ws &&
                row.cells[1].innerText === wsCol &&
                (row.cells[2].innerText !== table || row.cells[3].innerText !== tableCol)
            ) {
                sourceUsed = true;
                break;
            }
        }
        if (sourceUsed) {
            if (!confirm('This source worksheet and column is already mapped to another target. Do you want to continue and map it to multiple targets?')) {
                return;
            }
        }
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="border px-2 py-1">${ws}<input type="hidden" name="mappings[][worksheet]" value="${ws}"></td>
            <td class="border px-2 py-1">${wsCol}<input type="hidden" name="mappings[][worksheet_column]" value="${wsCol}"></td>
            <td class="border px-2 py-1">${table}<input type="hidden" name="mappings[][table]" value="${table}"></td>
            <td class="border px-2 py-1">${tableCol}<input type="hidden" name="mappings[][table_column]" value="${tableCol}"></td>
            <td class="border px-2 py-1 text-center"><button type="button" class="text-red-600 hover:underline" onclick="this.closest('tr').remove()">Remove</button></td>
            <td class="border px-2 py-1 text-center">
                <button type="button" class="text-gray-500 hover:text-indigo-600 px-1" title="Move Up" onclick="moveMappingRow(this, -1)">&#8593;</button>
                <button type="button" class="text-gray-500 hover:text-indigo-600 px-1" title="Move Down" onclick="moveMappingRow(this, 1)">&#8595;</button>
            </td>
        `;
		tbody.appendChild(tr);
        }
    // Step 1: Upload Excel and fetch worksheet/column info
    function showMappingStep(e) {
        e.preventDefault();
        if (!window.canCreateMappingPreset) {
            showImportPresetMessage('error', presetCreationUnavailableMessage());
            return;
        }
        const form = document.getElementById('excelUploadForm');
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': form.querySelector('[name=_token]').value
            },
            body: formData
        })
        .then(async res => {
            let data;
            try {
                data = await res.json();
            } catch (err) {
                alert('Server returned invalid JSON.');
                return;
            }
            if (res.ok && data.worksheets) {
                // Populate worksheet select
                const wsSelect = document.getElementById('worksheetSelect');
                wsSelect.innerHTML = '<option value="">-- Select Worksheet --</option>';
                data.worksheets.forEach(ws => {
                    wsSelect.innerHTML += `<option value="${ws.name}">${ws.name}</option>`;
                });
                // Store worksheet columns and data for mapping
                window._excelWorksheets = data.worksheets.reduce((acc, ws) => {
                    acc[ws.name] = ws.columns;
                    return acc;
                }, {});
                window._excelWorksheetData = data.worksheets.reduce((acc, ws) => {
                    acc[ws.name] = ws.data;
                    return acc;
                }, {});
                form.classList.add('hidden');
                document.getElementById('mappingStep').classList.remove('hidden');
                updateWorksheetColumnSelect();
            } else {
                let msg = data && data.error ? data.error : 'Unknown error.';
                alert('Import failed: ' + msg);
            }
        })
        .catch(err => {
            alert('Failed to process Excel file. ' + (err && err.message ? err.message : ''));
        });
    }

    function attachMappingListeners() {
        document.getElementById('worksheetSelect').addEventListener('change', updateWorksheetColumnSelect);
        document.getElementById('tableSelect').addEventListener('change', updateTableColumnSelect);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachMappingListeners);
    } else {
        attachMappingListeners();
    }

    function updateWorksheetColumnSelect() {
        const ws = document.getElementById('worksheetSelect').value;
        const wsCols = (window._excelWorksheets && window._excelWorksheets[ws]) ? window._excelWorksheets[ws] : [];
        const wsColSelect = document.getElementById('worksheetColumnSelect');
        wsColSelect.innerHTML = '<option value="">-- Select Column --</option>';
        wsCols.forEach(col => {
            wsColSelect.innerHTML += `<option value="${col}">${col}</option>`;
        });
    }
    function updateTableColumnSelect() {
        const table = document.getElementById('tableSelect').value;
        const tableColSelect = document.getElementById('tableColumnSelect');
        tableColSelect.innerHTML = '<option value="">-- Select Column --</option>';
        if (!table) return;
        fetch(`{{ route('admin.facility.files.table_columns') }}?table=${table}`)
            .then(res => res.json())
            .then(data => {
                if (!data.columns) return;
                data.columns.forEach(col => {
                    tableColSelect.innerHTML += `<option value="${col}">${col}</option>`;
                });
            });
    }


    // Step 3: Submit mapping and handle duplicates/confirmation
    async function submitMapping() {
        const mappings = getCurrentMappings();
        if (!mappings.length) {
            alert('No mappings defined.');
            return;
        }
        // Use selected worksheet for data extraction
        const ws = document.getElementById('worksheetSelect').value;
        if (!ws || !window._excelWorksheetData || !window._excelWorksheetData[ws]) {
            alert('Please select a worksheet with data.');
            return;
        }
        // Use all rows from the selected worksheet
        let dataRows = window._excelWorksheetData[ws];
        if (!Array.isArray(dataRows) || !dataRows.length) {
            alert('No data rows found in selected worksheet.');
            return;
        }
        // Send all worksheet data for cross-worksheet mapping
        const worksheets = Object.keys(window._excelWorksheetData).map(name => ({
            name,
            data: window._excelWorksheetData[name]
        }));
        await doImportMapping(mappings, dataRows, false, worksheets);
    }

    function showMappingMessage(type, message) {
        const msgDiv = document.getElementById('mappingMessage');
        if (!msgDiv) return;
        msgDiv.classList.remove('hidden');
        msgDiv.className = 'mb-4';
        if (type === 'success') {
            msgDiv.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-800', 'rounded', 'px-4', 'py-3');
        } else {
            msgDiv.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-800', 'rounded', 'px-4', 'py-3');
        }
        msgDiv.innerText = message;
        setTimeout(() => { msgDiv.classList.add('hidden'); }, 6000);
    }

    function resolveImportRouteFacilityId() {
        const globalId = parseInt(window.importGlobalFacilityId ?? '99', 10);
        const presetFacilityId = window._lastImportPresetFacilityId
            ? parseInt(window._lastImportPresetFacilityId, 10)
            : 0;

        if (presetFacilityId > 0 && presetFacilityId !== globalId) {
            return presetFacilityId;
        }

        return document.querySelector('[name=facility_id]')?.value
            || window.importContextFacilityId
            || window._facilityId;
    }

    async function doImportMapping(mappings, dataRows, confirmOverwrite, worksheets) {
        const facilityId = resolveImportRouteFacilityId();
        const url = `/admin/facility/${facilityId}/files/import-data`;
        window.showImportDataLoader?.('Importing employee data…');
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
                },
                body: JSON.stringify({
                    mappings,
                    data: dataRows,
                    confirm_overwrite: confirmOverwrite,
                    worksheets,
                    import_log: {
                        source: 'facility_dashboard',
                        source_filename: window._lastImportFilename || null,
                        preset_id: window._lastImportPresetId ? parseInt(window._lastImportPresetId, 10) : null,
                        preset_facility_id: window._lastImportPresetFacilityId ? parseInt(window._lastImportPresetFacilityId, 10) : null,
                    },
                })
            });
            let result;
            const contentType = res.headers.get('content-type');
            if (contentType && contentType.indexOf('application/json') !== -1) {
                result = await res.json();
            } else {
                const text = await res.text();
                alert('Import failed: Server returned invalid response.');
                console.error('Non-JSON response:', text);
                return;
            }
            if (res.status === 409 && result.duplicates) {
                showDuplicateModal(result.duplicates);
                window._pendingImport = { mappings, dataRows, worksheets };
                return;
            }
            if (res.status === 422 && result.failures) {
                showDetailedFailuresModal(result.failures, result.message);
                return;
            }
            if (res.status === 422 && result.invalid_rows) {
                showInvalidRowsModal(result.invalid_rows, result.message);
                return;
            }
            // Show modal for detailed failures (itemized mapping errors)
            function showDetailedFailuresModal(failures, message) {
                let html = `<div><strong>${message || 'Some rows failed to import.'}</strong></div>`;
                html += '<div class="overflow-x-auto"><table class="border mt-2 text-xs"><thead><tr>' +
                    '<th>Row</th><th>Source Worksheet</th><th>Source Column</th><th>Target Table</th><th>Target Column</th><th>Value</th><th>Reason</th></tr></thead><tbody>';
                failures.forEach(rowFailArr => {
                    rowFailArr.forEach(fail => {
                        html += `<tr>` +
                            `<td>${fail.row ?? ''}</td>` +
                            `<td>${fail.source_worksheet ?? ''}</td>` +
                            `<td>${fail.source_column ?? ''}</td>` +
                            `<td>${fail.target_table ?? ''}</td>` +
                            `<td>${fail.target_column ?? ''}</td>` +
                            `<td>${fail.value ?? ''}</td>` +
                            `<td>${fail.reason ?? ''}</td>` +
                        `</tr>`;
                    });
                });
                html += '</tbody></table></div>';
                // Use a modal if you have one, otherwise alert
                if (window.showCustomModal) {
                    window.showCustomModal(html);
                } else {
                    // fallback: create a modal
                    let errorModal = document.getElementById('importErrorModal');
                    if (!errorModal) {
                        errorModal = document.createElement('div');
                        errorModal.id = 'importErrorModal';
                        errorModal.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-60';
                        errorModal.innerHTML = `<div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 relative">
                            <h3 class="text-xl font-semibold mb-2 text-red-700">Import Failed</h3>
                            <div class="mb-4 text-gray-700">${html}</div>
                            <div class="flex justify-end">
                                <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded" onclick="document.getElementById('importErrorModal').classList.add('hidden')">OK</button>
                            </div>
                        </div>`;
                        document.body.appendChild(errorModal);
                    } else {
                        errorModal.querySelector('div.mb-4').innerHTML = html;
                        errorModal.classList.remove('hidden');
                    }
                }
            }
            if (result.success) {
                let successMsg = 'Mapping imported successfully!';
                if (Array.isArray(result.unresolved_lookups) && result.unresolved_lookups.length) {
                    console.warn('Import unresolved lookups (could not find matching ID):', result.unresolved_lookups);
                    successMsg += ' Warning: ' + result.unresolved_lookups.length
                        + ' value(s) could not be matched to a database ID. Open the browser console (F12) or storage/logs/laravel.log for details.';
                    result.unresolved_lookups.forEach(entry => {
                        const suggestions = Array.isArray(entry.suggestions) && entry.suggestions.length
                            ? ' Use full name exactly, e.g.: ' + entry.suggestions.join(' | ')
                            : '';
                        const hint = entry.hint ? ' ' + entry.hint : '';
                        console.warn(
                            `Row ${entry.row} (${entry.employee_num}): ${entry.field} = "${entry.raw_value}" [${entry.status}]${hint}${suggestions}`
                        );
                    });
                }
                if (Array.isArray(result.importResults)) {
                    console.info('Import lookup debug per row:', result.importResults.map(r => ({
                        row: r.row,
                        employee_num: r.employee_num,
                        lookup_debug: r.lookup_debug,
                        assignment_action: r.assignment_action,
                        assignment_reason: r.assignment_reason,
                    })));
                    const skippedAssignments = result.importResults.filter(r => r.assignment_action === 'skipped');
                    if (skippedAssignments.length) {
                        successMsg += ' ' + skippedAssignments.length + ' row(s) had no assignment saved.';
                        console.warn('Assignment import skipped for rows:', skippedAssignments);
                    }
                }
                showMappingMessage('success', successMsg);
                showImportSuccessModal();
                window._importDataRows = null;
                return;
            }
            showMappingMessage('error', result.message || 'Import failed.');
        } catch (e) {
            showMappingMessage('error', 'Import failed: ' + (e && e.message ? e.message : ''));
        } finally {
            window.hideImportDataLoader?.();
        }
    }

    // Show modal for invalid rows (gender validation)
    function showInvalidRowsModal(invalidRows, message) {
        let html = `<div><strong>${message || 'Invalid data found.'}</strong></div>`;
        html += '<table class="border mt-2"><thead><tr><th>Row</th><th>Employee ID</th><th>Gender Value</th></tr></thead><tbody>';
        invalidRows.forEach(row => {
            html += `<tr><td>${row.row}</td><td>${row.employee_num}</td><td>${row.gender}</td></tr>`;
        });
        html += '</tbody></table>';
        // Use a modal if you have one, otherwise alert
        if (window.showCustomModal) {
            window.showCustomModal(html);
        } else {
            alert(message + '\n' + invalidRows.map(r => `Row ${r.row}: ${r.employee_num} (Gender: ${r.gender})`).join('\n'));
        }
    }

    function showDuplicateModal(duplicates) {
        const modal = document.getElementById('duplicateConfirmModal');
        const list = document.getElementById('duplicateList');
        list.innerHTML = '';
        duplicates.forEach(id => {
            const li = document.createElement('li');
            li.textContent = id;
            list.appendChild(li);
        });
        modal.classList.remove('hidden');
    }
    function hideDuplicateModal() {
        document.getElementById('duplicateConfirmModal').classList.add('hidden');
    }
    async function confirmDuplicateOverwrite() {
        hideDuplicateModal();
        if (window._pendingImport) {
            const worksheets = Object.keys(window._excelWorksheetData || {}).map(name => ({
                name,
                data: window._excelWorksheetData[name]
            }));
            await doImportMapping(
                window._pendingImport.mappings,
                window._pendingImport.dataRows,
                true,
                worksheets
            );
            window._pendingImport = null;
        }
    }
    function showImportSuccessModal() {
        document.getElementById('importSuccessModal').classList.remove('hidden');
    }
    function hideImportSuccessModal() {
        document.getElementById('importSuccessModal').classList.add('hidden');
        document.getElementById('importModal').classList.add('hidden');
    }

    function showImportPresetMessage(type, message) {
        const msgDiv = document.getElementById('importPresetMessage');
        if (!msgDiv) {
            showMappingMessage(type === 'success' ? 'success' : 'error', message);
            return;
        }
        msgDiv.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-800', 'border-amber-200', 'bg-amber-50', 'text-amber-900', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        if (type === 'success') {
            msgDiv.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        } else if (type === 'warning') {
            msgDiv.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-900');
        } else {
            msgDiv.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        }
        msgDiv.textContent = message;
    }

    function clearImportPresetMessage() {
        const msgDiv = document.getElementById('importPresetMessage');
        if (!msgDiv) return;
        msgDiv.classList.add('hidden');
        msgDiv.textContent = '';
    }

    function toggleImportPresetBtn() {
        const fileInput = document.getElementById('importFile');
        const btn = document.getElementById('importWithPresetBtn');
        const hasFile = fileInput && fileInput.files.length > 0;
        if (btn) {
            btn.disabled = !hasFile;
        }
    }

    async function importUsingPreset() {
        if (!window.canUseMappingPreset) {
            showImportPresetMessage('error', 'You do not have permission to import facility data.');
            return;
        }
        clearImportPresetMessage();

        const presetSelect = document.getElementById('presetSelect');
        const fileInput = document.getElementById('importFile');
        const presetId = presetSelect ? presetSelect.value : '';
        const file = fileInput && fileInput.files.length ? fileInput.files[0] : null;
        window._lastImportPresetId = presetId || null;
        window._lastImportFilename = file ? file.name : null;

        if (!file) {
            showImportPresetMessage('error', 'Please choose an Excel file to import.');
            fileInput?.focus();
            return;
        }

        if (!presetId) {
            showImportPresetMessage(
                'warning',
                window.canCreateMappingPreset
                    ? 'Please select a mapping preset from the list below, or click "Create Preset" to upload your file and define column mappings first.'
                    : 'Please select a mapping preset from the list above. Creating new presets is not available for your role yet.'
            );
            presetSelect?.focus();
            return;
        }

        const form = document.getElementById('excelUploadForm');
        if (!form) {
            showImportPresetMessage('error', 'Import form is not available.');
            return;
        }

        const btn = document.getElementById('importWithPresetBtn');
        const prevLabel = btn ? btn.textContent : '';
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Importing…';
        }

        window.showImportDataLoader?.('Reading file and importing…');
        try {
            const parseData = new FormData();
            parseData.append('file', file);
            parseData.append('_token', form.querySelector('[name=_token]').value);
            parseData.append('facility_id', form.querySelector('[name=facility_id]').value);

            const parseRes = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': form.querySelector('[name=_token]').value },
                body: parseData,
            });
            const parsed = await parseRes.json();
            if (!parseRes.ok || !parsed.worksheets) {
                showImportPresetMessage('error', parsed.error || 'Failed to read the Excel file.');
                return;
            }

            const facilityId = form.querySelector('[name=facility_id]').value;
            const presetsRes = await fetch(`/admin/facility/files/mapping-presets?facility_id=${facilityId}`);
            const presetsPayload = await presetsRes.json();
            const preset = (presetsPayload.presets || []).find(p => String(p.id) === String(presetId));

            if (!preset) {
                showImportPresetMessage('error', 'The selected preset could not be found. Refresh the page and try again.');
                return;
            }

            if (!Array.isArray(preset.mappings) || !preset.mappings.length) {
                showImportPresetMessage(
                    'warning',
                    window.canCreateMappingPreset
                        ? 'This preset has no column mappings. Click "Create Preset" to upload your file and save a mapping.'
                        : 'This preset has no column mappings. Please contact a Super Administrator to configure this preset.'
                );
                return;
            }

            window._lastImportPresetFacilityId = preset.facility_id || null;

            window._excelWorksheets = parsed.worksheets.reduce((acc, ws) => {
                acc[ws.name] = ws.columns;
                return acc;
            }, {});
            window._excelWorksheetData = parsed.worksheets.reduce((acc, ws) => {
                acc[ws.name] = ws.data;
                return acc;
            }, {});

            const worksheets = parsed.worksheets.map(ws => ({ name: ws.name, data: ws.data }));
            let primaryWs = preset.mappings[0].worksheet;
            let dataRows = window._excelWorksheetData[primaryWs];

            if (!Array.isArray(dataRows) || !dataRows.length) {
                const fallback = parsed.worksheets.find(ws => Array.isArray(ws.data) && ws.data.length);
                if (fallback) {
                    primaryWs = fallback.name;
                    dataRows = fallback.data;
                }
            }

            if (!Array.isArray(dataRows) || !dataRows.length) {
                showImportPresetMessage('error', 'No data rows were found in the Excel file for this preset.');
                return;
            }

            await doImportMapping(preset.mappings, dataRows, false, worksheets);
            clearImportPresetMessage();
        } catch (err) {
            showImportPresetMessage('error', 'Import failed: ' + (err?.message || 'Unknown error'));
        } finally {
            window.hideImportDataLoader?.();
            if (btn) {
                btn.textContent = prevLabel;
                toggleImportPresetBtn();
            }
        }
    }

    function initImportPresetForm() {
        const facilityId = document.querySelector('#excelUploadForm [name=facility_id]')?.value;
        const presetSelect = document.getElementById('presetSelect');
        const importBtn = document.getElementById('importWithPresetBtn');
        const createBtn = document.getElementById('createPresetBtn');

        if (facilityId && presetSelect) {
            reloadAllPresetDropdowns();
        }

        presetSelect?.addEventListener('change', () => {
            clearImportPresetMessage();
            toggleImportPresetBtn();
            const meta = getSelectedPresetMeta(presetSelect);
            window._lastImportPresetFacilityId = meta?.facilityId || null;
            togglePresetActionButtons(meta?.id || null, meta?.name, meta?.facilityId);
        });
        document.getElementById('importFile')?.addEventListener('change', () => {
            clearImportPresetMessage();
            toggleImportPresetBtn();
        });

        importBtn?.addEventListener('click', importUsingPreset);

        createBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            clearImportPresetMessage();
            if (!window.canCreateMappingPreset) {
                showImportPresetMessage('error', presetCreationUnavailableMessage());
                return;
            }
            showMappingStep(e);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initImportPresetForm);
    } else {
        initImportPresetForm();
    }

// Move mapping row up or down
function moveMappingRow(btn, direction) {
    const row = btn.closest('tr');
    if (!row) return;
    const tbody = row.parentElement;
    const rows = Array.from(tbody.children);
    const index = rows.indexOf(row);
    let targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= rows.length) return;
    if (direction === -1) {
        tbody.insertBefore(row, rows[targetIndex]);
    } else {
        if (rows[targetIndex].nextSibling) {
            tbody.insertBefore(row, rows[targetIndex].nextSibling);
        } else {
            tbody.appendChild(row);
        }
    }
}
</script>
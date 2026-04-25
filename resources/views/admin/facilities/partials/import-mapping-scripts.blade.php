<script>
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
        const name = document.getElementById('mappingPresetName').value.trim();
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
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Mapping preset saved!');
                loadMappingPresets();
            } else {
                alert('Failed to save preset.');
            }
        });
    }
    
    function loadMappingPresets() {
        fetch('/admin/facility/files/mapping-presets')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('mappingPresetSelect');
                select.innerHTML = '<option value="">-- Load Mapping Preset --</option>';
                if (data.presets) {
                    data.presets.forEach(preset => {
                        select.innerHTML += `<option value="${preset.id}">${preset.name}</option>`;
                    });
                }
            });
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
                    return;
                }
                fetch(`/admin/facility/files/mapping-presets`)
                    .then(res => res.json())
                    .then(data => {
                        const preset = data.presets.find(p => p.id == id);
                        if (preset) {
                            setMappings(preset.mappings);
                            if (nameInput) nameInput.value = preset.name;
                        }
                        if (deleteBtn) deleteBtn.classList.remove('hidden');
                    });
            });
        }
        
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
                        loadMappingPresets();
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

    async function doImportMapping(mappings, dataRows, confirmOverwrite, worksheets) {
        const facilityId = document.querySelector('[name=facility_id]')?.value || window._facilityId;
        const url = `/admin/facility/${facilityId}/files/import-data`;
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
                },
                body: JSON.stringify({ mappings, data: dataRows, confirm_overwrite: confirmOverwrite, worksheets })
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
                window._pendingImport = { mappings, dataRows };
                return;
            }
            if (res.status === 422 && result.invalid_rows) {
                showInvalidRowsModal(result.invalid_rows, result.message);
                return;
            }
            if (result.success) {
                showMappingMessage('success', 'Mapping imported successfully!');
                showImportSuccessModal();
                window._importDataRows = null;
                return;
            }
            showMappingMessage('error', result.message || 'Import failed.');
        } catch (e) {
            showMappingMessage('error', 'Import failed: ' + (e && e.message ? e.message : ''));
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
            await doImportMapping(window._pendingImport.mappings, window._pendingImport.dataRows, true);
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
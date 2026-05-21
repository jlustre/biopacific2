@if($canImport ?? false)
@include('admin.partials.import-data-loader')

<div id="presetImportModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="presetImportModalTitle">
    <div class="absolute inset-0 bg-slate-900/50" data-preset-import-close></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-xl bg-white shadow-xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 id="presetImportModalTitle" class="text-lg font-semibold text-slate-900">Import with preset</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Preset: <span id="presetImportName" class="font-semibold text-slate-900"></span>
                    </p>
                </div>
                <button type="button" data-preset-import-close
                        title="Close" aria-label="Close"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="overflow-y-auto px-6 py-4 space-y-4">
                <div id="presetImportFormSection">
                    <div id="presetImportFacilityWrap" class="hidden">
                        <label for="presetImportFacility" class="mb-1 block text-sm font-semibold text-slate-700">
                            Target facility <span class="text-red-500">*</span>
                        </label>
                        <select id="presetImportFacility"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                            <option value="">Select facility…</option>
                            @foreach($importFacilities ?? [] as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Required for global presets — data will be imported into this facility.</p>
                    </div>

                    <div class="mt-4">
                        <label for="presetImportFile" class="mb-1 block text-sm font-semibold text-slate-700">
                            Excel file <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="presetImportFile" accept=".xlsx,.xls,.csv"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-teal-100 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-teal-800">
                    </div>

                    <div id="presetImportWorksheetWrap" class="mt-4 hidden">
                        <label for="presetImportWorksheet" class="mb-1 block text-sm font-semibold text-slate-700">Primary data worksheet</label>
                        <select id="presetImportWorksheet"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Rows from this sheet drive employee imports. Other sheets are still used for cross-sheet lookups when mapped.</p>
                    </div>

                    <label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="presetImportOverwrite" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        Overwrite existing employees if duplicate IDs are found
                    </label>
                </div>

                <div id="presetImportStatus" class="hidden rounded-lg border px-4 py-3 text-sm"></div>

                <div id="presetImportResults" class="hidden space-y-3">
                    <div id="presetImportSummary" class="rounded-lg border px-4 py-3 text-sm"></div>
                    <div id="presetImportDuplicatePanel" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <p class="font-semibold">Duplicate employee IDs detected</p>
                        <ul id="presetImportDuplicateList" class="mt-2 max-h-32 list-inside list-disc overflow-y-auto text-xs"></ul>
                        <button type="button" id="presetImportConfirmOverwrite"
                                class="mt-3 rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                            Overwrite and continue import
                        </button>
                    </div>
                    <div id="presetImportErrorsPanel" class="hidden">
                        <p class="mb-2 text-sm font-semibold text-red-800">Import errors</p>
                        <div class="max-h-64 overflow-auto rounded-lg border border-red-200">
                            <table class="min-w-full text-xs">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-red-900">Row</th>
                                        <th class="px-3 py-2 text-left font-semibold text-red-900">Employee</th>
                                        <th class="px-3 py-2 text-left font-semibold text-red-900">Source</th>
                                        <th class="px-3 py-2 text-left font-semibold text-red-900">Target</th>
                                        <th class="px-3 py-2 text-left font-semibold text-red-900">Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="presetImportErrorsBody" class="divide-y divide-red-100 bg-white"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="presetImportRowResultsPanel" class="hidden">
                        <p class="mb-2 text-sm font-semibold text-slate-700">Row-by-row results</p>
                        <div class="max-h-48 overflow-auto rounded-lg border border-slate-200">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Row</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Employee</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Action</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Details</th>
                                    </tr>
                                </thead>
                                <tbody id="presetImportRowResultsBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-6 py-4">
                <button type="button" data-preset-import-close
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Close
                </button>
                <button type="button" id="presetImportLoadBtn"
                        title="Preview worksheets" aria-label="Preview worksheets"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                    <i class="fas fa-file-upload"></i>
                </button>
                <button type="button" id="presetImportSubmitBtn"
                        class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60">
                    Run import
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const modal = document.getElementById('presetImportModal');
    if (!modal) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('[name=_token]')?.value || '';

    const parseUrl = @json($parseWorkbookUrl ?? '');
    const globalFacilityId = @json($globalId ?? 99);

    let currentPreset = null;
    let parsedWorksheets = [];
    let pendingConfirmOverwrite = false;

    const els = {
        name: document.getElementById('presetImportName'),
        facilityWrap: document.getElementById('presetImportFacilityWrap'),
        facility: document.getElementById('presetImportFacility'),
        file: document.getElementById('presetImportFile'),
        worksheetWrap: document.getElementById('presetImportWorksheetWrap'),
        worksheet: document.getElementById('presetImportWorksheet'),
        overwrite: document.getElementById('presetImportOverwrite'),
        status: document.getElementById('presetImportStatus'),
        results: document.getElementById('presetImportResults'),
        summary: document.getElementById('presetImportSummary'),
        duplicatePanel: document.getElementById('presetImportDuplicatePanel'),
        duplicateList: document.getElementById('presetImportDuplicateList'),
        errorsPanel: document.getElementById('presetImportErrorsPanel'),
        errorsBody: document.getElementById('presetImportErrorsBody'),
        rowResultsPanel: document.getElementById('presetImportRowResultsPanel'),
        rowResultsBody: document.getElementById('presetImportRowResultsBody'),
        loadBtn: document.getElementById('presetImportLoadBtn'),
        submitBtn: document.getElementById('presetImportSubmitBtn'),
        confirmOverwrite: document.getElementById('presetImportConfirmOverwrite'),
    };

    function escapeHtml(str) {
        return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function setStatus(type, message) {
        els.status.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-800', 'border-amber-200', 'bg-amber-50', 'text-amber-900', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-800', 'border-slate-200', 'bg-slate-50', 'text-slate-700');
        if (type === 'error') {
            els.status.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        } else if (type === 'warning') {
            els.status.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-900');
        } else if (type === 'success') {
            els.status.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        } else {
            els.status.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
        }
        els.status.textContent = message;
        els.status.classList.remove('hidden');
    }

    function clearResults() {
        els.results.classList.add('hidden');
        els.summary.innerHTML = '';
        els.duplicatePanel.classList.add('hidden');
        els.duplicateList.innerHTML = '';
        els.errorsPanel.classList.add('hidden');
        els.errorsBody.innerHTML = '';
        els.rowResultsPanel.classList.add('hidden');
        els.rowResultsBody.innerHTML = '';
        els.status.classList.add('hidden');
    }

    function resetModal() {
        clearResults();
        els.file.value = '';
        els.worksheet.innerHTML = '';
        els.worksheetWrap.classList.add('hidden');
        parsedWorksheets = [];
        pendingConfirmOverwrite = false;
        if (els.overwrite) els.overwrite.checked = false;
    }

    window.openPresetImportModal = function (preset) {
        currentPreset = preset;
        resetModal();
        els.name.textContent = preset.name || '';
        if (preset.isGlobal) {
            els.facilityWrap.classList.remove('hidden');
            els.facility.value = '';
        } else {
            els.facilityWrap.classList.add('hidden');
            els.facility.value = String(preset.facilityId || '');
        }
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        currentPreset = null;
        resetModal();
    }

    modal.querySelectorAll('[data-preset-import-close]').forEach(el => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    function resolveImportFacilityId() {
        if (currentPreset?.isGlobal) {
            return parseInt(els.facility.value, 10) || 0;
        }
        return parseInt(currentPreset?.facilityId, 10) || 0;
    }

    function validateBeforeRun() {
        if (!currentPreset) {
            setStatus('error', 'No preset selected.');
            return false;
        }
        if (!currentPreset.mappingsCount) {
            setStatus('error', 'This preset has no mappings. Edit the preset before importing.');
            return false;
        }
        if (!els.file.files?.length) {
            setStatus('error', 'Please choose an Excel file (.xlsx, .xls, or .csv).');
            els.file.focus();
            return false;
        }
        const facilityId = resolveImportFacilityId();
        if (currentPreset.isGlobal && (!facilityId || facilityId === globalFacilityId)) {
            setStatus('error', 'Please select a target facility for this global preset.');
            els.facility.focus();
            return false;
        }
        return true;
    }

    async function parseFilePreview() {
        if (!validateBeforeRun()) return;
        const file = els.file.files[0];
        const formData = new FormData();
        formData.append('file', file);

        els.loadBtn.disabled = true;
        setStatus('info', 'Reading workbook…');

        try {
            const res = await fetch(parseUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok || data.error) {
                setStatus('error', data.message || data.error || 'Failed to read the Excel file.');
                return;
            }

            parsedWorksheets = data.worksheets || [];
            if (!parsedWorksheets.length) {
                setStatus('error', 'No worksheets were found in this file.');
                return;
            }

            const expected = currentPreset.primaryWorksheet || '';
            els.worksheet.innerHTML = '';
            parsedWorksheets.forEach(ws => {
                const opt = document.createElement('option');
                opt.value = ws.name;
                const rowCount = Array.isArray(ws.data) ? ws.data.length : 0;
                opt.textContent = `${ws.name} (${rowCount} rows)`;
                if (ws.name === expected) opt.selected = true;
                els.worksheet.appendChild(opt);
            });
            if (!els.worksheet.value && parsedWorksheets[0]) {
                els.worksheet.value = parsedWorksheets[0].name;
            }
            els.worksheetWrap.classList.remove('hidden');
            setStatus('success', `Loaded ${parsedWorksheets.length} worksheet(s). Choose the primary data sheet, then click Run import.`);
        } catch (err) {
            setStatus('error', 'Could not read file: ' + (err?.message || 'Network error'));
        } finally {
            els.loadBtn.disabled = false;
        }
    }

    function renderFailures(failures) {
        els.errorsPanel.classList.remove('hidden');
        const rows = [];
        (failures || []).forEach(group => {
            const items = Array.isArray(group) ? group : [group];
            items.forEach(f => {
                const source = [f.source_worksheet, f.source_column].filter(Boolean).join(' → ') || '—';
                const target = [f.target_table, f.target_column].filter(Boolean).join('.') || '—';
                rows.push(`<tr>
                    <td class="px-3 py-2">${escapeHtml(f.row)}</td>
                    <td class="px-3 py-2 font-mono text-xs">${escapeHtml(f.employee_num ?? '—')}</td>
                    <td class="px-3 py-2">${escapeHtml(source)}</td>
                    <td class="px-3 py-2 font-mono text-xs">${escapeHtml(target)}</td>
                    <td class="px-3 py-2 text-red-800">${escapeHtml(f.reason ?? f.message ?? 'Unknown error')}</td>
                </tr>`);
            });
        });
        els.errorsBody.innerHTML = rows.join('') || '<tr><td colspan="5" class="px-3 py-4 text-center text-slate-500">No details available.</td></tr>';
    }

    function renderInvalidRows(invalidRows) {
        els.errorsPanel.classList.remove('hidden');
        const rows = (invalidRows || []).map(r => `<tr>
            <td class="px-3 py-2">${escapeHtml(r.row)}</td>
            <td class="px-3 py-2 font-mono text-xs">${escapeHtml(r.employee_num)}</td>
            <td class="px-3 py-2">—</td>
            <td class="px-3 py-2">gender</td>
            <td class="px-3 py-2 text-red-800">Invalid gender: ${escapeHtml(r.gender)}. Allowed: M, F, O, N.</td>
        </tr>`).join('');
        els.errorsBody.innerHTML = rows;
    }

    function renderImportResults(importResults) {
        if (!Array.isArray(importResults) || !importResults.length) return;
        els.rowResultsPanel.classList.remove('hidden');
        const actionClass = {
            inserted: 'text-emerald-700',
            updated: 'text-teal-700',
            skipped: 'text-amber-700',
            error: 'text-red-700',
        };
        els.rowResultsBody.innerHTML = importResults.map(r => {
            const cls = actionClass[r.action] || 'text-slate-600';
            const details = [
                r.reason,
                r.assignment_reason ? `Assignment: ${r.assignment_reason}` : null,
            ].filter(Boolean).join(' — ');
            return `<tr>
                <td class="px-3 py-2">${escapeHtml(r.row)}</td>
                <td class="px-3 py-2 font-mono text-xs">${escapeHtml(r.employee_num ?? '—')}</td>
                <td class="px-3 py-2 font-semibold ${cls}">${escapeHtml(r.action ?? '—')}</td>
                <td class="px-3 py-2 text-slate-600">${escapeHtml(details || '—')}</td>
            </tr>`;
        }).join('');
    }

    function renderImportResponse(res, data) {
        els.results.classList.remove('hidden');
        els.duplicatePanel.classList.add('hidden');

        if (res.status === 409 && Array.isArray(data.duplicates)) {
            els.summary.className = 'rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900';
            els.summary.innerHTML = '<strong>Import paused.</strong> ' + escapeHtml(data.message || 'Duplicate employee IDs found.');
            els.duplicatePanel.classList.remove('hidden');
            els.duplicateList.innerHTML = data.duplicates.map(id => `<li>${escapeHtml(id)}</li>`).join('');
            setStatus('warning', 'Confirm overwrite to replace existing employee records.');
            return;
        }

        if (!res.ok) {
            els.summary.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800';
            const headline = data.message || data.error || `Import failed (HTTP ${res.status}).`;
            els.summary.innerHTML = '<strong>Import failed.</strong> ' + escapeHtml(headline);

            if (Array.isArray(data.failures) && data.failures.length) {
                renderFailures(data.failures);
            } else if (Array.isArray(data.invalid_rows)) {
                renderInvalidRows(data.invalid_rows);
            } else if (Array.isArray(data.importResults)) {
                const errors = data.importResults.filter(r => r.action === 'error' || r.action === 'skipped');
                if (errors.length) renderImportResults(data.importResults);
            }

            if (Array.isArray(data.available_worksheets)) {
                setStatus('error', headline + ' Worksheets in file: ' + data.available_worksheets.join(', '));
            } else {
                setStatus('error', headline);
            }
            return;
        }

        if (data.success) {
            const imported = Array.isArray(data.imported) ? data.imported.length : 0;
            const results = data.importResults || [];
            const errors = results.filter(r => r.action === 'error');
            const skipped = results.filter(r => r.action === 'skipped');
            const unresolved = data.unresolved_lookups || [];

            let html = `<strong>Import completed.</strong> ${imported} employee record(s) inserted or updated.`;
            if (data.import_log_url) {
                html += ` <a href="${escapeHtml(data.import_log_url)}" class="font-semibold underline">View import log</a>.`;
            }
            if (errors.length) html += ` ${errors.length} row(s) had errors.`;
            if (skipped.length) html += ` ${skipped.length} row(s) skipped.`;
            if (unresolved.length) html += ` ${unresolved.length} lookup value(s) could not be matched to a database ID.`;

            if (errors.length || skipped.length || unresolved.length) {
                els.summary.className = 'rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900';
            } else {
                els.summary.className = 'rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800';
            }
            els.summary.innerHTML = html;
            renderImportResults(results);

            if (unresolved.length) {
                els.errorsPanel.classList.remove('hidden');
                const extra = unresolved.map(u => `<tr>
                    <td class="px-3 py-2">${escapeHtml(u.row)}</td>
                    <td class="px-3 py-2 font-mono text-xs">${escapeHtml(u.employee_num)}</td>
                    <td class="px-3 py-2">${escapeHtml(u.field)} = "${escapeHtml(u.raw_value)}"</td>
                    <td class="px-3 py-2">lookup</td>
                    <td class="px-3 py-2 text-amber-800">${escapeHtml(u.status)} ${escapeHtml(u.hint || '')}</td>
                </tr>`).join('');
                els.errorsBody.innerHTML = (els.errorsBody.innerHTML || '') + extra;
            }

            setStatus(errors.length ? 'warning' : 'success', errors.length ? 'Import finished with errors — review details below.' : 'Import finished successfully.');
            return;
        }

        setStatus('error', data.message || data.error || 'Import failed for an unknown reason.');
    }

    async function runImport(confirmOverwrite) {
        if (!validateBeforeRun()) return;

        const file = els.file.files[0];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('confirm_overwrite', confirmOverwrite ? '1' : '0');
        if (els.worksheet.value) {
            formData.append('primary_worksheet', els.worksheet.value);
        }
        const facilityId = resolveImportFacilityId();
        if (currentPreset.isGlobal && facilityId) {
            formData.append('facility_id', String(facilityId));
        }

        clearResults();
        els.results.classList.remove('hidden');
        els.submitBtn.disabled = true;
        els.submitBtn.textContent = 'Importing…';
        setStatus('info', 'Import in progress — this may take a moment for large files…');
        window.showImportDataLoader?.('Importing employee data…');

        try {
            const url = currentPreset.runImportUrl;
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: formData,
            });

            let data = {};
            const contentType = res.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                data = await res.json();
            } else {
                const text = await res.text();
                setStatus('error', 'Server returned an invalid response (not JSON).');
                els.summary.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800';
                els.summary.innerHTML = '<strong>Import failed.</strong> Unexpected server response.';
                console.error('Import response:', text);
                return;
            }

            renderImportResponse(res, data);
        } catch (err) {
            setStatus('error', 'Import request failed: ' + (err?.message || 'Network error'));
            els.summary.className = 'rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800';
            els.summary.innerHTML = '<strong>Import failed.</strong> Could not reach the server.';
            els.results.classList.remove('hidden');
        } finally {
            window.hideImportDataLoader?.();
            els.submitBtn.disabled = false;
            els.submitBtn.textContent = 'Run import';
        }
    }

    els.loadBtn?.addEventListener('click', parseFilePreview);
    els.submitBtn?.addEventListener('click', () => runImport(els.overwrite?.checked || pendingConfirmOverwrite));
    els.confirmOverwrite?.addEventListener('click', () => {
        pendingConfirmOverwrite = true;
        if (els.overwrite) els.overwrite.checked = true;
        runImport(true);
    });
})();
</script>
@endpush
@endonce
@endif

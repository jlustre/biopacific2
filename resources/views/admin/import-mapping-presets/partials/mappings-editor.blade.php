@php
    $mappings = old('mappings', $mappings ?? []);
    $targetTables = $targetTables ?? config('import-mapping.target_tables', []);
    $parseWorkbookUrl = $parseWorkbookUrl ?? '';
    $validateDraftMappingsUrl = $validateDraftMappingsUrl ?? '';
    $tableColumnsUrl = $tableColumnsUrl ?? '';
@endphp

<div class="space-y-4"
     x-data="mappingPresetEditor({
        initialRows: @js($mappings),
        parseUrl: @js($parseWorkbookUrl),
        validateUrl: @js($validateDraftMappingsUrl),
        tableColumnsUrl: @js($tableColumnsUrl),
        targetTables: @js($targetTables),
     })"
     x-init="init()">

    <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-4">
        <h3 class="text-sm font-semibold text-indigo-900">Sample workbook</h3>
        <p class="mt-1 text-xs text-indigo-700">Upload an Excel file to load worksheet names and source columns for mapping. The file is not saved with the preset.</p>
        <div class="mt-3 flex flex-wrap items-end gap-3">
            <div class="min-w-0 flex-1">
                <label class="mb-1 block text-xs font-semibold text-slate-600">Excel file (.xlsx, .xls, .csv)</label>
                <input type="file" x-ref="workbookFile" accept=".xlsx,.xls,.csv"
                       class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-indigo-800">
            </div>
            <button type="button" @click="parseWorkbook()" :disabled="parsing"
                    title="Load workbook" aria-label="Load workbook"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                <i class="fas" :class="parsing ? 'fa-spinner fa-spin' : 'fa-file-upload'"></i>
            </button>
            <button type="button" @click="validateMappings()" :disabled="!fileLoaded || !rows.length || validating"
                    title="Validate mappings against workbook and database"
                    class="rounded-lg border border-violet-200 px-4 py-2 text-sm font-semibold text-violet-800 hover:bg-violet-50 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-show="!validating">Validate</span>
                <span x-show="validating" x-cloak>Validating…</span>
            </button>
        </div>
        <p x-show="parseError" x-cloak class="mt-2 text-sm text-red-700" x-text="parseError"></p>
        <p x-show="fileLoaded && !parseError" x-cloak class="mt-2 text-sm text-emerald-700">
            Workbook loaded — <span x-text="worksheetNames.length"></span> worksheet(s) available.
        </p>
        <p x-show="validateError" x-cloak class="mt-2 text-sm text-red-700" x-text="validateError"></p>
    </div>

    <div x-show="validationResults" x-cloak class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
        <div class="rounded-lg border px-4 py-3 text-sm"
             :class="validationResults?.valid ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'">
            <template x-if="validationResults?.valid">
                <span><strong>Validation passed.</strong> All mappings exist in the workbook and target tables.</span>
            </template>
            <template x-if="validationResults && !validationResults.valid">
                <span><strong>Validation failed.</strong> <span x-text="validationResults.summary?.failed"></span> of <span x-text="validationResults.summary?.total"></span> mapping(s) have issues.</span>
            </template>
        </div>
        <div class="max-h-64 overflow-auto rounded-lg border border-slate-200">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Source</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Target</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="row in (validationResults?.results || [])" :key="row.index">
                        <tr :class="row.valid ? '' : 'bg-red-50/50'">
                            <td class="px-3 py-2" x-text="row.index + 1"></td>
                            <td class="px-3 py-2" x-text="[row.worksheet, row.worksheet_column].filter(Boolean).join(' → ')"></td>
                            <td class="px-3 py-2 font-mono" x-text="[row.table, row.table_column].filter(Boolean).join('.')"></td>
                            <td class="px-3 py-2 font-semibold" :class="row.valid ? 'text-emerald-700' : 'text-red-700'" x-text="row.valid ? 'OK' : 'Failed'"></td>
                            <td class="px-3 py-2 text-slate-600" x-text="(row.issues || []).join(' ') || '—'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Column mappings</h3>
                <p class="text-xs text-slate-500">Map source columns from the workbook to database fields.</p>
            </div>
            <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800" x-text="rows.length + ' mapping(s)'"></span>
        </div>

        <div class="mb-4 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Worksheet</label>
                <template x-if="fileLoaded">
                    <select x-model="draft.worksheet" @change="onWorksheetChange()"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                        <option value="">Select worksheet</option>
                        <template x-for="name in worksheetNames" :key="name">
                            <option :value="name" x-text="name"></option>
                        </template>
                    </select>
                </template>
                <template x-if="!fileLoaded">
                    <input type="text" x-model="draft.worksheet" placeholder="Sheet1"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                </template>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Source column</label>
                <template x-if="fileLoaded && draft.worksheet">
                    <select x-model="draft.worksheet_column"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                        <option value="">Select column</option>
                        <template x-for="col in sourceColumns" :key="col">
                            <option :value="col" x-text="col"></option>
                        </template>
                    </select>
                </template>
                <template x-if="!fileLoaded || !draft.worksheet">
                    <input type="text" x-model="draft.worksheet_column" placeholder="Employee ID"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                </template>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Target table</label>
                <select x-model="draft.table" @change="loadTargetColumns()"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <option value="">Select table</option>
                    <template x-for="table in targetTables" :key="table">
                        <option :value="table" x-text="table"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Target column</label>
                <template x-if="draft.table && targetColumns.length">
                    <select x-model="draft.table_column"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                        <option value="">Select column</option>
                        <template x-for="col in targetColumns" :key="col">
                            <option :value="col" x-text="col"></option>
                        </template>
                    </select>
                </template>
                <template x-if="!draft.table || !targetColumns.length">
                    <input type="text" x-model="draft.table_column" placeholder="employee_id"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                </template>
            </div>
            <div class="flex items-end justify-end">
                <button type="button" @click="addRow()"
                        title="Add mapping" aria-label="Add mapping"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600 text-white hover:bg-teal-700">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        <template x-if="rows.length === 0">
            <p class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm text-slate-500">
                Load a workbook, then add mappings using the fields above.
            </p>
        </template>

        <div x-show="rows.length > 0" x-cloak class="mb-3 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-600">Row order</p>
                <p class="text-xs text-slate-500">Sort or move rows — order is saved with the preset.</p>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Sort by</label>
                <select x-model="sortField"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <option value="worksheet">Worksheet</option>
                    <option value="worksheet_column">Source column</option>
                    <option value="table">Target table</option>
                    <option value="table_column">Target column</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Direction</label>
                <select x-model="sortDirection"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
                    <option value="asc">A → Z</option>
                    <option value="desc">Z → A</option>
                </select>
            </div>
            <button type="button" @click="sortRows()"
                    title="Apply sort" aria-label="Apply sort"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-100">
                <i class="fas fa-sort"></i>
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white" x-show="rows.length > 0" x-cloak>
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Worksheet</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Source column</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Target table</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Target column</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="index">
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-2 text-slate-500" x-text="index + 1"></td>
                            <td class="px-3 py-2" x-text="row.worksheet"></td>
                            <td class="px-3 py-2" x-text="row.worksheet_column"></td>
                            <td class="px-3 py-2 font-mono text-xs" x-text="row.table"></td>
                            <td class="px-3 py-2 font-mono text-xs" x-text="row.table_column"></td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" @click="moveRow(index, -1)" :disabled="index === 0"
                                            title="Move up" aria-label="Move up"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30">
                                        <i class="fas fa-arrow-up"></i>
                                    </button>
                                    <button type="button" @click="moveRow(index, 1)" :disabled="index === rows.length - 1"
                                            title="Move down" aria-label="Move down"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30">
                                        <i class="fas fa-arrow-down"></i>
                                    </button>
                                    <button type="button" @click="removeRow(index)"
                                            title="Remove mapping" aria-label="Remove mapping"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div x-ref="hiddenFields" class="hidden" aria-hidden="true"></div>
    </div>
</div>

@once
@push('scripts')
<script>
function mappingPresetEditor(config) {
    const initialRows = Array.isArray(config.initialRows) ? config.initialRows.map(r => ({ ...r })) : [];

    return {
        rows: initialRows,
        draft: {
            worksheet: initialRows[0]?.worksheet ?? '',
            worksheet_column: '',
            table: '',
            table_column: '',
        },
        worksheets: {},
        worksheetNames: [],
        targetColumns: [],
        targetTables: config.targetTables || [],
        parseUrl: config.parseUrl,
        validateUrl: config.validateUrl,
        tableColumnsUrl: config.tableColumnsUrl,
        fileLoaded: false,
        parsing: false,
        parseError: '',
        validating: false,
        validateError: '',
        validationResults: null,
        sortField: 'worksheet',
        sortDirection: 'asc',

        get sourceColumns() {
            if (!this.draft.worksheet || !this.worksheets[this.draft.worksheet]) {
                return [];
            }
            return this.worksheets[this.draft.worksheet];
        },

        init() {
            if (this.draft.table) {
                this.loadTargetColumns();
            }
            this.syncHiddenInputs();
            const form = this.$el.closest('form');
            if (form) {
                form.addEventListener('submit', () => this.syncHiddenInputs());
            }
        },

        syncHiddenInputs() {
            const container = this.$refs.hiddenFields;
            if (!container) {
                return;
            }
            container.innerHTML = '';
            const fields = ['worksheet', 'worksheet_column', 'table', 'table_column'];
            this.rows.forEach((row, index) => {
                fields.forEach((field) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `mappings[${index}][${field}]`;
                    input.value = row[field] ?? '';
                    container.appendChild(input);
                });
            });
        },

        async parseWorkbook() {
            const input = this.$refs.workbookFile;
            const file = input?.files?.[0];
            if (!file) {
                this.parseError = 'Please choose an Excel file first.';
                return;
            }

            this.parsing = true;
            this.parseError = '';
            this.validationResults = null;
            this.validateError = '';

            const formData = new FormData();
            formData.append('file', file);

            try {
                const res = await fetch(this.parseUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value || '',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await res.json().catch(() => ({}));

                if (!res.ok || data.error) {
                    this.parseError = data.error || 'Failed to load workbook.';
                    this.fileLoaded = false;
                    return;
                }

                this.worksheets = {};
                (data.worksheets || []).forEach(ws => {
                    this.worksheets[ws.name] = ws.columns || [];
                });
                this.worksheetNames = Object.keys(this.worksheets);
                this.fileLoaded = this.worksheetNames.length > 0;

                if (!this.draft.worksheet && this.worksheetNames.length) {
                    this.draft.worksheet = this.worksheetNames[0];
                } else if (this.draft.worksheet && !this.worksheets[this.draft.worksheet]) {
                    this.draft.worksheet = this.worksheetNames[0] || '';
                }
                this.draft.worksheet_column = '';
            } catch (err) {
                this.parseError = 'Failed to load workbook: ' + (err?.message || 'Unknown error');
                this.fileLoaded = false;
            } finally {
                this.parsing = false;
            }
        },

        onWorksheetChange() {
            this.draft.worksheet_column = '';
        },

        async validateMappings() {
            const input = this.$refs.workbookFile;
            const file = input?.files?.[0];
            if (!file) {
                this.validateError = 'Please choose an Excel file first.';
                return;
            }
            if (!this.rows.length) {
                this.validateError = 'Add at least one mapping before validating.';
                return;
            }
            if (!this.validateUrl) {
                this.validateError = 'Validation is not available.';
                return;
            }

            this.validating = true;
            this.validateError = '';
            this.validationResults = null;

            const formData = new FormData();
            formData.append('file', file);
            this.rows.forEach((row, index) => {
                ['worksheet', 'worksheet_column', 'table', 'table_column'].forEach((field) => {
                    formData.append(`mappings[${index}][${field}]`, row[field] ?? '');
                });
            });

            try {
                const res = await fetch(this.validateUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value || '',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await res.json().catch(() => ({}));

                if (!res.ok && !data.results) {
                    this.validateError = data.message || data.error || 'Validation request failed.';
                    return;
                }

                this.validationResults = data;
            } catch (err) {
                this.validateError = 'Validation failed: ' + (err?.message || 'Unknown error');
            } finally {
                this.validating = false;
            }
        },

        async loadTargetColumns() {
            this.draft.table_column = '';
            this.targetColumns = [];
            if (!this.draft.table || !this.tableColumnsUrl) {
                return;
            }
            try {
                const res = await fetch(`${this.tableColumnsUrl}?table=${encodeURIComponent(this.draft.table)}`);
                const data = await res.json();
                this.targetColumns = data.columns || [];
            } catch (err) {
                this.targetColumns = [];
            }
        },

        addRow() {
            const { worksheet, worksheet_column, table, table_column } = this.draft;
            if (!worksheet || !worksheet_column || !table || !table_column) {
                alert('Please complete all mapping fields before adding.');
                return;
            }
            for (const row of this.rows) {
                if (
                    row.worksheet === worksheet
                    && row.worksheet_column === worksheet_column
                    && row.table === table
                    && row.table_column === table_column
                ) {
                    alert('This mapping already exists.');
                    return;
                }
            }
            this.rows.push({ worksheet, worksheet_column, table, table_column });
            this.validationResults = null;
            this.syncHiddenInputs();
            this.draft = {
                worksheet: worksheet,
                worksheet_column: '',
                table: table,
                table_column: '',
            };
            if (table) {
                this.loadTargetColumns();
            }
        },

        removeRow(index) {
            this.rows.splice(index, 1);
            this.validationResults = null;
            this.syncHiddenInputs();
        },

        sortRows() {
            const field = this.sortField;
            const dir = this.sortDirection === 'desc' ? -1 : 1;
            this.rows = [...this.rows].sort((a, b) => {
                const left = (a[field] ?? '').toString().toLowerCase();
                const right = (b[field] ?? '').toString().toLowerCase();
                const cmp = left.localeCompare(right, undefined, { numeric: true, sensitivity: 'base' });
                return cmp * dir;
            });
            this.syncHiddenInputs();
        },

        moveRow(index, delta) {
            const target = index + delta;
            if (target < 0 || target >= this.rows.length) {
                return;
            }
            const [row] = this.rows.splice(index, 1);
            this.rows.splice(target, 0, row);
            this.syncHiddenInputs();
        },
    };
}
</script>
@endpush
@endonce

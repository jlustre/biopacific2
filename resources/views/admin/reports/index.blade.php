@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Reports</h1>
    <div class="flex flex-col md:flex-row md:justify-end gap-2 mb-4">
        @if(!empty($canScheduleReports))
        <a href="{{ route('admin.scheduled-reports.create', array_filter(['facility_id' => ($selectedFacilityId ?? 0) ?: null])) }}"
            class="px-4 py-2 bg-blue-600 text-white rounded flex items-center">
            <i class="fas fa-plus mr-2"></i> Schedule Report
        </a>
        @endif
        <a href="{{ route('admin.scheduled-reports.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded flex items-center">
            <i class="fas fa-clock mr-2"></i> Scheduled Reports
        </a>
        @if($canManageReports ?? false)
        <form method="POST" action="{{ route('admin.reports.sync-seeder') }}">
            @csrf
            <button type="submit"
                    class="px-4 py-2 bg-teal-600 text-white rounded flex items-center"
                    onclick="return confirm('Update ReportSeeder with the current reports in the database?');">
                <i class="fas fa-save mr-2"></i> Add/update seeder
            </button>
        </form>
        <a href="{{ route('admin.reports.create') }}" class="px-4 py-2 bg-green-600 text-white rounded flex items-center">
            <i class="fas fa-plus mr-2"></i> Create Report
        </a>
        @endif
    </div>
    @if(session('success'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
        {{ session('error') }}
    </div>
    @endif
    <form method="GET" action="{{ route('admin.reports.index') }}" class="mb-4 flex flex-wrap gap-2 items-end rounded border border-slate-200 bg-white p-3 shadow-sm">
        @if(!empty($canFilterAllFacilities))
        <div>
            <label class="block text-xs font-semibold mb-1">Facility</label>
            <select name="facility_id" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48">
                <option value="0">All Facilities</option>
                @foreach($facilities as $filterFacility)
                    <option value="{{ $filterFacility->id }}" @selected((int) ($selectedFacilityId ?? 0) === (int) $filterFacility->id)>
                        {{ $filterFacility->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @elseif(($selectedFacilityId ?? 0) > 0)
            <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
        @endif
        <div>
            <label class="block text-xs font-semibold mb-1">Department</label>
            <select name="department_id" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48">
                <option value="0">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((int) ($selectedDepartmentId ?? 0) === (int) $department->id)>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Position</label>
            <select name="position_id" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48">
                <option value="0">All Positions</option>
                @foreach($positions as $position)
                    <option value="{{ $position->id }}" @selected((int) ($selectedPositionId ?? 0) === (int) $position->id)>
                        {{ $position->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Reports To</label>
            <select name="reports_to" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48">
                <option value="0">All Supervisors</option>
                @foreach($supervisorPositions as $supervisor)
                    <option value="{{ $supervisor->id }}" @selected((int) ($selectedReportsTo ?? 0) === (int) $supervisor->id)>
                        {{ $supervisor->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48" placeholder="Name or description...">
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Category</label>
            <select name="category_id" class="form-select bg-teal-50 border border-teal-500 px-2 py-1 text-sm rounded w-48">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Filter</button>
            <a href="{{ route('admin.reports.index') }}" class="px-3 py-1 bg-gray-300 text-gray-800 rounded text-sm ml-2">Reset</a>
        </div>
    </form>
    <div class="p-6 bg-white rounded shadow">
        <table class="min-w-full border border-gray-200 table-auto text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">Category</th>
                    <th class="px-3 py-2 border">Name</th>
                    <th class="px-3 py-2 border">Description</th>
                    <th class="px-3 py-2 border">Active</th>
                    <th class="px-3 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td class="px-3 py-2 border">{{ $report->category ? $report->category->name : '' }}</td>
                    <td class="px-3 py-2 border">{{ $report->name }}</td>
                    <td class="px-3 py-2 border">{{ $report->description }}</td>
                    <td class="px-3 py-2 border text-center">{!! $report->is_active ? '<span class=\'text-green-600\'>Yes</span>' : '<span class=\'text-red-600\'>No</span>' !!}</td>
                    <td class="px-3 py-2 border flex gap-2">
                        <button class="px-2 py-0.5 bg-blue-600 text-white rounded text-sm run-report-btn" data-id="{{ $report->id }}">Run</button>
                        @if($canManageReports ?? false)
                        <a href="{{ route('admin.reports.edit', $report) }}" class="px-2 py-0.5 bg-yellow-500 text-white rounded text-sm">Edit</a>
                        <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Delete this report?');" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2 py-0.5 bg-red-600 text-white rounded text-sm">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 border text-center text-gray-500">No reports found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    </div>
    <!-- Modal -->
    <div id="report-modal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black bg-opacity-50 p-4">
        <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded bg-white p-6 shadow">
            <h2 class="mb-4 text-xl font-bold" id="modal-title">Run Report</h2>
            <form id="report-params-form">
                <div id="report-params-fields" class="space-y-3"></div>
                <div class="mt-4">
                    <label class="mb-1 block font-semibold">Output Format</label>
                    <select name="output_format" id="output_format_select" class="form-select w-full border border-teal-500 px-2 py-1">
                        <option value="table">Table</option>
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div class="mt-4 hidden" id="pdf_orientation_wrap">
                    <label class="mb-1 block font-semibold">PDF Orientation</label>
                    <select name="pdf_orientation" id="pdf_orientation_select" class="form-select w-full border border-teal-500 px-2 py-1">
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" class="close-modal rounded bg-gray-300 px-4 py-2">Cancel</button>
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white" id="run-btn">Run</button>
                    <button type="button" class="hidden rounded bg-teal-600 px-4 py-2 text-white" id="rerun-btn">Rerun</button>
                </div>
            </form>
            <div id="report-results" class="mt-6 overflow-x-auto"></div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canFilterAllFacilities = @json(!empty($canFilterAllFacilities));
    const orgOptions = {
        facility: @json(
            collect([['id' => 0, 'label' => 'All Facilities']])
                ->concat(($facilities ?? collect())->map(fn ($f) => ['id' => $f->id, 'label' => $f->name]))
                ->values()
        ),
        department: @json(
            collect([['id' => 0, 'label' => 'All Departments']])
                ->concat(($departments ?? collect())->map(fn ($d) => ['id' => $d->id, 'label' => $d->name]))
                ->values()
        ),
        position: @json(
            collect([['id' => 0, 'label' => 'All Positions']])
                ->concat(($positions ?? collect())->map(fn ($p) => ['id' => $p->id, 'label' => $p->title]))
                ->values()
        ),
        reports_to: @json(
            collect([['id' => 0, 'label' => 'All Supervisors']])
                ->concat(($supervisorPositions ?? collect())->map(fn ($p) => ['id' => $p->id, 'label' => $p->title]))
                ->values()
        ),
    };

    const pageDefaults = {
        facility_id: String(@json((int) ($selectedFacilityId ?? 0))),
        department_id: String(@json((int) ($selectedDepartmentId ?? 0))),
        position_id: String(@json((int) ($selectedPositionId ?? 0))),
        reports_to: String(@json((int) ($selectedReportsTo ?? 0))),
        days_ahead: '30',
        overdue_days: '90',
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildParamField(param) {
        const name = param.name || '';
        const label = param.label || name;
        const type = (param.type || 'text').toLowerCase();
        const defaultValue = pageDefaults[name] ?? (param.default != null ? String(param.default) : (
            ['facility_id', 'department_id', 'position_id', 'reports_to'].includes(name) ? '0' : ''
        ));

        if (['facility', 'department', 'position', 'reports_to'].includes(type) || ['facility_id', 'department_id', 'position_id', 'reports_to'].includes(name)) {
            const optionKey = type === 'facility' || name === 'facility_id' ? 'facility'
                : (type === 'department' || name === 'department_id' ? 'department'
                : (type === 'position' || name === 'position_id' ? 'position' : 'reports_to'));

            if (optionKey === 'facility' && !canFilterAllFacilities) {
                return `<input type="hidden" name="${escapeHtml(name)}" value="${escapeHtml(pageDefaults.facility_id)}">`
                    + `<div class="mt-2 text-sm text-slate-600"><span class="font-semibold">${escapeHtml(label)}:</span> current facility</div>`;
            }

            const options = (orgOptions[optionKey] || []).map(opt => {
                const selected = String(opt.id) === String(defaultValue) ? ' selected' : '';
                return `<option value="${escapeHtml(opt.id)}"${selected}>${escapeHtml(opt.label)}</option>`;
            }).join('');

            return `<label class="mt-2 block text-sm font-semibold">${escapeHtml(label)}`
                + `<select name="${escapeHtml(name)}" class="form-select mt-1 w-full rounded border border-teal-500 px-2 py-1">${options}</select>`
                + `</label>`;
        }

        const inputType = type === 'integer' || type === 'number' ? 'number' : type;
        return `<label class="mt-2 block text-sm font-semibold">${escapeHtml(label)}`
            + `<input class="form-input mt-1 w-full rounded border border-teal-500 px-2 py-1" name="${escapeHtml(name)}" type="${escapeHtml(inputType)}" value="${escapeHtml(defaultValue)}">`
            + `</label>`;
    }

    const modal = document.getElementById('report-modal');
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('report-results').innerHTML = '';
    };
    document.querySelectorAll('.run-report-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(`/admin/reports/${this.dataset.id}/json`)
                .then(res => res.json())
                .then(report => {
                    document.getElementById('modal-title').textContent = report.name;
                    const outputSelect = document.getElementById('output_format_select');
                    const orientationWrap = document.getElementById('pdf_orientation_wrap');
                    const orientationSelect = document.getElementById('pdf_orientation_select');
                    if (orientationSelect) {
                        orientationSelect.value = report.default_pdf_orientation || 'portrait';
                    }
                    if (outputSelect && orientationWrap) {
                        const toggleOrientation = () => orientationWrap.classList.toggle('hidden', outputSelect.value !== 'pdf');
                        outputSelect.onchange = toggleOrientation;
                        toggleOrientation();
                    }
                    let params = report.parameters || [];
                    let fields = '';
                    if (!Array.isArray(params)) {
                        fields = "<div class='text-red-600 text-sm'>Parameters (JSON) must be an array of objects with name, label, and type.</div>";
                        params = [];
                    } else {
                        params.forEach(p => { fields += buildParamField(p); });
                    }
                    document.getElementById('report-params-fields').innerHTML = fields;
                    document.getElementById('run-btn').style.display = '';
                    document.getElementById('rerun-btn').classList.add('hidden');
                    document.getElementById('report-results').innerHTML = '';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.getElementById('report-params-form').onsubmit = function(e) {
                        e.preventDefault();
                        runReport();
                    };

                    function runReport() {
                        const formData = new FormData(document.getElementById('report-params-form'));
                        const params = {};
                        for (const [k,v] of formData.entries()) {
                            if (!['output_format', 'pdf_orientation'].includes(k)) params[k] = v === '' ? 0 : v;
                        }
                        const output_format = formData.get('output_format') || 'table';
                        const pdf_orientation = formData.get('pdf_orientation') || (report.default_pdf_orientation || 'portrait');
                        fetch(`/admin/reports/${report.id}/run`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({params, output_format, pdf_orientation}),
                        })
                        .then(r=>r.json())
                        .then(data => {
                            let html = '';
                            const outputFormat = document.getElementById('output_format_select').value;
                            const params = {};
                            new FormData(document.getElementById('report-params-form')).forEach((v, k) => {
                                if (!['output_format', 'pdf_orientation'].includes(k)) params[k] = v;
                            });
                            function buildQueryString(params) {
                                let q = '';
                                for (const [k, v] of Object.entries(params)) {
                                    q += `&params[${encodeURIComponent(k)}]=${encodeURIComponent(v)}`;
                                }
                                return q;
                            }
                            if (!data || (outputFormat === 'table' && !Array.isArray(data.results))) {
                                html = `<div class="text-red-600 font-semibold">${escapeHtml(data?.error || 'An error occurred or the server returned invalid data.')}</div>`;
                            } else if (outputFormat === 'pdf') {
                                let query = 'download=pdf&pdf_orientation=' + encodeURIComponent(document.getElementById('pdf_orientation_select').value || 'portrait') + buildQueryString(params);
                                html = `<a href="/admin/reports/${report.id}/run?${query}" class="px-3 py-1 bg-red-600 text-white rounded" target="_blank">View PDF</a><div class="text-gray-600 mt-2">PDF generated. Click above to view.</div>`;
                            } else if (outputFormat === 'csv') {
                                let query = 'download=csv' + buildQueryString(params);
                                html = `<a href="/admin/reports/${report.id}/run?${query}" class="px-3 py-1 bg-green-600 text-white rounded">Download CSV</a>`;
                                html += `<pre class="bg-gray-100 p-2 rounded text-xs mt-2">${data.csv ? escapeHtml(data.csv) : ''}</pre>`;
                            } else if (outputFormat === 'json') {
                                const jsonStr = JSON.stringify(data.results, null, 2);
                                html = `<div class="flex items-center gap-2 mb-2">
                                    <button type="button" id="copy-json-btn" class="px-2 py-1 bg-teal-600 text-white rounded text-xs">Copy JSON</button>
                                    <span id="copy-json-message" class="hidden text-green-600 text-xs font-semibold">Copied!</span>
                                </div>`;
                                html += `<pre id="json-output" class="bg-gray-100 p-2 rounded text-xs mt-2">${escapeHtml(jsonStr)}</pre>`;
                            } else if (!data.results.length) {
                                html = '<div class="text-slate-600">No results for the selected filters.</div>';
                            } else {
                                html = '<table class="min-w-full border mt-4 text-xs"><thead><tr>';
                                Object.keys(data.results[0]).forEach(col => html += `<th class='border px-2 py-1'>${escapeHtml(col)}</th>`);
                                html += '</tr></thead><tbody>';
                                data.results.forEach(row => {
                                    html += '<tr>';
                                    Object.values(row).forEach(val => {
                                        if (typeof val === 'object' && val !== null) {
                                            html += `<td class='border px-2 py-1'>${escapeHtml(JSON.stringify(val))}</td>`;
                                        } else {
                                            html += `<td class='border px-2 py-1'>${escapeHtml(val)}</td>`;
                                        }
                                    });
                                    html += '</tr>';
                                });
                                html += '</tbody></table>';
                            }
                            document.getElementById('report-results').innerHTML = html;
                            if (outputFormat === 'json') {
                                const copyBtn = document.getElementById('copy-json-btn');
                                const jsonOutput = document.getElementById('json-output');
                                const copyMsg = document.getElementById('copy-json-message');
                                if (copyBtn && jsonOutput) {
                                    copyBtn.addEventListener('click', function() {
                                        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                                            navigator.clipboard.writeText(jsonOutput.textContent).then(function() {
                                                if (copyMsg) {
                                                    copyMsg.classList.remove('hidden');
                                                    setTimeout(() => { copyMsg.classList.add('hidden'); }, 1500);
                                                }
                                            });
                                        }
                                    });
                                }
                            }
                            document.getElementById('run-btn').style.display = 'none';
                            document.getElementById('rerun-btn').classList.remove('hidden');
                        })
                        .catch(() => {
                            document.getElementById('report-results').innerHTML = '<div class="text-red-600 font-semibold">A server error occurred while running the report.</div>';
                        });
                    }

                    document.getElementById('rerun-btn').onclick = function() {
                        runReport();
                    };
                });
        });
    });
    document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', function(e) { if(e.target === modal) closeModal(); });
});
</script>
@endsection

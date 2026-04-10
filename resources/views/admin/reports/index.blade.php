@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Reports</h1>
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.reports.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">Create Report</a>
    </div>
    <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
        <div>
            <label class="block text-xs font-semibold mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input border border-teal-500 px-2 py-1 text-sm rounded w-48" placeholder="Name or description...">
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Category</label>
            <select name="category_id" class="form-select border border-teal-500 px-2 py-1 text-sm rounded w-48">
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
                        <a href="{{ route('admin.reports.edit', $report) }}" class="px-2 py-0.5 bg-yellow-500 text-white rounded text-sm">Edit</a>
                        <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Delete this report?');" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2 py-0.5 bg-red-600 text-white rounded text-sm">Delete</button>
                        </form>
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
    <div id="report-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="bg-white p-6 rounded shadow w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4" id="modal-title">Run Report</h2>
            <form id="report-params-form">
                <div id="report-params-fields"></div>
                <div class="mt-4">
                    <label class="block font-semibold mb-1">Output Format</label>
                    <select name="output_format" id="output_format_select" class="form-select w-full border border-teal-500 px-2 py-1">
                        <option value="table">Table</option>
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div class="flex justify-end mt-4 gap-2">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded close-modal">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" id="run-btn">Run</button>
                    <button type="button" class="px-4 py-2 bg-teal-600 text-white rounded hidden" id="rerun-btn">Rerun</button>
                </div>
            </form>
            <div id="report-results" class="mt-6"></div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('report-modal');
    const closeModal = () => { modal.classList.add('hidden'); document.getElementById('report-results').innerHTML = ''; };
    document.querySelectorAll('.run-report-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(`/admin/reports/${this.dataset.id}/json`)
                .then(res => res.json())
                .then(report => {
                    document.getElementById('modal-title').textContent = report.name;
                    let params = report.parameters || [];
                    let fields = '';
                    if (!Array.isArray(params)) {
                        // Defensive: if parameters is not an array, show a message
                        fields = "<div class='text-red-600 text-sm'>Parameters (JSON) must be an array of objects with name, label, and type. Example:<br><code>[{&quot;name&quot;:&quot;is_active&quot;,&quot;label&quot;:&quot;Is Active&quot;,&quot;type&quot;:&quot;text&quot;}]</code></div>";
                        params = [];
                    } else {
                        params.forEach(p => {
                            fields += `<label class='block mt-2'>${p.label || p.name}<input class='form-input mt-1 w-full border border-teal-500 px-2 py-1 rounded' name='${p.name||''}' type='${p.type||'text'}'></label>`;
                        });
                    }
                    document.getElementById('report-params-fields').innerHTML = fields;
                    modal.classList.remove('hidden');
                    document.getElementById('report-params-form').onsubmit = function(e) {
                        e.preventDefault();
                        runReport();
                    };

                    function runReport() {
                        const formData = new FormData(document.getElementById('report-params-form'));
                        const params = {};
                        for (const [k,v] of formData.entries()) {
                            if (k !== 'output_format') params[k]=v;
                        }
                        const output_format = formData.get('output_format') || 'table';
                        fetch(`/admin/reports/${report.id}/run`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({params, output_format}),
                        })
                        .then(r=>r.json())
                        .then(data => {
                            let html = '';
                            const outputFormat = document.getElementById('output_format_select').value;
                            const params = {};
                            // Collect params for download links
                            new FormData(document.getElementById('report-params-form')).forEach((v, k) => {
                                if (k !== 'output_format') params[k] = v;
                            });
                            function buildQueryString(params) {
                                let q = '';
                                for (const [k, v] of Object.entries(params)) {
                                    q += `&params[${encodeURIComponent(k)}]=${encodeURIComponent(v)}`;
                                }
                                return q;
                            }
                            if (!data || (outputFormat === 'table' && !Array.isArray(data.results))) {
                                html = '<div class="text-red-600 font-semibold">An error occurred or the server returned invalid data.</div>';
                            } else if (outputFormat === 'pdf') {
                                // PDF: show View PDF button
                                let query = 'download=pdf' + buildQueryString(params);
                                html = `<a href="/admin/reports/${report.id}/run?${query}" class="px-3 py-1 bg-red-600 text-white rounded" target="_blank">View PDF</a><div class="text-gray-600 mt-2">PDF generated. Click above to view.</div>`;
                            } else if (outputFormat === 'csv') {
                                // CSV: show Download CSV button and CSV content
                                let query = 'download=csv' + buildQueryString(params);
                                html = `<a href="/admin/reports/${report.id}/run?${query}" class="px-3 py-1 bg-green-600 text-white rounded">Download CSV</a>`;
                                html += `<pre class="bg-gray-100 p-2 rounded text-xs mt-2">${data.csv ? data.csv : ''}</pre>`;
                            } else if (outputFormat === 'json') {
                                const jsonStr = JSON.stringify(data.results, null, 2);
                                html = `<div class="flex items-center gap-2 mb-2">
                                    <button type="button" id="copy-json-btn" class="px-2 py-1 bg-teal-600 text-white rounded text-xs">Copy JSON</button>
                                    <span id="copy-json-message" class="hidden text-green-600 text-xs font-semibold">Copied!</span>
                                </div>`;
                                html += `<pre id="json-output" class="bg-gray-100 p-2 rounded text-xs mt-2">${jsonStr}</pre>`;
                            } else {
                                // Table
                                html = '<table class="min-w-full border mt-4"><thead><tr>';
                                if (data.results.length) {
                                    Object.keys(data.results[0]).forEach(col => html += `<th class='border px-2 py-1'>${col}</th>`);
                                    html += '</tr></thead><tbody>';
                                    data.results.forEach(row => {
                                        html += '<tr>';
                                        Object.values(row).forEach(val => {
                                            if (typeof val === 'object' && val !== null) {
                                                html += `<td class='border px-2 py-1'>${JSON.stringify(val)}</td>`;
                                            } else {
                                                html += `<td class='border px-2 py-1'>${val}</td>`;
                                            }
                                        });
                                        html += '</tr>';
                                    });
                                    html += '</tbody></table>';
                                } else {
                                    html += '<tr><td>No results</td></tr></thead></table>';
                                }
                            }
                            document.getElementById('report-results').innerHTML = html;
                            // Add copy-to-clipboard logic for JSON
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
                                        } else {
                                            // Fallback for older browsers
                                            const range = document.createRange();
                                            range.selectNodeContents(jsonOutput);
                                            const sel = window.getSelection();
                                            sel.removeAllRanges();
                                            sel.addRange(range);
                                            document.execCommand('copy');
                                            sel.removeAllRanges();
                                            if (copyMsg) {
                                                copyMsg.classList.remove('hidden');
                                                setTimeout(() => { copyMsg.classList.add('hidden'); }, 1500);
                                            }
                                        }
                                    });
                                }
                            }
                            // Hide Run button, show Rerun button
                            document.getElementById('run-btn').style.display = 'none';
                            document.getElementById('rerun-btn').classList.remove('hidden');
                        })
                        .catch(() => {
                            document.getElementById('report-results').innerHTML = '<div class="text-red-600 font-semibold">A server error occurred while running the report.</div>';
                        });
                    }

                    // Rerun button logic
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

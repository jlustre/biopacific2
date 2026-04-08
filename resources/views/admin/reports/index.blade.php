@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Reports</h1>
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.reports.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">Create Report</a>
    </div>
    <div class="p-6 bg-white rounded shadow">
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">Name</th>
                    <th class="px-3 py-2 border">Description</th>
                    <th class="px-3 py-2 border">Active</th>
                    <th class="px-3 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td class="px-3 py-2 border">{{ $report->name }}</td>
                    <td class="px-3 py-2 border">{{ $report->description }}</td>
                    <td class="px-3 py-2 border text-center">{!! $report->is_active ? '<span class=\'text-green-600\'>Yes</span>' : '<span class=\'text-red-600\'>No</span>' !!}</td>
                    <td class="px-3 py-2 border flex gap-2">
                        <button class="px-3 py-1 bg-blue-600 text-white rounded run-report-btn" data-id="{{ $report->id }}">Run</button>
                        <a href="{{ route('admin.reports.edit', $report) }}" class="px-3 py-1 bg-yellow-500 text-white rounded">Edit</a>
                        <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Delete this report?');" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div id="report-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="bg-white p-6 rounded shadow w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4" id="modal-title">Run Report</h2>
            <form id="report-params-form">
                <div id="report-params-fields"></div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="px-4 py-2 mr-2 bg-gray-300 rounded close-modal">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Run</button>
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
            fetch(`/admin/reports/${this.dataset.id}`)
                .then(res => res.json())
                .then(report => {
                    document.getElementById('modal-title').textContent = report.name;
                    const params = report.parameters || [];
                    let fields = '';
                    params.forEach(p => {
                        fields += `<label class='block mt-2'>${p.label || p.name}<input class='form-input mt-1 w-full' name='${p.name}' type='${p.type||'text'}'></label>`;
                    });
                    document.getElementById('report-params-fields').innerHTML = fields;
                    modal.classList.remove('hidden');
                    document.getElementById('report-params-form').onsubmit = function(e) {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        const params = {};
                        for (const [k,v] of formData.entries()) params[k]=v;
                        fetch(`/admin/reports/${report.id}/run`, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                            body: JSON.stringify({params}),
                        })
                        .then(r=>r.json())
                        .then(data => {
                            let html = '<table class="min-w-full border mt-4"><thead><tr>';
                            if(data.results.length) {
                                Object.keys(data.results[0]).forEach(col => html += `<th class='border px-2 py-1'>${col}</th>`);
                                html += '</tr></thead><tbody>';
                                data.results.forEach(row => {
                                    html += '<tr>';
                                    Object.values(row).forEach(val => html += `<td class='border px-2 py-1'>${val}</td>`);
                                    html += '</tr>';
                                });
                                html += '</tbody></table>';
                            } else {
                                html += '<tr><td>No results</td></tr></thead></table>';
                            }
                            document.getElementById('report-results').innerHTML = html;
                        });
                    };
                });
        });
    });
    document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', closeModal));
    modal.addEventListener('click', function(e) { if(e.target === modal) closeModal(); });
});
</script>
@endsection

@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Report: {{ $report->name }}</h1>
        <a href="{{ url('/admin/facility/' . ($facility->id ?? ($report->facility_id ?? 1)) . '/reports') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-700">&larr; Back to Reports List</a>
    </div>
    <div class="mb-2 text-gray-700">
        <span class="font-semibold">Category:</span>
        {{ $report->category ? $report->category->name : '-' }}
    </div>
    <div class="mb-2 text-gray-700">{{ $report->description }}</div>
    <div class="mb-4">
        <strong>SQL Template:</strong>
        <pre class="bg-gray-100 p-2 rounded text-xs border border-teal-600 px-2 py-1">{{ $report->sql_template }}</pre>
    </div>
    <form method="POST" action="{{ route('admin.reports.run', $report->id) }}">
        @csrf
        @if(!empty($report->parameters))
            <div class="mb-4">
                @php
                    $lastParams = old('params', session('last_params', []));
                @endphp
                @foreach($report->parameters as $param)
                    @php
                        $paramName = is_array($param) ? $param['name'] : $param;
                        $paramLabel = is_array($param) ? ($param['label'] ?? $param['name']) : $param;
                        $paramValue = isset($lastParams[$paramName]) ? $lastParams[$paramName] : '';
                    @endphp
                    <label class="block mb-2 font-semibold">{{ $paramLabel }}
                        <input type="text" name="params[{{ $paramName }}]" value="{{ $paramValue }}" class="border border-teal-600 rounded px-2 py-1 w-full" required>
                    </label>
                @endforeach
            </div>
        @endif
        <div class="mb-4">
            <label class="block mb-2 font-semibold">Output Format</label>
            @php
                $selectedFormat = old('output_format', session('output_format', 'table'));
            @endphp
            <select name="output_format" class="border border-teal-600 rounded px-2 py-1 w-full">
                <option value="table" @if($selectedFormat==='table') selected @endif>Table</option>
                <option value="csv" @if($selectedFormat==='csv') selected @endif>CSV</option>
                <option value="json" @if($selectedFormat==='json') selected @endif>JSON</option>
                <option value="pdf" @if($selectedFormat==='pdf') selected @endif>PDF</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Run Report</button>
    </form>
    @if(session('results'))
        <div class="mt-6">
            <h2 class="text-lg font-bold mb-2">Results</h2>
            @if(session('output_format', 'table') === 'csv')
                <div class="mb-2">
                    @php
                        $params = session('last_params', []);
                        $query = 'download=csv';
                        if (is_array($params) && count($params)) {
                            foreach ($params as $k => $v) {
                                $query .= '&params['.urlencode($k).']='.urlencode($v);
                            }
                        }
                    @endphp
                    <a href="{{ url()->current() }}?{!! $query !!}" class="px-3 py-1 bg-green-600 text-white rounded">Download CSV</a>
                </div>
                <pre class="bg-gray-100 p-2 rounded text-xs">{!! session('csv') !!}</pre>
            @elseif(session('output_format') === 'json')
                <div class="flex items-center gap-2 mb-2">
                    <button type="button" id="copy-json-btn" class="px-2 py-1 bg-teal-600 text-white rounded text-xs">Copy JSON</button>
                    <span id="copy-json-message" class="hidden text-green-600 text-xs font-semibold">Copied!</span>
                </div>
                <pre id="json-output" class="bg-gray-100 p-2 rounded text-xs">{!! json_encode(session('results'), JSON_PRETTY_PRINT) !!}</pre>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var copyBtn = document.getElementById('copy-json-btn');
                    var jsonOutput = document.getElementById('json-output');
                    var copyMsg = document.getElementById('copy-json-message');
                    if (copyBtn && jsonOutput) {
                        copyBtn.addEventListener('click', function() {
                            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                                navigator.clipboard.writeText(jsonOutput.textContent).then(function() {
                                    if (copyMsg) {
                                        copyMsg.classList.remove('hidden');
                                        setTimeout(function() { copyMsg.classList.add('hidden'); }, 1500);
                                    }
                                });
                            } else {
                                // Fallback for older browsers
                                var range = document.createRange();
                                range.selectNodeContents(jsonOutput);
                                var sel = window.getSelection();
                                sel.removeAllRanges();
                                sel.addRange(range);
                                document.execCommand('copy');
                                sel.removeAllRanges();
                                if (copyMsg) {
                                    copyMsg.classList.remove('hidden');
                                    setTimeout(function() { copyMsg.classList.add('hidden'); }, 1500);
                                }
                            }
                        });
                    }
                });
                </script>
            @elseif(session('output_format') === 'pdf')
                <div class="mb-2">
                    @php
                        $params = session('last_params', []);
                        $query = 'download=pdf';
                        if (is_array($params) && count($params)) {
                            foreach ($params as $k => $v) {
                                $query .= '&params['.urlencode($k).']='.urlencode($v);
                            }
                        }
                    @endphp
                    <a href="{{ url()->current() }}?{!! $query !!}" class="px-3 py-1 bg-red-600 text-white rounded" target="_blank">View PDF</a>
                </div>
                <div class="text-gray-600">PDF generated. Click above to view.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 table-auto">
                        <thead>
                            <tr>
                                @foreach(array_keys(session('results')[0] ?? []) as $col)
                                    <th class="px-3 py-2 border">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('results') as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td class="px-3 py-2 border">
                                            @if(is_array($cell) || is_object($cell))
                                                {{ json_encode($cell) }}
                                            @else
                                                {{ $cell }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

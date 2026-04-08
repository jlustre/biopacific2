@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Report: {{ $report->name }}</h1>
    <div class="mb-2 text-gray-700">{{ $report->description }}</div>
    <div class="mb-4">
        <strong>SQL Template:</strong>
        <pre class="bg-gray-100 p-2 rounded text-xs">{{ $report->sql_template }}</pre>
    </div>
    <form method="POST" action="{{ route('admin.reports.run', $report->id) }}">
        @csrf
        @if(!empty($report->parameters))
            <div class="mb-4">
                @foreach($report->parameters as $param)
                    <label class="block mb-2 font-semibold">{{ $param }}
                        <input type="text" name="params[{{ $param }}]" class="border rounded px-2 py-1 w-full" required>
                    </label>
                @endforeach
            </div>
        @endif
        <div class="mb-4">
            <label class="block mb-2 font-semibold">Output Format</label>
            <select name="output_format" class="border rounded px-2 py-1 w-full">
                <option value="table" @if(request('output_format')==='table') selected @endif>Table</option>
                <option value="csv" @if(request('output_format')==='csv') selected @endif>CSV</option>
                <option value="json" @if(request('output_format')==='json') selected @endif>JSON</option>
                <option value="pdf" @if(request('output_format')==='pdf') selected @endif>PDF</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Run Report</button>
    </form>
    @if(session('results'))
        <div class="mt-6">
            <h2 class="text-lg font-bold mb-2">Results</h2>
            @if(session('output_format', 'table') === 'csv')
                <div class="mb-2">
                    <a href="{{ route('admin.reports.show', [$report->id, 'download' => 'csv']) }}" class="px-3 py-1 bg-green-600 text-white rounded">Download CSV</a>
                </div>
                <pre class="bg-gray-100 p-2 rounded text-xs">{!! session('csv') !!}</pre>
            @elseif(session('output_format') === 'json')
                <pre class="bg-gray-100 p-2 rounded text-xs">{!! json_encode(session('results'), JSON_PRETTY_PRINT) !!}</pre>
            @elseif(session('output_format') === 'pdf')
                <div class="mb-2">
                    <a href="{{ route('admin.reports.show', [$report->id, 'download' => 'pdf']) }}" class="px-3 py-1 bg-red-600 text-white rounded">Download PDF</a>
                </div>
                <div class="text-gray-600">PDF generated. Click above to download.</div>
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
                                        <td class="px-3 py-2 border">{{ $cell }}</td>
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

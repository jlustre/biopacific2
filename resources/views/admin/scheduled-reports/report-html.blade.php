<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scheduled Report HTML</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #111827;
            margin: 16px;
        }

        h2 {
            font-size: 18px;
            margin: 0 0 6px;
        }

        .meta {
            margin: 0 0 14px;
            color: #4b5563;
        }

        table.results {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 12px;
        }

        table.results th,
        table.results td {
            border: 1px solid #333;
            padding: 6px;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: normal;
        }

        table.results th {
            background: #f0f0f0;
            font-weight: 700;
            text-align: left;
            line-height: 1.25;
        }
    </style>
</head>
<body>
@php
    $rows = collect($results ?? [])->map(fn ($row) => (array) $row);
    $columns = array_keys($rows->first() ?? []);
    $labelFor = static function (string $column): string {
        return \Illuminate\Support\Str::of($column)
            ->replace('_', ' ')
            ->title()
            ->toString();
    };
@endphp
    <h2>{{ $scheduledReport->name }}</h2>
    <p class="meta"><strong>Run At:</strong> {{ $runAt }}</p>
    @if($rows->isNotEmpty())
    <table class="results">
        <thead>
        <tr>
            @foreach($columns as $col)
                <th>{{ $labelFor((string) $col) }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($columns as $col)
                    <td>{{ $row[$col] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
        <p>No results found.</p>
    @endif
</body>
</html>

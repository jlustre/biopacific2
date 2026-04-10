<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px; font-size: 12px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Report: {{ $report->name }}</h2>
    <p>{{ $report->description }}</p>
    <table>
        <thead>
            <tr>
                @foreach(array_keys($results[0] ?? []) as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($results as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>
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
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scheduled Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 12px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>{{ $scheduledReport->name }} (PDF)</h2>
    <p><strong>Run At:</strong> {{ $runAt }}</p>
    @if(!empty($results))
    <table>
        <thead>
        <tr>
            @foreach(array_keys((array)$results[0]) as $col)
                <th>{{ $col }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($results as $row)
            <tr>
                @foreach((array)$row as $val)
                    <td>{{ $val }}</td>
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
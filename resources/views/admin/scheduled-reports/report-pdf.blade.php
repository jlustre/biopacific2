<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scheduled Report PDF</title>
    <style>
        @page {
            margin: 12mm 10mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.25;
            color: #111827;
            margin: 0;
        }

        h2 {
            font-size: 13px;
            line-height: 1.15;
            margin: 0 0 2px;
        }

        .report-header {
            border-bottom: 2px solid #0f766e;
            margin-bottom: 8px;
            padding-bottom: 8px;
            width: 100%;
        }

        .header-table {
            border-collapse: collapse;
            width: 100%;
        }

        .header-table td {
            border: 0;
            vertical-align: top;
        }

        .logo-cell {
            padding-right: 10px;
            white-space: nowrap;
            width: 1%;
        }

        .logo-cell img {
            display: block;
            height: auto;
            width: 54px;
        }

        .brand-name {
            color: #0f766e;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .meta-table {
            background: #f3f4f6;
            border: 0.5px solid #cbd5e1;
            border-collapse: collapse;
            margin-bottom: 8px;
            width: 100%;
        }

        .meta-table td {
            border: 0;
            color: #374151;
            font-size: 8px;
            padding: 4px 6px;
            vertical-align: top;
        }

        .meta-label {
            color: #0f766e;
            display: block;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta-value {
            display: block;
            font-weight: 600;
            margin-top: 1px;
        }

        table.results {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }

        table.results th,
        table.results td {
            border: 0.5px solid #9ca3af;
            padding: 3px 4px;
            font-size: 7.5px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: normal;
            hyphens: auto;
        }

        table.results th {
            background: #e5e7eb;
            color: #111827;
            font-weight: 700;
            text-align: left;
            line-height: 1.15;
            text-transform: uppercase;
        }

        table.results td {
            line-height: 1.25;
        }

        .empty {
            border: 1px solid #d1d5db;
            color: #6b7280;
            margin-top: 12px;
            padding: 14px;
            text-align: center;
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
    $logoPath = public_path('images/bplogo.png');
    $hasLogo = is_string($logoPath) && file_exists($logoPath);
@endphp
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($hasLogo)
                        <img src="{{ $logoPath }}" alt="Bio-Pacific logo">
                    @endif
                </td>
                <td>
                    <div class="brand-name">Bio-Pacific HR Management</div>
                    <h2>{{ $scheduledReport->name }}</h2>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">Run At</span>
                <span class="meta-value">{{ $runAt }}</span>
            </td>
            <td>
                <span class="meta-label">Format</span>
                <span class="meta-value">PDF{{ ($scheduledReport->pdf_orientation ?? 'P') === 'L' ? ' · Landscape' : ' · Portrait' }}</span>
            </td>
        </tr>
    </table>

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
        <p class="empty">No results found.</p>
    @endif
</body>
</html>

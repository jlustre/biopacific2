@php
    $pdfOrientation = $pdfOrientation ?? 'portrait';
    $isLandscape = $pdfOrientation === 'landscape';
    $rows = collect($results ?? [])->map(fn ($row) => (array) $row);
    $columns = array_keys($rows->first() ?? []);
    $generatedAt = $generatedAt ?? now();
    $generatedBy = $generatedBy ?? 'System';
    $dateScope = $dateScope ?? 'All available records';
    $logoPath = $logoPath ?? public_path('images/bplogo.png');
    $hasLogo = is_string($logoPath) && file_exists($logoPath);
    $facilityNameFromRows = $rows
        ->pluck('facility_name')
        ->filter(fn ($value) => is_string($value) && trim($value) !== '')
        ->map(fn ($value) => trim($value))
        ->unique()
        ->values();
    $facilityNameFromTitle = null;

    if (str_contains((string) $report->name, 'Expiring Licenses & Certifications - ')) {
        $facilityNameFromTitle = trim((string) \Illuminate\Support\Str::after((string) $report->name, 'Expiring Licenses & Certifications - '));
    }

    $facilityDisplayName = $facilityNameFromRows->count() === 1
        ? $facilityNameFromRows->first()
        : $facilityNameFromTitle;
    $isFacilitySpecificReport = is_string($facilityDisplayName) && $facilityDisplayName !== '';

    if ($isFacilitySpecificReport) {
        $columns = array_values(array_filter($columns, fn ($column) => $column !== 'facility_name'));
    }
    $headerLabels = [
        'source' => 'Source',
        'facility_name' => 'Facility',
        'employee_num' => 'Emp #',
        'employee_name' => 'Employee',
        'document_name' => 'Document',
        'credential_number' => 'Cred #',
        'issuing_authority' => 'Issuer',
        'issue_date' => 'Issued',
        'expiration_date' => 'Expires',
        'days_until_expiration' => 'Days',
        'expiration_status' => 'Status',
        'verification_status' => 'Verified',
        'id' => 'ID',
        'name' => 'Name',
        'description' => 'Description',
        'domain' => 'Domain',
        'is_active' => 'Active',
    ];
    $labelFor = function (string $column) use ($headerLabels) {
        if (isset($headerLabels[$column])) {
            return $headerLabels[$column];
        }

        return \Illuminate\Support\Str::of($column)
            ->replace('_', ' ')
            ->title()
            ->toString();
    };
    $classFor = function (string $column) {
        return match ($column) {
            'facility_name', 'employee_name', 'document_name', 'issuing_authority', 'description' => 'wrap wide',
            'source', 'expiration_status', 'verification_status' => 'wrap medium',
            'employee_num', 'credential_number' => 'wrap narrow',
            'issue_date', 'expiration_date', 'days_until_expiration', 'id', 'is_active' => 'nowrap tight',
            default => 'wrap',
        };
    };
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: {{ $isLandscape ? '16px 14px' : '22px 18px' }};
        }

        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: {{ $isLandscape ? '8px' : '10px' }};
            line-height: 1.25;
            margin: 0;
        }

        h2 {
            font-size: {{ $isLandscape ? '14px' : '16px' }};
            line-height: 1.15;
            margin: 0 0 4px;
        }

        .description {
            color: #4b5563;
            font-size: {{ $isLandscape ? '8px' : '9px' }};
            line-height: 1.3;
            margin: 0;
        }

        .report-header {
            border-bottom: 2px solid #0f766e;
            margin-bottom: 8px;
            padding-bottom: 8px;
            width: 100%;
        }

        .header-table,
        .meta-table {
            border-collapse: collapse;
            width: 100%;
        }

        .meta-table,
        .results-table {
            table-layout: fixed;
        }

        .header-table td,
        .meta-table td {
            border: 0;
        }

        .logo-cell {
            padding-right: 10px;
            vertical-align: top;
            white-space: nowrap;
            width: 1%;
        }

        .logo-cell img {
            display: block;
            height: auto;
            max-width: none;
            width: {{ $isLandscape ? '58px' : '54px' }};
        }

        .header-content-cell {
            vertical-align: top;
        }

        .facility-header-cell {
            text-align: right;
            vertical-align: top;
            white-space: normal;
            width: {{ $isLandscape ? '31%' : '33%' }};
        }

        .facility-header-label {
            color: #0f766e;
            display: block;
            font-size: {{ $isLandscape ? '7px' : '8px' }};
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .facility-header-name {
            color: #111827;
            display: block;
            font-size: {{ $isLandscape ? '9px' : '10px' }};
            font-weight: 700;
            line-height: 1.2;
            margin-top: 2px;
        }

        .brand-name {
            color: #0f766e;
            font-size: {{ $isLandscape ? '9px' : '10px' }};
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .meta-table {
            background: #f3f4f6;
            border: 0.5px solid #cbd5e1;
            margin-bottom: 8px;
        }

        .meta-table td {
            color: #374151;
            font-size: {{ $isLandscape ? '7.5px' : '8.5px' }};
            padding: 4px 6px;
            vertical-align: top;
        }

        .meta-label {
            color: #0f766e;
            display: block;
            font-size: {{ $isLandscape ? '6.5px' : '7.5px' }};
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta-value {
            display: block;
            font-weight: 600;
            margin-top: 1px;
        }

        .results-table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .results-table th,
        .results-table td {
            border: 0.5px solid #9ca3af;
            padding: {{ $isLandscape ? '2px 3px' : '3px 4px' }};
            vertical-align: top;
        }

        .results-table th {
            background: #e5e7eb;
            color: #111827;
            font-size: {{ $isLandscape ? '7px' : '8px' }};
            font-weight: 700;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .results-table td {
            font-size: {{ $isLandscape ? '7px' : '9px' }};
        }

        .wrap {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .nowrap {
            white-space: nowrap;
        }

        .wide {
            width: {{ $isLandscape ? '11%' : '14%' }};
        }

        .medium {
            width: {{ $isLandscape ? '8%' : '10%' }};
        }

        .narrow {
            width: {{ $isLandscape ? '6%' : '8%' }};
        }

        .tight {
            text-align: center;
            width: {{ $isLandscape ? '5%' : '7%' }};
        }

        .empty {
            border: 1px solid #d1d5db;
            color: #6b7280;
            padding: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($hasLogo)
                        <img src="{{ $logoPath }}" alt="Bio-Pacific logo">
                    @endif
                </td>
                <td class="header-content-cell">
                    <div class="brand-name">Bio-Pacific HR Management</div>
                    <h2>{{ $report->name }}</h2>
                    @if($report->description)
                        <p class="description">{{ $report->description }}</p>
                    @endif
                </td>
                @if($isFacilitySpecificReport)
                    <td class="facility-header-cell">
                        <span class="facility-header-label">Facility</span>
                        <span class="facility-header-name">{{ $facilityDisplayName }}</span>
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 20%;">
                <span class="meta-label">Run Date</span>
                <span class="meta-value">{{ $generatedAt->format('M j, Y g:i A') }}</span>
            </td>
            <td style="width: 28%;">
                <span class="meta-label">Generated By</span>
                <span class="meta-value">{{ $generatedBy }}</span>
            </td>
            <td style="width: 30%;">
                <span class="meta-label">Date Scope</span>
                <span class="meta-value">{{ $dateScope }}</span>
            </td>
            <td style="width: 10%;">
                <span class="meta-label">Rows</span>
                <span class="meta-value">{{ $rows->count() }}</span>
            </td>
            <td style="width: 12%;">
                <span class="meta-label">Layout</span>
                <span class="meta-value">{{ ucfirst($pdfOrientation) }}</span>
            </td>
        </tr>
    </table>

    @if($rows->isEmpty() || $columns === [])
        <div class="empty">No results found.</div>
    @else
        <table class="results-table">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th class="{{ $classFor($column) }}">{{ $labelFor($column) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($columns as $column)
                            @php
                                $cell = $row[$column] ?? null;
                            @endphp
                            <td class="{{ $classFor($column) }}">
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
    @endif
</body>
</html>

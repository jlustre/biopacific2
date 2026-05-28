<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $sectionLabel }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h2 { margin: 0; font-size: 13px; }
        h3 { margin: 0 0 4px; font-size: 11px; }
        .section { margin-bottom: 10px; }
        .meta-table, .items-table, .form-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .meta-table td, .items-table th, .items-table td, .form-table td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; }
        .meta-label { width: 12%; font-weight: bold; background: #e2e8f0; white-space: nowrap; }
        .meta-value { white-space: nowrap; }
        .items-table th { background: #e2e8f0; text-align: left; font-size: 8px; }
        .items-table .col-item-no { width: 7%; text-align: center; }
        .items-table .col-item { width: 55%; }
        .items-table .col-date { width: 9%; text-align: center; }
        .items-table .col-reviewer { width: 13%; }
        .items-table .col-rating { width: 7%; text-align: center; }
        .items-table td.col-date,
        .items-table td.col-rating { text-align: center; font-size: 8px; }
        .items-table td.col-reviewer,
        .items-table td.col-item-no { font-size: 8px; }
        .parent-row td { background: #dbeafe; font-weight: bold; font-size: 9px; color: #1e3a8a; }
        .subsection-heading-row td { background: #e2e8f0; font-weight: bold; font-size: 11px; color: #0f172a; padding: 6px 4px; }
        .child-row td.col-item { font-size: 9px; }
        .facility-name { font-size: 18px; font-weight: bold; color: #0f172a; margin: 0 0 3px; text-align: center; line-height: 1.2; }
        .competency-title { font-size: 14px; font-weight: bold; color: #1e293b; margin: 0; text-align: center; line-height: 1.25; }
        .meta-employee-name { font-size: 11px; font-weight: bold; }
        .muted { color: #475569; font-size: 9px; }
        .meta-line { display: table; width: 100%; font-size: 11px; color: #334155; }
        .meta-line-left { display: table-cell; text-align: left; }
        .meta-line-right { display: table-cell; text-align: right; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .summary-grid td { border: 1px solid #94a3b8; padding: 4px; text-align: center; font-size: 9px; }
        .summary-label { font-weight: bold; background: #f8fafc; }
        .field-label { font-size: 9px; font-weight: bold; background: #f8fafc; padding: 4px 6px; }
        .field-box { min-height: 64px; background: #f1f5f9; padding: 8px; }
        .field-box.employee { background: #eff6ff; color: #1d4ed8; }
        .signature-value { min-height: 52px; background: #fff; padding: 8px 6px 28px; vertical-align: bottom; }
        .signature-label { font-size: 8px; font-weight: bold; background: #f8fafc; padding: 4px 6px; }
        .pdf-header { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .pdf-header td { vertical-align: middle; }
        .pdf-header-logo { width: 84px; }
        .pdf-header-logo img { width: 70px; height: auto; display: block; }
        .pdf-header-center { text-align: center; vertical-align: middle; padding: 0 8px; }
        .pdf-header-spacer { width: 84px; }
    </style>
</head>
<body>
    @php
        $sectionSummary = $sectionSummary ?? [];
        $signatureBlock = $signatureBlock ?? [];
        $employeeName = trim(($employee->last_name ?? '').', '.($employee->first_name ?? '').(($employee->middle_name ?? null) ? ' '.$employee->middle_name : ''), ', ');
        $ratingCounts = collect($items ?? [])
            ->filter(fn ($item) => empty($item['is_parent']))
            ->reduce(function (array $carry, array $item) {
                $rating = strtoupper(trim((string) ($item['rating'] ?? '')));
                if (isset($carry[$rating])) {
                    $carry[$rating]++;
                }

                return $carry;
            }, ['E' => 0, 'S' => 0, 'U' => 0, 'N' => 0]);
        $unsatisfactoryCommentItems = collect($items ?? [])
            ->filter(fn ($item) => empty($item['is_parent']))
            ->filter(fn ($item) => strtoupper(trim((string) ($item['rating'] ?? ''))) === 'U')
            ->filter(fn ($item) => trim((string) ($item['comments'] ?? '')) !== '')
            ->values();
        $isTracheostomySection = ($sectionLabel ?? '') === 'TRACHEOSTOMY CARE';
        $hasTracheostomyEquipment = $isTracheostomySection
            && collect($items ?? [])->contains(fn (array $item) => ! empty($item['skip_item_number']));
        $itemCounter = 0;
        $numberedItems = collect($items ?? [])->map(function (array $item) use (&$itemCounter) {
            if (! empty($item['is_parent'])
                || ! empty($item['is_subsection_heading'])
                || ! empty($item['skip_item_number'])) {
                $item['item_no'] = '';

                return $item;
            }

            $itemCounter++;
            $item['item_no'] = str_pad((string) $itemCounter, 2, '0', STR_PAD_LEFT);

            return $item;
        });
        $commentReferences = $numberedItems
            ->filter(fn (array $item) => empty($item['is_parent']) && trim((string) ($item['comments'] ?? '')) !== '')
            ->values();
        $totalRateableCount = isset($totalRateableOverride) && is_numeric($totalRateableOverride) && (int) $totalRateableOverride > 0
            ? (int) $totalRateableOverride
            : $numberedItems
                ->filter(fn (array $item) => empty($item['is_parent']))
                ->count();
        $logoPath = public_path('images/bplogo.png');
        $hasLogo = is_string($logoPath) && file_exists($logoPath);
    @endphp

    <div class="section">
        <table class="pdf-header">
            <tr>
                <td class="pdf-header-logo">
                    @if($hasLogo)
                    <img src="{{ $logoPath }}" alt="Bio-Pacific logo">
                    @endif
                </td>
                <td class="pdf-header-center">
                    @if(!empty($facilityName))
                    <div class="facility-name">{{ $facilityName }}</div>
                    @endif
                    <div class="competency-title">{{ $sectionLabel }}</div>
                </td>
                <td class="pdf-header-spacer"></td>
            </tr>
        </table>
        <div class="meta-line" style="margin-top: 2px;">
            <span class="meta-line-left">Assessment Period: {{ $periodLabel ?? 'N/A' }}</span>
            <span class="meta-line-right">Generated: {{ now()->format('m-d-y') }}</span>
        </div>
    </div>

    <div class="section">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Employee</td>
                <td class="meta-employee-name meta-value">{{ $employeeName }}</td>
                <td class="meta-label">Title/Position</td>
                <td class="meta-value">{{ $signatureBlock['employee_title'] ?? ($assessment->employee_title ?? '') }}</td>
                <td class="meta-label">Status</td>
                <td class="meta-value">{{ ucwords(str_replace('_', ' ', (string) ($assessment->status ?? 'draft'))) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="muted" style="margin-bottom: 4px;">
            <strong>Average Legend:</strong> Below 1.5 = Unsatisfactory | 1.5 to 2.49 = Satisfactory | 2.5 and above = Excellent
        </div>
        <table class="summary-grid">
            <tr>
                <td class="summary-label">Items Rated</td>
                <td class="summary-label">Total Points</td>
                <td class="summary-label">Average</td>
                <td class="summary-label">Overall</td>
            </tr>
            <tr>
                <td>{{ ($ratedCount ?? 0).'/'.$totalRateableCount }}</td>
                <td>{{ $sectionSummary['total_score'] ?? 0 }}</td>
                <td>{{ is_numeric($sectionSummary['average_score'] ?? null) ? number_format((float) $sectionSummary['average_score'], 2) : ($sectionSummary['average_score'] ?? '0.00') }}</td>
                <td>{{ $sectionSummary['overall_rating'] ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        @if($isTracheostomySection && $hasTracheostomyEquipment)
        <h3>Equipment / Supplies</h3>
        @elseif(! $isTracheostomySection || ! $hasTracheostomyEquipment)
        <h3>Competency Items</h3>
        @endif
        @if(! $isTracheostomySection || ! $hasTracheostomyEquipment)
        <div class="muted" style="margin-bottom: 4px;">
            <strong>Rating Legend:</strong> E = Excellent (3) | S = Satisfactory (2) | U = Unsatisfactory (1) | N = Not Applicable
        </div>
        @endif
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-item-no">Item No.</th>
                    <th class="col-item">Item</th>
                    <th class="col-date">Date</th>
                    <th class="col-reviewer">Reviewer</th>
                    <th class="col-rating">Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach($numberedItems as $item)
                    @if(!empty($item['is_subsection_heading']))
                    <tr class="subsection-heading-row">
                        <td colspan="5">{{ $item['item_label'] ?? '' }}</td>
                    </tr>
                    @if(!empty($item['with_rating_legend']))
                    <tr>
                        <td colspan="5" class="muted" style="font-size: 9px; padding: 4px; border: 1px solid #94a3b8;">
                            <strong>Rating Legend:</strong> E = Excellent (3) | S = Satisfactory (2) | U = Unsatisfactory (1) | N = Not Applicable
                        </td>
                    </tr>
                    @endif
                    @elseif(!empty($item['is_parent']))
                    <tr class="parent-row">
                        <td class="col-item-no"></td>
                        <td class="col-item" colspan="4" style="padding-left: {{ 4 + (($item['indent_level'] ?? 0) * 8) }}px;">
                            {{ $item['item_label'] ?? '' }}
                        </td>
                    </tr>
                    @else
                    <tr class="child-row">
                        <td class="col-item-no">{{ $item['item_no'] ?? '' }}</td>
                        <td class="col-item" style="padding-left: {{ 4 + (($item['indent_level'] ?? 0) * 8) }}px;">{{ $item['item_label'] ?? '' }}</td>
                        <td class="col-date">{{ $item['review_date'] ?? '' }}</td>
                        <td class="col-reviewer">{{ $item['reviewer_name'] ?? '' }}</td>
                        <td class="col-rating">{{ $item['rating'] ?? '' }}</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Rating Summary</h3>
        <table class="summary-grid">
            <tr>
                <td class="summary-label">Excellent (E)</td>
                <td class="summary-label">Satisfactory (S)</td>
                <td class="summary-label">Unsatisfactory (U)</td>
                <td class="summary-label">Not Applicable (N)</td>
            </tr>
            <tr>
                <td>{{ $ratingCounts['E'] }}</td>
                <td>{{ $ratingCounts['S'] }}</td>
                <td>{{ $ratingCounts['U'] }}</td>
                <td>{{ $ratingCounts['N'] }}</td>
            </tr>
        </table>
    </div>

    @if($commentReferences->isNotEmpty())
    <div class="section">
        <h3>Item Comments Reference</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Item No.</th>
                    <th style="width: 82%;">Corresponding Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($commentReferences as $item)
                <tr>
                    <td>Item No. {{ $item['item_no'] ?? '' }}</td>
                    <td>{!! nl2br(e($item['comments'] ?? '')) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="section">
        <table class="form-table">
            <tr>
                <td class="field-label" colspan="3">REVIEWER COMMENTS</td>
            </tr>
            <tr>
                <td class="field-box" colspan="3">{!! nl2br(e($signatureBlock['reviewer_comments'] ?? '')) !!}</td>
            </tr>
            <tr>
                <td class="field-label" colspan="3">EMPLOYEE COMMENTS</td>
            </tr>
            <tr>
                <td class="field-box employee" colspan="3">{!! nl2br(e($signatureBlock['employee_comments'] ?? '')) !!}</td>
            </tr>
            <tr>
                <td class="signature-label">REVIEWER NAME/SIGNATURE</td>
                <td class="signature-label">REVIEWER TITLE</td>
                <td class="signature-label">REVIEW SIGN DATE</td>
            </tr>
            <tr>
                <td class="signature-value">{{ $signatureBlock['reviewer_name'] ?? '' }}</td>
                <td class="signature-value">{{ $signatureBlock['reviewer_title'] ?? '' }}</td>
                <td class="signature-value">{{ $signatureBlock['review_sign_date'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="signature-label">EMPLOYEE NAME/SIGNATURE</td>
                <td class="signature-label">EMPLOYEE TITLE</td>
                <td class="signature-label">EMPLOYEE SIGN DATE</td>
            </tr>
            <tr>
                <td class="signature-value">{{ $signatureBlock['employee_name'] ?? '' }}</td>
                <td class="signature-value">{{ $signatureBlock['employee_title'] ?? '' }}</td>
                <td class="signature-value">{{ $signatureBlock['employee_sign_date'] ?? '' }}</td>
            </tr>
        </table>
    </div>
</body>
</html>

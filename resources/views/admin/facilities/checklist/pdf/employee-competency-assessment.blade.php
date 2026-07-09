<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Competency Assessment</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h2, h3 { margin: 0 0 6px; }
        h2 { font-size: 14px; }
        h3 { font-size: 11px; }
        .section { margin-bottom: 12px; }
        .meta-table, .items-table, .summary-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .meta-table td, .items-table th, .items-table td, .summary-grid td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; }
        .meta-label { width: 18%; font-weight: bold; background: #e2e8f0; }
        .items-table th { background: #e2e8f0; text-align: left; font-size: 8px; }
        .items-table .col-section { width: 18%; font-size: 8px; }
        .items-table .col-item { width: 42%; font-size: 8px; }
        .items-table .col-rating { width: 8%; text-align: center; font-size: 8px; }
        .items-table .col-date { width: 12%; text-align: center; font-size: 8px; }
        .items-table .col-comments { width: 20%; font-size: 8px; }
        .summary-label { font-weight: bold; background: #f8fafc; text-align: center; font-size: 9px; }
        .summary-grid td { text-align: center; font-size: 9px; }
        .muted { color: #475569; font-size: 9px; }
        .facility-name { font-size: 16px; font-weight: bold; color: #0f172a; margin: 0 0 3px; text-align: center; }
        .report-title { font-size: 13px; font-weight: bold; color: #1e293b; margin: 0; text-align: center; }
        .field-label { font-size: 9px; font-weight: bold; background: #f8fafc; padding: 4px 6px; }
        .field-box { min-height: 48px; background: #f1f5f9; padding: 8px; }
        .signature-name-cell { vertical-align: middle; text-align: left; }
        .signature-image-wrap { text-align: center; margin-top: 4px; }
        .signature-image { max-height: 42px; max-width: 180px; display: inline-block; }
    </style>
</head>
<body>
    @php
        use App\Support\PartFPerformanceScoring;
        use App\Support\PartGCompetencyScoring;

        $snapshot = $snapshot ?? [];
        $summary = $snapshot['summary'] ?? [];
        $form = $snapshot['form'] ?? [];
        $items = collect($snapshot['items'] ?? []);
        $employeeName = trim((string) ($form['employee_name'] ?? ''));
        if ($employeeName === '') {
            $employeeName = trim(($employee->last_name ?? '').', '.($employee->first_name ?? '').(($employee->middle_name ?? null) ? ' '.$employee->middle_name : ''), ', ');
        }
        $averageScore = is_numeric($summary['average_score'] ?? $assessment->average_score ?? null)
            ? (float) ($summary['average_score'] ?? $assessment->average_score)
            : null;
        $overallRatingLabel = (string) ($summary['overall_rating'] ?? $assessment->overall_rating ?? 'N/A');
        $overallRatingCode = $overallRatingCode
            ?? PartFPerformanceScoring::overallRatingCode($overallRatingLabel, $averageScore);
        $ratingCountDefaults = ['E' => 0, 'M' => 0, 'B' => 0];
        $ratingCounts = $items->reduce(function (array $carry, $item) {
            if (! is_array($item)) {
                return $carry;
            }

            $rating = PartGCompetencyScoring::normalizeItemRating($item['rating'] ?? null);
            if ($rating !== null && isset($carry[$rating])) {
                $carry[$rating]++;
            }

            return $carry;
        }, $ratingCountDefaults);
        $belowExpectationsComments = $items
            ->filter(function ($item) {
                if (! is_array($item)) {
                    return false;
                }

                return PartGCompetencyScoring::normalizeItemRating($item['rating'] ?? null) === 'B'
                    && trim((string) ($item['comments'] ?? '')) !== '';
            })
            ->values();
    @endphp

    <div class="section">
        @if(!empty($facilityName))
        <div class="facility-name">{{ $facilityName }}</div>
        @endif
        <div class="report-title">Competency Assessment</div>
        <div class="muted" style="margin-top: 4px;">
            Assessment Period: {{ $periodLabel ?? ($period ? ($period->date_from . ' to ' . $period->date_to) : 'N/A') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Generated: {{ now()->format('m-d-y') }}
        </div>
        <div class="muted">
            Status: {{ $assessmentStatusLabel ?? \App\Support\AssessmentWorkflowStatus::label($assessment->workflowStatus()) }}
        </div>
    </div>

    <div class="section">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Employee</td>
                <td>{{ $employeeName }}</td>
                <td class="meta-label">Employee Title</td>
                <td>{{ $form['employee_title'] ?? ($assessment->employee_title ?? '') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Reviewer</td>
                <td>{{ $form['reviewer_name'] ?? ($assessment->reviewer_name ?? '') }}</td>
                <td class="meta-label">Reviewer Title</td>
                <td>{{ $form['reviewer_title'] ?? ($assessment->reviewer_title ?? '') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Review Date</td>
                <td>{{ $form['review_date'] ?? optional($assessment->review_date)->toDateString() }}</td>
                <td class="meta-label">Employee Signed</td>
                <td>{{ $form['employee_date'] ?? optional($assessment->employee_signed_at)->toDateString() }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Competency Evaluation Summary</h3>
        @include('admin.facilities.checklist.partials.part-f-rating-legend', [
            'legendVariant' => 'pdf',
            'showOverallFooter' => true,
            'overallRatingLabel' => $overallRatingLabel,
            'overallAverage' => $averageScore,
            'overallRatingCode' => $overallRatingCode,
        ])
        @php
            $totalSnapshotItems = $items->count();
            $itemsRatedCount = $items->filter(fn ($item) => is_array($item) && PartGCompetencyScoring::isValidItemRating($item['rating'] ?? null))->count();
            $earnedPoints = (int) ($summary['total_score'] ?? $assessment->total_score ?? 0);
            $maxPoints = PartGCompetencyScoring::maxPointsForScorableItems($totalSnapshotItems);
        @endphp
        <table class="summary-grid" style="margin-top: 4px;">
            <tr>
                <td class="summary-label">Items Rated</td>
                <td class="summary-label">Total Points</td>
                <td class="summary-label">Average</td>
                <td class="summary-label">Overall</td>
            </tr>
            <tr>
                <td>{{ $itemsRatedCount.'/'.$totalSnapshotItems }}</td>
                <td>{{ $earnedPoints.'/'.$maxPoints }}</td>
                <td>{{ is_numeric($averageScore) ? number_format($averageScore, 2) : ($summary['average_score'] ?? '0.00') }}</td>
                <td>
                    @if($overallRatingCode !== '')
                    <strong>{{ $overallRatingCode }}</strong>
                    @if($overallRatingLabel !== '')
                    — {{ $overallRatingLabel }}
                    @endif
                    @else
                    {{ $overallRatingLabel }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Rating Summary</h3>
        <table class="summary-grid">
            <tr>
                <td class="summary-label">Exceeds (E)</td>
                <td class="summary-label">Meets (M)</td>
                <td class="summary-label">Below (B)</td>
            </tr>
            <tr>
                <td>{{ $ratingCounts['E'] ?? 0 }}</td>
                <td>{{ $ratingCounts['M'] ?? 0 }}</td>
                <td>{{ $ratingCounts['B'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    @if(trim((string) ($form['comments'] ?? $assessment->comments ?? '')) !== '')
    <div class="section">
        <div class="field-label">REVIEWER COMMENTS</div>
        <div class="field-box">{!! nl2br(e($form['comments'] ?? $assessment->comments ?? '')) !!}</div>
    </div>
    @endif

    @if(!empty($form['further_action_required']) || !empty($assessment->further_action_required))
    <div class="section">
        <div class="field-label">FURTHER ACTION REQUIRED</div>
        <div class="field-box">{!! nl2br(e($form['further_action_required'] ?? $assessment->further_action_required ?? '')) !!}</div>
    </div>
    @endif

    <div class="section">
        <h3>Competency Items</h3>
        <div class="muted" style="margin-bottom: 4px;">
            <strong>Item Rating Scale:</strong> {{ PartGCompetencyScoring::itemRatingLegendText() }}
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-section">Section</th>
                    <th class="col-item">Item</th>
                    <th class="col-rating">Rating</th>
                    <th class="col-date">Assessment Date</th>
                    <th class="col-comments">Comments</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                @if(is_array($item))
                <tr>
                    <td class="col-section">{{ $item['section'] ?? '' }}</td>
                    <td class="col-item">{{ ltrim((string) ($item['item_label'] ?? ''), '-') }}</td>
                    <td class="col-rating">{{ PartGCompetencyScoring::normalizeItemRating($item['rating'] ?? null) ?? '' }}</td>
                    <td class="col-date">{{ $item['assessment_date'] ?? '' }}</td>
                    <td class="col-comments">{{ $item['comments'] ?? '' }}</td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" class="muted" style="padding: 8px;">No competency items recorded.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($belowExpectationsComments->isNotEmpty())
    <div class="section">
        <h3>Below Expectations (B) Item Comments</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 24%;">Section / Item</th>
                    <th style="width: 76%;">Corresponding Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($belowExpectationsComments as $item)
                <tr>
                    <td>{{ ($item['section'] ?? '').' — '.ltrim((string) ($item['item_label'] ?? ''), '-') }}</td>
                    <td>{!! nl2br(e($item['comments'] ?? '')) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @php
        $signatureBlock = $signatureBlock ?? [];
    @endphp
    <div class="section">
        <table class="summary-grid">
            <tr>
                <td class="field-label" colspan="2">REVIEWER COMMENTS</td>
            </tr>
            <tr>
                <td colspan="2" class="field-box">{!! nl2br(e($signatureBlock['reviewer_comments'] ?? $form['comments'] ?? $assessment->comments ?? '')) !!}</td>
            </tr>
            <tr>
                <td class="field-label" colspan="2">EMPLOYEE COMMENTS</td>
            </tr>
            <tr>
                <td colspan="2" class="field-box">{!! nl2br(e($signatureBlock['employee_comments'] ?? $assessment->employee_comments ?? '')) !!}</td>
            </tr>
            <tr>
                <td class="summary-label">REVIEWER NAME/SIGNATURE</td>
                <td class="summary-label">REVIEWER TITLE</td>
            </tr>
            <tr>
                <td class="signature-name-cell">
                    <div>{{ $signatureBlock['reviewer_name'] ?? ($form['reviewer_name'] ?? $assessment->reviewer_name ?? '') }}</div>
                    @if(!empty($signatureBlock['reviewer_signature_image_path']))
                    <div class="signature-image-wrap">
                        <img src="{{ $signatureBlock['reviewer_signature_image_path'] }}" alt="Reviewer signature" class="signature-image">
                    </div>
                    @endif
                </td>
                <td>{{ $signatureBlock['reviewer_title'] ?? ($form['reviewer_title'] ?? $assessment->reviewer_title ?? '') }}</td>
            </tr>
            <tr>
                <td class="summary-label">REVIEW SIGN DATE</td>
                <td class="summary-label">EMPLOYEE SIGN DATE</td>
            </tr>
            <tr>
                <td>{{ $signatureBlock['review_sign_date'] ?? '' }}</td>
                <td>{{ $signatureBlock['employee_sign_date'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="summary-label">EMPLOYEE NAME/SIGNATURE</td>
                <td class="summary-label">EMPLOYEE TITLE</td>
            </tr>
            <tr>
                <td class="signature-name-cell">
                    <div>{{ $signatureBlock['employee_name'] ?? ($form['employee_name'] ?? $assessment->employee_name ?? $employeeName) }}</div>
                    @if(!empty($signatureBlock['employee_signature_image_path']))
                    <div class="signature-image-wrap">
                        <img src="{{ $signatureBlock['employee_signature_image_path'] }}" alt="Employee signature" class="signature-image">
                    </div>
                    @endif
                </td>
                <td>{{ $signatureBlock['employee_title'] ?? ($form['employee_title'] ?? $assessment->employee_title ?? '') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>

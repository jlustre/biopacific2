<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Competency Assessment</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h2, h3 { margin: 0 0 8px; }
        .section { margin-bottom: 16px; }
        .meta-table, .items-table { width: 100%; border-collapse: collapse; }
        .meta-table td, .items-table th, .items-table td { border: 1px solid #94a3b8; padding: 6px; vertical-align: top; }
        .meta-label { width: 24%; font-weight: bold; background: #e2e8f0; }
        .items-table th { background: #e2e8f0; text-align: left; }
        .muted { color: #475569; }
    </style>
</head>
<body>
    @php
        $snapshot = $snapshot ?? [];
        $summary = $snapshot['summary'] ?? [];
        $form = $snapshot['form'] ?? [];
        $items = $snapshot['items'] ?? [];
        $employeeName = $form['employee_name'] ?? trim(($employee->last_name ?? '') . ', ' . ($employee->first_name ?? '') . (($employee->middle_name ?? null) ? ' ' . $employee->middle_name : ''));
    @endphp

    <div class="section">
        <h2>Competency Assessment</h2>
        <div class="muted">Assessment Period: {{ $period ? ($period->date_from . ' to ' . $period->date_to) : 'N/A' }}</div>
        <div class="muted">Status: {{ ucwords(str_replace('_', ' ', (string) ($assessment->status ?? 'draft'))) }}</div>
    </div>

    <div class="section">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Employee</td>
                <td>{{ $employeeName }}</td>
                <td class="meta-label">Employee Title</td>
                <td>{{ $form['employee_title'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Reviewer</td>
                <td>{{ $form['reviewer_name'] ?? '' }}</td>
                <td class="meta-label">Reviewer Title</td>
                <td>{{ $form['reviewer_title'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Review Date</td>
                <td>{{ $form['review_date'] ?? '' }}</td>
                <td class="meta-label">Employee Signed</td>
                <td>{{ $form['employee_date'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Total</td>
                <td>{{ $summary['total_score'] ?? 0 }}</td>
                <td class="meta-label">Average / Overall</td>
                <td>{{ $summary['average_score'] ?? '0.00' }} / {{ $summary['overall_rating'] ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Comments</h3>
        <div>{{ $form['comments'] ?? '' }}</div>
    </div>

    @if(!empty($form['further_action_required']))
    <div class="section">
        <h3>Further Action Required</h3>
        <div>{{ $form['further_action_required'] }}</div>
    </div>
    @endif

    <div class="section">
        <h3>Competency Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Item</th>
                    <th>Rating</th>
                    <th>Assessment Date</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['section'] ?? '' }}</td>
                    <td>{{ $item['item_label'] ?? '' }}</td>
                    <td>{{ $item['rating'] ?? '' }}</td>
                    <td>{{ $item['assessment_date'] ?? '' }}</td>
                    <td>{{ $item['comments'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
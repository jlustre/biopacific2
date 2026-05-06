<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BPEmpChecklist;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeePerformanceAssessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class EmployeePerformanceAssessmentController extends Controller {
    protected function revokeLegacyAssessment(string $employeeNum, int $assessmentPeriodId, string $itemKey): bool
    {
        if (str_starts_with($itemKey, 'F_')) {
            $assessment = EmployeePerformanceAssessment::query()
                ->where('employee_num', $employeeNum)
                ->where('assessment_period_id', $assessmentPeriodId)
                ->first();

            if (!$assessment || !$assessment->items) {
                return false;
            }

            $items = json_decode($assessment->items, true) ?: [];
            if (!array_key_exists($itemKey, $items)) {
                return false;
            }

            unset($items[$itemKey]);
            $assessment->items = empty($items) ? null : json_encode($items);
            $assessment->save();

            return true;
        }

        if (str_starts_with($itemKey, 'G_')) {
            $legacyChecklistKey = 'competency::' . substr($itemKey, 2);
            $checklist = BPEmpChecklist::query()
                ->where('employee_num', $employeeNum)
                ->first();

            if (!$checklist || !is_array($checklist->items) || !array_key_exists($legacyChecklistKey, $checklist->items)) {
                return false;
            }

            $items = $checklist->items;
            unset($items[$legacyChecklistKey]);
            $checklist->items = $items;

            $checklist->save();

            return true;
        }

        return false;
    }

    protected function inferAssessmentType(string $itemKey): string
    {
        return str_starts_with($itemKey, 'G_') ? 'competency' : 'performance';
    }

    protected function serializeEntry($entry): ?array
    {
        if (!$entry) {
            return null;
        }

        return [
            'id' => $entry->id,
            'rating' => $entry->rating,
            'verified_dt' => optional($entry->assessment_date)->toDateString(),
            'verified_by' => $entry->assessed_by,
            'comments' => $entry->comments,
            'assessment_type' => $entry->assessment_type,
            'revoked_at' => optional($entry->revoked_at)->format('Y-m-d H:i:s'),
            'revoked_by' => $entry->revoked_by,
        ];
    }

    protected function historyPayload(string $employeeNum, int $assessmentPeriodId, string $itemKey): array
    {
        $entries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->where('item_key', $itemKey)
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get();

        $latest = $entries->first(fn ($entry) => $entry->revoked_at === null);

        return [
            'latest' => $this->serializeEntry($latest),
            'history' => $entries->map(fn ($entry) => $this->serializeEntry($entry))->values()->all(),
        ];
    }


    /**
     * Return all employees reviewed for a given assessment period and facility (AJAX for PART F modal)
     * GET params: assessment_period_id, facility_id
     */
    public function getReviewedEmployees(Request $request)
    {
        $assessmentPeriodId = $request->query('assessment_period_id');
        $facilityId = $request->query('facility_id');
        Log::debug('getReviewedEmployees input', ['assessment_period_id' => $assessmentPeriodId, 'facility_id' => $facilityId]);
        if (!$assessmentPeriodId || !$facilityId) {
            return response()->json(['success' => false, 'message' => 'Missing assessment_period_id or facility_id.'], 400);
        }
        $assessments = \App\Models\EmployeePerformanceAssessment::where('assessment_period_id', $assessmentPeriodId)
            ->get();
        $empIds = $assessments->pluck('employee_num')->unique()->values();
        $employees = \App\Models\BPEmployee::whereIn('employee_num', $empIds)
            ->whereHas('currentAssignment', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->with(['currentAssignment.facility', 'currentAssignment.position', 'currentAssignment.department'])
            ->get();
        Log::debug('getReviewedEmployees found employees', ['count' => $employees->count(), 'employee_nums' => $employees->pluck('employee_num')]);
        $result = $employees->map(function($emp) use ($assessments, $assessmentPeriodId) {
            $assessment = $assessments->firstWhere('employee_num', $emp->employee_num);
            return [
                'employee_num' => $emp->employee_num,
                'name' => trim($emp->last_name . ', ' . $emp->first_name . ($emp->middle_name ? ' ' . $emp->middle_name : '')),
                'position' => $emp->currentAssignment && $emp->currentAssignment->position ? $emp->currentAssignment->position->position_title : '',
                'department' => $emp->currentAssignment && $emp->currentAssignment->department ? $emp->currentAssignment->department->dept_name : '',
                'assessment_date' => $assessment ? $assessment->assessment_date : null,
                'reviewed_by' => $assessment && $assessment->assessed_by ? (\App\Models\User::find($assessment->assessed_by)->name ?? null) : null,
            ];
        })->values();
        Log::debug('getReviewedEmployees result', ['result' => $result]);
        return response()->json(['success' => true, 'employees' => $result]);
    }
    /**
     * Delete an assessment period. If assessments exist, return affected records unless force=1 is set.
     */
    public function destroyPeriod(Request $request, $id)
    {
        $force = $request->query('force', false);
        $period = \App\Models\EmployeeAssessmentPeriod::find($id);
        if (!$period) {
            return response()->json(['success' => false, 'message' => 'Assessment period not found.'], 404);
        }
        $affected = $period->assessments()->get();
        if ($affected->count() > 0 && !$force) {
            // Return affected records for modal display
            $affectedList = $affected->map(function($a) {
                $emp = $a->employee ?? null;
                return [
                    'employee_num' => $a->employee_num,
                    'employee_name' => $emp ? ($emp->last_name . ', ' . $emp->first_name . ($emp->middle_name ? ' ' . $emp->middle_name : '')) : null,
                    'assessment_date' => $a->assessment_date,
                ];
            })->toArray();
            return response()->json([
                'success' => false,
                'affected' => $affectedList,
                'message' => 'There are employee assessments linked to this period. Deleting will remove them. Continue?',
            ], 200);
        }
        // Delete all related assessments if force
        foreach ($affected as $a) {
            $a->delete();
        }
        $period->delete();
        return response()->json(['success' => true, 'message' => 'Assessment period deleted.']);
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_num' => 'required',
                'item_key' => 'required',
                'item_label' => 'nullable|string|max:255',
                'source_item_id' => 'nullable|integer',
                'rating' => 'required|in:E,S,U,N',
                'assessment_date' => 'required|date',
                'comments' => 'nullable|string',
                'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }
            throw $e;
        }

        // Keep the period-level wrapper record for signatures and review metadata.
        $assessment = EmployeePerformanceAssessment::firstOrCreate(
            [
                'employee_num' => $validated['employee_num'],
                'assessment_period_id' => $validated['assessment_period_id'],
            ],
            [
                'items' => json_encode([]),
                'assessment_date' => $validated['assessment_date'],
                'assessed_by' => Auth::id(),
            ]
        );

        $assessment->assessment_date = $validated['assessment_date'];
        $assessment->assessed_by = Auth::id();
        $assessment->save();

        EmployeeAssessmentItemEntry::create([
            'employee_num' => $validated['employee_num'],
            'assessment_period_id' => $validated['assessment_period_id'],
            'assessment_type' => $this->inferAssessmentType($validated['item_key']),
            'item_key' => $validated['item_key'],
            'item_label' => $validated['item_label'] ?? null,
            'source_item_id' => $validated['source_item_id'] ?? null,
            'rating' => $validated['rating'],
            'assessment_date' => $validated['assessment_date'],
            'assessed_by' => Auth::id(),
            'comments' => $validated['comments'] ?? null,
        ]);

        $historyPayload = $this->historyPayload(
            (string) $validated['employee_num'],
            (int) $validated['assessment_period_id'],
            (string) $validated['item_key']
        );

        return response()->json([
            'success' => true,
            'data' => [
                'latest' => $historyPayload['latest'],
                'history' => $historyPayload['history'],
            ]
        ]);
    }

    public function revoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_num' => 'required',
                'item_key' => 'required',
                'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            ]);

            Log::debug('Revoke request', $validated);

            $entry = EmployeeAssessmentItemEntry::query()
                ->where('employee_num', $validated['employee_num'])
                ->where('assessment_period_id', $validated['assessment_period_id'])
                ->where('item_key', $validated['item_key'])
                ->whereNull('revoked_at')
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->first();
            if (!$entry) {
                $legacyRevoked = $this->revokeLegacyAssessment(
                    (string) $validated['employee_num'],
                    (int) $validated['assessment_period_id'],
                    (string) $validated['item_key']
                );

                if (!$legacyRevoked) {
                    Log::warning('Assessment not found for revoke', $validated);
                    return response()->json(['success' => false, 'message' => 'Assessment not found for employee_num: ' . $validated['employee_num'] . ', assessment_period_id: ' . $validated['assessment_period_id']], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'latest' => null,
                        'history' => [],
                    ]
                ]);
            }

            $entry->revoked_at = now();
            $entry->revoked_by = Auth::id();
            $entry->save();

            $historyPayload = $this->historyPayload(
                (string) $validated['employee_num'],
                (int) $validated['assessment_period_id'],
                (string) $validated['item_key']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'latest' => $historyPayload['latest'],
                    'history' => $historyPayload['history'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in revoke: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new assessment period (for New Period modal)
     */
    public function createPeriod(Request $request)
    {
        // Support JSON requests
        if (0 === strpos($request->header('Content-Type'), 'application/json')) {
            $request->merge(json_decode($request->getContent(), true) ?? []);
        }
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'review_type' => 'required|string|max:2',
        ]);

        $period = new \App\Models\EmployeeAssessmentPeriod();
        $period->date_from = $validated['date_from'];
        $period->date_to = $validated['date_to'];
        $period->review_type = $validated['review_type'];
        $period->created_by = Auth::id();
        // Set period_year from date_from
        $period->period_year = date('Y', strtotime($validated['date_from']));
        // Optionally set period_sequence to 0 (or calculate as needed)
        $period->period_sequence = 0;
        $period->save();

        return response()->json([
            'success' => true,
            'message' => 'Assessment period created.',
            'data' => $period,
        ]);
    }
}

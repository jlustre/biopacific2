<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeePerformanceAssessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class EmployeePerformanceAssessmentController extends Controller {

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
        $empIds = $assessments->pluck('emp_id')->unique()->values();
        $employees = \App\Models\BPEmployee::whereIn('emp_id', $empIds)
            ->whereHas('currentAssignment', function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            })
            ->with(['currentAssignment.facility', 'currentAssignment.position', 'currentAssignment.department'])
            ->get();
        Log::debug('getReviewedEmployees found employees', ['count' => $employees->count(), 'emp_ids' => $employees->pluck('emp_id')]);
        $result = $employees->map(function($emp) use ($assessments, $assessmentPeriodId) {
            $assessment = $assessments->firstWhere('emp_id', $emp->emp_id);
            return [
                'emp_id' => $emp->emp_id,
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
                    'emp_id' => $a->emp_id,
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
                'emp_id' => 'required',
                'item_key' => 'required',
                'rating' => 'required',
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

        // Find or create the assessment record for this employee and assessment period
        $assessment = EmployeePerformanceAssessment::firstOrCreate(
            [
                'emp_id' => $validated['emp_id'],
                'assessment_period_id' => $validated['assessment_period_id'],
            ],
            [
                'items' => json_encode([]),
                'assessment_date' => $validated['assessment_date'],
                'assessed_by' => Auth::id(),
            ]
        );

        // Decode items JSON, update the relevant item, and re-save
        $items = $assessment->items ? json_decode($assessment->items, true) : [];
        $items[$validated['item_key']] = [
            'rating' => $validated['rating'],
            'verified_dt' => $validated['assessment_date'],
            'verified_by' => Auth::id(),
            'comments' => $validated['comments'] ?? null,
        ];
        $assessment->items = json_encode($items);
        $assessment->assessment_date = $validated['assessment_date'];
        $assessment->assessed_by = Auth::id();
        $assessment->comments = $validated['comments'] ?? null;
        $assessment->save();

        // Prepare response structure for JS
        $itemsArr = $assessment->items ? json_decode($assessment->items, true) : [];
        $assessedBy = $assessment->assessed_by;
        $assessmentDate = $assessment->assessment_date;
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $itemsArr,
                'assessed_by' => $assessedBy,
                'assessment_date' => $assessmentDate,
            ]
        ]);
    }

    public function revoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'emp_id' => 'required',
                'item_key' => 'required',
                'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            ]);

            Log::debug('Revoke request', $validated);

            $assessment = EmployeePerformanceAssessment::where('emp_id', $validated['emp_id'])
                ->where('assessment_period_id', $validated['assessment_period_id'])
                ->first();
            if (!$assessment) {
                Log::warning('Assessment not found for revoke', $validated);
                return response()->json(['success' => false, 'message' => 'Assessment not found for emp_id: ' . $validated['emp_id'] . ', assessment_period_id: ' . $validated['assessment_period_id']], 404);
            }

            $items = $assessment->items ? json_decode($assessment->items, true) : [];
            if (!array_key_exists($validated['item_key'], $items)) {
                Log::warning('Item key not found in assessment items', ['item_key' => $validated['item_key'], 'items' => $items]);
            }
            unset($items[$validated['item_key']]);
            $assessment->items = json_encode($items);
            $assessment->save();

            $itemsArr = $assessment->items ? json_decode($assessment->items, true) : [];
            $assessedBy = $assessment->assessed_by;
            $assessmentDate = $assessment->assessment_date;
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $itemsArr,
                    'assessed_by' => $assessedBy,
                    'assessment_date' => $assessmentDate,
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
}

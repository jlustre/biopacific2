<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeePerformanceAssessment;
use Illuminate\Support\Facades\Auth;

class EmployeePerformanceAssessmentController extends Controller
{
    /**
     * Create a new assessment period for an employee (if not exists).
     * Expects: emp_id, eff_date (YYYY-MM-DD)
     * Returns: success, message
     */
    public function createPeriod(Request $request)
    {
        $validated = $request->validate([
            'emp_id' => 'required',
            'eff_date' => 'required|date',
        ]);

        // Check if period already exists
        $exists = EmployeePerformanceAssessment::where('emp_id', $validated['emp_id'])
            ->where('eff_date', $validated['eff_date'])
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment period already exists.'
            ], 409);
        }

        // Create new period (empty items)
        $assessment = EmployeePerformanceAssessment::create([
            'emp_id' => $validated['emp_id'],
            'eff_date' => $validated['eff_date'],
            'items' => json_encode([]),
            'assessment_date' => null,
            'assessed_by' => null,
            'comments' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assessment period created.',
            'data' => [
                'id' => $assessment->id,
                'eff_date' => $assessment->eff_date,
            ]
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'emp_id' => 'required',
            'item_key' => 'required',
            'rating' => 'required',
            'assessment_date' => 'required|date',
            'comments' => 'nullable|string',
            'eff_date' => 'required|date',
        ]);

        // Find or create the assessment record for this employee and period
        $assessment = EmployeePerformanceAssessment::firstOrCreate(
            [
                'emp_id' => $validated['emp_id'],
                'eff_date' => $validated['eff_date'],
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
        $validated = $request->validate([
            'emp_id' => 'required',
            'item_key' => 'required',
            'eff_date' => 'required|date',
        ]);

        $assessment = EmployeePerformanceAssessment::where('emp_id', $validated['emp_id'])
            ->where('eff_date', $validated['eff_date'])
            ->first();
        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Assessment not found.'], 404);
        }

        $items = $assessment->items ? json_decode($assessment->items, true) : [];
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
    }
}

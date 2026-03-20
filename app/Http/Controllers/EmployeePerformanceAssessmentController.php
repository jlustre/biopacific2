<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeePerformanceAssessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmployeePerformanceAssessmentController extends Controller
{
    /**
     * Create a new assessment period for an employee (if not exists).
     * Expects: emp_id, assessment_period_id
     * Returns: success, message
     */
    public function createPeriod(Request $request)
    {
        try {
            $validated = $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
            ]);

            $force = $request->input('force', false);

            // Check for overlapping periods
            $overlap = \App\Models\EmployeeAssessmentPeriod::where(function($q) use ($validated) {
                $q->where('date_from', '<=', $validated['date_to'])
                  ->where('date_to', '>=', $validated['date_from']);
            })->first();
            if ($overlap && !$force) {
                return response()->json([
                    'success' => false,
                    'warning' => true,
                    'message' => 'The selected date range overlaps with an existing assessment period (ID: ' . $overlap->id . ', ' . $overlap->date_from . ' to ' . $overlap->date_to . '). Proceed anyway?',
                ], 200);
            }

            // Check if period already exists for this exact date range
            $period = \App\Models\EmployeeAssessmentPeriod::where('date_from', $validated['date_from'])
                ->where('date_to', $validated['date_to'])
                ->first();
            if ($period) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment period already exists.'
                ], 409);
            }

            // Create new assessment period (global)
            $period = \App\Models\EmployeeAssessmentPeriod::create([
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'created_by' => Auth::id(),
                'period_year' => date('Y', strtotime($validated['date_from'])),
                'period_sequence' => 1, // You may want to implement sequence logic if needed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment period created.',
                'data' => [
                    'assessment_period_id' => $period->id,
                    'date_from' => $period->date_from,
                    'date_to' => $period->date_to,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create assessment period: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assessment period: ' . $e->getMessage(),
            ], 500);
        }
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
        $validated = $request->validate([
            'emp_id' => 'required',
            'item_key' => 'required',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
        ]);

        $assessment = EmployeePerformanceAssessment::where('emp_id', $validated['emp_id'])
            ->where('assessment_period_id', $validated['assessment_period_id'])
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

<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use App\Models\EmployeePerformanceSectionComment;
use Illuminate\Support\Facades\Auth;

class EmployeePerformanceSectionCommentController extends Controller
{
    /**
     * Store or update a section comment for an employee and assessment period.
     * Expects: emp_id, assessment_period_id, section_label, comment
     * Returns: success, message, comment
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'emp_id' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'doc_type_id' => 'required|integer|exists:doc_types,id',
            'comment' => 'nullable|string',
        ]);

        $comment = EmployeePerformanceSectionComment::updateOrCreate(
            [
                'emp_id' => $validated['emp_id'],
                'assessment_period_id' => $validated['assessment_period_id'],
                'doc_type_id' => $validated['doc_type_id'],
            ],
            [
                'comment' => $validated['comment'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Section comment saved.',
            'data' => $comment,
            'input' => $validated,
        ]);
    }

    /**
     * Retrieve a section comment for an employee and assessment period.
     * Expects: emp_id, assessment_period_id, section_label
     * Returns: success, comment
     */
    public function show(Request $request)
        {

        $validated = $request->validate([
            'emp_id' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'doc_type_id' => 'required|integer|exists:doc_types,id',
            'item_key' => 'required|string',
        ]);

        // Fetch from employee_performance_assessments table
        $assessment = \App\Models\EmployeePerformanceAssessment::where([
            'emp_id' => $validated['emp_id'],
            'assessment_period_id' => $validated['assessment_period_id'],
        ])->first();

        $comment = null;
        $items = null;
        if ($assessment && $assessment->items) {
            $items = json_decode($assessment->items, true);
            if (is_array($items)) {
                $itemKey = $validated['item_key'];
                if (isset($items[$itemKey]) && isset($items[$itemKey]['comments'])) {
                    $comment = $items[$itemKey]['comments'];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $comment ? ['comment' => $comment] : null,
            'input' => $validated,
        ]);
    }
}

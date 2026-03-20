<?php

namespace App\Http\Controllers;

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
            'emp_id' => 'required|integer',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'section_label' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $comment = EmployeePerformanceSectionComment::updateOrCreate(
            [
                'emp_id' => $validated['emp_id'],
                'assessment_period_id' => $validated['assessment_period_id'],
                'section_label' => $validated['section_label'],
            ],
            [
                'comment' => $validated['comment'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Section comment saved.',
            'data' => $comment,
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
            'emp_id' => 'required|integer',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'section_label' => 'required|string',
        ]);

        $comment = EmployeePerformanceSectionComment::where([
            'emp_id' => $validated['emp_id'],
            'assessment_period_id' => $validated['assessment_period_id'],
            'section_label' => $validated['section_label'],
        ])->first();

        return response()->json([
            'success' => true,
            'data' => $comment,
        ]);
    }
}

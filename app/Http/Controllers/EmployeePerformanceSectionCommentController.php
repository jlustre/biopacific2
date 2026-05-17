<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use App\Models\EmployeePerformanceSectionComment;
use App\Models\EmployeePerformanceAssessment;
use Illuminate\Support\Facades\Auth;
use App\Support\PreventsSelfAssessment;

class EmployeePerformanceSectionCommentController extends Controller
{
    /**
     * Store or update a section comment for an employee and assessment period.
     * Expects: employee_num, assessment_period_id, section_label, comment
     * Returns: success, message, comment
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'doc_type_id' => 'required|integer|exists:doc_types,id',
            'comment' => 'nullable|string',
        ]);

        if ($response = PreventsSelfAssessment::jsonDenyIfSelf(Auth::user(), (string) $validated['employee_num'])) {
            return $response;
        }

        $finalizedAssessment = EmployeePerformanceAssessment::query()
            ->where('employee_num', $validated['employee_num'])
            ->where('assessment_period_id', $validated['assessment_period_id'])
            ->where('finalized', 1)
            ->first();

        if ($finalizedAssessment) {
            return response()->json([
                'success' => false,
                'message' => 'This performance assessment is already completed for the selected period and can no longer be changed.',
            ], 422);
        }

        $result = EmployeePerformanceSectionComment::syncForSection(
            $validated['employee_num'],
            (int) $validated['assessment_period_id'],
            (int) $validated['doc_type_id'],
            $validated['comment'] ?? null,
        );

        $message = match ($result['action']) {
            'created', 'updated' => 'Section comment saved.',
            'deleted' => 'Section comment cleared.',
            default => 'No comment to save.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'action' => $result['action'],
            'data' => $result['record'],
            'input' => $validated,
        ]);
    }

    /**
     * Retrieve a section comment for an employee and assessment period.
     * Expects: employee_num, assessment_period_id, section_label
     * Returns: success, comment
     */
    public function show(Request $request)
        {

        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'doc_type_id' => 'required|integer|exists:doc_types,id',
            'item_key' => 'required|string',
        ]);

        $sectionComment = EmployeePerformanceSectionComment::query()
            ->where('employee_num', $validated['employee_num'])
            ->where('assessment_period_id', $validated['assessment_period_id'])
            ->where('doc_type_id', $validated['doc_type_id'])
            ->first();

        $comment = $sectionComment?->comment;

        return response()->json([
            'success' => true,
            'data' => filled($comment) ? ['comment' => $comment] : null,
            'input' => $validated,
        ]);
    }
}

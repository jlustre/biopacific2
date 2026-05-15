<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LnCompetencySkillsSubmitController extends Controller
{
    /**
     * Save as Draft (AJAX)
     */
    public function saveDraft(Request $request)
    {
        Log::error('saveDraft hit');
        Log::info('saveDraft request all', $request->all());
        try {
            $employeeNum = $request->input('employee_num');
            $assessmentPeriodId = $request->input('employee_assessment_period_id');
            $items = $request->input('items', []);

            if (! filled($employeeNum) || ! filled($assessmentPeriodId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select an assessment period before saving a draft.',
                ], 422);
            }
            $summaryComments = $request->input('summary_comments', null);
            $employeeComments = $request->input('employee_comments', null);
            // If items is a string (from JS), decode it first
            if (is_string($items)) {
                $decoded = json_decode($items, true);
                if (is_array($decoded)) {
                    $items = $decoded;
                }
            }
            Log::info('saveDraft items', ['items' => $items]);

            $existing = \App\Models\EmployeeCompetencyAssessment::query()
                ->where('employee_num', $employeeNum)
                ->where('assessment_period_id', $assessmentPeriodId)
                ->first();

            $payload = [];
            if ($existing?->responses) {
                $decoded = is_array($existing->responses)
                    ? $existing->responses
                    : json_decode($existing->responses, true);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }

            foreach ($items as $itemId => $data) {
                $payload[(int) $itemId] = $data;
            }

            // Save to employee_competency_assessments (draft)
            $assessment = \App\Models\EmployeeCompetencyAssessment::updateOrCreate(
                [
                    'employee_num' => $employeeNum,
                    'assessment_period_id' => $assessmentPeriodId,
                ],
                [
                    'responses' => $payload,
                    'comments' => $summaryComments,
                    'employee_comments' => $employeeComments,
                    'status' => 'draft',
                    'updated_at' => now(),
                ]
            );

            return response()->json(['success' => true, 'message' => 'Draft saved successfully!']);
        } catch (\Exception $e) {
            Log::error('saveDraft exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to save draft.', 'error' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.response' => 'required|in:E,S,U,N',
            'items.*.comments' => 'nullable|string',
            'review_date' => 'required|date',
        ]);

        $employeeNum = $request->input('employee_num');
        $assessmentPeriodId = $request->input('employee_assessment_period_id');
        $responses = $validated['items'];

        // Save all responses as JSON in the responses column
        DB::table('ln_competency_skill_responses')->updateOrInsert(
            [
                'employee_num' => $employeeNum,
                'employee_assessment_period_id' => $assessmentPeriodId,
            ],
            [
                'responses' => json_encode($responses),
                'updated_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Section saved successfully.');
    }
}

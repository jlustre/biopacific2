<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BPEmpChecklist;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Models\EmployeePerformanceAssessment;
use App\Models\BPEmployee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class EmployeePerformanceAssessmentController extends Controller {
    protected function finalizedPerformanceAssessment(string $employeeNum, int $assessmentPeriodId): ?EmployeePerformanceAssessment
    {
        return EmployeePerformanceAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->where('finalized', 1)
            ->first();
    }

    protected function completedCompetencyAssessment(string $employeeNum, int $assessmentPeriodId): ?EmployeeCompetencyAssessment
    {
        return EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->where('status', 'completed')
            ->first();
    }

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

    protected function competencyScore(?string $rating): ?int
    {
        return match (strtoupper((string) $rating)) {
            'E' => 3,
            'S' => 2,
            'U' => 1,
            default => null,
        };
    }

    protected function getAssessableCompetencyItems(BPEmployee $employee)
    {
        $positionId = $employee->currentAssignment?->position_id;
        $items = EmployeeCompetencyItem::query()
            ->applicableToPosition($positionId)
            ->orderBy('order')
            ->get()
            ->values();

        return $items->filter(function ($item, $index) use ($items) {
            $rawItemText = trim((string) $item->item);
            preg_match('/^(-+)/', $rawItemText, $itemIndentMatches);
            $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);

            $nextItem = $items->get($index + 1);
            $nextRawItemText = trim((string) ($nextItem?->item ?? ''));
            preg_match('/^(-+)/', $nextRawItemText, $nextItemIndentMatches);
            $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);

            return !($nextItem && $nextIndentLevel > $indentLevel);
        })->values();
    }

    protected function latestCompetencyEntries(string $employeeNum, int $assessmentPeriodId)
    {
        $entries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('item_key')
            ->map(fn ($entries) => $entries->first());

        $legacyChecklistItems = optional(BPEmpChecklist::query()
            ->where('employee_num', $employeeNum)
            ->first())->items ?? [];

        foreach ($legacyChecklistItems as $legacyKey => $legacyItem) {
            if (!str_starts_with((string) $legacyKey, 'competency::')) {
                continue;
            }

            $competencyItemId = (int) \Illuminate\Support\Str::after((string) $legacyKey, 'competency::');
            if ($competencyItemId <= 0) {
                continue;
            }

            $itemKey = 'G_' . $competencyItemId;
            if ($entries->has($itemKey)) {
                continue;
            }

            $assessmentDate = null;
            if (!empty($legacyItem['verified_dt'])) {
                try {
                    $assessmentDate = Carbon::parse($legacyItem['verified_dt']);
                } catch (\Throwable $exception) {
                    $assessmentDate = null;
                }
            }

            $entries->put($itemKey, (object) [
                'item_key' => $itemKey,
                'rating' => $legacyItem['rating'] ?? 'S',
                'assessment_date' => $assessmentDate,
                'assessed_by' => $legacyItem['verified_by'] ?? null,
                'comments' => $legacyItem['comments'] ?? null,
            ]);
        }

        return $entries;
    }

    protected function syncCompetencyAssessmentSnapshot(EmployeeCompetencyAssessment $assessment, array $formData = []): void
    {
        $snapshot = $assessment->snapshot_json ?? [];
        $snapshot['status'] = $assessment->status;
        $snapshot['submitted_at'] = optional($assessment->submitted_at)->toDateTimeString();
        $snapshot['employee_signed_at'] = optional($assessment->employee_signed_at)->toDateTimeString();
        $snapshot['reviewer_signed_at'] = optional($assessment->reviewer_signed_at)->toDateTimeString();
        $snapshot['completed_at'] = optional($assessment->completed_at)->toDateTimeString();
        $snapshot['summary'] = [
            'total_score' => $assessment->total_score,
            'average_score' => number_format((float) $assessment->average_score, 2, '.', ''),
            'overall_rating' => $assessment->overall_rating,
        ];
        $snapshot['form'] = array_merge($snapshot['form'] ?? [], $formData, [
            'comments' => $assessment->comments,
            'further_action_required' => $assessment->further_action_required,
            'reviewer_name' => $assessment->reviewer_name,
            'reviewer_title' => $assessment->reviewer_title,
            'review_date' => optional($assessment->review_date)->toDateString(),
            'employee_name' => $assessment->employee_name,
            'employee_title' => $assessment->employee_title,
            'employee_date' => optional($assessment->employee_signed_at)->toDateString(),
        ]);

        if ($assessment->pdf_path) {
            $snapshot['pdf_path'] = $assessment->pdf_path;
        }

        $assessment->snapshot_json = $snapshot;
    }

    protected function generateCompetencyAssessmentPdf(EmployeeCompetencyAssessment $assessment): string
    {
        $employee = BPEmployee::query()
            ->where('employee_num', $assessment->employee_num)
            ->first();
        $period = \App\Models\EmployeeAssessmentPeriod::find($assessment->assessment_period_id);

        $pdf = Pdf::loadView('admin.facilities.checklist.pdf.employee-competency-assessment', [
            'assessment' => $assessment,
            'snapshot' => $assessment->snapshot_json ?? [],
            'employee' => $employee,
            'period' => $period,
        ])->setPaper('letter');

        $filePath = 'competency-assessments/' . $assessment->employee_num . '/assessment-' . $assessment->id . '.pdf';
        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
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
                'position' => $emp->currentAssignment && $emp->currentAssignment->position ? $emp->currentAssignment->position->title : '',
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

            if ($this->inferAssessmentType((string) $validated['item_key']) === 'performance'
                && $this->finalizedPerformanceAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This performance assessment is already completed for the selected period and can no longer be changed.',
                ], 422);
            }

            if ($this->inferAssessmentType((string) $validated['item_key']) === 'competency'
                && $this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
                ], 422);
            }

            if (($validated['rating'] ?? null) === 'U' && blank($validated['comments'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'comments' => ['Comments are required when the rating is Unsatisfactory.'],
                ]);
            }
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

    public function submitCompetencyAssessment(Request $request)
    {
        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'required_item_keys' => 'nullable|array',
            'required_item_keys.*' => 'string',
            'comments' => 'nullable|string',
            'further_action_required' => 'nullable|string',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'review_date' => 'required|date',
            'employee_name' => 'required|string|max:255',
            'employee_title' => 'nullable|string|max:255',
        ]);

        if ($this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
            ], 422);
        }

        $employee = BPEmployee::query()
            ->where('employee_num', $validated['employee_num'])
            ->firstOrFail();

        $assessableItems = $this->getAssessableCompetencyItems($employee);
        $requiredItemKeys = collect($validated['required_item_keys'] ?? [])
            ->filter(fn ($itemKey) => filled($itemKey))
            ->values();

        if ($requiredItemKeys->isEmpty()) {
            $requiredItemKeys = $assessableItems
                ->map(fn ($item) => 'G_' . $item->id)
                ->values();
        }

        $latestEntries = $this->latestCompetencyEntries(
            (string) $validated['employee_num'],
            (int) $validated['assessment_period_id']
        );

        $missingCount = $requiredItemKeys->filter(function ($itemKey) use ($latestEntries) {
            $entry = $latestEntries->get($itemKey);
            return !$entry || blank($entry->rating);
        })->count();

        if ($missingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Complete all competency assessments before submitting. ' . $missingCount . ' item(s) still need a rating.',
                'missing_count' => $missingCount,
            ], 422);
        }

        $snapshotItems = $assessableItems->map(function ($item) use ($latestEntries) {
            $itemKey = 'G_' . $item->id;
            $entry = $latestEntries->get($itemKey);

            return [
                'item_key' => $itemKey,
                'source_item_id' => $item->id,
                'section' => $item->section,
                'item_label' => $item->item,
                'rating' => $entry?->rating,
                'assessment_date' => optional($entry?->assessment_date)->toDateString(),
                'assessed_by' => $entry?->assessed_by,
                'comments' => $entry?->comments,
            ];
        })->values();

        $totalScore = 0;
        $ratedCount = 0;
        foreach ($snapshotItems as $snapshotItem) {
            $score = $this->competencyScore($snapshotItem['rating'] ?? null);
            if ($score === null) {
                continue;
            }

            $totalScore += $score;
            $ratedCount++;
        }

        $averageScore = $ratedCount > 0 ? round($totalScore / $ratedCount, 2) : 0;
        $overallRating = $ratedCount === 0
            ? 'N/A'
            : ($averageScore >= 2.5
                ? 'Excellent'
                : ($averageScore >= 1.5 ? 'Satisfactory' : 'Unsatisfactory'));

        if ($overallRating === 'Unsatisfactory' && blank($validated['further_action_required'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Describe the further action required before submitting an unsatisfactory assessment.',
            ], 422);
        }

        $submittedAt = now();

        $assessment = EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $validated['employee_num'],
                'assessment_period_id' => $validated['assessment_period_id'],
            ],
            [
                'status' => 'for_employee_signature',
                'submitted_by' => Auth::id(),
                'submitted_at' => $submittedAt,
                'total_score' => $totalScore,
                'average_score' => $averageScore,
                'overall_rating' => $overallRating,
                'comments' => $validated['comments'] ?? null,
                'further_action_required' => $validated['further_action_required'] ?? null,
                'reviewer_name' => $validated['reviewer_name'],
                'reviewer_title' => $validated['reviewer_title'] ?? null,
                'review_date' => $validated['review_date'],
                'employee_name' => $validated['employee_name'],
                'employee_title' => $validated['employee_title'] ?? null,
                'employee_signed_at' => null,
                'reviewer_signed_at' => null,
                'pdf_path' => null,
                'pdf_generated_at' => null,
                'completed_at' => null,
                'snapshot_json' => [
                    'submitted_at' => $submittedAt->toDateTimeString(),
                    'summary' => [
                        'total_score' => $totalScore,
                        'average_score' => number_format($averageScore, 2, '.', ''),
                        'overall_rating' => $overallRating,
                    ],
                    'form' => [
                        'comments' => $validated['comments'] ?? null,
                        'further_action_required' => $validated['further_action_required'] ?? null,
                        'reviewer_name' => $validated['reviewer_name'],
                        'reviewer_title' => $validated['reviewer_title'] ?? null,
                        'review_date' => $validated['review_date'],
                        'employee_name' => $validated['employee_name'],
                        'employee_title' => $validated['employee_title'] ?? null,
                    ],
                    'items' => $snapshotItems,
                ],
            ]
        );

        $this->syncCompetencyAssessmentSnapshot($assessment);
        $assessment->save();

        return response()->json([
            'success' => true,
            'message' => 'Competency assessment submitted successfully.',
            'data' => [
                'id' => $assessment->id,
                'status' => $assessment->status,
            ],
        ]);
    }

    public function saveCompetencyAssessmentDraft(Request $request)
    {
        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'comments' => 'nullable|string',
            'further_action_required' => 'nullable|string',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'review_date' => 'nullable|date',
            'employee_name' => 'required|string|max:255',
            'employee_title' => 'nullable|string|max:255',
        ]);

        if ($this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
            ], 422);
        }

        $employee = BPEmployee::query()
            ->where('employee_num', $validated['employee_num'])
            ->firstOrFail();

        $assessableItems = $this->getAssessableCompetencyItems($employee);
        $latestEntries = $this->latestCompetencyEntries(
            (string) $validated['employee_num'],
            (int) $validated['assessment_period_id']
        );

        $snapshotItems = $assessableItems->map(function ($item) use ($latestEntries) {
            $itemKey = 'G_' . $item->id;
            $entry = $latestEntries->get($itemKey);

            return [
                'item_key' => $itemKey,
                'source_item_id' => $item->id,
                'section' => $item->section,
                'item_label' => $item->item,
                'rating' => $entry?->rating,
                'assessment_date' => optional($entry?->assessment_date)->toDateString(),
                'assessed_by' => $entry?->assessed_by,
                'comments' => $entry?->comments,
            ];
        })->values();

        $totalScore = 0;
        $ratedCount = 0;
        foreach ($snapshotItems as $snapshotItem) {
            $score = $this->competencyScore($snapshotItem['rating'] ?? null);
            if ($score === null) {
                continue;
            }

            $totalScore += $score;
            $ratedCount++;
        }

        $averageScore = $ratedCount > 0 ? round($totalScore / $ratedCount, 2) : 0;
        $overallRating = $ratedCount === 0
            ? 'N/A'
            : ($averageScore >= 2.5
                ? 'Excellent'
                : ($averageScore >= 1.5 ? 'Satisfactory' : 'Unsatisfactory'));

        $assessment = EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $validated['employee_num'],
                'assessment_period_id' => $validated['assessment_period_id'],
            ],
            [
                'status' => 'draft',
                'submitted_by' => Auth::id(),
                'submitted_at' => null,
                'total_score' => $totalScore,
                'average_score' => $averageScore,
                'overall_rating' => $overallRating,
                'comments' => $validated['comments'] ?? null,
                'further_action_required' => $validated['further_action_required'] ?? null,
                'reviewer_name' => $validated['reviewer_name'],
                'reviewer_title' => $validated['reviewer_title'] ?? null,
                'review_date' => $validated['review_date'] ?? null,
                'employee_name' => $validated['employee_name'],
                'employee_title' => $validated['employee_title'] ?? null,
                'employee_signed_at' => null,
                'reviewer_signed_at' => null,
                'pdf_path' => null,
                'pdf_generated_at' => null,
                'completed_at' => null,
                'snapshot_json' => [
                    'summary' => [
                        'total_score' => $totalScore,
                        'average_score' => number_format($averageScore, 2, '.', ''),
                        'overall_rating' => $overallRating,
                    ],
                    'form' => [
                        'comments' => $validated['comments'] ?? null,
                        'further_action_required' => $validated['further_action_required'] ?? null,
                        'reviewer_name' => $validated['reviewer_name'],
                        'reviewer_title' => $validated['reviewer_title'] ?? null,
                        'review_date' => $validated['review_date'] ?? null,
                        'employee_name' => $validated['employee_name'],
                        'employee_title' => $validated['employee_title'] ?? null,
                    ],
                    'items' => $snapshotItems,
                ],
            ]
        );

        $this->syncCompetencyAssessmentSnapshot($assessment);
        $assessment->save();

        return response()->json([
            'success' => true,
            'message' => 'Competency assessment draft saved.',
            'data' => [
                'id' => $assessment->id,
                'status' => $assessment->status,
            ],
        ]);
    }

    public function employeeSignCompetencyAssessment(Request $request)
    {
        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'employee_name' => 'required|string|max:255',
            'employee_title' => 'nullable|string|max:255',
            'employee_date' => 'required|date',
        ]);

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $validated['employee_num'])
            ->where('assessment_period_id', $validated['assessment_period_id'])
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Competency assessment submission not found.'], 404);
        }

        if ($assessment->status !== 'for_employee_signature') {
            return response()->json(['success' => false, 'message' => 'This assessment is not waiting for employee signature.'], 422);
        }

        $assessment->employee_name = $validated['employee_name'];
        $assessment->employee_title = $validated['employee_title'] ?? null;
        $assessment->employee_signed_at = Carbon::parse($validated['employee_date'])->startOfDay();
        $assessment->status = 'for_reviewer_signature';
        $this->syncCompetencyAssessmentSnapshot($assessment, [
            'employee_date' => $validated['employee_date'],
        ]);
        $assessment->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee signature recorded successfully.',
            'data' => [
                'status' => $assessment->status,
            ],
        ]);
    }

    public function reviewerSignCompetencyAssessment(Request $request)
    {
        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'review_date' => 'required|date',
        ]);

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $validated['employee_num'])
            ->where('assessment_period_id', $validated['assessment_period_id'])
            ->first();

        if (!$assessment) {
            return response()->json(['success' => false, 'message' => 'Competency assessment submission not found.'], 404);
        }

        if ($assessment->status !== 'for_reviewer_signature') {
            return response()->json(['success' => false, 'message' => 'This assessment is not waiting for reviewer signature.'], 422);
        }

        $assessment->reviewer_name = $validated['reviewer_name'];
        $assessment->reviewer_title = $validated['reviewer_title'] ?? null;
        $assessment->review_date = $validated['review_date'];
        $assessment->reviewer_signed_at = Carbon::parse($validated['review_date'])->startOfDay();
        $assessment->status = 'completed';
        $assessment->completed_at = now();
        $this->syncCompetencyAssessmentSnapshot($assessment);
        $assessment->save();

        $assessment->pdf_path = $this->generateCompetencyAssessmentPdf($assessment);
        $assessment->pdf_generated_at = now();
        $this->syncCompetencyAssessmentSnapshot($assessment);
        $assessment->save();

        return response()->json([
            'success' => true,
            'message' => 'Reviewer signature recorded and PDF generated successfully.',
            'data' => [
                'status' => $assessment->status,
                'pdf_url' => route('admin.employees.competency-assessment.pdf', $assessment->id),
            ],
        ]);
    }

    public function downloadCompetencyAssessmentPdf(EmployeeCompetencyAssessment $assessment)
    {
        if (!$assessment->pdf_path || !Storage::disk('public')->exists($assessment->pdf_path)) {
            abort(404, 'Competency assessment PDF not found.');
        }

        return Storage::disk('public')->download($assessment->pdf_path, basename($assessment->pdf_path));
    }

    public function revoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_num' => 'required',
                'item_key' => 'required',
                'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            ]);

            if (str_starts_with((string) $validated['item_key'], 'F_')
                && $this->finalizedPerformanceAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This performance assessment is already completed for the selected period and can no longer be changed.',
                ], 422);
            }

            if (str_starts_with((string) $validated['item_key'], 'G_')
                && $this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
                ], 422);
            }

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

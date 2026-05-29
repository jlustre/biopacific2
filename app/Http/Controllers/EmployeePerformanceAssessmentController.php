<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BPEmpChecklist;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Models\EmployeePerformanceAssessment;
use App\Models\EmployeePerformanceItem;
use App\Models\EmployeePerformanceSectionComment;
use App\Models\DocType;
use App\Models\BPEmployee;
use App\Support\PartFPerformancePdfItemsBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Support\CompetencyAssessmentHistoryBuilder;
use App\Support\EmployeeAssessmentPeriodCalculator;
use App\Support\PartFPerformanceScoring;
use App\Support\PartGCompetencyScoring;
use App\Support\PreventsSelfAssessment;
use App\Services\EmployeeAssessmentPeriodService;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\User;
use Illuminate\Http\JsonResponse;


class EmployeePerformanceAssessmentController extends Controller {
    protected function denyIfSelfAssessing(string $employeeNum): ?JsonResponse
    {
        return PreventsSelfAssessment::jsonDenyIfSelf(Auth::user(), $employeeNum);
    }

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

            $items = $assessment->itemsArray();
            if (!array_key_exists($itemKey, $items)) {
                return false;
            }

            unset($items[$itemKey]);
            $assessment->items = empty($items) ? null : $items;
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
        return PartGCompetencyScoring::numericScore($rating);
    }

    protected function syncPerformanceAssessmentSummary(EmployeePerformanceAssessment $assessment): void
    {
        $ratings = [];
        foreach ($assessment->itemsArray() as $itemKey => $itemData) {
            if (! preg_match('/^F_(\d+)$/', (string) $itemKey, $matches)) {
                continue;
            }

            $rating = EmployeePerformanceAssessment::itemRating($itemData);
            if ($rating !== null) {
                $ratings[(int) $matches[1]] = $rating;
            }
        }

        $employeeRecord = BPEmployee::query()
            ->with('currentAssignment.position')
            ->where('employee_num', $assessment->employee_num)
            ->first();
        $positionId = $employeeRecord?->currentAssignment?->position_id;
        $positionTitle = $employeeRecord?->currentAssignment?->position?->title;

        $summary = PartFPerformanceScoring::summarize(
            $ratings,
            PartFPerformanceScoring::scorableItemIds(
                $positionId ? (int) $positionId : null,
                $positionTitle
            )
        );
        $assessment->total_score = $summary['total_score'];
        $assessment->average_score = $summary['average_score'];
        $assessment->overall_rating = $summary['overall_rating'];
    }

    protected function getCompetencyItems(BPEmployee $employee)
    {
        $positionId = $employee->currentAssignment?->position_id;

        return EmployeeCompetencyItem::query()
            ->applicableToPosition($positionId)
            ->orderBy('order')
            ->get()
            ->values();
    }

    protected function getExcludedCompetencyItemKeys($items, array $excludedSectionLabels): array
    {
        $excludedSectionLookup = array_fill_keys(
            collect($excludedSectionLabels)
                ->filter(fn ($sectionLabel) => filled($sectionLabel))
                ->map(fn ($sectionLabel) => (string) $sectionLabel)
                ->unique()
                ->values()
                ->all(),
            true
        );

        if ($excludedSectionLookup === []) {
            return [];
        }

        return $items
            ->filter(fn ($item) => isset($excludedSectionLookup[(string) $item->section]))
            ->map(fn ($item) => 'G_' . $item->id)
            ->values()
            ->all();
    }

    protected function getAssessableCompetencyItems(BPEmployee $employee, array $excludedItemKeys = [])
    {
        $items = $this->getCompetencyItems($employee);
        $excludedItemLookup = array_fill_keys($excludedItemKeys, true);

        return $items->filter(function ($item, $index) use ($items, $excludedItemLookup) {
            $rawItemText = trim((string) $item->item);
            preg_match('/^(-+)/', $rawItemText, $itemIndentMatches);
            $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);

            $nextItem = $items->get($index + 1);
            $nextRawItemText = trim((string) ($nextItem?->item ?? ''));
            preg_match('/^(-+)/', $nextRawItemText, $nextItemIndentMatches);
            $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);

            return !($nextItem && $nextIndentLevel > $indentLevel)
                && !isset($excludedItemLookup['G_' . $item->id]);
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

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->first();

        $responses = $assessment?->responses;
        if (is_string($responses)) {
            $responses = json_decode($responses, true);
        }

        if (is_array($responses)) {
            foreach ($responses as $itemId => $data) {
                $sourceItemId = (int) $itemId;
                if ($sourceItemId <= 0) {
                    continue;
                }

                $itemKey = 'G_'.$sourceItemId;
                if ($entries->has($itemKey)) {
                    continue;
                }

                $rating = is_array($data)
                    ? ($data['response'] ?? null)
                    : $data;

                if (! is_string($rating) || $rating === '') {
                    continue;
                }

                $entries->put($itemKey, (object) [
                    'item_key' => $itemKey,
                    'rating' => strtoupper($rating),
                    'assessment_date' => optional($assessment?->updated_at)->toDateString() ?? now()->toDateString(),
                    'assessed_by' => $assessment?->submitted_by,
                    'comments' => null,
                ]);
            }
        }

        return $entries;
    }

    protected function syncCompetencyAssessmentSnapshot(EmployeeCompetencyAssessment $assessment, array $formData = []): void
    {
        $snapshot = $assessment->snapshot_json ?? [];
        $excludedSectionLabels = collect($formData['excluded_section_labels'] ?? ($snapshot['excluded_section_labels'] ?? []))
            ->filter(fn ($sectionLabel) => filled($sectionLabel))
            ->map(fn ($sectionLabel) => (string) $sectionLabel)
            ->unique()
            ->values()
            ->all();
        $tracheostomyEquipmentChecks = $this->normalizeTracheostomyChecks(
            $formData['tracheostomy_equipment_checks'] ?? ($snapshot['tracheostomy_equipment_checks'] ?? [])
        );
        $tracheostomyProcedureReviews = $this->normalizeTracheostomyProcedureReviews(
            $formData['tracheostomy_procedure_reviews'] ?? ($snapshot['tracheostomy_procedure_reviews'] ?? [])
        );
        $handHygieneObservation = $this->normalizeHandHygieneObservation(
            $formData['hand_hygiene_observation'] ?? ($snapshot['hand_hygiene_observation'] ?? [])
        );
        $medicationAdministrationComments = isset($formData['medication_administration_comments'])
            ? trim((string) $formData['medication_administration_comments'])
            : trim((string) ($snapshot['medication_administration_comments'] ?? ''));
        $hoyerLiftTrainingComments = isset($formData['hoyer_lift_training_comments'])
            ? trim((string) $formData['hoyer_lift_training_comments'])
            : trim((string) ($snapshot['hoyer_lift_training_comments'] ?? ''));

        $snapshot['status'] = $assessment->status;
        $snapshot['submitted_at'] = optional($assessment->submitted_at)->toDateTimeString();
        $snapshot['employee_signed_at'] = optional($assessment->employee_signed_at)->toDateTimeString();
        $snapshot['reviewer_signed_at'] = optional($assessment->reviewer_signed_at)->toDateTimeString();
        $snapshot['completed_at'] = optional($assessment->completed_at)->toDateTimeString();
        $snapshot['excluded_section_labels'] = $excludedSectionLabels;
        $snapshot['tracheostomy_equipment_checks'] = $tracheostomyEquipmentChecks;
        $snapshot['tracheostomy_procedure_reviews'] = $tracheostomyProcedureReviews;
        $snapshot['hand_hygiene_observation'] = $handHygieneObservation;
        $snapshot['medication_administration_comments'] = $medicationAdministrationComments;
        $snapshot['hoyer_lift_training_comments'] = $hoyerLiftTrainingComments;
        $snapshot['summary'] = [
            'total_score' => $assessment->total_score,
            'average_score' => number_format((float) $assessment->average_score, 2, '.', ''),
            'overall_rating' => $assessment->overall_rating,
        ];
        $snapshot['form'] = array_merge($snapshot['form'] ?? [], $formData, [
            'comments' => $assessment->comments,
            'further_action_required' => $assessment->further_action_required,
            'medication_administration_comments' => $medicationAdministrationComments,
            'hoyer_lift_training_comments' => $hoyerLiftTrainingComments,
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
            ->with('currentAssignment.facility')
            ->where('employee_num', $assessment->employee_num)
            ->first();
        $period = \App\Models\EmployeeAssessmentPeriod::find($assessment->assessment_period_id);
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $summary = is_array($snapshot['summary'] ?? null) ? $snapshot['summary'] : [];
        $averageScore = is_numeric($summary['average_score'] ?? $assessment->average_score ?? null)
            ? (float) ($summary['average_score'] ?? $assessment->average_score)
            : null;
        $overallRatingLabel = (string) ($summary['overall_rating'] ?? $assessment->overall_rating ?? 'N/A');

        $pdf = Pdf::loadView('admin.facilities.checklist.pdf.employee-competency-assessment', [
            'assessment' => $assessment,
            'snapshot' => $snapshot,
            'employee' => $employee,
            'period' => $period,
            'facilityName' => trim((string) ($employee?->currentAssignment?->facility?->name ?? '')),
            'periodLabel' => $period instanceof EmployeeAssessmentPeriod
                ? $period->displayDateRange()
                : 'N/A',
            'overallRatingCode' => PartFPerformanceScoring::overallRatingCode($overallRatingLabel, $averageScore),
            'ratedCount' => collect($snapshot['items'] ?? [])
                ->filter(fn ($item) => is_array($item) && PartGCompetencyScoring::isValidItemRating($item['rating'] ?? null))
                ->count(),
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

    protected function normalizeTracheostomyChecks($checks): array
    {
        return collect(is_array($checks) ? $checks : [])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeTracheostomyProcedureReviews($reviews): array
    {
        return collect(is_array($reviews) ? $reviews : [])
            ->mapWithKeys(function ($rating, $procedureKey) {
                $normalizedProcedureKey = trim((string) $procedureKey);
                $normalizedRating = PartGCompetencyScoring::normalizeItemRating((string) $rating);

                if ($normalizedProcedureKey === '' || $normalizedRating === null) {
                    return [];
                }

                return [$normalizedProcedureKey => $normalizedRating];
            })
            ->all();
    }

    protected function normalizeHandHygieneObservation($observation): array
    {
        $allowedCheckKeys = [
            'random',
            'unannounced',
            'enter_the_room',
            'leave_the_room',
            'touch_resident',
            'touch_equipment_in_the_room',
            'remove_gloves',
            'before_med_pass',
            'after_med_pass',
            'before_treatment_pass',
            'after_treatment_pass',
            'before_feeding',
            'after_feeding',
            'other',
        ];
        $allowedNoteKeys = [
            'other_text',
            'other_line_1',
            'other_line_2',
            'comments',
        ];

        $normalizedObservation = is_array($observation) ? $observation : [];
        $checks = collect($normalizedObservation['checks'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && in_array($value, $allowedCheckKeys, true))
            ->unique()
            ->values()
            ->all();
        $notes = collect(is_array($normalizedObservation['notes'] ?? null) ? $normalizedObservation['notes'] : [])
            ->mapWithKeys(function ($value, $key) use ($allowedNoteKeys) {
                $normalizedKey = trim((string) $key);

                if (!in_array($normalizedKey, $allowedNoteKeys, true)) {
                    return [];
                }

                return [$normalizedKey => trim((string) $value)];
            })
            ->filter(fn ($value) => $value !== '')
            ->all();

        return [
            'checks' => $checks,
            'notes' => $notes,
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
     * Delete an assessment period when no employees are linked to it.
     */
    public function destroyPeriod(Request $request, $id)
    {
        $period = \App\Models\EmployeeAssessmentPeriod::find($id);
        if (!$period) {
            return response()->json(['success' => false, 'message' => 'Assessment period not found.'], 404);
        }

        $assignedEmployeeNums = $period->assignedEmployeeNums();
        if ($assignedEmployeeNums->isNotEmpty()) {
            $employeesByNum = \App\Models\BPEmployee::query()
                ->whereIn('employee_num', $assignedEmployeeNums)
                ->get()
                ->keyBy('employee_num');

            $assignedEmployees = $assignedEmployeeNums->map(function ($employeeNum) use ($employeesByNum) {
                $employee = $employeesByNum->get($employeeNum);

                return [
                    'employee_num' => $employeeNum,
                    'employee_name' => $employee
                        ? trim($employee->last_name . ', ' . $employee->first_name . ($employee->middle_name ? ' ' . $employee->middle_name : ''))
                        : $employeeNum,
                ];
            })->values()->all();

            return response()->json([
                'success' => false,
                'blocked' => true,
                'assigned_employees' => $assignedEmployees,
                'message' => 'This assessment period cannot be deleted because ' . count($assignedEmployees) . ' employee(s) already have performance or competency data for this period.',
            ], 422);
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
                'rating' => 'required|in:E,M,B,S,U,N',
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

            if ($response = $this->denyIfSelfAssessing((string) $validated['employee_num'])) {
                return $response;
            }

            // Removed requirement for comments when rating is Unsatisfactory ('U')
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
                'items' => [],
                'assessment_date' => $validated['assessment_date'],
                'assessed_by' => Auth::id(),
            ]
        );

        $assessment->assessment_date = $validated['assessment_date'];
        $assessment->assessed_by = Auth::id();

        if (str_starts_with((string) $validated['item_key'], 'F_')) {
            $employeeRecord = BPEmployee::query()
                ->with('currentAssignment.position')
                ->where('employee_num', $validated['employee_num'])
                ->first();
            $positionTitle = $employeeRecord?->currentAssignment?->position?->title;
            $scorableItemIds = PartFPerformanceScoring::scorableItemIds(null, $positionTitle);
            $sourceItemId = $validated['source_item_id']
                ?? (preg_match('/^F_(\d+)$/', (string) $validated['item_key'], $matches) ? (int) $matches[1] : null);

            if (! $sourceItemId || ! isset($scorableItemIds[$sourceItemId])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This performance item does not apply to the employee\'s position.',
                ], 422);
            }

            $normalizedRating = PartFPerformanceScoring::normalizeItemRating($validated['rating']);
            if ($normalizedRating === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance item ratings must be E, M, or B.',
                ], 422);
            }

            $validated['rating'] = $normalizedRating;
            $items = $assessment->itemsArray();
            $items[(string) $validated['item_key']] = ['rating' => $normalizedRating];
            $assessment->items = $items;
            $this->syncPerformanceAssessmentSummary($assessment);
        }

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
            'excluded_section_labels' => 'nullable|array',
            'excluded_section_labels.*' => 'string',
            'tracheostomy_equipment_checks' => 'nullable|array',
            'tracheostomy_equipment_checks.*' => 'string',
            'tracheostomy_procedure_reviews' => 'nullable|array',
            'tracheostomy_procedure_reviews.*' => 'nullable|string|in:E,M,B,S,U',
            'hand_hygiene_observation' => 'nullable|array',
            'hand_hygiene_observation.checks' => 'nullable|array',
            'hand_hygiene_observation.checks.*' => 'string',
            'hand_hygiene_observation.notes' => 'nullable|array',
            'hand_hygiene_observation.notes.*' => 'nullable|string',
            'medication_administration_comments' => 'nullable|string',
            'hoyer_lift_training_comments' => 'nullable|string',
            'comments' => 'nullable|string',
            'further_action_required' => 'nullable|string',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'review_date' => 'required|date',
            'employee_name' => 'required|string|max:255',
            'employee_title' => 'nullable|string|max:255',
        ]);

        if ($response = $this->denyIfSelfAssessing((string) $validated['employee_num'])) {
            return $response;
        }

        if ($this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
            ], 422);
        }

        $employee = BPEmployee::query()
            ->where('employee_num', $validated['employee_num'])
            ->firstOrFail();

        $allCompetencyItems = $this->getCompetencyItems($employee);
        $excludedSectionLabels = collect($validated['excluded_section_labels'] ?? [])
            ->filter(fn ($sectionLabel) => filled($sectionLabel))
            ->map(fn ($sectionLabel) => (string) $sectionLabel)
            ->unique()
            ->values()
            ->all();
        $tracheostomyEquipmentChecks = $this->normalizeTracheostomyChecks($validated['tracheostomy_equipment_checks'] ?? []);
        $tracheostomyProcedureReviews = $this->normalizeTracheostomyProcedureReviews($validated['tracheostomy_procedure_reviews'] ?? []);
        $handHygieneObservation = $this->normalizeHandHygieneObservation($validated['hand_hygiene_observation'] ?? []);
        $medicationAdministrationComments = trim((string) ($validated['medication_administration_comments'] ?? ''));
        $hoyerLiftTrainingComments = trim((string) ($validated['hoyer_lift_training_comments'] ?? ''));
        $excludedItemKeys = $this->getExcludedCompetencyItemKeys($allCompetencyItems, $excludedSectionLabels);
        $assessableItems = $this->getAssessableCompetencyItems($employee, $excludedItemKeys);
        $requiredItemKeys = $assessableItems
            ->map(fn ($item) => 'G_' . $item->id)
            ->values();

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
                'rating' => PartGCompetencyScoring::normalizeItemRating($entry?->rating) ?? '',
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
        $overallRating = PartGCompetencyScoring::overallLabelOrNa($averageScore, $ratedCount);

        if (PartFPerformanceScoring::isBelowExpectationsRating($overallRating) && blank($validated['further_action_required'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Describe the further action required before submitting a below expectations assessment.',
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
                    'excluded_section_labels' => $excludedSectionLabels,
                    'tracheostomy_equipment_checks' => $tracheostomyEquipmentChecks,
                    'tracheostomy_procedure_reviews' => $tracheostomyProcedureReviews,
                    'hand_hygiene_observation' => $handHygieneObservation,
                    'medication_administration_comments' => $medicationAdministrationComments,
                    'hoyer_lift_training_comments' => $hoyerLiftTrainingComments,
                    'items' => $snapshotItems,
                ],
            ]
        );

        $this->syncCompetencyAssessmentSnapshot($assessment, [
            'excluded_section_labels' => $excludedSectionLabels,
            'tracheostomy_equipment_checks' => $tracheostomyEquipmentChecks,
            'tracheostomy_procedure_reviews' => $tracheostomyProcedureReviews,
            'hand_hygiene_observation' => $handHygieneObservation,
            'medication_administration_comments' => $medicationAdministrationComments,
            'hoyer_lift_training_comments' => $hoyerLiftTrainingComments,
        ]);
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
        try {
            $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'excluded_section_labels' => 'nullable|array',
            'excluded_section_labels.*' => 'string',
            'tracheostomy_equipment_checks' => 'nullable|array',
            'tracheostomy_equipment_checks.*' => 'string',
            'tracheostomy_procedure_reviews' => 'nullable|array',
            'tracheostomy_procedure_reviews.*' => 'nullable|string|in:E,M,B,S,U',
            'hand_hygiene_observation' => 'nullable|array',
            'hand_hygiene_observation.checks' => 'nullable|array',
            'hand_hygiene_observation.checks.*' => 'string',
            'hand_hygiene_observation.notes' => 'nullable|array',
            'hand_hygiene_observation.notes.*' => 'nullable|string',
            'medication_administration_comments' => 'nullable|string',
            'hoyer_lift_training_comments' => 'nullable|string',
            'comments' => 'nullable|string',
            'further_action_required' => 'nullable|string',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'review_date' => 'nullable|date',
            'employee_name' => 'required|string|max:255',
            'employee_title' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.',
                ], 422);
            }

            throw $e;
        }

        if ($response = $this->denyIfSelfAssessing((string) $validated['employee_num'])) {
            return $response;
        }

        if ($this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
            ], 422);
        }

        $employee = BPEmployee::query()
            ->where('employee_num', $validated['employee_num'])
            ->firstOrFail();

        $allCompetencyItems = $this->getCompetencyItems($employee);
        $existingAssessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $validated['employee_num'])
            ->where('assessment_period_id', $validated['assessment_period_id'])
            ->first();
        $existingSnapshot = is_array($existingAssessment?->snapshot_json) ? $existingAssessment->snapshot_json : [];

        $excludedSectionLabels = collect($validated['excluded_section_labels'] ?? ($existingSnapshot['excluded_section_labels'] ?? []))
            ->filter(fn ($sectionLabel) => filled($sectionLabel))
            ->map(fn ($sectionLabel) => (string) $sectionLabel)
            ->unique()
            ->values()
            ->all();
        $tracheostomyEquipmentChecks = $this->normalizeTracheostomyChecks(
            $validated['tracheostomy_equipment_checks'] ?? ($existingSnapshot['tracheostomy_equipment_checks'] ?? [])
        );
        $tracheostomyProcedureReviews = $this->normalizeTracheostomyProcedureReviews(
            $validated['tracheostomy_procedure_reviews'] ?? ($existingSnapshot['tracheostomy_procedure_reviews'] ?? [])
        );
        $handHygieneObservation = $this->normalizeHandHygieneObservation(
            $validated['hand_hygiene_observation'] ?? ($existingSnapshot['hand_hygiene_observation'] ?? [])
        );
        $medicationAdministrationComments = trim((string) (
            $validated['medication_administration_comments']
            ?? ($existingSnapshot['medication_administration_comments'] ?? '')
        ));
        $hoyerLiftTrainingComments = trim((string) (
            $validated['hoyer_lift_training_comments']
            ?? ($existingSnapshot['hoyer_lift_training_comments'] ?? '')
        ));
        $excludedItemKeys = $this->getExcludedCompetencyItemKeys($allCompetencyItems, $excludedSectionLabels);
        $assessableItems = $this->getAssessableCompetencyItems($employee, $excludedItemKeys);
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
                'rating' => PartGCompetencyScoring::normalizeItemRating($entry?->rating) ?? '',
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
        $overallRating = PartGCompetencyScoring::overallLabelOrNa($averageScore, $ratedCount);

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
                    'excluded_section_labels' => $excludedSectionLabels,
                    'tracheostomy_equipment_checks' => $tracheostomyEquipmentChecks,
                    'tracheostomy_procedure_reviews' => $tracheostomyProcedureReviews,
                    'hand_hygiene_observation' => $handHygieneObservation,
                    'medication_administration_comments' => $medicationAdministrationComments,
                    'hoyer_lift_training_comments' => $hoyerLiftTrainingComments,
                    'items' => $snapshotItems,
                ],
            ]
        );

        $this->syncCompetencyAssessmentSnapshot($assessment, [
            'excluded_section_labels' => $excludedSectionLabels,
            'tracheostomy_equipment_checks' => $tracheostomyEquipmentChecks,
            'tracheostomy_procedure_reviews' => $tracheostomyProcedureReviews,
            'hand_hygiene_observation' => $handHygieneObservation,
            'medication_administration_comments' => $medicationAdministrationComments,
            'hoyer_lift_training_comments' => $hoyerLiftTrainingComments,
        ]);
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

    public function saveCompetencyAssessmentPreferences(Request $request)
    {
        $validated = $request->validate([
            'employee_num' => 'required|string',
            'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            'excluded_section_labels' => 'nullable|array',
            'excluded_section_labels.*' => 'string',
            'tracheostomy_equipment_checks' => 'nullable|array',
            'tracheostomy_equipment_checks.*' => 'string',
            'tracheostomy_procedure_reviews' => 'nullable|array',
            'tracheostomy_procedure_reviews.*' => 'nullable|string|in:E,M,B,S,U',
            'hand_hygiene_observation' => 'nullable|array',
            'hand_hygiene_observation.checks' => 'nullable|array',
            'hand_hygiene_observation.checks.*' => 'string',
            'hand_hygiene_observation.notes' => 'nullable|array',
            'hand_hygiene_observation.notes.*' => 'nullable|string',
            'medication_administration_comments' => 'nullable|string',
            'hoyer_lift_training_comments' => 'nullable|string',
        ]);

        if ($response = $this->denyIfSelfAssessing((string) $validated['employee_num'])) {
            return $response;
        }

        if ($this->completedCompetencyAssessment((string) $validated['employee_num'], (int) $validated['assessment_period_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'This competency assessment is already completed for the selected period and can no longer be changed.',
            ], 422);
        }

        $excludedSectionLabels = collect($validated['excluded_section_labels'] ?? [])
            ->filter(fn ($sectionLabel) => filled($sectionLabel))
            ->map(fn ($sectionLabel) => (string) $sectionLabel)
            ->unique()
            ->values()
            ->all();
        $tracheostomyEquipmentChecks = $this->normalizeTracheostomyChecks($validated['tracheostomy_equipment_checks'] ?? []);
        $tracheostomyProcedureReviews = $this->normalizeTracheostomyProcedureReviews($validated['tracheostomy_procedure_reviews'] ?? []);
        $handHygieneObservation = $this->normalizeHandHygieneObservation($validated['hand_hygiene_observation'] ?? []);
        $medicationAdministrationComments = trim((string) ($validated['medication_administration_comments'] ?? ''));
        $hoyerLiftTrainingComments = trim((string) ($validated['hoyer_lift_training_comments'] ?? ''));

        $assessment = EmployeeCompetencyAssessment::query()->firstOrNew([
            'employee_num' => $validated['employee_num'],
            'assessment_period_id' => $validated['assessment_period_id'],
        ]);

        if (!$assessment->exists) {
            $assessment->status = 'draft';
            $assessment->submitted_by = Auth::id();
            $assessment->total_score = 0;
            $assessment->average_score = 0;
            $assessment->overall_rating = null;
        } elseif (!$assessment->submitted_by) {
            $assessment->submitted_by = Auth::id();
        }

        $this->syncCompetencyAssessmentSnapshot($assessment, [
            'excluded_section_labels' => $excludedSectionLabels,
            'tracheostomy_equipment_checks' => $tracheostomyEquipmentChecks,
            'tracheostomy_procedure_reviews' => $tracheostomyProcedureReviews,
            'hand_hygiene_observation' => $handHygieneObservation,
            'medication_administration_comments' => $medicationAdministrationComments,
            'hoyer_lift_training_comments' => $hoyerLiftTrainingComments,
        ]);
        $assessment->save();

        return response()->json([
            'success' => true,
            'message' => 'Competency assessment preferences saved.',
            'data' => [
                'id' => $assessment->id,
                'status' => $assessment->status,
                'excluded_section_labels' => $excludedSectionLabels,
                'tracheostomy_equipment_checks' => $tracheostomyEquipmentChecks,
                'tracheostomy_procedure_reviews' => $tracheostomyProcedureReviews,
                'hand_hygiene_observation' => $handHygieneObservation,
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

        if ($response = $this->denyIfSelfAssessing((string) $validated['employee_num'])) {
            return $response;
        }

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

    public function downloadPerformanceAssessmentPdf(EmployeePerformanceAssessment $assessment)
    {
        $this->syncPerformanceAssessmentSummary($assessment);
        $assessment->save();

        $items = PartFPerformancePdfItemsBuilder::build($assessment);
        if ($items === []) {
            abort(404, 'No performance assessment items found for this period.');
        }

        $filePath = $this->performanceAssessmentPdfPath($assessment);
        $this->generatePerformanceAssessmentPdf($assessment, $filePath, $items);

        $filename = basename($filePath);

        return response()->file(Storage::disk('public')->path($filePath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function downloadCompetencySectionPdf(Request $request, EmployeeCompetencyAssessment $assessment)
    {
        $sectionLabel = trim((string) $request->query('section', ''));
        if ($sectionLabel === '') {
            abort(404, 'Competency section not specified.');
        }

        $items = $this->competencySectionPdfItems($assessment, $sectionLabel);
        if ($items === []) {
            abort(404, 'No competency items found for this section.');
        }

        $filePath = $this->competencySectionPdfPath($assessment, $sectionLabel);
        $this->generateCompetencySectionPdf($assessment, $sectionLabel, $filePath, $items);

        $filename = basename($filePath);

        return response()->file(Storage::disk('public')->path($filePath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  list<array{item_label: string, indent_level: int, rating: string, review_date: string, reviewer_name: string}>  $items
     */
    protected function generateCompetencySectionPdf(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        string $filePath,
        array $items,
    ): string {
        $employee = BPEmployee::query()
            ->with('currentAssignment.facility')
            ->where('employee_num', $assessment->employee_num)
            ->first();
        $period = EmployeeAssessmentPeriod::find($assessment->assessment_period_id);
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $computedSectionSummary = $this->summarizeCompetencySectionItems($items);
        $sectionSummary = array_merge(
            is_array($snapshot['section_summaries'][$sectionLabel] ?? null)
                ? $snapshot['section_summaries'][$sectionLabel]
                : [],
            $computedSectionSummary,
        );
        $sectionComments = is_array($snapshot['section_comments'][$sectionLabel] ?? null)
            ? $snapshot['section_comments'][$sectionLabel]
            : [];

        $ratedCount = $this->competencySectionPdfRatedCount($sectionLabel, $items, $snapshot);

        $pdf = Pdf::loadView('admin.facilities.checklist.pdf.employee-competency-section-compact', [
            'assessment' => $assessment,
            'employee' => $employee,
            'period' => $period,
            'facilityName' => trim((string) ($employee?->currentAssignment?->facility?->name ?? '')),
            'periodLabel' => $period instanceof EmployeeAssessmentPeriod
                ? $period->displayDateRange()
                : 'N/A',
            'sectionLabel' => $sectionLabel,
            'sectionSummary' => $sectionSummary,
            'sectionComments' => $sectionComments,
            'signatureBlock' => $this->competencySectionPdfSignatureBlock($assessment, $sectionComments),
            'items' => $items,
            'ratedCount' => $ratedCount,
            'totalRateableOverride' => CompetencyAssessmentHistoryBuilder::rateableItemCountForSection($sectionLabel),
        ])->setPaper('letter');

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * @param  list<array{item_label: string, indent_level: int, is_parent?: bool, rating?: string, review_date?: string, reviewer_name?: string, comments?: string}>  $items
     */
    protected function generatePerformanceAssessmentPdf(
        EmployeePerformanceAssessment $assessment,
        string $filePath,
        array $items,
    ): string {
        $employee = BPEmployee::query()
            ->with(['currentAssignment.facility', 'currentAssignment.position'])
            ->where('employee_num', $assessment->employee_num)
            ->first();
        $positionId = $employee?->currentAssignment?->position_id;
        $positionTitle = $employee?->currentAssignment?->position?->title;
        $period = EmployeeAssessmentPeriod::find($assessment->assessment_period_id);

        $ratedCount = collect($items)
            ->filter(fn (array $item) => empty($item['is_parent']) && PartFPerformanceScoring::isValidItemRating($item['rating'] ?? null))
            ->count();

        $periodLabel = $period instanceof EmployeeAssessmentPeriod
            ? $this->formatPerformanceHistoryPeriodLabel($period)
            : 'N/A';

        $developmentNotes = $this->performanceAssessmentPdfDevelopmentNotes($assessment);
        $averageScore = is_numeric($assessment->average_score ?? null)
            ? (float) $assessment->average_score
            : null;
        $overallRatingLabel = (string) ($assessment->overall_rating ?? 'N/A');
        $overallRatingCode = PartFPerformanceScoring::overallRatingCode($overallRatingLabel, $averageScore);

        $pdf = Pdf::loadView('admin.facilities.checklist.pdf.employee-competency-section-compact', [
            'assessment' => $assessment,
            'employee' => $employee,
            'period' => $period,
            'facilityName' => trim((string) ($employee?->currentAssignment?->facility?->name ?? '')),
            'periodLabel' => $periodLabel,
            'sectionLabel' => 'PART F — Performance Appraisal',
            'isPerformanceAssessment' => true,
            'areasForDevelopment' => $developmentNotes['areas_for_development'],
            'developmentPlans' => $developmentNotes['development_plans'],
            'overallRatingCode' => $overallRatingCode,
            'sectionSummary' => [
                'total_score' => $assessment->total_score ?? 0,
                'average_score' => $averageScore !== null
                    ? number_format($averageScore, 2, '.', '')
                    : ($assessment->average_score ?? '0.00'),
                'overall_rating' => $overallRatingLabel,
            ],
            'sectionComments' => [],
            'signatureBlock' => $this->performanceAssessmentPdfSignatureBlock($assessment, $developmentNotes),
            'items' => $items,
            'ratedCount' => $ratedCount,
            'totalRateableOverride' => count(PartFPerformanceScoring::scorableItemIds(
                $positionId ? (int) $positionId : null,
                $positionTitle
            )),
            'assessmentStatusLabel' => ! empty($assessment->finalized) ? 'Completed' : 'In Progress',
        ])->setPaper('letter');

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    protected function performanceAssessmentPdfPath(EmployeePerformanceAssessment $assessment): string
    {
        return 'performance-assessments/'.$assessment->employee_num.'/assessment-'.$assessment->id.'.pdf';
    }

    /**
     * @return array{areas_for_development: string, development_plans: string, employee_comments: string}
     */
    protected function performanceAssessmentPdfDevelopmentNotes(EmployeePerformanceAssessment $assessment): array
    {
        $docTypesByName = DocType::query()
            ->whereIn('name', [
                'Areas Requiring Further Development',
                'Development Plans',
                'Employee Comments',
            ])
            ->pluck('id', 'name');

        $commentsByDocTypeId = EmployeePerformanceSectionComment::query()
            ->where('employee_num', $assessment->employee_num)
            ->where('assessment_period_id', $assessment->assessment_period_id)
            ->whereIn('doc_type_id', $docTypesByName->values())
            ->pluck('comment', 'doc_type_id');

        $commentFor = static function (string $name) use ($docTypesByName, $commentsByDocTypeId): string {
            $docTypeId = $docTypesByName->get($name);
            if (! $docTypeId) {
                return '';
            }

            return trim((string) ($commentsByDocTypeId->get($docTypeId) ?? ''));
        };

        return [
            'areas_for_development' => $commentFor('Areas Requiring Further Development'),
            'development_plans' => $commentFor('Development Plans'),
            'employee_comments' => $commentFor('Employee Comments'),
        ];
    }

    /**
     * @param  array{areas_for_development?: string, development_plans?: string, employee_comments?: string}  $developmentNotes
     * @return array<string, string>
     */
    protected function performanceAssessmentPdfSignatureBlock(
        EmployeePerformanceAssessment $assessment,
        array $developmentNotes = [],
    ): array {
        $reviewerName = '';
        if ($assessment->assessed_by) {
            $reviewerName = trim((string) (User::query()->where('id', $assessment->assessed_by)->value('name') ?? ''));
        }

        return [
            'reviewer_comments' => trim((string) ($assessment->comments ?? '')),
            'employee_comments' => trim((string) ($developmentNotes['employee_comments'] ?? '')),
            'reviewer_name' => $reviewerName,
            'reviewer_title' => '',
            'review_sign_date' => $this->formatCompetencyPdfShortDate($assessment->review_dt),
            'employee_name' => trim(($employee = BPEmployee::query()->where('employee_num', $assessment->employee_num)->first())
                ? trim(($employee->last_name ?? '').', '.($employee->first_name ?? ''), ', ')
                : ''),
            'employee_title' => trim((string) ($employee?->currentAssignment?->position?->title ?? $employee?->position ?? '')),
            'employee_sign_date' => $this->formatCompetencyPdfShortDate($assessment->acknowledge_dt),
        ];
    }

    protected function formatPerformanceHistoryPeriodLabel(EmployeeAssessmentPeriod $period): string
    {
        try {
            $from = Carbon::parse(EmployeeAssessmentPeriod::formatDateForDisplay($period->date_from))->format('m-d-y');
            $to = Carbon::parse(EmployeeAssessmentPeriod::formatDateForDisplay($period->date_to))->format('m-d-y');

            return $from.' to '.$to;
        } catch (\Throwable) {
            return $period->displayDateRange();
        }
    }

    protected function competencySectionPdfPath(EmployeeCompetencyAssessment $assessment, string $sectionLabel): string
    {
        $sectionKey = substr(md5($sectionLabel), 0, 12);

        return 'competency-assessments/'.$assessment->employee_num.'/assessment-'.$assessment->id.'/section-'.$sectionKey.'.pdf';
    }

    /**
     * @return list<array{item_label: string, indent_level: int, is_parent?: bool, rating?: string, review_date?: string, reviewer_name?: string, comments?: string}>
     */
    protected function competencySectionPdfItems(EmployeeCompetencyAssessment $assessment, string $sectionLabel): array
    {
        if ($sectionLabel === 'TRACHEOSTOMY CARE') {
            return $this->tracheostomyCompetencySectionPdfItems($assessment);
        }

        $competencyItems = EmployeeCompetencyItem::query()
            ->where('section', $sectionLabel)
            ->orderBy('order')
            ->get();

        if ($competencyItems->isEmpty()) {
            return [];
        }

        $itemIds = $competencyItems->pluck('id')->map(fn ($id) => (int) $id)->all();
        $responses = $assessment->responses;
        if (is_string($responses)) {
            $responses = json_decode($responses, true);
        }
        $responses = is_array($responses) ? $responses : [];

        $entries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $assessment->employee_num)
            ->where('assessment_period_id', $assessment->assessment_period_id)
            ->where('assessment_type', 'competency')
            ->whereIn('source_item_id', $itemIds)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('source_item_id')
            ->map(fn ($group) => $group->first());

        $reviewerIds = $entries->pluck('assessed_by')->filter()->unique()->values();
        $reviewerNamesById = $reviewerIds->isEmpty()
            ? collect()
            : User::query()->whereIn('id', $reviewerIds)->pluck('name', 'id');

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $sectionReviewDate = $this->resolveCompetencySectionReviewDate($assessment, $snapshot, $sectionLabel);

        $structuredItems = [];
        $rawItems = $competencyItems->values();

        foreach ($rawItems as $index => $item) {
            $indentLevel = 0;
            if (preg_match('/^(-+)/', (string) $item->item, $matches)) {
                $indentLevel = strlen($matches[1]);
            }

            $isParent = false;
            if (isset($rawItems[$index + 1])) {
                $next = $rawItems[$index + 1];
                if (preg_match('/^(-+)/', (string) $next->item, $nextMatches)) {
                    $isParent = strlen($nextMatches[1]) > $indentLevel;
                }
            }

            $structuredItems[] = [
                'id' => (int) $item->id,
                'item_label' => ltrim((string) $item->item, '-'),
                'indent_level' => $indentLevel,
                'is_parent' => $isParent,
            ];
        }

        $ratedItemIds = [];
        foreach ($structuredItems as $structuredItem) {
            if ($structuredItem['is_parent']) {
                continue;
            }

            $itemId = $structuredItem['id'];
            $responseData = $this->competencyResponsePayloadForItem($responses, $itemId);
            $entry = $entries->get($itemId);
            $rating = $responseData['response'] ?? null;

            if (($rating === null || $rating === '') && $entry) {
                $rating = $entry->rating;
            }

            $rating = PartGCompetencyScoring::normalizeItemRating(
                $responseData['response'] ?? null
            ) ?? PartGCompetencyScoring::normalizeItemRating($entry?->rating);

            if ($rating === null && ($entry?->rating || filled($responseData['response'] ?? null))) {
                $rating = PartGCompetencyScoring::normalizeItemRating(
                    (string) ($responseData['response'] ?? $entry?->rating)
                );
            }

            if ($rating !== null) {
                $ratedItemIds[$itemId] = true;
            }
        }

        $items = [];

        foreach ($structuredItems as $index => $structuredItem) {
            if ($structuredItem['is_parent']) {
                if (! $this->competencyParentHasRatedDescendant($structuredItems, $index, $ratedItemIds)) {
                    continue;
                }

                $items[] = [
                    'item_label' => $structuredItem['item_label'],
                    'indent_level' => $structuredItem['indent_level'],
                    'is_parent' => true,
                ];

                continue;
            }

            $itemId = $structuredItem['id'];
            if (! isset($ratedItemIds[$itemId])) {
                continue;
            }

            $responseData = $this->competencyResponsePayloadForItem($responses, $itemId);
            $entry = $entries->get($itemId);
            $rating = PartGCompetencyScoring::normalizeItemRating(
                $responseData['response'] ?? $entry?->rating
            ) ?? '';

            $reviewDate = $this->formatCompetencyPdfShortDate(
                $responseData['review_date'] ?? optional($entry?->assessment_date)->toDateString()
            );
            if ($reviewDate === '') {
                $reviewDate = $sectionReviewDate;
            }
            $reviewerName = trim((string) ($responseData['reviewer_name'] ?? ''));
            if ($reviewerName === '' && $entry?->assessed_by) {
                $reviewerName = trim((string) ($reviewerNamesById[$entry->assessed_by] ?? ''));
            }
            if ($reviewerName === '' && filled($assessment->reviewer_name)) {
                $reviewerName = trim((string) $assessment->reviewer_name);
            }

            $items[] = [
                'item_label' => $structuredItem['item_label'],
                'indent_level' => $structuredItem['indent_level'],
                'is_parent' => false,
                'rating' => $rating,
                'review_date' => $reviewDate,
                'reviewer_name' => $reviewerName,
                'comments' => trim((string) ($responseData['comments'] ?? $entry?->comments ?? '')),
            ];
        }

        return $items;
    }

    /**
     * @return list<array{item_label: string, indent_level: int, is_parent?: bool, rating?: string, review_date?: string, reviewer_name?: string, comments?: string}>
     */
    protected function tracheostomyCompetencySectionPdfItems(EmployeeCompetencyAssessment $assessment): array
    {
        $competencyItems = EmployeeCompetencyItem::query()
            ->where('section', 'TRACHEOSTOMY CARE')
            ->orderBy('order')
            ->get();

        if ($competencyItems->isEmpty()) {
            return [];
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $equipmentChecks = $this->normalizeTracheostomyChecks($snapshot['tracheostomy_equipment_checks'] ?? []);
        $procedureReviews = $this->normalizeTracheostomyProcedureReviews($snapshot['tracheostomy_procedure_reviews'] ?? []);

        $responses = $assessment->responses;
        if (is_string($responses)) {
            $responses = json_decode($responses, true);
        }
        $responses = is_array($responses) ? $responses : [];

        $hasProcedureActivity = $procedureReviews !== [];
        if (! $hasProcedureActivity) {
            foreach ($responses as $responseData) {
                $rating = is_array($responseData)
                    ? strtoupper(trim((string) ($responseData['response'] ?? '')))
                    : strtoupper(trim((string) $responseData));
                if (PartGCompetencyScoring::isValidItemRating($rating)) {
                    $hasProcedureActivity = true;
                    break;
                }
            }
        }

        $hasEquipmentItemsInSection = $competencyItems->contains(function (EmployeeCompetencyItem $item) {
            $raw = (string) $item->item;
            if (preg_match('/^-\d+\./', $raw) === 1) {
                return false;
            }

            if (preg_match('/^(-+)/', $raw, $matches)) {
                return strlen($matches[1]) >= 2;
            }

            return false;
        });

        if (! $hasEquipmentItemsInSection && ! $hasProcedureActivity) {
            return [];
        }

        $sectionComments = is_array($snapshot['section_comments']['TRACHEOSTOMY CARE'] ?? null)
            ? $snapshot['section_comments']['TRACHEOSTOMY CARE']
            : [];
        $sectionReviewDate = $this->resolveCompetencySectionReviewDate($assessment, $snapshot, 'TRACHEOSTOMY CARE');
        $sectionReviewerName = trim((string) ($sectionComments['reviewer_name'] ?? $assessment->reviewer_name ?? ''));

        $equipmentCheckSet = array_flip($equipmentChecks);
        $items = [];
        $rawItems = $competencyItems->values();
        $hasEquipmentRows = false;
        $competencyItemsHeadingAdded = false;

        foreach ($rawItems as $index => $item) {
            $raw = (string) $item->item;

            if (preg_match('/^-\d+\./', $raw) === 1) {
                if (preg_match('/^-(\d+)\.\s*(.+)$/', $raw, $matches)) {
                    $procedureKey = (string) $matches[1];
                    $itemId = (int) $item->id;
                    $responseData = $this->competencyResponsePayloadForItem($responses, $itemId);
                    $rating = PartGCompetencyScoring::normalizeItemRating(
                        $responseData['response'] ?? ($procedureReviews[$procedureKey] ?? null)
                    );

                    if ($rating !== null) {
                        if ($hasEquipmentRows && ! $competencyItemsHeadingAdded) {
                            $items[] = [
                                'item_label' => 'Competency Items',
                                'is_subsection_heading' => true,
                                'with_rating_legend' => true,
                            ];
                            $competencyItemsHeadingAdded = true;
                        }

                        $segments = array_map('trim', explode('||', $matches[2], 2));
                        $label = ltrim($segments[0] ?? '', '-');
                        if (! empty($segments[1])) {
                            $label .= ' ('.$segments[1].')';
                        }

                        $itemReviewDate = $this->formatCompetencyPdfShortDate($responseData['review_date'] ?? null);
                        if ($itemReviewDate === '') {
                            $itemReviewDate = $sectionReviewDate;
                        }

                        $itemReviewerName = trim((string) ($responseData['reviewer_name'] ?? ''));
                        if ($itemReviewerName === '') {
                            $itemReviewerName = $sectionReviewerName;
                        }

                        $items[] = [
                            'item_label' => $procedureKey.'. '.$label,
                            'indent_level' => 1,
                            'is_parent' => false,
                            'rating' => $rating,
                            'review_date' => $itemReviewDate,
                            'reviewer_name' => $itemReviewerName,
                            'comments' => trim((string) ($responseData['comments'] ?? '')),
                        ];
                    }
                }

                continue;
            }

            $indentLevel = 0;
            if (preg_match('/^(-+)/', $raw, $indentMatches)) {
                $indentLevel = strlen($indentMatches[1]);
            }

            $hasChildItems = false;
            if (isset($rawItems[$index + 1])) {
                $nextRaw = (string) $rawItems[$index + 1]->item;
                if (preg_match('/^(-+)/', $nextRaw, $nextMatches)) {
                    $hasChildItems = strlen($nextMatches[1]) > $indentLevel;
                }
            }

            $isEquipmentHeader = $indentLevel === 1 && $hasChildItems;
            $isEquipmentItem = $indentLevel >= 2;

            if ($isEquipmentHeader) {
                $hasEquipmentChildren = false;

                for ($childIndex = $index + 1; $childIndex < $rawItems->count(); $childIndex++) {
                    $childRaw = (string) $rawItems[$childIndex]->item;

                    if (preg_match('/^-\d+\./', $childRaw) === 1) {
                        break;
                    }

                    $childIndent = 0;
                    if (preg_match('/^(-+)/', $childRaw, $childIndentMatches)) {
                        $childIndent = strlen($childIndentMatches[1]);
                    }

                    if ($childIndent <= $indentLevel) {
                        break;
                    }

                    if ($childIndent >= 2) {
                        $hasEquipmentChildren = true;
                        break;
                    }
                }

                if ($hasEquipmentChildren) {
                    $items[] = [
                        'item_label' => ltrim($raw, '-'),
                        'indent_level' => $indentLevel,
                        'is_parent' => true,
                    ];
                    $hasEquipmentRows = true;
                }

                continue;
            }

            if ($isEquipmentItem) {
                $items[] = [
                    'item_label' => ltrim($raw, '-'),
                    'indent_level' => $indentLevel,
                    'is_parent' => false,
                    'skip_item_number' => true,
                    'rating' => isset($equipmentCheckSet[$raw]) ? '✓' : 'x',
                    'review_date' => $sectionReviewDate,
                    'reviewer_name' => $sectionReviewerName,
                    'comments' => '',
                ];
                $hasEquipmentRows = true;
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  list<array{item_label: string, indent_level: int, is_parent?: bool, rating?: string}>  $items
     */
    protected function competencySectionPdfRatedCount(string $sectionLabel, array $items, array $snapshot): int
    {
        if ($sectionLabel === 'TRACHEOSTOMY CARE') {
            $equipmentChecks = $this->normalizeTracheostomyChecks($snapshot['tracheostomy_equipment_checks'] ?? []);
            $procedureReviews = $this->normalizeTracheostomyProcedureReviews($snapshot['tracheostomy_procedure_reviews'] ?? []);

            return count($equipmentChecks) + count($procedureReviews);
        }

        return collect($items)
            ->filter(fn (array $item) => empty($item['is_parent']) && PartGCompetencyScoring::isValidItemRating($item['rating'] ?? null))
            ->count();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, EmployeeCompetencyItem>  $sectionItems
     */
    protected function countTracheostomyProcedureSteps(\Illuminate\Support\Collection $sectionItems): int
    {
        return $sectionItems
            ->filter(fn (EmployeeCompetencyItem $item) => preg_match('/^-\d+\./', (string) $item->item) === 1)
            ->count();
    }

    /**
     * @param  list<array{id: int, item_label: string, indent_level: int, is_parent: bool}>  $structuredItems
     * @param  array<int, true>  $ratedItemIds
     */
    protected function competencyParentHasRatedDescendant(array $structuredItems, int $parentIndex, array $ratedItemIds): bool
    {
        $parentIndent = $structuredItems[$parentIndex]['indent_level'];

        for ($index = $parentIndex + 1; $index < count($structuredItems); $index++) {
            $candidate = $structuredItems[$index];

            if ($candidate['indent_level'] <= $parentIndent) {
                break;
            }

            if (! $candidate['is_parent'] && isset($ratedItemIds[$candidate['id']])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $responses
     * @return array{response?: string, review_date?: string, reviewer_name?: string, comments?: string}
     */
    protected function competencyResponsePayloadForItem(array $responses, int $itemId): array
    {
        $raw = $responses[$itemId] ?? $responses[(string) $itemId] ?? null;

        if (is_array($raw)) {
            $normalized = PartGCompetencyScoring::normalizeItemRating($raw['response'] ?? null);

            return [
                'response' => $normalized,
                'review_date' => $raw['review_date'] ?? null,
                'reviewer_name' => $raw['reviewer_name'] ?? null,
                'comments' => $raw['comments'] ?? null,
            ];
        }

        if ($raw === null || $raw === '') {
            return [];
        }

        return [
            'response' => PartGCompetencyScoring::normalizeItemRating((string) $raw),
        ];
    }

    protected function formatCompetencyPdfShortDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('m-d-y');
        } catch (\Throwable) {
            $value = trim((string) $value);

            return strlen($value) >= 10 ? Carbon::parse(substr($value, 0, 10))->format('m-d-y') : $value;
        }
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    protected function resolveCompetencySectionReviewDate(
        EmployeeCompetencyAssessment $assessment,
        array $snapshot,
        string $sectionLabel,
    ): string {
        $sectionComments = is_array($snapshot['section_comments'][$sectionLabel] ?? null)
            ? $snapshot['section_comments'][$sectionLabel]
            : [];
        $sectionSummary = is_array($snapshot['section_summaries'][$sectionLabel] ?? null)
            ? $snapshot['section_summaries'][$sectionLabel]
            : [];

        $candidates = [
            $sectionComments['review_date'] ?? null,
            $sectionSummary['review_date'] ?? null,
            $sectionSummary['submitted_at'] ?? null,
            optional($assessment->review_date)->toDateString(),
            optional($assessment->submitted_at)->toDateString(),
            optional($assessment->updated_at)->toDateString(),
        ];

        foreach ($candidates as $candidate) {
            $formatted = $this->formatCompetencyPdfShortDate($candidate);
            if ($formatted !== '') {
                return $formatted;
            }
        }

        return $this->formatCompetencyPdfShortDate(now()->toDateString());
    }

    /**
     * @param  array<string, mixed>  $sectionComments
     * @return array<string, string>
     */
    protected function competencySectionPdfSignatureBlock(
        EmployeeCompetencyAssessment $assessment,
        array $sectionComments,
    ): array {
        $reviewDate = $sectionComments['review_date']
            ?? optional($assessment->review_date)->toDateString()
            ?? '';
        $employeeSignDate = $sectionComments['employee_signed_at']
            ?? optional($assessment->employee_signed_at)->toDateString()
            ?? '';

        return [
            'reviewer_comments' => trim((string) ($sectionComments['reviewer_comments'] ?? $assessment->comments ?? '')),
            'employee_comments' => trim((string) ($sectionComments['employee_comments'] ?? $assessment->employee_comments ?? '')),
            'reviewer_name' => trim((string) ($assessment->reviewer_name ?? '')),
            'reviewer_title' => trim((string) ($assessment->reviewer_title ?? '')),
            'review_sign_date' => $this->formatCompetencyPdfShortDate($reviewDate),
            'employee_name' => trim((string) ($assessment->employee_name ?? '')),
            'employee_title' => trim((string) ($assessment->employee_title ?? '')),
            'employee_sign_date' => $this->formatCompetencyPdfShortDate($employeeSignDate),
        ];
    }

    /**
     * @param  list<array{item_label: string, indent_level: int, rating: string, review_date?: string, reviewer_name?: string}>  $items
     * @return array{total_score: int, average_score: float, overall_rating: string}
     */
    protected function summarizeCompetencySectionItems(array $items): array
    {
        $total = 0;
        $count = 0;

        foreach ($items as $item) {
            $score = PartGCompetencyScoring::numericScore($item['rating'] ?? null);

            if ($score === null) {
                continue;
            }

            $total += $score;
            $count++;
        }

        $average = $count > 0 ? round($total / $count, 2) : 0;

        return [
            'total_score' => $total,
            'average_score' => $average,
            'overall_rating' => PartGCompetencyScoring::overallLabelOrNa($average, $count),
        ];
    }

    public function revoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_num' => 'required',
                'item_key' => 'required',
                'assessment_period_id' => 'required|integer|exists:employee_assessment_periods,id',
            ]);

            if ($response = $this->denyIfSelfAssessing((string) $validated['employee_num'])) {
                return $response;
            }

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
     * Create or update an assessment period (View Periods modal / Edit Period).
     */
    public function createPeriod(Request $request)
    {
        if (str_starts_with((string) $request->header('Content-Type'), 'application/json')) {
            $request->merge(json_decode($request->getContent(), true) ?? []);
        }

        $validated = $request->validate([
            'employee_num' => 'required|string|exists:bp_employees,employee_num',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'review_type' => 'required|string|in:A,Q',
            'id' => 'nullable|integer|exists:employee_assessment_periods,id',
            'review_date' => 'nullable|date',
            'use_rule' => 'sometimes|boolean',
            'force' => 'sometimes|boolean',
            'force_edit' => 'sometimes|boolean',
        ]);

        $employee = BPEmployee::query()->where('employee_num', $validated['employee_num'])->firstOrFail();
        $reviewType = $validated['review_type'];
        $on = ! empty($validated['review_date'])
            ? Carbon::parse($validated['review_date'])->startOfDay()
            : now()->startOfDay();

        if ($reviewType === 'A' && $request->boolean('use_rule')) {
            $annual = EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn($employee, $on);
            if (! $annual) {
                return response()->json([
                    'success' => false,
                    'message' => 'No prior completed annual period is available for this review date. Set hire/rehire dates on the Personal tab, or use a custom date range.',
                ], 422);
            }
            $validated['date_from'] = $annual['date_from'];
            $validated['date_to'] = $annual['date_to'];
        } elseif ($reviewType === 'A' && (empty($validated['date_from']) || empty($validated['date_to']))) {
            $annual = EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn($employee, $on);
            if ($annual) {
                $validated['date_from'] = $annual['date_from'];
                $validated['date_to'] = $annual['date_to'];
            }
        }

        if (empty($validated['date_from']) || empty($validated['date_to'])) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment period start and end dates are required.',
            ], 422);
        }

        $overlapQuery = EmployeeAssessmentPeriod::query()
            ->where('employee_num', $employee->employee_num)
            ->where('date_from', '<=', $validated['date_to'])
            ->where('date_to', '>=', $validated['date_from']);

        if (! empty($validated['id'])) {
            $overlapQuery->where('id', '!=', $validated['id']);
        }

        if ($overlapQuery->exists() && ! $request->boolean('force')) {
            return response()->json([
                'success' => false,
                'warning' => true,
                'message' => 'This date range overlaps another assessment period for this employee. Save anyway?',
            ], 409);
        }

        if (! empty($validated['id'])) {
            $period = EmployeeAssessmentPeriod::query()
                ->where('id', $validated['id'])
                ->where('employee_num', $employee->employee_num)
                ->firstOrFail();

            if ($period->employeeHasAssessmentData() && ! $request->boolean('force_edit')) {
                return response()->json([
                    'success' => false,
                    'warning' => true,
                    'message' => 'This employee already has assessment data for this period. Edit anyway?',
                ], 409);
            }

            $period->fill([
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'review_type' => $reviewType,
                'period_year' => (int) date('Y', strtotime($validated['date_from'])),
            ]);
            $period->save();
        } else {
            $period = EmployeeAssessmentPeriod::query()->firstOrCreate(
                [
                    'employee_num' => $employee->employee_num,
                    'date_from' => $validated['date_from'],
                    'date_to' => $validated['date_to'],
                ],
                [
                    'review_type' => $reviewType,
                    'period_year' => (int) date('Y', strtotime($validated['date_from'])),
                    'period_sequence' => 0,
                    'created_by' => Auth::id(),
                ]
            );

            if ($period->review_type !== $reviewType) {
                $period->review_type = $reviewType;
                $period->save();
            }
        }

        $fresh = $period->fresh();
        $fresh->setAttribute('date_from', $fresh->date_from?->format('Y-m-d'));
        $fresh->setAttribute('date_to', $fresh->date_to?->format('Y-m-d'));

        return response()->json([
            'success' => true,
            'message' => empty($validated['id']) ? 'Assessment period saved.' : 'Assessment period updated.',
            'data' => $fresh,
            'period' => $fresh,
        ]);
    }
}

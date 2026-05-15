<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeCompetencyItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LnCompetencySkillsController extends Controller
{
    public function show(Request $request)
    {
        Log::info('LN DEBUG: CONTROLLER EXECUTED');
        Log::info('LN DEBUG: controller loaded');
        // Example: get employee (customize as needed)
        $employee = $request->user(); // or fetch by ID/route param
        $selectedCompetencyAssessment = null; // Fetch as needed

        // Dummy/fake assessment periods for now; replace with real query/model as needed
        $assessmentPeriods = [
            (object)[
                'id' => 1,
                'period_year' => date('Y'),
                'date_from' => date('Y-01-01'),
                'date_to' => date('Y-12-31'),
            ],
        ];

        $selectedAssessmentPeriodId = request('assessment_period_id') ?? ($assessmentPeriods[0]->id ?? null);

        // Position and guidance
        $partGPosition = 'No Position Assigned';
        if ($employee && $employee->currentAssignment && $employee->currentAssignment->position) {
            $partGPosition = $employee->currentAssignment->position->title;
        }
        
        $partGLicensedNurseGuidancePositions = [
            'Director of Nursing',
            'Registered Nurse',
            'Licensed Vocational Nurse',
            'Licensed Nurse',
            'Charge Nurse',
            'IP Nurse',
        ];
        $partGShowLicensedNurseGuidance = in_array($partGPosition, $partGLicensedNurseGuidancePositions, true);

        // Assessment status
        $partGSubmissionStatus = $selectedCompetencyAssessment->status ?? null;
        $partGAssessmentLocked = $partGSubmissionStatus === 'completed';
        $partGSubmissionStatusLabel = $partGSubmissionStatus ? ucwords(str_replace('_', ' ', (string) $partGSubmissionStatus)) : null;

        // Excluded sections and other state
        $partGDontIncludeSections = [
            'BLOOD ADMINISTRATION COMPETENCY',
            'BLOOD GLUCOSE SYSTEM SKILLS COMPETENCY',
            'TRACHEOSTOMY CARE COMPETENCY',
            'NURSE TREATMENT SKILLS COMPETENCY',
            'HAND HYGIENE COMPETENCY SKILLS',
            'VENTILATOR MANAGEMENT SKILLS COMPETENCY',
            'PERSONAL PROTECTIVE EQUIPMENT (PPE)',
            'MEDICATION ADMINISTRATION COMPETENCY',
            'USE OF HOYER LIFT TRAINING',
            'CNA SKILLS CHECKLIST',
            'PERINEAL CARE',
            'DIRECTOR OF STAFF DEVELOPMENT COMPETENCIES',
        ];
        $partGExcludedSectionLabels = [];
        $partGTracheostomyEquipmentChecks = [];
        $partGTracheostomyProcedureReviews = [];

        // All items grouped by section
        $employeeCompetencyItems = EmployeeCompetencyItem::orderBy('order')->get();
        $partGSections = $employeeCompetencyItems->groupBy('section');

        // Prepare display logic for LICENSED NURSE COMPETENCY SKILLS
        $lnItems = $partGSections['LICENSED NURSE COMPETENCY SKILLS'] ?? collect();
        $lnCompetencyItems = [];
        foreach ($lnItems as $index => $item) {
            $indentLevel = 0;
            if (preg_match('/^(-+)/', $item->item, $matches)) {
                $indentLevel = strlen($matches[1]);
            }
            $isParent = false;
            if (isset($lnItems[$index + 1])) {
                if (preg_match('/^(-+)/', $lnItems[$index + 1]->item, $nextMatches)) {
                    $nextIndent = strlen($nextMatches[1]);
                    $isParent = $nextIndent > $indentLevel;
                }
            }
            $lnCompetencyItems[] = [
                'id' => $item->id,
                'item' => ltrim($item->item, '-'),
                'indentLevel' => $indentLevel,
                'isParent' => $isParent,
                'rowClass' => $index % 2 === 0 ? 'bg-slate-200' : 'bg-slate-50',
            ];
        }

        // Load saved draft responses for the current user
        $employeeNum = $employee->employee_num ?? null;
        $draftResponses = [];
        $rawDraftRow = null;

        if ($employeeNum && $selectedAssessmentPeriodId) {
            // Try to load draft first
            $jsonRow = DB::table('ln_competency_skill_responses')
                ->where('employee_num', $employeeNum)
                ->where('employee_assessment_period_id', $selectedAssessmentPeriodId)
                ->where('is_draft', (int)1)
                ->first();
            $rawDraftRow = $jsonRow;
            if (!$jsonRow) {
                // If no draft, load final
                $jsonRow = DB::table('ln_competency_skill_responses')
                    ->where('employee_num', $employeeNum)
                    ->where('employee_assessment_period_id', $selectedAssessmentPeriodId)
                    ->where(function($q) {
                        $q->where('is_draft', 0)->orWhereNull('is_draft');
                    })
                    ->first();
                $rawDraftRow = $jsonRow;
            }
            if ($jsonRow && $jsonRow->responses) {
                Log::info('LN DEBUG: raw responses', ['responses' => $jsonRow->responses]);
                $decoded = json_decode($jsonRow->responses, true);
                // If still a string, decode again (handle double-encoding)
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                Log::info('LN DEBUG: decoded responses', ['decoded' => $decoded]);
                // Flatten to [competency_item_id => response]
                if (is_array($decoded)) {
                    foreach ($decoded as $itemId => $data) {
                        $draftResponses[$itemId] = $data['response'] ?? null;
                    }
                }
                Log::info('LN DEBUG: draftResponses array', ['draftResponses' => $draftResponses]);
            }
        }

        // Dummy/fake assessment periods for now; replace with real query/model as needed
        $assessmentPeriods = [
            (object)[
                'id' => 1,
                'period_year' => date('Y'),
                'date_from' => date('Y-01-01'),
                'date_to' => date('Y-12-31'),
            ],
        ];

        $selectedAssessmentPeriodId = request('assessment_period_id') ?? ($assessmentPeriods[0]->id ?? null);

        return view('admin.facilities.checklist.employee-checklist-part_g', [
            'employee' => $employee,
            'assessmentPeriods' => $assessmentPeriods,
            'selectedAssessmentPeriodId' => $selectedAssessmentPeriodId,
            'partGSections' => $partGSections,
            'partGPosition' => $partGPosition,
            'partGSubmissionStatusLabel' => $partGSubmissionStatusLabel,
            'partGAssessmentLocked' => $partGAssessmentLocked,
            'partGLicensedNurseGuidancePositions' => $partGLicensedNurseGuidancePositions,
            'partGShowLicensedNurseGuidance' => $partGShowLicensedNurseGuidance,
            'partGSubmissionStatus' => $partGSubmissionStatus,
            'partGDontIncludeSections' => $partGDontIncludeSections,
            'partGExcludedSectionLabels' => $partGExcludedSectionLabels,
            'partGTracheostomyEquipmentChecks' => $partGTracheostomyEquipmentChecks,
            'partGTracheostomyProcedureReviews' => $partGTracheostomyProcedureReviews,
            'lnCompetencyItems' => $lnCompetencyItems,
            'draftResponses' => $draftResponses,
            'rawDraftRow' => $rawDraftRow,
        ]);
    }
}

{{-- All logic is now in the controller. Only use passed variables. --}}
<div id="partG" class="tab-content">
    <div class="overflow-x-auto">
        {{-- All logic moved to controller. The following variables must be passed: --}}

            @php
                $draftResponses = $draftResponses ?? [];
        $partGSections = $employeeCompetencyItems->groupBy('section');
        $partGPosition = $employee->currentAssignment?->position?->title ?? 'No Position Assigned';
        
        $partGLicensedNurseGuidancePositions = [
            'Director of Nursing',
            'Registered Nurse',
            'Licensed Vocational Nurse',
            'Licensed Nurse',
            'Charge Nurse',
            'IP Nurse',
        ];
        $partGShowLicensedNurseGuidance = in_array($partGPosition, $partGLicensedNurseGuidancePositions, true);
        $partGShowCnaSkillsChecklist = $partGSections->has('CNA SKILLS');
        $partGShowPerinealCare = $partGSections->has('PERINEAL CARE');
        $partGShowHoyerLiftTraining = $partGSections->has('USE OF HOYER LIFT');
        $partGShowCnaCompetencyGuidance = ! $partGShowLicensedNurseGuidance
            && ($partGShowCnaSkillsChecklist || $partGShowPerinealCare || $partGShowHoyerLiftTraining);
        $partGShowDsdCompetency = $partGSections->keys()
            ->intersect(collect(\App\Livewire\Admin\Facilities\Checklist\PartGSections\DirectorOfStaffDevelopmentCompetency::dsdSectionKeys()))
            ->isNotEmpty();
        $partGSubmissionStatus = $selectedCompetencyAssessment?->status;
        $partGWorkflowStatus = $selectedCompetencyAssessment?->workflowStatus() ?? \App\Support\AssessmentWorkflowStatus::DRAFT;
        $partGAssessmentLocked = \App\Support\AssessmentWorkflowStatus::isLocked($partGWorkflowStatus);
        $partGEmployeeCanConfirm = \App\Support\AssessmentWorkflowStatus::employeeCanConfirm($partGWorkflowStatus);
        $partGReviewerCanApprove = \App\Support\AssessmentWorkflowStatus::reviewerCanApprove($partGWorkflowStatus);
        $partGSubmissionStatusLabel = \App\Support\AssessmentWorkflowStatus::label($partGWorkflowStatus);
        $partGRatingsLocked = $partGAssessmentLocked
            || $partGEmployeeCanConfirm
            || ($partGReviewerCanApprove && !empty($evaluatorActionsDisabled))
            || (!empty($evaluatorActionsDisabled) && ! $partGEmployeeCanConfirm);
        $partGCompetencyEmployeeComments = $selectedCompetencyAssessment?->employee_comments ?? '';
        $partGCompetencyEmployeeAckDate = $selectedCompetencyAssessment?->employee_signed_at?->format('Y-m-d')
            ?? ($partGEmployeeCanConfirm ? now()->toDateString() : '');
        $partGDontIncludeSections = [
            'BLOOD ADMINISTRATION',
            'BLOOD GLUCOSE SYSTEM SKILLS',
            'TRACHEOSTOMY CARE',
            'NURSE TREATMENT SKILLS',
            'HAND HYGIENE SKILLS',
            'VENTILATOR MANAGEMENT SKILLS',
            'PERSONAL PROTECTIVE EQUIPMENT (PPE)',
            'MEDICATION ADMINISTRATION',
            'USE OF HOYER LIFT',
            'CNA SKILLS',
            'PERINEAL CARE',
            'DIRECTOR OF STAFF DEVELOPMENT',
        ];
        $partGExcludedSectionLabels = collect($selectedCompetencyAssessment?->snapshot_json['excluded_section_labels'] ?? [])
            ->filter(fn ($sectionLabel) => filled($sectionLabel))
            ->map(fn ($sectionLabel) => (string) $sectionLabel)
            ->values()
            ->all();
        $partGTracheostomyEquipmentChecks = collect($selectedCompetencyAssessment?->snapshot_json['tracheostomy_equipment_checks'] ?? [])
            ->map(fn ($itemLabel) => (string) $itemLabel)
            ->filter(fn ($itemLabel) => filled($itemLabel))
            ->values()
            ->all();
        $partGTracheostomyProcedureReviews = collect($selectedCompetencyAssessment?->snapshot_json['tracheostomy_procedure_reviews'] ?? [])
            ->mapWithKeys(function ($rating, $procedureKey) {
                $normalized = \App\Support\PartGCompetencyScoring::normalizeItemRating((string) $rating);

                return $normalized === null
                    ? []
                    : [(string) $procedureKey => $normalized];
            })
            ->all();
        $hasAssessmentPeriod = !empty($selectedAssessmentPeriodId);
        $partGHasCompetenciesForPosition = $employeeCompetencyItems->isNotEmpty();
        $partGHasPositionAssigned = filled($employee->currentAssignment?->position_id ?? $employee->currentAssignment?->position?->id);

        @endphp
        {{-- $partGSections, $partGPosition, $partGLicensedNurseGuidancePositions, $partGShowLicensedNurseGuidance, $partGSubmissionStatus, $partGAssessmentLocked, $partGSubmissionStatusLabel, $partGDontIncludeSections, $partGExcludedSectionLabels, $partGTracheostomyEquipmentChecks, $partGTracheostomyProcedureReviews --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-bold">COMPETENCIES CHECKLIST: {{ $partGPosition }}</h2>
            @if($selectedCompetencyAssessment && $partGSubmissionStatusLabel)
            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $partGAssessmentLocked ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-900' }}">{{ $partGAssessmentLocked ? 'Read Only' : $partGSubmissionStatusLabel }}</span>
            @endif
        </div>
        <div class="mb-4 grid gap-3 xl:grid-cols-2 xl:items-stretch">
            <div>
                @include('admin.facilities.checklist.employee-assessment-subject-summary', [
                    'managerId' => 'partG',
                ])
            </div>

            <div>
                @include('admin.facilities.checklist.employee-assessment-period-manager', [
                    'managerId' => 'partG',
                    'contextLabel' => 'Competency Assessment',
                ])
            </div>
        </div>

        @if(!$partGHasPositionAssigned)
        <p class="mb-4 rounded-md border border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
            Assign a position to this employee to load the competencies checklist.
        </p>
        @elseif(!$partGHasCompetenciesForPosition)
        <p class="mb-4 rounded-md border border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
            No competency checklist items apply to &ldquo;{{ $partGPosition }}&rdquo; for this employee.
            Competency assessments are configured per position; assign a different position or contact an administrator if this role should have a checklist.
        </p>
        @endif

        @if(!$hasAssessmentPeriod)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800 shadow-sm" role="alert">
            <strong>No assessment period selected.</strong> Please create or select an assessment period above to enable
            competency assessment actions.
        </div>
        @elseif($partGHasCompetenciesForPosition)
        <div class="mb-4">
            @include('admin.facilities.checklist.partials.part-g-average-legend')
        </div>
        @if($partGShowLicensedNurseGuidance)
            <p class="mb-1 text-[11px] leading-relaxed text-slate-700 md:text-xs">
                These competencies checklists are intended for all licensed nurses. If a section does not apply to the employee&rsquo;s position, check that checkbox <strong>Exclude</strong> so it is not counted in the assessment.
            </p>
            @livewire('admin.facilities.checklist.part-g-sections.licensed-nurse-competency-skills', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('licensed-nurse-competency-skills-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.licensed-nurse-emar-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('licensed-nurse-emar-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.licensed-nurse-point-of-care-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('licensed-nurse-point-of-care-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.matrixcare-physician-order-documentation-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('matrixcare-physician-order-documentation-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.blood-administration-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('blood-administration-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.blood-glucose-system-skills-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('blood-glucose-system-skills-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.tracheostomy-care-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('tracheostomy-care-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.nurse-treatment-skills-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('nurse-treatment-skills-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.hand-hygiene-competency-skills', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('hand-hygiene-competency-skills-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.ventilator-management-skills-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('ventilator-management-skills-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.personal-protective-equipment-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('personal-protective-equipment-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

            @livewire('admin.facilities.checklist.part-g-sections.medication-administration-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('medication-administration-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowCnaCompetencyGuidance)
            <p class="mb-1 text-[11px] leading-relaxed text-slate-700 md:text-xs">
                Complete the competency checklists below for this assessment period. If a section does not apply, check <strong>Exclude</strong> so it is not counted in the assessment.
            </p>
        @endif

        @if($partGShowCnaSkillsChecklist)
            @livewire('admin.facilities.checklist.part-g-sections.cna-skills-checklist-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('cna-skills-checklist-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowPerinealCare)
            @livewire('admin.facilities.checklist.part-g-sections.perineal-care-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('perineal-care-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowHoyerLiftTraining)
            @livewire('admin.facilities.checklist.part-g-sections.use-of-hoyer-lift-training-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('use-of-hoyer-lift-training-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowDsdCompetency)
            <p class="mb-1 text-[11px] leading-relaxed text-slate-700 md:text-xs">
                The following areas apply to the Director of Staff Development role. If the assessment does not apply for this period, check <strong>Exclude</strong> so it is not counted.
            </p>
            @livewire('admin.facilities.checklist.part-g-sections.director-of-staff-development-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGRatingsLocked,
            ], key('director-of-staff-development-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif
        @endif

        @include('admin.facilities.checklist.part-g-competency-workflow-form')

        @livewire('admin.facilities.checklist.competency-assessment-history-table', [
            'employeeNum' => $employee->employee_num,
            'selectedAssessmentPeriodId' => $selectedAssessmentPeriodId,
        ], key('competency-assessment-history-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))

    </div>
</div>
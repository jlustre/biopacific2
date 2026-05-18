 <!-- @if($partGSections->has('LICENSED NURSE COMPETENCY SKILLS'))
            @livewire('admin.facilities.checklist.part-g-sections.licensed-nurse-competency-skills', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('licensed-nurse-competency-skills-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('LICENSED NURSE eMAR COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.licensed-nurse-emar-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('licensed-nurse-emar-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('LICENSED NURSE POINT OF CARE COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.licensed-nurse-point-of-care-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('licensed-nurse-point-of-care-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('MATRIXCARE PHYSICIAN ORDER AND DOCUMENTATION COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.matrixcare-physician-order-documentation-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('matrixcare-physician-order-documentation-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('BLOOD ADMINISTRATION COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.blood-administration-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('blood-administration-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('BLOOD GLUCOSE SYSTEM SKILLS COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.blood-glucose-system-skills-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('blood-glucose-system-skills-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('TRACHEOSTOMY CARE COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.tracheostomy-care-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('tracheostomy-care-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('NURSE TREATMENT SKILLS COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.nurse-treatment-skills-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('nurse-treatment-skills-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('HAND HYGIENE COMPETENCY SKILLS'))
            @livewire('admin.facilities.checklist.part-g-sections.hand-hygiene-competency-skills', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('hand-hygiene-competency-skills-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('VENTILATOR MANAGEMENT SKILLS COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.ventilator-management-skills-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('ventilator-management-skills-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        <!-- @if($partGSections->has('PERSONAL PROTECTIVE EQUIPMENT (PPE)'))
            @livewire('admin.facilities.checklist.part-g-sections.personal-protective-equipment-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('personal-protective-equipment-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif -->

        @if($partGSections->has('MEDICATION ADMINISTRATION COMPETENCY'))
            @livewire('admin.facilities.checklist.part-g-sections.medication-administration-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
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
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('cna-skills-checklist-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowPerinealCare)
            @livewire('admin.facilities.checklist.part-g-sections.perineal-care-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('perineal-care-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowHoyerLiftTraining)
            @livewire('admin.facilities.checklist.part-g-sections.use-of-hoyer-lift-training-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('use-of-hoyer-lift-training-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif

        @if($partGShowDsdCompetency)
            <p class="mb-1 text-[11px] leading-relaxed text-slate-700 md:text-xs">
                The following areas apply to the Director of Staff Development role. If the assessment does not apply for this period, check <strong>Exclude</strong> so it is not counted.
            </p>
            @livewire('admin.facilities.checklist.part-g-sections.director-of-staff-development-competency', [
                'employeeNum' => $employee->employee_num,
                'assessmentPeriodId' => $selectedAssessmentPeriodId,
                'assessmentLocked' => $partGAssessmentLocked,
            ], key('director-of-staff-development-competency-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none')))
        @endif
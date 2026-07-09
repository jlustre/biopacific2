@include('admin.facilities.checklist.partials.part-g-section-acknowledgement-form', [
    'employeeNum' => $employeeNum ?? $this->employeeNum,
    'assessmentPeriodId' => $assessmentPeriodId ?? $this->assessmentPeriodId,
    'acknowledgementKey' => $acknowledgementKey ?? $this->partGAccordionKey(),
    'sectionLabel' => $sectionLabel ?? $this->partGSectionLabel(),
])

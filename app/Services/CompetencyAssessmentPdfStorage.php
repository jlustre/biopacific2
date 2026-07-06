<?php

namespace App\Services;

use App\Models\EmployeeCompetencyAssessment;
use Illuminate\Support\Facades\Storage;

class CompetencyAssessmentPdfStorage
{
    public function assessmentPdfPath(EmployeeCompetencyAssessment $assessment): string
    {
        return 'competency-assessments/'.$assessment->employee_num.'/assessment-'.$assessment->id.'.pdf';
    }

    public function sectionPdfDirectory(EmployeeCompetencyAssessment $assessment): string
    {
        return 'competency-assessments/'.$assessment->employee_num.'/assessment-'.$assessment->id;
    }

    public function sectionPdfPath(EmployeeCompetencyAssessment $assessment, string $sectionLabel): string
    {
        $sectionKey = substr(md5($sectionLabel), 0, 12);

        return $this->sectionPdfDirectory($assessment).'/section-'.$sectionKey.'.pdf';
    }

    public function deletePdfFile(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    public function deleteSectionPdfs(EmployeeCompetencyAssessment $assessment): void
    {
        $directory = $this->sectionPdfDirectory($assessment);
        $disk = Storage::disk('public');

        foreach ($disk->allFiles($directory) as $file) {
            $disk->delete($file);
        }

        if ($disk->exists($directory)) {
            $disk->deleteDirectory($directory);
        }
    }

    /**
     * Remove the full assessment PDF and all section PDFs for this assessment record.
     */
    public function deleteAllPdfs(EmployeeCompetencyAssessment $assessment): void
    {
        $paths = array_unique(array_filter([
            $assessment->pdf_path,
            $this->assessmentPdfPath($assessment),
        ]));

        foreach ($paths as $path) {
            $this->deletePdfFile($path);
        }

        $this->deleteSectionPdfs($assessment);
    }
}

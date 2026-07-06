<?php

namespace Tests\Unit;

use App\Models\EmployeeCompetencyAssessment;
use App\Services\CompetencyAssessmentPdfStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompetencyAssessmentPdfStorageTest extends TestCase
{
    public function test_delete_all_pdfs_removes_full_and_section_copies(): void
    {
        Storage::fake('public');

        $assessment = new EmployeeCompetencyAssessment([
            'id' => 77,
            'employee_num' => 'EMP-077',
            'assessment_period_id' => 5,
            'pdf_path' => 'competency-assessments/EMP-077/legacy-assessment.pdf',
        ]);

        $storage = app(CompetencyAssessmentPdfStorage::class);

        Storage::disk('public')->put($assessment->pdf_path, 'legacy');
        Storage::disk('public')->put($storage->assessmentPdfPath($assessment), 'current');
        Storage::disk('public')->put(
            $storage->sectionPdfPath($assessment, 'LICENSED NURSE eMAR COMPETENCY'),
            'section'
        );

        $storage->deleteAllPdfs($assessment);

        Storage::disk('public')->assertMissing($assessment->pdf_path);
        Storage::disk('public')->assertMissing($storage->assessmentPdfPath($assessment));
        Storage::disk('public')->assertMissing(
            $storage->sectionPdfPath($assessment, 'LICENSED NURSE eMAR COMPETENCY')
        );
    }

    public function test_persist_replaces_existing_full_pdf_without_leaving_legacy_path(): void
    {
        Storage::fake('public');

        $assessment = EmployeeCompetencyAssessment::make([
            'id' => 88,
            'employee_num' => 'EMP-088',
            'assessment_period_id' => 6,
            'pdf_path' => 'competency-assessments/EMP-088/old-name.pdf',
            'snapshot_json' => ['items' => []],
            'status' => 'completed',
        ]);

        $storage = app(CompetencyAssessmentPdfStorage::class);
        Storage::disk('public')->put($assessment->pdf_path, 'old');

        $previousPath = $assessment->pdf_path;
        $newPath = $storage->assessmentPdfPath($assessment);

        $storage->deleteSectionPdfs($assessment);
        Storage::disk('public')->put($newPath, 'new');

        if ($previousPath && $previousPath !== $newPath) {
            $storage->deletePdfFile($previousPath);
        }

        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists($newPath);
    }
}

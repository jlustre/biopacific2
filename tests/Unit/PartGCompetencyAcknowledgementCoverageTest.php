<?php

namespace Tests\Unit;

use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionWorkflowUi;
use App\Services\CompetencySectionWorkflowService;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Tests\TestCase;

class PartGCompetencyAcknowledgementCoverageTest extends TestCase
{
    /**
     * @return list<class-string>
     */
    private function partGCompetencyComponentClasses(): array
    {
        $directory = app_path('Livewire/Admin/Facilities/Checklist/PartGSections');
        $classes = [];

        foreach (File::files($directory) as $file) {
            if (! str_ends_with($file->getFilename(), 'Competency.php')
                && ! str_ends_with($file->getFilename(), 'CompetencySkills.php')) {
                continue;
            }

            $class = 'App\\Livewire\\Admin\\Facilities\\Checklist\\PartGSections\\'.$file->getFilenameWithoutExtension();
            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }

    public function test_all_part_g_competency_components_expose_section_workflow_metadata(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $classes = $this->partGCompetencyComponentClasses();

        $this->assertNotEmpty($classes);

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->hasConstant('SECTION'),
                $class.' is missing a SECTION constant.',
            );

            $sectionLabel = (string) $reflection->getConstant('SECTION');
            $this->assertNotSame('', trim($sectionLabel), $class.' has a blank SECTION constant.');

            $traits = class_uses_recursive($class);
            $this->assertContains(
                ManagesPartGSectionWorkflowUi::class,
                $traits,
                $class.' must use ManagesPartGSectionWorkflowUi.',
            );

            $accordionKey = $service->accordionKeyForSectionLabel($sectionLabel);
            $this->assertNotNull(
                $accordionKey,
                'Missing accordion key mapping for section ['.$sectionLabel.'] used by '.$class.'.',
            );

            $bladePath = resource_path('views/livewire/admin/facilities/checklist/part-g-sections/'.str($class)->classBasename()->kebab()->value().'.blade.php');
            $this->assertFileExists($bladePath, 'Missing blade view for '.$class);

            $bladeContents = File::get($bladePath);
            $this->assertStringContainsString(
                'section-acknowledgement-host',
                $bladeContents,
                $class.' blade must include the shared section acknowledgement host partial.',
            );
        }
    }
}

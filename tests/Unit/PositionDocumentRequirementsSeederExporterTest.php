<?php

namespace Tests\Unit;

use App\Services\PositionDocumentRequirementsSeederExporter;
use Tests\TestCase;

class PositionDocumentRequirementsSeederExporterTest extends TestCase
{
    public function test_it_infers_known_document_sets_for_a_position(): void
    {
        $exporter = app(PositionDocumentRequirementsSeederExporter::class);
        $sets = [
            'all_staff' => ['I-9 Form', 'W-4 Form'],
            'annual_compliance' => ['Annual In-Service Training'],
            'rn_license' => ['Registered Nurse License'],
        ];

        $inferred = $exporter->inferSetNamesForDocuments(
            ['Annual In-Service Training', 'I-9 Form', 'Registered Nurse License', 'W-4 Form'],
            $sets
        );

        $this->assertSame(['all_staff', 'annual_compliance', 'rn_license'], $inferred);
    }

    public function test_it_returns_empty_when_documents_do_not_match_known_sets(): void
    {
        $exporter = app(PositionDocumentRequirementsSeederExporter::class);
        $sets = [
            'all_staff' => ['I-9 Form', 'W-4 Form'],
        ];

        $inferred = $exporter->inferSetNamesForDocuments(['I-9 Form', 'Custom Document'], $sets);

        $this->assertSame([], $inferred);
    }
}

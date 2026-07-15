<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentComplianceAccessTest extends TestCase
{
    public function test_documents_settings_requires_authentication(): void
    {
        $this->get('/admin/upload-types?tab=requirements')
            ->assertRedirect('/login');
    }

    public function test_facility_document_files_are_not_public(): void
    {
        $this->get('/admin/facility/creekside-healthcare-center/uploads/1/view')
            ->assertRedirect('/login');

        $this->get('/admin/facility/creekside-healthcare-center/uploads/1/download')
            ->assertRedirect('/login');
    }
}

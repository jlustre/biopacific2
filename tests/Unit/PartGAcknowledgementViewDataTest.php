<?php

namespace Tests\Unit;

use App\Support\PartGAcknowledgementViewData;
use Tests\TestCase;

class PartGAcknowledgementViewDataTest extends TestCase
{
    public function test_build_returns_null_without_section_label(): void
    {
        $this->assertNull(PartGAcknowledgementViewData::build('EMP-100', 12));
        $this->assertNull(PartGAcknowledgementViewData::build('EMP-100', 12, null, ''));
    }
}

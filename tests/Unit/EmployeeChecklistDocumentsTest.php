<?php

namespace Tests\Unit;

use App\Models\BPEmpChecklist;
use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use App\Support\EmployeeChecklistDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeChecklistDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_verified_sets_verified_by_for_parts_a_through_d(): void
    {
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-CHECK-001',
            'first_name' => 'Checklist',
            'last_name' => 'Employee',
        ]);

        foreach (['PART A', 'PART B', 'PART C', 'PART D'] as $index => $section) {
            $item = ChecklistItem::query()->create([
                'name' => "Doc {$section}",
                'section' => $section,
                'doc_type_id' => 1,
                'order' => $index + 1,
                'isExpiring' => false,
            ]);

            EmployeeChecklistDocuments::markVerified(
                $employee,
                $item,
                '2026-07-15',
                null,
                42
            );

            $checklist = BPEmpChecklist::query()
                ->where('employee_num', $employee->employee_num)
                ->first();

            $entry = $checklist->items['item_' . $item->id] ?? null;

            $this->assertIsArray($entry);
            $this->assertTrue($entry['on_file']);
            $this->assertSame('2026-07-15', $entry['verified_dt']);
            $this->assertSame(42, $entry['verified_by']);
        }
    }

    public function test_mark_on_file_clears_verification(): void
    {
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-CHECK-002',
            'first_name' => 'Checklist',
            'last_name' => 'Employee',
        ]);
        $item = ChecklistItem::query()->create([
            'name' => 'Pending Doc',
            'section' => 'PART A',
            'doc_type_id' => 1,
            'order' => 1,
            'isExpiring' => false,
        ]);

        EmployeeChecklistDocuments::markVerified($employee, $item, '2026-07-15', null, 7);
        EmployeeChecklistDocuments::markOnFile($employee, $item);

        $entry = BPEmpChecklist::query()
            ->where('employee_num', $employee->employee_num)
            ->first()
            ->items['item_' . $item->id];

        $this->assertTrue($entry['on_file']);
        $this->assertNull($entry['verified_dt']);
        $this->assertNull($entry['verified_by']);
    }
}

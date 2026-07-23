<?php

namespace Tests\Unit;

use App\Models\BPEmpJobData;
use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use App\Models\Department;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\User;
use App\Services\ChecklistUploadTypeSyncService;
use App\Services\DocumentCatalogDedupeService;
use App\Services\EmployeeDocumentRequirementsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentCatalogUnificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_upload_type_upserts_matching_checklist_item_name(): void
    {
        $type = UploadType::query()->create([
            'name' => 'Application Form',
            'requires_expiry' => false,
            'checklist_section' => 'PART A',
            'doc_type_id' => null,
            'sort_order' => 1,
            'applies_to_all_positions' => true,
        ]);

        $item = app(ChecklistUploadTypeSyncService::class)->syncUploadType($type->fresh());

        $this->assertNotNull($item);
        $this->assertSame('Application Form', $item->name);
        $this->assertSame('PART A', $item->section);
        $this->assertSame($item->id, $type->fresh()->checklist_item_id);

        $type->update(['name' => 'Employment Application']);
        $item = app(ChecklistUploadTypeSyncService::class)->syncUploadType($type->fresh());

        $this->assertSame('Employment Application', $item->name);
        $this->assertSame('Employment Application', ChecklistItem::query()->find($item->id)->name);
    }

    public function test_dedupe_merges_duplicate_general_type_into_canonical_name(): void
    {
        $facility = Facility::query()->create([
            'name' => 'Dedupe Facility',
            'slug' => 'dedupe-facility',
            'color_scheme_id' => null,
        ]);
        $user = User::factory()->create();
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-DEDUP-1',
            'first_name' => 'Dedup',
            'last_name' => 'Employee',
            'user_id' => $user->id,
        ]);

        $canonical = UploadType::query()->create([
            'name' => 'I - 9*',
            'requires_expiry' => true,
            'checklist_section' => 'PART A',
            'applies_to_all_positions' => true,
        ]);
        app(ChecklistUploadTypeSyncService::class)->syncUploadType($canonical);

        $duplicate = UploadType::query()->create([
            'name' => 'I-9 Form',
            'requires_expiry' => false,
            'checklist_section' => null,
        ]);

        Upload::query()->create([
            'facility_id' => $facility->id,
            'employee_num' => $employee->employee_num,
            'user_id' => $user->id,
            'upload_type_id' => $duplicate->id,
            'file_path' => 'employee_documents/EMP-DEDUP-1/i9.pdf',
            'original_filename' => 'i9.pdf',
            'uploaded_at' => now(),
            'verification_status' => Upload::VERIFICATION_APPROVED,
        ]);

        $result = app(DocumentCatalogDedupeService::class)->run();

        $this->assertGreaterThanOrEqual(1, $result['merged']);
        $this->assertTrue(UploadType::query()->where('name', 'I-9')->exists());
        $this->assertFalse(UploadType::query()->where('name', 'I-9 Form')->exists());
        $this->assertSame(
            (int) UploadType::query()->where('name', 'I-9')->value('id'),
            (int) Upload::query()->where('original_filename', 'i9.pdf')->value('upload_type_id')
        );
        $this->assertSame('I-9', ChecklistItem::query()->where('section', 'PART A')->where('name', 'I-9')->value('name'));
    }

    public function test_catalog_for_employee_respects_all_positions_and_specific_requirements(): void
    {
        $department = Department::query()->create([
            'name' => 'Nursing',
            'is_active' => true,
        ]);
        $position = Position::query()->create([
            'title' => 'Registered Nurse',
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $other = Position::query()->create([
            'title' => 'Cook',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        UploadType::query()->create([
            'name' => 'W-4',
            'checklist_section' => 'PART C',
            'applies_to_all_positions' => true,
        ]);
        $rnOnly = UploadType::query()->create([
            'name' => 'Professional License',
            'checklist_section' => 'PART A',
            'applies_to_all_positions' => false,
        ]);
        UploadType::query()->create([
            'name' => 'Unused Doc',
            'applies_to_all_positions' => false,
        ]);

        $rnOnly->positions()->sync([$position->id => ['is_required' => true]]);

        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-CAT-1',
            'first_name' => 'Nurse',
            'last_name' => 'Test',
        ]);

        BPEmpJobData::query()->create([
            'employee_num' => $employee->employee_num,
            'effdt' => '2024-01-01',
            'effseq' => 1,
            'position_id' => $position->id,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $employee = $employee->fresh(['currentAssignment']);
        $this->assertNotNull($employee->currentAssignment);

        $service = app(EmployeeDocumentRequirementsService::class);
        $catalog = $service->catalogUploadTypesForEmployee($employee);
        $names = $catalog->pluck('name')->all();

        $this->assertContains('W-4', $names);
        $this->assertContains('Professional License', $names);
        $this->assertNotContains('Unused Doc', $names);

        $otherReq = $service->requiredGeneralUploadTypesForPosition($other)->pluck('name')->all();
        $this->assertContains('W-4', $otherReq);
        $this->assertNotContains('Professional License', $otherReq);
    }

    public function test_part_e_checklist_items_are_not_document_catalog_entries(): void
    {
        $item = ChecklistItem::query()->create([
            'name' => 'Orientation Safety Tour',
            'section' => 'PART E',
            'doc_type_id' => 1,
            'isExpiring' => false,
            'is_required' => true,
            'order' => 1,
        ]);

        $linked = UploadType::query()->create([
            'name' => 'Orientation Safety Tour',
            'requires_expiry' => false,
            'checklist_item_id' => $item->id,
            'checklist_section' => 'PART E',
        ]);

        $result = app(ChecklistUploadTypeSyncService::class)->syncChecklistItem($item->fresh());

        $this->assertNull($result);
        $this->assertNull($linked->fresh()->checklist_item_id);
        $this->assertNull($linked->fresh()->checklist_section);
        $this->assertSame(
            0,
            UploadType::query()->where('checklist_section', 'PART E')->count()
        );
    }
}

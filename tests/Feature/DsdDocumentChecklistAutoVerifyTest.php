<?php

namespace Tests\Feature;

use App\Models\BPEmpChecklist;
use App\Models\BPEmpJobData;
use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use App\Models\Facility;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DsdDocumentChecklistAutoVerifyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'facility-dsd', 'guard_name' => 'web']);
        Storage::fake('public');
    }

    public function test_dsd_upload_auto_verifies_linked_checklist_item(): void
    {
        [$dsd, $employee, $uploadType, $checklistItem] = $this->seedDsdEmployeeAndChecklistType('PART A');

        $this->actingAs($dsd)
            ->post(route('admin.employees.documents.upload', ['employee_num' => $employee->id]), [
                'upload_type_id' => $uploadType->id,
                'document' => UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'),
                'comments' => 'Uploaded by DSD',
            ])
            ->assertRedirect();

        $entry = BPEmpChecklist::query()
            ->where('employee_num', $employee->employee_num)
            ->first()
            ?->items['item_' . $checklistItem->id] ?? null;

        $this->assertIsArray($entry);
        $this->assertTrue($entry['on_file']);
        $this->assertNotNull($entry['verified_dt']);
        $this->assertSame($dsd->id, $entry['verified_by']);
    }

    public function test_dsd_approve_auto_verifies_linked_checklist_item(): void
    {
        [$dsd, $employee, $uploadType, $checklistItem, $facility] = $this->seedDsdEmployeeAndChecklistType('PART C');
        $employeeUser = User::factory()->create();
        $employee->update(['user_id' => $employeeUser->id]);

        $upload = Upload::query()->create([
            'facility_id' => $facility->id,
            'employee_num' => $employee->employee_num,
            'user_id' => $employeeUser->id,
            'upload_type_id' => $uploadType->id,
            'checklist_item_id' => $checklistItem->id,
            'file_path' => "employee_documents/{$employee->employee_num}/pending.pdf",
            'original_filename' => 'pending.pdf',
            'uploaded_at' => now(),
            'verification_status' => Upload::VERIFICATION_PENDING,
            'submitted_for_review_at' => now(),
        ]);

        $this->actingAs($dsd)
            ->post(route('admin.employees.documents.approve', [
                'employee' => $employee->id,
                'upload' => $upload->id,
            ]))
            ->assertRedirect();

        $entry = BPEmpChecklist::query()
            ->where('employee_num', $employee->employee_num)
            ->first()
            ?->items['item_' . $checklistItem->id] ?? null;

        $this->assertIsArray($entry);
        $this->assertTrue($entry['on_file']);
        $this->assertNotNull($entry['verified_dt']);
        $this->assertSame($dsd->id, $entry['verified_by']);
    }

    public function test_dsd_approve_resolves_checklist_item_from_upload_type_when_missing(): void
    {
        [$dsd, $employee, $uploadType, $checklistItem, $facility] = $this->seedDsdEmployeeAndChecklistType('PART D');
        $employeeUser = User::factory()->create();
        $employee->update(['user_id' => $employeeUser->id]);

        $upload = Upload::query()->create([
            'facility_id' => $facility->id,
            'employee_num' => $employee->employee_num,
            'user_id' => $employeeUser->id,
            'upload_type_id' => $uploadType->id,
            'checklist_item_id' => null,
            'file_path' => "employee_documents/{$employee->employee_num}/legacy-pending.pdf",
            'original_filename' => 'legacy-pending.pdf',
            'uploaded_at' => now(),
            'verification_status' => Upload::VERIFICATION_PENDING,
            'submitted_for_review_at' => now(),
        ]);

        $this->actingAs($dsd)
            ->post(route('admin.employees.documents.approve', [
                'employee' => $employee->id,
                'upload' => $upload->id,
            ]))
            ->assertRedirect();

        $this->assertSame($checklistItem->id, $upload->fresh()->checklist_item_id);

        $entry = BPEmpChecklist::query()
            ->where('employee_num', $employee->employee_num)
            ->first()
            ?->items['item_' . $checklistItem->id] ?? null;

        $this->assertIsArray($entry);
        $this->assertSame($dsd->id, $entry['verified_by']);
    }

    /**
     * @return array{0: User, 1: BPEmployee, 2: UploadType, 3: ChecklistItem, 4: Facility}
     */
    private function seedDsdEmployeeAndChecklistType(string $section): array
    {
        $facility = Facility::query()->create([
            'name' => 'Auto Verify Facility',
            'slug' => 'auto-verify-facility-' . strtolower(str_replace(' ', '-', $section)),
            'color_scheme_id' => null,
        ]);

        $dsd = User::factory()->create(['facility_id' => $facility->id]);
        $dsd->assignRole('facility-dsd');

        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-AV-' . substr(md5($section . microtime()), 0, 6),
            'first_name' => 'Auto',
            'last_name' => 'Verify',
        ]);

        BPEmpJobData::query()->create([
            'employee_num' => $employee->employee_num,
            'effdt' => '2024-01-01',
            'effseq' => 1,
            'facility_id' => $facility->id,
            'created_by' => $dsd->id,
            'updated_by' => $dsd->id,
        ]);

        $checklistItem = ChecklistItem::query()->create([
            'name' => "Checklist {$section}",
            'section' => $section,
            'doc_type_id' => 1,
            'order' => 1,
            'isExpiring' => false,
            'position_ids' => null,
        ]);

        $uploadType = UploadType::query()->create([
            'name' => $checklistItem->name,
            'requires_expiry' => false,
            'is_license_or_certification' => false,
            'checklist_item_id' => $checklistItem->id,
            'checklist_section' => $section,
        ]);

        return [$dsd, $employee->fresh(['currentAssignment']), $uploadType, $checklistItem, $facility];
    }
}

<?php

namespace Tests\Feature;

use App\Models\BPEmpJobData;
use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use App\Models\Department;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\User;
use App\Services\MemberDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class MemberRequiredDocumentsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_documents_page_lists_all_and_position_requirements_with_uploaded_first(): void
    {
        $user = User::factory()->create();
        $facility = Facility::query()->create([
            'name' => 'Required Docs Facility',
            'slug' => 'required-docs-facility',
            'color_scheme_id' => null,
        ]);
        $department = Department::query()->create([
            'name' => 'Nursing',
            'is_active' => true,
        ]);
        $position = Position::query()->create([
            'title' => 'Registered Nurse',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $allEmployeesType = UploadType::query()->create([
            'name' => 'W-4 Form',
            'applies_to_all_positions' => true,
            'requires_expiry' => false,
        ]);
        $positionType = UploadType::query()->create([
            'name' => 'Professional License',
            'applies_to_all_positions' => false,
            'requires_expiry' => true,
        ]);
        $missingType = UploadType::query()->create([
            'name' => 'Background Check',
            'applies_to_all_positions' => true,
            'requires_expiry' => false,
        ]);
        UploadType::query()->create([
            'name' => 'Cook Certificate',
            'applies_to_all_positions' => false,
            'requires_expiry' => false,
        ]);

        $positionType->positions()->sync([$position->id => ['is_required' => true]]);

        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-REQ-DOCS-1',
            'user_id' => $user->id,
            'first_name' => 'Required',
            'last_name' => 'Docs',
            'email' => $user->email,
        ]);

        BPEmpJobData::query()->create([
            'employee_num' => $employee->employee_num,
            'effdt' => '2024-01-01',
            'effseq' => 1,
            'position_id' => $position->id,
            'facility_id' => $facility->id,
            'dept_id' => $department->id,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        Upload::query()->create([
            'facility_id' => $facility->id,
            'employee_num' => $employee->employee_num,
            'user_id' => $user->id,
            'upload_type_id' => $allEmployeesType->id,
            'file_path' => "employee_documents/{$employee->employee_num}/w4.pdf",
            'original_filename' => 'w4.pdf',
            'uploaded_at' => now()->subDay(),
            'verification_status' => Upload::VERIFICATION_APPROVED,
        ]);

        Upload::query()->create([
            'facility_id' => $facility->id,
            'employee_num' => $employee->employee_num,
            'user_id' => $user->id,
            'upload_type_id' => $positionType->id,
            'file_path' => "employee_documents/{$employee->employee_num}/license.pdf",
            'original_filename' => 'license.pdf',
            'uploaded_at' => now()->subDays(2),
            'expires_at' => now()->addYear()->toDateString(),
            'verification_status' => Upload::VERIFICATION_APPROVED,
        ]);

        $page = app(MemberDashboardService::class)->buildDocumentsPage(
            $user->fresh(),
            Request::create('/dashboard/documents', 'GET')
        );

        $required = collect($page['documentsCenter']['required_documents'] ?? []);
        $titles = $required->pluck('title')->all();

        $this->assertContains('W-4 Form', $titles);
        $this->assertContains('Professional License', $titles);
        $this->assertContains('Background Check', $titles);
        $this->assertNotContains('Cook Certificate', $titles);

        $this->assertTrue((bool) $required->firstWhere('title', 'W-4 Form')['is_uploaded']);
        $this->assertTrue((bool) $required->firstWhere('title', 'Professional License')['is_uploaded']);
        $this->assertFalse((bool) $required->firstWhere('title', 'Background Check')['is_uploaded']);

        // Default sort is alphabetical by document title.
        $this->assertSame(
            ['Background Check', 'Professional License', 'W-4 Form'],
            $required->pluck('title')->all()
        );

        $searchPage = app(MemberDashboardService::class)->buildDocumentsPage(
            $user->fresh(),
            Request::create('/dashboard/documents', 'GET', [
                'rq' => 'license',
                'rper_page' => 10,
            ])
        );
        $this->assertSame(1, $searchPage['documentsCenter']['required_documents_total']);
        $this->assertSame(
            'Professional License',
            $searchPage['documentsCenter']['required_documents'][0]['title'] ?? null
        );

        $statusPage = app(MemberDashboardService::class)->buildDocumentsPage(
            $user->fresh(),
            Request::create('/dashboard/documents', 'GET', [
                'rstatus' => 'missing',
            ])
        );
        $this->assertSame(1, $statusPage['documentsCenter']['required_documents_total']);
        $this->assertSame(
            'Background Check',
            $statusPage['documentsCenter']['required_documents'][0]['title'] ?? null
        );

        $requiredFilterPage = app(MemberDashboardService::class)->buildDocumentsPage(
            $user->fresh(),
            Request::create('/dashboard/documents', 'GET', [
                'rrequired' => 'all_employees',
            ])
        );
        $requiredFilterTitles = collect($requiredFilterPage['documentsCenter']['required_documents'] ?? [])
            ->pluck('title')
            ->all();
        $this->assertContains('W-4 Form', $requiredFilterTitles);
        $this->assertContains('Background Check', $requiredFilterTitles);
        $this->assertNotContains('Professional License', $requiredFilterTitles);

        $this->actingAs($user)
            ->get(route('member.documents'))
            ->assertOk()
            ->assertSee('Required documents', false)
            ->assertSee('W-4 Form', false)
            ->assertSee('Professional License', false)
            ->assertSee('Background Check', false)
            ->assertSee('Sorted alphabetically', false)
            ->assertSee('Search', false)
            ->assertDontSee('Cook Certificate', false);
    }

    public function test_part_e_orientation_items_are_excluded_from_required_documents(): void
    {
        $user = User::factory()->create();
        $facility = Facility::query()->create([
            'name' => 'Orientation Facility',
            'slug' => 'orientation-facility',
            'color_scheme_id' => null,
        ]);
        $department = Department::query()->create([
            'name' => 'Nursing',
            'is_active' => true,
        ]);
        $position = Position::query()->create([
            'title' => 'CNA',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        UploadType::query()->create([
            'name' => 'W-4 Form',
            'applies_to_all_positions' => true,
        ]);

        ChecklistItem::query()->create([
            'name' => 'Orientation Safety Tour',
            'section' => 'PART E',
            'doc_type_id' => 1,
            'isExpiring' => false,
            'is_required' => true,
            'order' => 1,
            'position_ids' => null,
        ]);

        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-REQ-DOCS-3',
            'user_id' => $user->id,
            'first_name' => 'Orientation',
            'last_name' => 'Exclude',
            'email' => $user->email,
        ]);

        BPEmpJobData::query()->create([
            'employee_num' => $employee->employee_num,
            'effdt' => '2024-01-01',
            'effseq' => 1,
            'position_id' => $position->id,
            'facility_id' => $facility->id,
            'dept_id' => $department->id,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $page = app(MemberDashboardService::class)->buildDocumentsPage(
            $user->fresh(),
            Request::create('/dashboard/documents', 'GET')
        );

        $titles = collect($page['documentsCenter']['required_documents'] ?? [])->pluck('title')->all();

        $this->assertContains('W-4 Form', $titles);
        $this->assertNotContains('Orientation Safety Tour', $titles);

        $this->actingAs($user)
            ->get(route('member.documents'))
            ->assertOk()
            ->assertSee('W-4 Form', false)
            ->assertDontSee('Orientation Safety Tour', false);
    }

    public function test_all_employee_requirements_still_listed_without_position(): void
    {
        $user = User::factory()->create();
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-REQ-DOCS-2',
            'user_id' => $user->id,
            'first_name' => 'No',
            'last_name' => 'Position',
            'email' => $user->email,
        ]);

        UploadType::query()->create([
            'name' => 'I-9 Form',
            'applies_to_all_positions' => true,
        ]);
        UploadType::query()->create([
            'name' => 'Position Only Doc',
            'applies_to_all_positions' => false,
        ]);

        $page = app(MemberDashboardService::class)->buildDocumentsPage(
            $user->fresh(),
            Request::create('/dashboard/documents', 'GET')
        );

        $titles = collect($page['documentsCenter']['required_documents'] ?? [])->pluck('title')->all();

        $this->assertContains('I-9 Form', $titles);
        $this->assertNotContains('Position Only Doc', $titles);
        $this->assertSame($employee->employee_num, $user->resolvedBpEmployee()?->employee_num);
    }
}

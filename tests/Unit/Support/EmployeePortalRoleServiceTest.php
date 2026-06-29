<?php

namespace Tests\Unit\Support;

use App\Models\BPEmployee;
use App\Models\BPEmpJobData;
use App\Models\Department;
use App\Models\Facility;
use App\Models\Position;
use App\Models\PositionPortalRoleMapping;
use App\Models\User;
use App\Support\EmployeePortalRoleService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeePortalRoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_administrator_position_assigns_facility_admin_role(): void
    {
        [$employee, $user] = $this->employeeWithPosition('Administrator');

        app(EmployeePortalRoleService::class)->assignRegistrationRole($user, $employee);

        $this->assertTrue($user->fresh()->hasRole('facility-admin'));
        $this->assertFalse($user->fresh()->hasRole('regular-user'));
    }

    public function test_director_of_staff_development_assigns_facility_dsd_role(): void
    {
        [$employee, $user] = $this->employeeWithPosition('Director of Staff Development');

        app(EmployeePortalRoleService::class)->assignRegistrationRole($user, $employee);

        $this->assertTrue($user->fresh()->hasRole('facility-dsd'));
        $this->assertFalse($user->fresh()->hasRole('regular-user'));
    }

    public function test_unmapped_position_assigns_regular_user_role(): void
    {
        [$employee, $user] = $this->employeeWithPosition('Certified Nursing Assistant');

        app(EmployeePortalRoleService::class)->assignRegistrationRole($user, $employee);

        $this->assertTrue($user->fresh()->hasRole('regular-user'));
        $this->assertFalse($user->fresh()->hasRole('facility-admin'));
    }

    /**
     * @return array{0: BPEmployee, 1: User}
     */
    private function employeeWithPosition(string $positionTitle): array
    {
        $facility = Facility::query()->create([
            'name' => 'Test Facility',
            'slug' => 'test-facility',
            'facility_number' => '99999',
        ]);

        $department = Department::query()->create([
            'name' => 'Administration',
            'code' => 'ADM',
        ]);

        $position = Position::query()->create([
            'title' => $positionTitle,
            'department_id' => $department->id,
            'supervisor_role' => true,
        ]);

        $roleName = match ($positionTitle) {
            'Administrator' => 'facility-admin',
            'Director of Staff Development' => 'facility-dsd',
            default => null,
        };

        if ($roleName) {
            PositionPortalRoleMapping::query()->create([
                'position_id' => $position->id,
                'role_name' => $roleName,
                'is_active' => true,
            ]);
        }

        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-ROLE-001',
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'role-test@example.com',
            'gender' => 'N',
            'dob' => '1990-01-01',
            'original_hire_dt' => '2024-01-01',
            'ssn' => '123456789',
        ]);

        BPEmpJobData::query()->create([
            'employee_num' => $employee->employee_num,
            'effdt' => '2024-01-01',
            'effseq' => 1,
            'facility_id' => $facility->id,
            'dept_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $user = User::factory()->create([
            'email' => $employee->email,
            'facility_id' => $facility->id,
        ]);

        return [$employee->fresh(), $user];
    }
}

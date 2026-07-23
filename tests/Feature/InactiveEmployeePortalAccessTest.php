<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\BPEmployee;
use App\Models\User;
use App\Support\Rbac\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InactiveEmployeePortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_employee_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive.employee@example.com',
        ]);

        BPEmployee::query()->create([
            'employee_num' => 'EMP-INACTIVE-1',
            'user_id' => $user->id,
            'first_name' => 'Inactive',
            'last_name' => 'Employee',
            'email' => $user->email,
            'gender' => 'N',
            'is_active' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_active_employee_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'active.employee@example.com',
            'email_verified_at' => now(),
        ]);

        BPEmployee::query()->create([
            'employee_num' => 'EMP-ACTIVE-1',
            'user_id' => $user->id,
            'first_name' => 'Active',
            'last_name' => 'Employee',
            'email' => $user->email,
            'gender' => 'N',
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard.index', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_only_rdhr_dsd_or_super_admin_can_change_active_status(): void
    {
        Permission::findOrCreate(Permissions::EDIT_EMPLOYEE_CORE_TABS, 'web');

        foreach (['admin', 'facility-admin', 'facility-dsd', 'rdhr', 'super-admin'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-ACTIVE-STATUS-1',
            'first_name' => 'Status',
            'last_name' => 'Target',
            'email' => 'status.target@example.com',
            'gender' => 'N',
            'is_active' => true,
        ]);

        $payload = [
            'first_name' => 'Status',
            'last_name' => 'Target',
            'gender' => 'N',
            'email' => 'status.target@example.com',
            'is_active' => '0',
        ];

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo(Permissions::EDIT_EMPLOYEE_CORE_TABS);

        $this->actingAs($admin)
            ->put(route('admin.employees.personal.update', $employee->id), $payload)
            ->assertRedirect();

        $this->assertTrue($employee->fresh()->is_active);

        $facilityAdmin = User::factory()->create(['email_verified_at' => now()]);
        $facilityAdmin->assignRole('facility-admin');
        $facilityAdmin->givePermissionTo(Permissions::EDIT_EMPLOYEE_CORE_TABS);

        $this->actingAs($facilityAdmin)
            ->put(route('admin.employees.personal.update', $employee->id), $payload)
            ->assertRedirect();

        $this->assertTrue($employee->fresh()->is_active);

        $dsd = User::factory()->create(['email_verified_at' => now()]);
        $dsd->assignRole('facility-dsd');
        $dsd->givePermissionTo(Permissions::EDIT_EMPLOYEE_CORE_TABS);

        $this->actingAs($dsd)
            ->put(route('admin.employees.personal.update', $employee->id), $payload)
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);

        $employee->update(['is_active' => true]);

        $rdhr = User::factory()->create(['email_verified_at' => now()]);
        $rdhr->assignRole('rdhr');
        $rdhr->givePermissionTo(Permissions::EDIT_EMPLOYEE_CORE_TABS);

        $this->actingAs($rdhr)
            ->put(route('admin.employees.personal.update', $employee->id), $payload)
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->is_active);
    }

    public function test_middleware_logs_out_inactive_employee_on_authenticated_request(): void
    {
        $user = User::factory()->create([
            'email' => 'session.inactive@example.com',
            'email_verified_at' => now(),
        ]);

        BPEmployee::query()->create([
            'employee_num' => 'EMP-INACTIVE-2',
            'user_id' => $user->id,
            'first_name' => 'Session',
            'last_name' => 'Inactive',
            'email' => $user->email,
            'gender' => 'N',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}

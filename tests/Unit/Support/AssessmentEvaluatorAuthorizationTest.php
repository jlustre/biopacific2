<?php

namespace Tests\Unit\Support;

use App\Models\BPEmployee;
use App\Models\Position;
use App\Models\User;
use App\Support\AssessmentEvaluatorAuthorization;
use Mockery;
use Tests\TestCase;

class AssessmentEvaluatorAuthorizationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_evaluator_portal_roles_include_facility_admin_and_dsd(): void
    {
        $roles = AssessmentEvaluatorAuthorization::evaluatorPortalRoles();

        $this->assertContains('facility-admin', $roles);
        $this->assertContains('facility-dsd', $roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('rdhr', $roles);
    }

    public function test_regular_user_without_supervisor_position_cannot_evaluate(): void
    {
        $user = $this->makeUser(canEvaluateByRole: false, supervisorRole: false);

        $this->assertFalse(AssessmentEvaluatorAuthorization::canEvaluate($user));
        $this->assertTrue(AssessmentEvaluatorAuthorization::isEvaluatorActionBlocked($user, 'EMP-200'));
    }

    public function test_facility_dsd_can_evaluate(): void
    {
        $user = $this->makeUser(canEvaluateByRole: true, supervisorRole: false);

        $this->assertTrue(AssessmentEvaluatorAuthorization::canEvaluate($user));
    }

    public function test_supervisor_position_can_evaluate_without_evaluator_role(): void
    {
        $user = $this->makeUser(canEvaluateByRole: false, supervisorRole: true);

        $this->assertTrue(AssessmentEvaluatorAuthorization::canEvaluate($user));
    }

    protected function makeUser(bool $canEvaluateByRole, bool $supervisorRole): User
    {
        $position = new Position([
            'title' => 'Charge Nurse',
            'supervisor_role' => $supervisorRole,
        ]);

        $assignment = new BPEmpJobDataStub($position);
        $employee = new BPEmployee([
            'employee_num' => 'EMP-SUP-001',
        ]);
        $employee->setRelation('currentAssignment', $assignment);

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')
            ->with(AssessmentEvaluatorAuthorization::evaluatorPortalRoles())
            ->andReturn($canEvaluateByRole);
        $user->shouldReceive('resolvedBpEmployee')
            ->with(['currentAssignment.position'])
            ->andReturn($employee);

        return $user;
    }
}

class BPEmpJobDataStub
{
    public function __construct(public Position $position) {}
}

<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberChecklistDocumentController extends Controller
{
    public function competencySectionPdf(Request $request, EmployeeCompetencyAssessment $assessment)
    {
        $this->authorizeOwnedCompetencyAssessment($assessment);

        return app(EmployeePerformanceAssessmentController::class)
            ->downloadCompetencySectionPdf($request, $assessment);
    }

    public function competencyAssessmentPdf(EmployeeCompetencyAssessment $assessment)
    {
        $this->authorizeOwnedCompetencyAssessment($assessment);

        return app(EmployeePerformanceAssessmentController::class)
            ->downloadCompetencyAssessmentPdf($assessment);
    }

    public function performanceAssessmentPdf(EmployeePerformanceAssessment $assessment)
    {
        $this->authorizeOwnedPerformanceAssessment($assessment);

        return app(EmployeePerformanceAssessmentController::class)
            ->downloadPerformanceAssessmentPdf($assessment);
    }

    protected function authorizeOwnedCompetencyAssessment(EmployeeCompetencyAssessment $assessment): void
    {
        abort_unless($this->ownsEmployeeNum((string) $assessment->employee_num), 403);
    }

    protected function authorizeOwnedPerformanceAssessment(EmployeePerformanceAssessment $assessment): void
    {
        abort_unless($this->ownsEmployeeNum((string) $assessment->employee_num), 403);
    }

    protected function ownsEmployeeNum(string $employeeNum): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            return true;
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee()
            : null;

        return $employee && (string) $employee->employee_num === $employeeNum;
    }
}

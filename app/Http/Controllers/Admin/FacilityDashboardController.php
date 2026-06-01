<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BPEmpCredential;
use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\Facility;
use App\Models\JobOpening;
use App\Models\Upload;
use App\Support\AssessmentWorkflowStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FacilityDashboardController extends Controller
{
    public function show(Facility $facility)
    {
        $user = auth()->user();
        // Facility-scoped roles may only access their assigned facility; admin/super-admin/rdhr may access any
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor', 'don']) && !$user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            if ((int) $user->facility_id !== (int) $facility->id) {
                abort(403, 'Unauthorized: You do not have access to this facility.');
            }
        }
        $facilityKey = $facility->getRouteKey();

        $isDonDashboard = $user->hasRole('don')
            && ! $user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd']);

        $donDepartmentId = null;
        $donDepartmentName = null;
        if ($isDonDashboard) {
            $donEmployee = $user->resolvedBpEmployee(['currentAssignment.department']);
            $donDepartmentId = $donEmployee?->currentAssignment?->dept_id;
            $donDepartmentName = $donEmployee?->currentAssignment?->department?->name;
        }

        $scopedEmployeeQuery = BPEmployee::query()->whereHas('currentAssignment', function ($query) use ($facility, $isDonDashboard, $donDepartmentId) {
            $query->where('facility_id', $facility->id);

            if ($isDonDashboard && $donDepartmentId) {
                $query->where('dept_id', $donDepartmentId);
            }
        });

        $scopedEmployeeNums = $scopedEmployeeQuery->pluck('employee_num');

        $stats = [
            'job_openings' => JobOpening::query()
                ->where('facility_id', $facility->id)
                ->when($isDonDashboard && filled($donDepartmentName), function ($query) use ($donDepartmentName) {
                    $query->whereRaw("LOWER(COALESCE(department, '')) = ?", [mb_strtolower((string) $donDepartmentName)]);
                })
                ->count(),
            'expiring_credentials_30' => BPEmpCredential::query()
                ->whereIn('employee_num', $scopedEmployeeNums)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', Carbon::today())
                ->whereDate('expiry_date', '<=', Carbon::today()->addDays(30))
                ->count(),
            'department_tasks' => Upload::query()
                ->where('facility_id', $facility->id)
                ->whereIn('employee_num', $scopedEmployeeNums)
                ->where('verification_status', Upload::VERIFICATION_PENDING)
                ->count(),
        ];

        if ($isDonDashboard) {
            $periodsDueSoon = EmployeeAssessmentPeriod::query()
                ->whereIn('employee_num', $scopedEmployeeNums)
                ->whereDate('date_to', '>=', Carbon::today())
                ->whereDate('date_to', '<=', Carbon::today()->addDays(30))
                ->get(['id', 'employee_num']);

            $periodIdsDueSoon = $periodsDueSoon->pluck('id')->unique()->values();

            $completedCompetencyKeys = EmployeeCompetencyAssessment::query()
                ->whereIn('assessment_period_id', $periodIdsDueSoon)
                ->whereIn('employee_num', $scopedEmployeeNums)
                ->get(['employee_num', 'assessment_period_id', 'status'])
                ->filter(fn ($assessment) => AssessmentWorkflowStatus::isCompleted($assessment->status))
                ->map(fn ($assessment) => $assessment->employee_num . '|' . $assessment->assessment_period_id)
                ->unique();

            $completedPerformanceKeys = EmployeePerformanceAssessment::query()
                ->whereIn('assessment_period_id', $periodIdsDueSoon)
                ->whereIn('employee_num', $scopedEmployeeNums)
                ->get(['employee_num', 'assessment_period_id', 'status', 'finalized'])
                ->filter(fn ($assessment) => !empty($assessment->finalized) || AssessmentWorkflowStatus::isCompleted($assessment->status))
                ->map(fn ($assessment) => $assessment->employee_num . '|' . $assessment->assessment_period_id)
                ->unique();

            $dueCompetencyCount = $periodsDueSoon
                ->map(fn ($period) => $period->employee_num . '|' . $period->id)
                ->reject(fn ($key) => $completedCompetencyKeys->contains($key))
                ->count();

            $duePerformanceCount = $periodsDueSoon
                ->map(fn ($period) => $period->employee_num . '|' . $period->id)
                ->reject(fn ($key) => $completedPerformanceKeys->contains($key))
                ->count();

            $stats['competencies_due_30'] = $dueCompetencyCount;
            $stats['performance_due_30'] = $duePerformanceCount;
        } else {
            $stats['testimonials'] = $facility->testimonials()->count();
            $stats['gallery_images'] = $facility->galleryImages()->count();
            $stats['news_items'] = $facility->news()->count();
            $stats['services'] = $facility->services()->count();
        }

        return view('admin.facilities.dashboard', compact('facility', 'facilityKey', 'stats', 'isDonDashboard', 'donDepartmentName'));
    }
}

<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\JobApplication;
use App\Models\Position;
use App\Models\User;

class JobApplicationPolicy
{
    /**
     * @return array{facility_id:int|null,department_name:string|null,position_titles:array<int,string>}
     */
    protected function donScope(User $user): array
    {
        $employee = $user->resolvedBpEmployee(['currentAssignment']);

        $facilityId = $user->facility_id
            ? (int) $user->facility_id
            : ($employee?->currentAssignment?->facility_id ? (int) $employee->currentAssignment->facility_id : null);

        $departmentId = $employee?->currentAssignment?->dept_id
            ? (int) $employee->currentAssignment->dept_id
            : null;

        $departmentName = $departmentId
            ? Department::query()->whereKey($departmentId)->value('name')
            : null;

        $positionTitles = $departmentId
            ? Position::query()->where('department_id', $departmentId)->pluck('title')->filter()->values()->all()
            : [];

        return [
            'facility_id' => $facilityId,
            'department_name' => $departmentName,
            'position_titles' => $positionTitles,
        ];
    }

    protected function canDonView(User $user, JobApplication $jobApplication): bool
    {
        $scope = $this->donScope($user);
        $jobOpening = $jobApplication->jobOpening;

        if (! $jobOpening || ! $scope['facility_id']) {
            return false;
        }

        if ((int) $jobOpening->facility_id !== (int) $scope['facility_id']) {
            return false;
        }

        $departmentMatch = false;

        if ($scope['department_name'] && filled($jobOpening->department)) {
            $departmentMatch = mb_strtolower(trim((string) $jobOpening->department))
                === mb_strtolower(trim((string) $scope['department_name']));
        }

        if (! $departmentMatch && ! empty($scope['position_titles'])) {
            $departmentMatch = in_array(
                mb_strtolower((string) $jobOpening->title),
                array_map('mb_strtolower', $scope['position_titles']),
                true
            );
        }

        return $departmentMatch;
    }

    public function view(User $user, JobApplication $jobApplication): bool
    {
        if ($user->hasRole(['admin', User::superAdminRoleName()])) {
            return true;
        }

        if ($user->hasRole('don')) {
            return $this->canDonView($user, $jobApplication);
        }

        if ($user->hasRole(['rdhr', 'facility-admin', 'facility-dsd', 'facility-editor'])) {
            $facilityId = $jobApplication->jobOpening?->facility_id;

            if ($facilityId && $user->facility_id && (int) $user->facility_id === (int) $facilityId) {
                return true;
            }

            if (! $jobApplication->jobOpening && $jobApplication->user_id && (int) $user->id === (int) $jobApplication->user_id) {
                return true;
            }
        }

        return false;
    }

    public function update(User $user, JobApplication $jobApplication): bool
    {
        return $this->view($user, $jobApplication);
    }

    public function delete(User $user, JobApplication $jobApplication): bool
    {
        return $this->view($user, $jobApplication);
    }
}

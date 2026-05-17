<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function view(User $user, JobApplication $jobApplication): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole(['hrrd', 'facility-admin', 'facility-dsd', 'facility-editor'])) {
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

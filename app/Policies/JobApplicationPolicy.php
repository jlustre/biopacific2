<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class JobApplicationPolicy
{
    /**
     * Determine whether the user can view the job application.
     */
    public function view(User $user, JobApplication $jobApplication): bool
    {
        Log::info('POLICY DEBUG', [
            'myname' => 'JobApplicationPolicy@view',
            'user_id' => $user->id,
            'user_roles' => $user->getRoleNames(),
            'user_facility_id' => $user->facility_id,
            'job_application_id' => $jobApplication->id,
            'job_application_user_id' => $jobApplication->user_id,
            'job_opening_facility_id' => $jobApplication->jobOpening ? $jobApplication->jobOpening->facility_id : null,
        ]);

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole(['hrrd', 'facility-admin', 'facility-dsd'])) {
            if ($jobApplication->jobOpening && $jobApplication->jobOpening->facility_id && $user->facility_id === $jobApplication->jobOpening->facility_id) {
                return true;
            }
            if (!$jobApplication->jobOpening && $jobApplication->user_id && $user->id === $jobApplication->user_id) {
                return true;
            }
        }
        return false;
    }

}

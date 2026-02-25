<?php

namespace App\Policies;

use App\Models\PreEmploymentApplication;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PreEmploymentApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'hrrd', 'facility-admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return $user->hasRole(['admin', 'hrrd', 'facility-admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return $user->id === $preEmploymentApplication->user_id && $preEmploymentApplication->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return false;
    }

    /**
     * Determine whether the user can hire an applicant (change status to hired).
     */
    public function hireApplicant(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return $user->hasRole(['admin', 'hrrd', 'facility-admin']);
    }

    /**
     * Determine whether the user can reject an applicant.
     */
    public function rejectApplicant(User $user, PreEmploymentApplication $preEmploymentApplication): bool
    {
        return $user->hasRole(['admin', 'hrrd', 'facility-admin']);
    }
}

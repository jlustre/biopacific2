<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;
use App\Support\MemberPortalLayout;
use App\Support\SelectedFacility;

class FacilityGalleryPolicy
{
    public function viewAny(User $user): bool
    {
        return MemberPortalLayout::userIsSystemAdmin($user)
            || MemberPortalLayout::userCanAccessFacilityMemberNav($user);
    }

    public function view(User $user, Gallery $gallery): bool
    {
        if (MemberPortalLayout::userIsSystemAdmin($user)) {
            return true;
        }

        $facilityId = $this->userFacilityId($user);
        if ($facilityId === null) {
            return false;
        }

        // Unpublished galleries are only visible at the owning facility (to members who can list them).
        if (! $gallery->isPublished() && ! $gallery->isOwnedByFacility($facilityId)) {
            return false;
        }

        return $gallery->isSharedWithFacility($facilityId);
    }

    public function create(User $user): bool
    {
        return MemberPortalLayout::userIsSystemAdmin($user)
            || MemberPortalLayout::userCanAccessFacilityOpsNav($user);
    }

    public function update(User $user, Gallery $gallery): bool
    {
        if (MemberPortalLayout::userIsSystemAdmin($user)) {
            return true;
        }

        $facilityId = $this->userFacilityId($user);

        return $gallery->isOwnedBy($user)
            && $facilityId !== null
            && $gallery->isOwnedByFacility($facilityId);
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        return $this->update($user, $gallery);
    }

    public function manageImages(User $user, Gallery $gallery): bool
    {
        return $this->update($user, $gallery);
    }

    protected function userFacilityId(User $user): ?int
    {
        if ((int) ($user->facility_id ?? 0) > 0) {
            return (int) $user->facility_id;
        }

        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment'])
            : null;
        $assignmentFacilityId = (int) ($employee?->currentAssignment?->facility_id ?? 0);
        if ($assignmentFacilityId > 0) {
            return $assignmentFacilityId;
        }

        $selectedId = SelectedFacility::id();

        return $selectedId ? (int) $selectedId : null;
    }
}

<?php

namespace App\Policies;

use App\Models\PersonalTask;
use App\Models\User;

class PersonalTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PersonalTask $personalTask): bool
    {
        return $this->isParticipant($user, $personalTask);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PersonalTask $personalTask): bool
    {
        return (int) $personalTask->created_by === (int) $user->id
            && $personalTask->status === PersonalTask::STATUS_PENDING;
    }

    public function delete(User $user, PersonalTask $personalTask): bool
    {
        return (int) $personalTask->created_by === (int) $user->id
            && $personalTask->status === PersonalTask::STATUS_PENDING;
    }

    public function complete(User $user, PersonalTask $personalTask): bool
    {
        return (int) $personalTask->assigned_to === (int) $user->id
            && $personalTask->status === PersonalTask::STATUS_PENDING;
    }

    public function confirm(User $user, PersonalTask $personalTask): bool
    {
        return (int) $personalTask->created_by === (int) $user->id
            && $personalTask->status === PersonalTask::STATUS_COMPLETED
            && (int) $personalTask->created_by !== (int) $personalTask->assigned_to;
    }

    protected function isParticipant(User $user, PersonalTask $personalTask): bool
    {
        return (int) $personalTask->created_by === (int) $user->id
            || (int) $personalTask->assigned_to === (int) $user->id;
    }
}

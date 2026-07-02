<?php

namespace App\Policies;

use App\Models\Backup;
use App\Models\User;
use App\Support\Rbac\Permissions;

class BackupPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageBackups($user);
    }

    public function view(User $user, Backup $backup): bool
    {
        return $this->canManageBackups($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageBackups($user);
    }

    public function delete(User $user, Backup $backup): bool
    {
        return $this->canManageBackups($user);
    }

    public function download(User $user, Backup $backup): bool
    {
        return $this->canManageBackups($user) && $backup->canDownload();
    }

    public function restore(User $user, Backup $backup): bool
    {
        return $this->canManageBackups($user) && $backup->canRestore();
    }

    protected function canManageBackups(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return true;
        }

        return $user->can(Permissions::MANAGE_BACKUPS);
    }
}

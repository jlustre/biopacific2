<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\User;

class EmployeeJobDataAuthorization
{
    public const SELF_EDIT_DENIED_MESSAGE = 'You cannot create or update your own job data. Contact HR or a system administrator.';

    /**
     * @return array<int, string>
     */
    public static function rolesAllowedToEditOwnJobData(): array
    {
        return [
            User::superAdminRoleName(),
            'admin',
            'rdhr',
        ];
    }

    public static function canManageOwnJobData(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        return $actor->hasRole(self::rolesAllowedToEditOwnJobData());
    }

    public static function isOwnEmployeeRecord(?User $actor, BPEmployee $employee): bool
    {
        return PreventsSelfAssessment::isSelfAssessment($actor, $employee);
    }

    public static function canManageJobData(?User $actor, BPEmployee $employee): bool
    {
        if (! $actor) {
            return false;
        }

        if (self::isOwnEmployeeRecord($actor, $employee)) {
            return self::canManageOwnJobData($actor);
        }

        return true;
    }

    public static function assertCanManage(?User $actor, BPEmployee $employee, ?string $message = null): void
    {
        if (! self::canManageJobData($actor, $employee)) {
            abort(403, $message ?? self::SELF_EDIT_DENIED_MESSAGE);
        }
    }
}

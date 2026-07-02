<?php

namespace App\Support\MemberPortal;

final class ProfileHrStatus
{
    public const INCOMPLETE = 'incomplete';

    public const PENDING_HR = 'pending_hr';

    public const CONFIRMED = 'confirmed';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::INCOMPLETE => 'Incomplete',
            self::PENDING_HR => 'Pending HR confirmation',
            self::CONFIRMED => 'Confirmed by HR',
        ];
    }
}

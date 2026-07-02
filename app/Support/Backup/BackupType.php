<?php

namespace App\Support\Backup;

final class BackupType
{
    public const FULL = 'full';

    public const STRUCTURAL = 'structural';

    public const TRANSACTIONAL = 'transactional';

    public const FILES = 'files';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::FULL => 'Full Backup',
            self::STRUCTURAL => 'Structural Data',
            self::TRANSACTIONAL => 'Transactional Data',
            self::FILES => 'Files Only',
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::labels());
    }
}

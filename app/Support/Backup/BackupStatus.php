<?php

namespace App\Support\Backup;

final class BackupStatus
{
    public const PENDING = 'pending';

    public const PROCESSING = 'processing';

    public const COMPLETED = 'completed';

    public const FAILED = 'failed';

    public const RESTORED = 'restored';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::RESTORED => 'Restored',
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

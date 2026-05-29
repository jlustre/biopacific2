<?php

namespace App\Support;

class UploadSubmissionReason
{
    public const INITIAL = 'initial';

    public const RENEWAL = 'renewal';

    public const CORRECTION = 'correction';

    public const COMPLIANCE = 'compliance';

    public const EXPIRATION_UPDATE = 'expiration_update';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::INITIAL => 'Initial upload — new document',
            self::RENEWAL => 'Renewal or replacement',
            self::CORRECTION => 'Corrected or updated version',
            self::COMPLIANCE => 'Required compliance submission',
            self::EXPIRATION_UPDATE => 'Updating an expiring document',
        ];
    }

    public static function label(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return self::options()[$key] ?? $key;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::options());
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::options());
    }
}

<?php

namespace App\Support;

class ContentVisibility
{
    public const BOTH = 'both';

    public const WEBSITE = 'website';

    public const PORTAL = 'portal';

    public const CHANNEL_WEBSITE = 'website';

    public const CHANNEL_PORTAL = 'portal';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::BOTH => 'Website and portal',
            self::WEBSITE => 'Website only',
            self::PORTAL => 'Portal only',
        ];
    }

    public static function isValid(?string $value): bool
    {
        return in_array($value, [self::BOTH, self::WEBSITE, self::PORTAL], true);
    }

    public static function normalize(?string $value, string $default = self::BOTH): string
    {
        return self::isValid($value) ? $value : $default;
    }

    /**
     * Values that should appear on a given public surface.
     *
     * @return list<string>
     */
    public static function valuesForChannel(string $channel): array
    {
        return match ($channel) {
            self::CHANNEL_WEBSITE => [self::WEBSITE, self::BOTH],
            self::CHANNEL_PORTAL => [self::PORTAL, self::BOTH],
            default => [self::BOTH, self::WEBSITE, self::PORTAL],
        };
    }

    public static function label(?string $value): string
    {
        return self::options()[self::normalize($value)] ?? 'Website and portal';
    }
}

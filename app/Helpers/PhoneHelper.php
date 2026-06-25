<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Strip all non-digit characters from a phone value.
     */
    public static function digitsOnly(?string $phone): string
    {
        return preg_replace('/\D/', '', (string) $phone);
    }

    /**
     * Normalize a phone number for database storage as (999) 999-9999 when possible.
     * Handles slash-separated values such as 510/994-1337.
     */
    public static function normalizeForStorage(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = self::digitsOnly($phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }

        // Best effort when the number is not 10 digits: at least remove slashes.
        return str_replace('/', '', trim($phone));
    }

    /**
     * Format a phone number for display
     *
     * @param string|null $phone
     * @param string $fallback
     * @return string
     */
    public static function format($phone, $fallback = '(555) 123-4567')
    {
        if (empty($phone)) {
            return $fallback;
        }

        $normalized = self::normalizeForStorage($phone);

        if ($normalized !== null && preg_match('/^\(\d{3}\) \d{3}-\d{4}$/', $normalized)) {
            return $normalized;
        }

        return $normalized ?? $phone;
    }

    /**
     * Format phone number for tel: links (digits only with country code)
     *
     * @param string|null $phone
     * @param string $fallback
     * @return string
     */
    public static function forTel($phone, $fallback = '15551234567')
    {
        if (empty($phone)) {
            return $fallback;
        }

        $digits = self::digitsOnly($phone);

        // Add country code if not present
        if (strlen($digits) == 10) {
            return '1' . $digits;
        }

        return $digits;
    }
}
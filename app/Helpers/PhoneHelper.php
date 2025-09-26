<?php

namespace App\Helpers;

class PhoneHelper
{
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

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Format as (XXX) XXX-XXXX if we have exactly 10 digits
        if (strlen($digits) == 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }

        // Format as (XXX) XXX-XXXX XXXX for 11 digits (with country code)
        if (strlen($digits) == 11 && substr($digits, 0, 1) == '1') {
            return '(' . substr($digits, 1, 3) . ') ' . substr($digits, 4, 3) . '-' . substr($digits, 7);
        }

        // Return original if we can't format it properly
        return $phone;
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

        $digits = preg_replace('/\D/', '', $phone);

        // Add country code if not present
        if (strlen($digits) == 10) {
            return '1' . $digits;
        }

        return $digits;
    }
}
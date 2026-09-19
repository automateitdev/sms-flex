<?php

namespace App\Support;

/**
 * Pure segment-counting logic — no balance, no vendor calls. Used by the
 * controller (to report segments back to the caller) and by AutomasVendor
 * (to decide whether smsformat=8 / type=long need to be set).
 */
class SmsLengthCalculator
{
    public static function isUnicode(string $message): bool
    {
        return !(bool) preg_match('/^[\x20-\x7E\n\r]+$/', $message);
    }

    public static function segments(string $message): int
    {
        if (empty(trim($message))) {
            return 0;
        }

        $isUnicode = self::isUnicode($message);
        $hasExtendedGSM = (bool) preg_match('/[~^{}\[\]\\\\|€]/u', $message);

        $length = mb_strlen($message, 'UTF-8');
        $segments = 1;

        if (!$isUnicode) {
            $singleLimit = $hasExtendedGSM ? 140 : 160;
            $multiLimit  = $hasExtendedGSM ? 134 : 153;

            if ($length > $singleLimit) {
                $segments = (int) ceil($length / $multiLimit);
            }
        } else {
            $singleLimit = 70;
            $multiLimit  = 67;

            if ($length > $singleLimit) {
                $segments = (int) ceil($length / $multiLimit);
            }
        }

        return $segments;
    }
}

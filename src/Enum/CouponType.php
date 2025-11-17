<?php

namespace App\Enum;

abstract class CouponType
{
    public const FIXED = 'fixed';
    public const PERCENTAGE = 'percentage';

    public static function getValidTypes(): array
    {
        return [
            self::FIXED,
            self::PERCENTAGE,
        ];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::getValidTypes());
    }
}

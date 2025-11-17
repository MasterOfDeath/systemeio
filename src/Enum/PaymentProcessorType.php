<?php

namespace App\Enum;

abstract class PaymentProcessorType
{
    public const PAYPAL = 'paypal';
    public const STRIPE = 'stripe';

    public static function getValidTypes(): array
    {
        return [
            self::PAYPAL,
            self::STRIPE,
        ];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::getValidTypes());
    }
}

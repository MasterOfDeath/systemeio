<?php

namespace App\Service;

use App\ValueObject\Money;

interface PaymentProcessorInterface
{
    public function process(Money $amount): void;
}

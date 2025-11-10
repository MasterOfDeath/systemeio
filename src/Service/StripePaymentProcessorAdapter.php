<?php

namespace App\Service;

use App\Exception\PaymentException;
use App\ValueObject\Money;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    private const MIN_AMOUNT = 100;

    public function __construct(
        private StripePaymentProcessor $processor,
    ) {
    }

    public function process(Money $amount): void
    {
        if ($amount->toCurrency() < self::MIN_AMOUNT) {
            throw new PaymentException('Stripe payment failed for amount: '.$amount->toCurrency().' less than '.self::MIN_AMOUNT);
        }

        $result = $this->processor->processPayment($amount->toCurrency());
        if (!$result) {
            throw new PaymentException('Stripe payment failed for amount: '.$amount->toCurrency());
        }
    }
}

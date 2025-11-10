<?php

namespace App\Service;

use App\Exception\PaymentException;
use App\ValueObject\Money;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private PaypalPaymentProcessor $processor,
    ) {
    }

    public function process(Money $amount): void
    {
        try {
            $this->processor->pay($amount->toCents());
        } catch (\Exception $exception) {
            throw new PaymentException('Paypal payment failed for amount: '.$amount->toCurrency().' Error: '.$exception->getMessage());
        }
    }
}

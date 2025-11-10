<?php

namespace App\Service;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PaymentProcessorFactory
{
    public function create(string $processorName): PaymentProcessorInterface
    {
        return match (strtolower($processorName)) {
            'paypal' => new PaypalPaymentProcessorAdapter(new PaypalPaymentProcessor()),
            'stripe' => new StripePaymentProcessorAdapter(new StripePaymentProcessor()),
            default => throw new \InvalidArgumentException("Unknown payment processor: {$processorName}"),
        };
    }
}

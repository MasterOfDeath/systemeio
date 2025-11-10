<?php

namespace App\Tests\Unit\Service;

use App\Exception\PaymentException;
use App\Service\StripePaymentProcessorAdapter;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentProcessorAdapterTest extends TestCase
{
    private StripePaymentProcessorAdapter $adapter;
    private StripePaymentProcessor|MockObject $stripeProcessor;

    protected function setUp(): void
    {
        $this->stripeProcessor = $this->createMock(StripePaymentProcessor::class);
        $this->adapter = new StripePaymentProcessorAdapter($this->stripeProcessor);
    }

    public function testProcessSuccessfulPayment()
    {
        $amount = Money::fromCents(10000); // 100.00 EUR

        $this->stripeProcessor->expects($this->once())
            ->method('processPayment')
            ->with(100.0)
            ->willReturn(true);

        $this->adapter->process($amount);
    }

    public function testProcessFailedPayment()
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Stripe payment failed for amount: 100');

        $amount = Money::fromCents(10000); // 100.00 EUR

        $this->stripeProcessor->expects($this->once())
            ->method('processPayment')
            ->with(100.0)
            ->willReturn(false);

        $this->adapter->process($amount);
    }

    public function testProcessWithAmountLessThanMinimum()
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Stripe payment failed for amount: 99 less than 100');

        $amount = Money::fromCents(9900); // 99.00 EUR (less than minimum 100)

        // The processPayment method should not be called when amount is below minimum
        $this->stripeProcessor->expects($this->never())
            ->method('processPayment');

        $this->adapter->process($amount);
    }
}

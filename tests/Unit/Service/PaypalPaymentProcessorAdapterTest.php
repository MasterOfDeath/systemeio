<?php

namespace App\Tests\Unit\Service;

use App\Exception\PaymentException;
use App\Service\PaypalPaymentProcessorAdapter;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

class PaypalPaymentProcessorAdapterTest extends TestCase
{
    private PaypalPaymentProcessorAdapter $adapter;
    private PaypalPaymentProcessor|MockObject $paypalProcessor;

    protected function setUp(): void
    {
        $this->paypalProcessor = $this->createMock(PaypalPaymentProcessor::class);
        $this->adapter = new PaypalPaymentProcessorAdapter($this->paypalProcessor);
    }

    public function testProcessSuccessfulPayment()
    {
        $amount = Money::fromCents(10000); // 100.00 EUR

        $this->paypalProcessor->expects($this->once())
            ->method('pay')
            ->with(10000);

        $this->adapter->process($amount);
    }

    public function testProcessFailedPayment()
    {
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Paypal payment failed for amount: 100 Error: Test exception');

        $amount = Money::fromCents(10000); // 100.00 EUR

        $this->paypalProcessor->expects($this->once())
            ->method('pay')
            ->with(10000)
            ->willThrowException(new \Exception('Test exception'));

        $this->adapter->process($amount);
    }
}

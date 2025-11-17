<?php

namespace App\Tests\Unit\Service;

use App\Service\PaymentProcessorFactory;
use App\Service\PaypalPaymentProcessorAdapter;
use App\Service\StripePaymentProcessorAdapter;
use PHPUnit\Framework\TestCase;

class PaymentProcessorFactoryTest extends TestCase
{
    private PaymentProcessorFactory $factory;

    protected function setUp(): void
    {
        $paypalProcessor = $this->createMock(PaypalPaymentProcessorAdapter::class);
        $stripeProcessor = $this->createMock(StripePaymentProcessorAdapter::class);

        $processors = ['paypal' => $paypalProcessor, 'stripe' => $stripeProcessor];

        $this->factory = new PaymentProcessorFactory($processors);
    }

    public function testCreateStripeProcessor()
    {
        $processor = $this->factory->create('stripe');
        $this->assertInstanceOf(StripePaymentProcessorAdapter::class, $processor);
    }

    public function testCreatePaypalProcessor()
    {
        $processor = $this->factory->create('paypal');
        $this->assertInstanceOf(PaypalPaymentProcessorAdapter::class, $processor);
    }

    public function testCreateWithInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown payment processor: invalid');

        $this->factory->create('invalid');
    }

    public function testCreateIsCaseInsensitive()
    {
        $stripeProcessor = $this->factory->create('STRIPE');
        $paypalProcessor = $this->factory->create('PAYPAL');

        $this->assertInstanceOf(StripePaymentProcessorAdapter::class, $stripeProcessor);
        $this->assertInstanceOf(PaypalPaymentProcessorAdapter::class, $paypalProcessor);
    }
}

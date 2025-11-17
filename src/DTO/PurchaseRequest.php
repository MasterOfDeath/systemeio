<?php

namespace App\DTO;

use App\Enum\PaymentProcessorType;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
{
    #[Assert\NotBlank(message: 'Product ID is required')]
    #[Assert\Type(type: 'integer', message: 'Product must be an integer')]
    #[Assert\Positive(message: 'Product ID must be positive')]
    public ?int $product = null;

    #[AppAssert\TaxNumber]
    public ?string $taxNumber = null;

    #[AppAssert\ValidCouponType]
    public ?string $couponCode = null;

    #[Assert\NotBlank(message: 'Payment processor is required')]
    #[Assert\Type(type: 'string', message: 'Payment processor must be a string')]
    #[Assert\Choice(choices: [PaymentProcessorType::PAYPAL, PaymentProcessorType::STRIPE], message: 'Payment processor must be either paypal or stripe')]
    public ?string $paymentProcessor = null;
}

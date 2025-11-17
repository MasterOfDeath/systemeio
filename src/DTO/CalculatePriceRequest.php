<?php

namespace App\DTO;

use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class CalculatePriceRequest
{
    #[Assert\NotBlank(message: 'Product ID is required')]
    #[Assert\Type(type: 'integer', message: 'Product must be an integer')]
    #[Assert\Positive(message: 'Product ID must be positive')]
    public ?int $product = null;

    #[AppAssert\TaxNumber]
    public ?string $taxNumber = null;

    #[AppAssert\ValidCouponType]
    public ?string $couponCode = null;
}

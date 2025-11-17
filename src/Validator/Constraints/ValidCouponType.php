<?php

namespace App\Validator\Constraints;

use App\Validator\ValidCouponTypeValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidCouponType extends Constraint
{
    public string $message = 'The coupon type "{{ type }}" is not valid.';

    public function validatedBy(): string
    {
        return ValidCouponTypeValidator::class;
    }
}

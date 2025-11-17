<?php

namespace App\Validator\Constraints;

use App\Validator\TaxNumberValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class TaxNumber extends Constraint
{
    public string $message = 'Invalid tax number format';

    public function validatedBy(): string
    {
        return TaxNumberValidator::class;
    }
}

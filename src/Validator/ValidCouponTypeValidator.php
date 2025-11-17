<?php

namespace App\Validator;

use App\Repository\CouponRepository;
use App\Validator\Constraints\ValidCouponType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidCouponTypeValidator extends ConstraintValidator
{
    public function __construct(
        private CouponRepository $couponRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidCouponType) {
            throw new UnexpectedTypeException($constraint, ValidCouponType::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $coupon = $this->couponRepository->findOneByCode($value);
        if (!$coupon) {
            return; // Let the not found validation handle this case
        }

        if (!\App\Enum\CouponType::isValid($coupon->getType())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ type }}', $coupon->getType())
                ->addViolation();
        }
    }
}

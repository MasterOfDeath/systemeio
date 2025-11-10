<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\ValueObject\Money;

class PriceCalculator
{
    private TaxRateProvider $taxRateProvider;

    public function __construct(TaxRateProvider $taxRateProvider)
    {
        $this->taxRateProvider = $taxRateProvider;
    }

    public function calculate(Product $product, string $taxNumber, ?Coupon $coupon = null): Money
    {
        $priceInCents = $product->getPrice();
        $priceInCents = $this->applyCoupon($priceInCents, $coupon);

        $taxRate = $this->taxRateProvider->getForTaxNumber($taxNumber);
        $taxAmount = (int) round($priceInCents * ($taxRate->getRate() / 100));
        $priceWithTax = $priceInCents + $taxAmount;

        return Money::fromCents($priceWithTax);
    }

    private function applyCoupon(int $priceInCents, ?Coupon $coupon): int
    {
        if (null === $coupon) {
            return $priceInCents;
        }

        if ($coupon->isFixed()) {
            $discount = $coupon->getValue();
            $result = $priceInCents - $discount;

            return max(0, $result);
        }

        if ($coupon->isPercentage()) {
            $discount = (int) round($priceInCents * $coupon->getValue() / 100);

            return max(0, $priceInCents - $discount);
        }

        return $priceInCents;
    }
}

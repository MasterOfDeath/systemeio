<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\ValueObject\Money;

class PriceCalculator
{
    private const TAX_RATES = [
        'DE' => 19,  // Germany - 19%
        'IT' => 22,  // Italy - 22%
        'FR' => 20,  // France - 20%
        'GR' => 24,  // Greece - 24%
    ];

    public function calculate(Product $product, string $taxNumber, ?Coupon $coupon = null): Money
    {
        $priceInCents = $product->getPrice();
        $priceInCents = $this->applyCoupon($priceInCents, $coupon);

        $countryCode = $this->extractCountryCode($taxNumber);
        $taxRate = self::TAX_RATES[$countryCode] ?? 0;

        $priceWithTax = (int) round($priceInCents * (1 + $taxRate / 100));

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

    public function extractCountryCode(string $taxNumber): string
    {
        return substr($taxNumber, 0, 2);
    }
}

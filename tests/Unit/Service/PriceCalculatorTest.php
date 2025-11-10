<?php

namespace App\Tests\Unit\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\PriceCalculator;
use App\Service\TaxRateProvider;
use App\ValueObject\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $priceCalculator;
    private TaxRateProvider $taxRateProvider;

    protected function setUp(): void
    {
        $this->taxRateProvider = new TaxRateProvider([
            'DE' => 19,
            'IT' => 22,
            'FR' => 20,
            'GR' => 24,
        ]);

        $this->priceCalculator = new PriceCalculator($this->taxRateProvider);
    }

    public function testCalculateWithoutCoupon()
    {
        $product = $this->createProduct(10000); // 100.00 EUR
        $taxNumber = 'DE123456789';

        $result = $this->priceCalculator->calculate($product, $taxNumber);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertEquals(11900, $result->toCents()); // 100 * 1.19 = 119.00 EUR
    }

    public function testCalculateWithFixedCoupon()
    {
        $product = $this->createProduct(10000); // 100.00 EUR
        $coupon = $this->createCoupon(1000, true); // 10.00 EUR fixed discount
        $taxNumber = 'IT123456789';

        $result = $this->priceCalculator->calculate($product, $taxNumber, $coupon);

        $this->assertEquals(10980, $result->toCents()); // (100 - 10) * 1.22 = 109.80 EUR
    }

    public function testCalculateWithPercentageCoupon()
    {
        $product = $this->createProduct(10000); // 100.00 EUR
        $coupon = $this->createCoupon(10, false); // 10% discount
        $taxNumber = 'FR123456789';

        $result = $this->priceCalculator->calculate($product, $taxNumber, $coupon);

        $this->assertEquals(10800, $result->toCents()); // (100 - 10%) * 1.20 = 108.00 EUR
    }

    #[DataProvider('taxRateProvider')]
    public function testCalculateWithDifferentTaxRates($countryCode, $expectedPriceInCents)
    {
        $product = $this->createProduct(10000); // 100.00 EUR

        $result = $this->priceCalculator->calculate($product, $countryCode.'123456789');

        $this->assertEquals($expectedPriceInCents, $result->toCents(), "Failed for country code: $countryCode");
    }

    public static function taxRateProvider(): array
    {
        return [
            'Germany (19%)' => ['DE', 11900],
            'Italy (22%)' => ['IT', 12200],
            'France (20%)' => ['FR', 12000],
            'Greece (24%)' => ['GR', 12400],
        ];
    }

    public function testApplyCouponWithNegativeResult()
    {
        $product = $this->createProduct(1000); // 10.00 EUR
        $coupon = $this->createCoupon(2000, true); // 20.00 EUR fixed discount (more than product price)
        $taxNumber = 'DE123456789';

        $result = $this->priceCalculator->calculate($product, $taxNumber, $coupon);

        $this->assertEquals(0, $result->toCents()); // Should not be negative
    }

    private function createProduct(int $priceInCents): Product
    {
        $product = new Product();
        $product->setPrice($priceInCents);

        return $product;
    }

    private function createCoupon(int $value, bool $isFixed): Coupon
    {
        $coupon = new Coupon();
        $coupon->setCode('TEST'.uniqid());
        $coupon->setType($isFixed ? Coupon::TYPE_FIXED : Coupon::TYPE_PERCENTAGE);
        $coupon->setValue($value);

        return $coupon;
    }
}

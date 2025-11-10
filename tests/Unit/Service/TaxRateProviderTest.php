<?php

namespace App\Tests\Unit\Service;

use App\Service\TaxRateProvider;
use App\ValueObject\TaxRate;
use PHPUnit\Framework\TestCase;

class TaxRateProviderTest extends TestCase
{
    private TaxRateProvider $taxRateProvider;

    protected function setUp(): void
    {
        $taxRates = [
            'DE' => 19,
            'IT' => 22,
            'FR' => 20,
            'GR' => 24,
        ];

        $this->taxRateProvider = new TaxRateProvider($taxRates);
    }

    public function testGetForTaxNumberWithValidTaxNumber()
    {
        $taxRate = $this->taxRateProvider->getForTaxNumber('DE123456789');

        $this->assertInstanceOf(TaxRate::class, $taxRate);
        $this->assertEquals('DE', $taxRate->getCountryCode());
        $this->assertEquals(19, $taxRate->getRate());
    }

    public function testGetForTaxNumberWithDifferentCountry()
    {
        $taxRate = $this->taxRateProvider->getForTaxNumber('IT987654321');

        $this->assertInstanceOf(TaxRate::class, $taxRate);
        $this->assertEquals('IT', $taxRate->getCountryCode());
        $this->assertEquals(22, $taxRate->getRate());
    }

    public function testGetForTaxNumberWithLowerCaseCountryCode()
    {
        $taxRate = $this->taxRateProvider->getForTaxNumber('fr123456789');

        $this->assertInstanceOf(TaxRate::class, $taxRate);
        $this->assertEquals('fr', $taxRate->getCountryCode());
        $this->assertEquals(20, $taxRate->getRate());
    }

    public function testGetForTaxNumberWithEmptyTaxNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No tax rate found for country code: ');

        $this->taxRateProvider->getForTaxNumber('');
    }

    public function testGetForTaxNumberWithUnknownCountryCode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No tax rate found for country code: US');

        $this->taxRateProvider->getForTaxNumber('US123456789');
    }

    public function testGetForTaxNumberWithShortTaxNumber()
    {
        $taxRate = $this->taxRateProvider->getForTaxNumber('GR1');

        $this->assertInstanceOf(TaxRate::class, $taxRate);
        $this->assertEquals('GR', $taxRate->getCountryCode());
        $this->assertEquals(24, $taxRate->getRate());
    }

    public function testGetForTaxNumberWithSpecialCharacters()
    {
        $taxRate = $this->taxRateProvider->getForTaxNumber('IT-AB-123-456');

        $this->assertInstanceOf(TaxRate::class, $taxRate);
        $this->assertEquals('IT', $taxRate->getCountryCode());
        $this->assertEquals(22, $taxRate->getRate());
    }

    public function testConstructorWithEmptyRates()
    {
        $provider = new TaxRateProvider([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No tax rate found for country code: DE');

        $provider->getForTaxNumber('DE123456789');
    }

    public function testConstructorWithNonNumericRate()
    {
        $provider = new TaxRateProvider(['XX' => 'not_a_number']);

        // The rate should be cast to int (0 in this case)
        $taxRate = $provider->getForTaxNumber('XX123');

        $this->assertInstanceOf(TaxRate::class, $taxRate);
        $this->assertEquals('XX', $taxRate->getCountryCode());
        $this->assertEquals(0, $taxRate->getRate());
    }

    public function testGetForTaxNumberWithDifferentCaseInCountryCode()
    {
        $taxRate1 = $this->taxRateProvider->getForTaxNumber('De123456789');
        $taxRate2 = $this->taxRateProvider->getForTaxNumber('dE987654321');

        $this->assertEquals('De', $taxRate1->getCountryCode());
        $this->assertEquals('dE', $taxRate2->getCountryCode());
        $this->assertEquals($taxRate1->getRate(), $taxRate2->getRate());
    }
}

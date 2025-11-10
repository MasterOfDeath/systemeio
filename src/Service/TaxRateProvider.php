<?php

namespace App\Service;

use App\ValueObject\TaxRate;

class TaxRateProvider
{
    /** @var array<string, int> */
    private array $taxRates = [];

    public function __construct(array $taxRates = [])
    {
        $this->taxRates = [];
        foreach ($taxRates as $countryCode => $rate) {
            $this->taxRates[strtoupper($countryCode)] = (int) $rate;
        }
    }

    public function getForTaxNumber(string $taxNumber): TaxRate
    {
        $countryCode = $this->extractCountryCode($taxNumber);
        $rate = $this->getForCountry($countryCode);

        return new TaxRate($countryCode, $rate);
    }

    private function getForCountry(string $countryCode): int
    {
        $countryCode = strtoupper($countryCode);
        if (!isset($this->taxRates[$countryCode])) {
            throw new \InvalidArgumentException(sprintf('No tax rate found for country code: %s', $countryCode));
        }

        return $this->taxRates[$countryCode];
    }

    private function extractCountryCode(string $taxNumber): string
    {
        return substr($taxNumber, 0, 2);
    }
}

<?php

namespace App\ValueObject;

class TaxRate
{
    private string $countryCode;
    private int $rate;

    public function __construct(string $countryCode, int $rate)
    {
        if ($rate < 0 || $rate > 100) {
            throw new \InvalidArgumentException('Tax rate must be between 0 and 100');
        }

        $this->countryCode = $countryCode;
        $this->rate = $rate;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getRate(): int
    {
        return $this->rate;
    }
}

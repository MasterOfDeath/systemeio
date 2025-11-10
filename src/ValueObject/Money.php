<?php

namespace App\ValueObject;

class Money
{
    private int $cents;
    private const CENTS_IN_CURRENCY = 100;

    private function __construct(int $cents)
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative');
        }
        $this->cents = $cents;
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromCurrency(float $amount): self
    {
        $cents = (int) round($amount * self::CENTS_IN_CURRENCY);

        return new self($cents);
    }

    public function toCents(): int
    {
        return $this->cents;
    }

    public function toCurrency(): float
    {
        return $this->cents / self::CENTS_IN_CURRENCY;
    }

    public function __toString(): string
    {
        return sprintf('%.2f', $this->toCurrency());
    }
}

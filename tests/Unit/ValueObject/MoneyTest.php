<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testFromCents()
    {
        $money = Money::fromCents(12345);

        $this->assertEquals(12345, $money->toCents());
        $this->assertEquals(123.45, $money->toCurrency());
        $this->assertEquals('123.45', (string) $money);
    }

    #[DataProvider('currencyProvider')]
    public function testToCurrency(int $cents, float $expected)
    {
        $money = Money::fromCents($cents);
        $this->assertEquals($expected, $money->toCurrency());
    }

    #[DataProvider('stringProvider')]
    public function testToString(int $cents, string $expected)
    {
        $money = Money::fromCents($cents);
        $this->assertEquals($expected, (string) $money);
    }

    public static function currencyProvider(): array
    {
        return [
            '1.00 EUR' => [100, 1.00],
            '10.00 EUR' => [1000, 10.00],
            '10.50 EUR' => [1050, 10.50],
            '0.05 EUR' => [5, 0.05],
            '0.00 EUR' => [0, 0.00],
        ];
    }

    public static function stringProvider(): array
    {
        return [
            '1.00' => [100, '1.00'],
            '10.00' => [1000, '10.00'],
            '10.50' => [1050, '10.50'],
            '0.05' => [5, '0.05'],
            '0.00' => [0, '0.00'],
            '12345.67' => [1234567, '12345.67'],
        ];
    }

    #[DataProvider('fromCurrencyProvider')]
    public function testFromCurrency(float $amount, int $expectedCents, string $description)
    {
        $money = Money::fromCurrency($amount);
        $this->assertEquals($expectedCents, $money->toCents(), $description);
    }

    public static function fromCurrencyProvider(): array
    {
        return [
            '1.00 EUR' => [1.00, 100, '1.00 EUR should be 100 cents'],
            '10.50 EUR' => [10.50, 1050, '10.50 EUR should be 1050 cents'],
            '0.99 EUR' => [0.99, 99, '0.99 EUR should be 99 cents'],
            '0.00 EUR' => [0.00, 0, '0.00 EUR should be 0 cents'],
            '123.45 EUR' => [123.45, 12345, '123.45 EUR should be 12345 cents'],
            'Rounded up 0.999 EUR' => [0.999, 100, '0.999 EUR should be rounded to 100 cents'],
            'Rounded down 0.994 EUR' => [0.994, 99, '0.994 EUR should be rounded to 99 cents'],
        ];
    }

    public function testFromCurrencyWithNegativeAmount()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Money amount cannot be negative');

        Money::fromCurrency(-1.00);
    }
}

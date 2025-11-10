<?php

namespace App\Tests\Unit\Validator\Constraints;

use App\Validator\Constraints\TaxNumber;
use App\Validator\Constraints\TaxNumberValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TaxNumberValidatorTest extends TestCase
{
    private TaxNumberValidator $validator;
    private ExecutionContextInterface|MockObject $context;
    private ConstraintViolationBuilderInterface|MockObject $violationBuilder;

    protected function setUp(): void
    {
        $this->validator = new TaxNumberValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->validator->initialize($this->context);
    }

    #[DataProvider('validTaxNumbersProvider')]
    public function testValidTaxNumbers($taxNumber): void
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($taxNumber, new TaxNumber());
    }

    #[DataProvider('invalidTaxNumbersProvider')]
    public function testInvalidTaxNumbers($taxNumber): void
    {
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($this->violationBuilder);

        $this->violationBuilder->expects($this->once())
            ->method('setParameter')
            ->willReturn($this->violationBuilder);

        $this->violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->validator->validate($taxNumber, new TaxNumber());
    }

    public function testNonStringValue(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedValueException::class);
        $this->validator->validate(12345, new TaxNumber());
    }

    public static function validTaxNumbersProvider(): array
    {
        return [
            'German tax number' => ['DE123456789'],
            'Italian tax number' => ['IT12345678901'],
            'Greek tax number' => ['GR123456789'],
            'French tax number' => ['FRAB123456789'],
        ];
    }

    public static function invalidTaxNumbersProvider(): array
    {
        return [
            'Too short German tax number' => ['DE12345678'],
            'Too long German tax number' => ['DE1234567890'],
            'Invalid French tax number format' => ['FR1234567890'],
            'Invalid Italian tax number format' => ['IT1234567890'],
            'Invalid Greek tax number format' => ['GR12345678'],
            'Invalid country code' => ['XX1234567890'],
            'Lowercase letters' => ['de123456789'],
        ];
    }
}

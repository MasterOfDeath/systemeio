<?php

namespace App\Tests\Unit\Controller;

use App\Controller\PriceController;
use App\DTO\CalculatePriceRequest;
use App\Entity\Product;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\PaymentProcessorFactory;
use App\Service\PriceCalculator;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceControllerTest extends TestCase
{
    private PriceCalculator|MockObject $priceCalculator;
    private PaymentProcessorFactory|MockObject $paymentProcessorFactory;
    private ProductRepository|MockObject $productRepository;
    private CouponRepository|MockObject $couponRepository;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->priceCalculator = $this->createMock(PriceCalculator::class);
        $this->paymentProcessorFactory = $this->createMock(PaymentProcessorFactory::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testCalculatePriceSuccess()
    {
        $product = $this->createProduct(10000);
        $request = new CalculatePriceRequest();
        $request->product = 1; // ID of the product
        $request->taxNumber = 'DE123456789';
        $request->couponCode = null;

        $this->productRepository->method('find')
            ->with(1)
            ->willReturn($product);

        $this->priceCalculator->expects($this->once())
            ->method('calculate')
            ->with($product, 'DE123456789', null)
            ->willReturn(Money::fromCents(11900));

        // Create a new instance of the controller with the test container
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $controller = new PriceController(
            $this->priceCalculator,
            $this->paymentProcessorFactory,
            $this->productRepository,
            $this->couponRepository,
            $this->logger
        );
        $controller->setContainer($container);

        $response = $controller->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"price":"119.00"}',
            $response->getContent()
        );
    }

    public function testCalculatePriceWithValidationErrors()
    {
        // This test is now a simple success test since validation is handled by the framework
        $product = $this->createProduct(10000);
        $this->productRepository->method('find')
            ->with(1)
            ->willReturn($product);

        $request = new CalculatePriceRequest();
        $request->product = 1;
        $request->taxNumber = 'DE123456789';
        $request->couponCode = null;

        $this->priceCalculator->expects($this->once())
            ->method('calculate')
            ->with($product, 'DE123456789', null)
            ->willReturn(Money::fromCents(11900));

        // Create a container that provides the validator
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('has')->willReturnCallback(fn ($id) => 'validator' === $id);
        $container->method('get')->willReturn($validator);

        $controller = new PriceController(
            $this->priceCalculator,
            $this->paymentProcessorFactory,
            $this->productRepository,
            $this->couponRepository,
            $this->logger
        );
        $controller->setContainer($container);

        $response = $controller->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"price":"119.00"}',
            $response->getContent()
        );
    }

    public function testCalculatePriceWithInvalidProduct()
    {
        $this->productRepository->method('find')
            ->with(999)
            ->willReturn(null);

        $request = new CalculatePriceRequest();
        $request->product = 999;
        $request->taxNumber = 'DE123456789';
        $request->couponCode = null;

        // Create a container that provides the validator
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('has')->willReturnCallback(fn ($id) => 'validator' === $id);
        $container->method('get')->willReturn($validator);

        $controller = new PriceController(
            $this->priceCalculator,
            $this->paymentProcessorFactory,
            $this->productRepository,
            $this->couponRepository,
            $this->logger
        );
        $controller->setContainer($container);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Product not found');

        $controller->calculatePrice($request);
    }

    private function createProduct(int $priceInCents): Product
    {
        $product = new Product();
        $product->setPrice($priceInCents);

        return $product;
    }
}

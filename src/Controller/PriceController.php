<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Exception\PaymentException;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\PaymentProcessorFactory;
use App\Service\PriceCalculator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PriceController extends AbstractController
{
    public function __construct(
        private PriceCalculator $priceCalculator,
        private PaymentProcessorFactory $paymentProcessorFactory,
        private ProductRepository $productRepository,
        private CouponRepository $couponRepository,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(#[MapRequestPayload] CalculatePriceRequest $calculatePriceRequest): JsonResponse
    {
        [$product, $coupon] = $this->getProductAndCoupon(
            $calculatePriceRequest->product,
            $calculatePriceRequest->couponCode
        );

        $price = $this->priceCalculator->calculate($product, $calculatePriceRequest->taxNumber, $coupon);

        return $this->json(['price' => (string) $price], 200);
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(#[MapRequestPayload] PurchaseRequest $purchaseRequest): JsonResponse
    {
        [$product, $coupon] = $this->getProductAndCoupon(
            $purchaseRequest->product,
            $purchaseRequest->couponCode
        );

        $price = $this->priceCalculator->calculate($product, $purchaseRequest->taxNumber, $coupon);

        try {
            $processor = $this->paymentProcessorFactory->create($purchaseRequest->paymentProcessor);
            $processor->process($price);
        } catch (PaymentException $exception) {
            $this->logger->error('Payment processing failed: '.$exception->getMessage());
            throw new UnprocessableEntityHttpException('Payment processing failed: Internal error');
        } catch (\Exception $exception) {
            $this->logger->error('Payment processing failed: '.$exception->getMessage());
            throw new UnprocessableEntityHttpException('Payment processing failed: '.$exception->getMessage());
        }

        return $this->json(['success' => true, 'price' => (string) $price], 200);
    }

    private function getProductAndCoupon(int $productId, ?string $couponCode): array
    {
        $product = $this->productRepository->find($productId);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $coupon = null;
        if ($couponCode) {
            $coupon = $this->couponRepository->findOneByCode($couponCode);
            if (!$coupon) {
                throw $this->createNotFoundException('Coupon not found');
            }
        }

        return [$product, $coupon];
    }
}

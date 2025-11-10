<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 10],
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $errors = [];

        if ($exception instanceof ValidationFailedException) {
            $statusCode = 400;
            $violations = $exception->getViolations();

            foreach ($violations as $violation) {
                $errors[] = [
                    'type' => 'validation_error',
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $errors[] = [
                'type' => 'http_error',
                'message' => $exception->getMessage() ?: $this->getDefaultHttpMessage($statusCode),
            ];
        } else {
            $errors[] = [
                'type' => 'server_error',
                'message' => 'Internal server error',
            ];
        }

        $this->logger->error($exception->getMessage());

        $response = new JsonResponse(['success' => false, 'errors' => $errors], $statusCode);
        $event->setResponse($response);
    }

    private function getDefaultHttpMessage(int $statusCode): string
    {
        return match ($statusCode) {
            404 => 'Resource not found',
            403 => 'Access denied',
            401 => 'Not authorized',
            default => 'Bad request',
        };
    }
}

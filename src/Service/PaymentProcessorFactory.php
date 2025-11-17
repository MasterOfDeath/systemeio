<?php

namespace App\Service;

class PaymentProcessorFactory
{
    /**
     * @param iterable<string, PaymentProcessorInterface> $processors
     */
    public function __construct(
        private iterable $processors,
    ) {
    }

    public function create(string $name): PaymentProcessorInterface
    {
        $name = strtolower($name);

        foreach ($this->processors as $key => $processor) {
            if ($key === $name) {
                return $processor;
            }
        }

        throw new \InvalidArgumentException("Unknown payment processor: $name");
    }
}

<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Synchronizer;

final readonly class DigitalFileSynchronizerRegistry implements DigitalFileSynchronizerRegistryInterface
{
    public function __construct(private iterable $synchronizers)
    {
    }

    public function getForType(string $type): DigitalFileSynchronizerInterface
    {
        foreach ($this->synchronizers as $sync) {
            if ($sync->supports($type)) {
                return $sync;
            }
        }

        throw new \InvalidArgumentException(sprintf('No synchronizer found for type "%s".', $type));
    }
}

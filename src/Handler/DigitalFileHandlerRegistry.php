<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Handler;

final readonly class DigitalFileHandlerRegistry implements DigitalFileHandlerRegistryInterface
{
    /**
     * @param iterable<DigitalFileHandlerInterface> $handlers
     */
    public function __construct(private iterable $handlers)
    {
    }

    public function getHandlerForType(string $type): DigitalFileHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($type)) {
                return $handler;
            }
        }

        throw new \InvalidArgumentException(sprintf('No digital file handler found for type "%s".', $type));
    }
}
